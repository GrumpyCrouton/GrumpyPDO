<?php
class GrumpyPdo extends \PDO
{
    /**
     * @var array
     * Default attributes set for database connection.
     */
    protected $default_attributes = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    );
    /**
     * @var int
     * Holds the value of the amount of affected rows from the last query.
     */
    protected $last_affected_rows = 0;
    public function __construct($host, $user, $pass, $db, $attributes = array(), $charset = "utf8")
    {
        if(!is_array($attributes)) {
        if($attributes == NULL) {
            $attributes = array();
        } else {
            $attributes = $this->default_attributes;
        }
        } else {
            if(empty($attributes)) $attributes = $this->default_attributes;
        }
        parent::__construct("mysql:host={$host};dbname={$db};charset={$charset}", $user, $pass, $attributes);
    }
    public function run($query, $values = array())
    {
        if(!$values) {
            return $this->query($query);
        }
        $stmt = $this->prepare($query);
        if(!is_array($values[0])) {
            return $stmt->execute($values);
        }
        $this->last_affected_rows = 0;
        foreach($values as $value) 
        {
            $stmt->execute($value);
            $this->last_affected_rows += $stmt->rowCount();
        }
        return $this->last_affected_rows;
    }
    /**
     * Quick queries
     * Allows you to run a query without chaining the return type manually. This allows for slightly shorter syntax.
     */
    public function row($query, $values = array()) 
    {
        return $this->run($query, $values)->fetch();
    }
    public function cell($query, $values = array()) 
    {
        return $this->run($query, $values)->fetchColumn();
    }
    public function all($query, $values = array()) 
    {
        return $this->run($query, $values)->fetchAll();
    }
    /**
     * Other Methods
     */
    public function getLastAffectedRows() 
    {
        return $this->last_affected_rows;
    }
}
