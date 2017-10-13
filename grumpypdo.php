<?php
	class GrumpyPdo {
		
		private $host, $user, $pass, $db, $charset;
		private $dsn, $databaseObject;
		
		private $killOnError = true;
		private $echoOnError = false;
		
		//these options can obviously be changed but not "on the fly" (yet).
		private $opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		
		function __construct($host, $user, $pass, $db, $charset = "utf8") {
			
			$this->setData([
				"host" => $host,
				"user" => $user,
				"pass" => $pass,
				"db" => $db,
				"charset" => $charset,
			]);
			
			$this->createDBObject();
			
		}
		
		function query($query, $values = []) {
			if(empty($values)) {
				$stmt = $this->databaseObject->prepare($query);
				$stmt->execute($values);
			} else {
				$stmt = $this->databaseObject->query($query);
			}
			return $stmt;
		}
		
		function setData($data) {
			if(!is_array($data)) {
				$this->handleError("GrumpyPDO: Error Code SD13; The setData method requires an array with \$key => \$value pairs.");
			}
			foreach($data as $k => $v) {
				$this->{$k} = $v;
			}
			return true;
		}
		
		function createDBObject() {
			$this->dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
			$this->databaseObject = new PDO($this->dsn, $this->user, $this->pass, $this->opt);
		}
		
		function handleError($error) {
			error_log($error);
			if($this->echoOnError) echo $error;
			if($this->killOnError) die($error);
		}
		
		
	}
?>
