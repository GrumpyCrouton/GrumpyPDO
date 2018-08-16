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
    public function __construct($hostname, $username, $password, $database, $attributes = array(), $charset = "utf8")
    {
        $active_attrs = $this->default_attributes;
        if(!empty($attributes)) {
            array_replace($active_attrs, $attributes);
        }
        parent::__construct("mysql:host={$hostname};dbname={$database};charset={$charset}", $username, $password, $active_attrs);
    }
    public function run($query, $values = array())
    {
        if(!$values) {
            return $this->query($query);
        }
        if(is_array($values[0])) {
           return $this->multi($query, $values); 
        }
        $stmt = $this->prepare($query);
        $stmt->execute($values);
        return $stmt;
    }
    private function multi($query, $values = array())
    {
        $stmt = $this->prepare($query);
        foreach($values as $value) 
        {
            $stmt->execute($value);
        }
        return $stmt;
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
}
