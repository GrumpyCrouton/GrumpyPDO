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
    public function __construct($hostname, $username, $password, $database, $attributes = array() , $charset = "utf8")
    {
        $active_attrs = $this->default_attributes;
        if (!empty($attributes))
        {
            array_replace($active_attrs, $attributes);
        }
        parent::__construct("mysql:host={$hostname};dbname={$database};charset={$charset}", $username, $password, $active_attrs);
    }

    /**
     * @var string - A table name, which must already exist in your database.
     * @var array - A key value pair of values to insert into the database on the specified table
     *
     * Query will be generated with named placeholders.
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
     * @var array - A key value pair of values to pass to the queries SET clause.
     * @var array - A key value pair of values to pass to the queries WHERE clause. Each set will be separated by 'AND'.
     *
     * MUST USED ANONYMOUS PLACEHOLDERS, DOES NOT SUPPORT NAMED PARAMETERS.
     * This is to allow you to use the same columns in the SET and WHERE clause
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

        $query_params = array_merge(array_values($updates), array_values($where));
        $set_clause_parts = [];
        $where_clause_parts = [];

        foreach(array_keys($updates) as $key) {
            $set_clause_parts[] = "`{$key}`=?";
        }
        foreach(array_keys($where) as $key) {
            $where_clause_parts[] = "`{$key}`=?";
        }

        $set_clause = implode(', ', $set_clause_parts);
        $where_clause = implode(' AND ', $where_clause_parts);

        return $this->run("UPDATE {$table} SET {$set_clause} WHERE {$where_clause}", $query_params);
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
     * Quick queries
     * Allows you to run a query without chaining the return type manually. This allows for slightly shorter syntax.
     */

     /**
     * Fetch a singular row from the database in a flat array.
     */
    public function row($query, $values = array())
    {
        return $this->run($query, $values)->fetch();
    }
    /**
    * Fetch a single cell from the database. Doesn't support multiple rows.
    */
    public function cell($query, $values = array())
    {
        return $this->run($query, $values)->fetchColumn();
    }
    /**
    * Fetch all of the results from the database into a multidimensional array.
    */
    public function all($query, $values = array())
    {
        return $this->run($query, $values)->fetchAll();
    }
    /**
    * Fetch a single column from the database. Similar to $this->cell, except can have multiple rows.
    */
    public function column($query, $values = array())
    {
        return $this->run($query, $values)->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Private Methods
     * Methods called internally, and should not be used directly.
     */

     /**
     * Used to verify if a table exists in the currently selected database or not.
     */
    private function verify_table($table)
    {
        return in_array($table, $this->column("SHOW TABLES"));
    }
    /**
    * Used to determine if columns exist in a certain table in the selected database or not.
    */
    private function verify_columns($table, $columns)
    {
        $db_columns = $this->column("SHOW COLUMNS FROM `{$table}`");
        return !array_diff($columns, $db_columns);
    }
    /**
    * Used to handle running queries with multiple data sets. Automatically used when passing an array of data sets to $this->run
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
