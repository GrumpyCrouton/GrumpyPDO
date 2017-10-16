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
    public function __construct($host, $user, $pass, $db, $attributes = array(), $charset = "utf8")
    {
        if($attributes == NULL && !is_array($attributes)) {
            $attributes = array();
        } else {
            if(empty($attributes)) $attributes = $this->default_attributes
        }
        parent::__construct("mysql:host={$host};dbname={$db};charset={$charset}", $user, $pass, $atrributes);
    }
    public function run($query, $values = array())
    {
        if(!$values) {
            return $this->query($query);
        }
        $stmt = $this->prepare($query);
        $stmt->execute($values);
        return $stmt;
    }
}
