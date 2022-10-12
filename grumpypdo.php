<?php
class GrumpyPdo extends \PDO
{
    /**
     * @var array - Default attributes set for database connection.
     */
    protected $default_attributes = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    );

    private $verified_databases = [];

    public function __construct($hostname, $username, $password, $database, $attributes = array(), $dsn_prefix = "mysql", $dsn_param_string = "")
    {
        parent::__construct(
            "{$dsn_prefix}:host={$hostname};dbname={$database};{$dsn_param_string}", 
            $username, 
            $password, 
            array_replace($this->default_attributes, $attributes)
        );
    }

    /**
     * @var string - A parameterized query string using either anonymous placeholders or named placeholders.
     * @var array - A key value pair of values to pass to the query. Should reflect the placeholders placed in the query, including position when using anonymous placeholders.
     */
    public function run($query, $values = array())
    {
        if (!$values)
        {
            return $this->query($query);
        }
        if (is_array($values[0]))
        {
            return $this->multi($query, $values);
        }
        $stmt = $this->prepare($query);
        $stmt->execute($values);
        return $stmt;
    }

    /**
     * It takes a table name and an array of data to insert into the table
     * 
     * @param table The table to insert into
     * @param inserts Array of key => value pairs
     * 
     * @return The return value is the result of the query.
     */
    public function insert($table, $inserts)
    {
        $is_multi_set = !empty($inserts[0]) && is_array($inserts[0]);
        if(!$this->verify_table($table)) {
            throw new Exception('The given table does not exist in the database');
        }

        $set_keys = array_keys($is_multi_set ? $inserts[0] : $inserts);
        if(!$this->verify_columns($table, $set_keys)) {
            throw new Exception('One or more of the supplied columns do not exist in the supplied table');
        }

        $query_keys = [];
        $query_placeholders = [];
        foreach($set_keys as $key) {
            $query_placeholders[] = ":{$key}";
            $query_keys[] = "`$key`";
        }

        $query_keys = implode(', ', $query_keys);
        $query_placeholders = implode(', ', $query_placeholders);

        return $this->run("INSERT INTO {$table} ({$query_keys}) VALUES ({$query_placeholders})", $inserts);
    }

    /**
     * @var string - A table name, which must already exist in your database.
     * @var array - An array of key => value pairs used to generate the SET clause.
     * @var array - An array of key => value pairs to generate the WHERE clause. Each set will be separated by ' AND '.
     */
    public function update($table, $updates, $where)
    {
        if(!$this->verify_table($table)) {
            throw new Exception('The given table does not exist in the database');
        }

        $columns = array_merge(array_keys($updates), array_keys($where));
        if(!$this->verify_columns($table, $columns)) {
            throw new Exception('One or more of the supplied columns do not exist in the supplied table');
        }

        $data_sets = [
            'set' => [
                'array' => $updates, 'separator' => ', '
            ],
            'where' => [
                'array' => $where, 'separator' => ' AND '
            ],
        ];

        /* Creating a query string for the SET and WHERE clauses. */
        foreach($data_sets as $type => &$d) {
            foreach(array_keys($d['array']) as $key) {
                print_r($key);
                $d['query_clause'][] = "`{$key}`=?";
            }
            $d['query_clause'] = implode($d['separator'], $d['query_clause']);
        }

        return $this->run(
            "UPDATE {$table} SET {$data_sets['set']['query_clause']} WHERE {$data_sets['where']['query_clause']}", 
            array_merge(array_values($updates), array_values($where))
        );
    }

    /**
     * Quick queries
     * Allows you to run a query without chaining the return type manually. This allows for slightly shorter syntax.
     */
    /**
     * @return The first row of the result set.
     */
    public function row($query, $values = array())
    {
        return $this->run($query, $values)->fetch();
    }
    /**
     * @return The first column of the first row of the result set.
     */
    public function cell($query, $values = array())
    {
        return $this->run($query, $values)->fetchColumn();
    }
    /**
     * @return An array of all the rows in the result set.
     */
    public function all($query, $values = array())
    {
        return $this->run($query, $values)->fetchAll();
    }

    /**
     * @return An array of values from a single column in the result set.
     */
    public function column($query, $values = array())
    {
        return $this->run($query, $values)->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * If the table is not in the verified_databases array, then check if it exists in the database. If
     * it does, then add it to the verified_databases array.
     * 
     * @param table The table name
     */
    private function verify_table($table)
    {
        $exists = array_key_exists($table, $this->verified_databases);
        if(!$exists && $exists = in_array($table, $this->column('SHOW TABLES'))) {
            $this->verified_databases[$table] = $this->column("SHOW COLUMNS FROM `{$table}`");
        }
        return $exists;
    }

    /**
     * If the table exists in the verified_databases array, and the columns are all in the table, then
     * return true
     * 
     * @param table The table name
     * @param columns The columns you want to select from the table.
     */
    private function verify_columns($table, $columns)
    {
        return array_key_exists($table, $this->verified_databases) && !array_diff($columns, $this->verified_databases[$table]);
    }

    /**
    * Used to handle running queries with multiple data sets. Automatically used when passing an array of data sets to $this->run
    * 
    * @param query The query to execute
    * @param values The data sets associated with the query, an array of key => value pairs
    */
    private function multi($query, $values = array())
    {
        $stmt = $this->prepare($query);
        foreach ($values as $value)
        {
            $stmt->execute($value);
        }
        return $stmt;
    }
}
