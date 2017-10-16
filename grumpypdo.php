<?php

class GrumpyPdo extends \PDO
{
    /**
     * @var array
     */
    protected $opt = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => 
        PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false
    );

    /**
     * @var string
     */
    protected $charset = "utf8";

    public function __construct($host, $user, $pass, $db, $opt = array())
    {
        $charset = empty($opt['charset']) ? $this->charset : $opt['charset'];
        $options = empty($opt['options']) ? $this->opt : $opt['options'];
        parent::__construct($this->getDSN($host, $db, $charset), $user, $pass, $options);
    }

    public function run($query, $values = array())
    {
        if (!$values) {
            return $this->query($query)
        }
        $stmt = $this->prepare($query);
        $stmt->execute($values);
        return $stmt;
    }

    protected function getDSN($host, $db, $charset)
    {
        return "mysql:host={$host};dbname={$db};charset={$charset}";
    }
}
