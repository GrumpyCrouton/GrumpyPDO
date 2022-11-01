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

    private $verified_tables = [];

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
        $single_data_set = $is_multi_set ? $inserts[0] : $inserts;

        if(!$this->verifyTablesAndColumns($table, $set_keys = array_keys($single_data_set))) {
            return false;
        }

        if(!$is_multi_set) {
            $inserts = array_values($inserts);
        } else {
            foreach($inserts as &$i) {
                $i = array_values($i);
            }
        }

        $query_keys = implode('`, `', $set_keys);
        $query_placeholders = implode(', ', array_fill(0, count($set_keys), "?"));

        return $this->run("INSERT INTO `{$table}` (`{$query_keys}`) VALUES ({$query_placeholders})", $inserts);
    }

    /**
     * @var string - A table name, which must already exist in your database.
     * @var array - An array of key => value pairs used to generate the SET clause.
     * @var array - An array of key => value pairs to generate the WHERE clause. Each set will be separated by ' AND '.
     */
    public function update($table, $updates, $where)
    {
        $columns = array_merge(array_keys($updates), array_keys($where));
        if(!$this->verifyTablesAndColumns($table, $columns)) {
            return false;
        }

        /* Creating a query string for the SET and WHERE clauses, and an array of parameters. */
        $p = [];
        $d = [['v' => $updates, 's' => ', '], ['v' => $where, 's' => ' AND ']];
        foreach($d as &$s) {
            foreach(array_keys($s['v']) as $k) {
                $s['c'][] = "`{$k}`=?";
            }
            $s['c'] = implode($s['s'], $s['c']);
            $p = array_merge($p, array_values($s['v']));
        }

        return $this->run("UPDATE `{$table}` SET {$d[0]['c']} WHERE {$d[1]['c']}", $p);
    }

    /**
     * @return The first row of the result set. 
     * Read https://www.php.net/manual/en/pdostatement.fetch.php for more options
     */
    public function row($query, $values = array(), $mode = null, $cursorOrientation = null, $cursorOffset = 0)
    {
        return $this->run($query, $values)->fetch($mode, $cursorOrientation, $cursorOffset);
    }

    /**
     * @return The $column column of the first row of the result set.
     * Read https://www.php.net/manual/en/pdostatement.fetchcolumn.php for more options
     */
    public function cell($query, $values = array(), $column = 0)
    {
        return $this->run($query, $values)->fetchColumn($column);
    }

    /**
     * @return An array of all the rows in the result set.
     * Read https://www.php.net/manual/en/pdostatement.fetchall.php for more options
     */
    public function all($query, $values = array(), $mode = null, $c = null, $args = null)
    {
        $qry = $this->run($query, $values);
        switch($mode) {
            case \PDO::FETCH_COLUMN:
                return $qry->fetchAll($mode, $c);
            case \PDO::FETCH_CLASS:
                return $qry->fetchAll($mode, $c, $args);
            case \PDO::FETCH_FUNC:
                return $qry->fetchAll($mode, $c);
            default:
                return $qry->fetchAll($mode);
        }
    }

    /**
     * @return An array of values from a single column in the result set.
     */
    public function column($query, $values = array())
    {
        return $this->all($query, $values, \PDO::FETCH_COLUMN);
    }

    /**
     * @return An array with the values grouped by the value of the first column in the result set.
     * The values in each set a set of arrays similar to the all() method
     */
    public function group($query, $values = array())
    {
        return $this->all($query, $values, \PDO::FETCH_GROUP);
    }

    /** 
     * @return An array of the result of the query as a key-value pair. The first column is the index, 
     * the second column is the value. Does not support duplicate indexes
     */
    public function keypair($query, $values = array())
    {
        return $this->all($query, $values, \PDO::FETCH_KEY_PAIR);
    }

    /** 
     * @return An array of the result of the query as key-value pairs. The first column is the index, 
     * the value is an array of all of the values from the second column.
     */
    public function keypairs($query, $values = array())
    {
        return $this->all($query, $values, \PDO::FETCH_GROUP|\PDO::FETCH_COLUMN);
    }

    /**
     * It checks if the table exists in the database, if it does, it checks if the columns exist in the
     * table.
     * 
     * @param verify_table The table to verify
     * @param verify_columns An array of columns to verify exist in the table
     * 
     * @return a boolean value.
     */
    private function verifyTablesAndColumns($verify_table, $verify_columns)
    {
        $tables = &$this->verified_tables;
        if(!array_key_exists($verify_table, $tables)) {
            $tables = array_fill_keys(array_keys(array_flip($this->column('SHOW TABLES'))), null);
        }
        if($exists = array_key_exists($verify_table, $tables) && empty($tables[$verify_table])) {
            $tables[$verify_table] = $this->column("SHOW COLUMNS FROM `{$verify_table}`");
        } else {
            throw new Exception('The given table does not exist in the database');
        }
        $columns_valid = !array_diff($verify_columns, $tables[$verify_table]);
        if(!$columns_valid) {
            throw new Exception('One or more of the supplied columns do not exist in the supplied table');
        }
        return $columns_valid;
    }

    /**
    * Used to handle running queries with multiple data sets. Automatically used when passing an array of data sets to $this->run
    * Can only be used for insert queries
    * 
    * @param query The query to execute
    * @param values The data sets associated with the query, an array of key => value pairs
    */
    private function multi($query, $values = array())
    {
        if(substr(strtolower($query), 0, 6) !== "insert") {
            throw new Exception('Multi data sets can only be used with insert queries');
        }
        $stmt = $this->prepare($query);
        try 
        {
            $this->beginTransaction();
            foreach ($values as $value)
            {
                $stmt->execute($value);
            }
            $this->commit();
            return $stmt;
        } catch(Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }
}
