<?php
trait TransactionManager {
	var $db_scope;
	
	function __construct($db_scope = null) {
		if ($db_scope) {
			$this->db_scope = $db_scope;
		}
	}
	function executeQuery($query, $object_name, $bindings = null, $db_scope = null) {
		//echo $object_name . "; " . $db_scope . "\n";
		if ($db_scope) {
			$this->db_scope = $db_scope;
		} else {
			$this->db_scope = $this->getOntologyScope($object_name);
		}
		
		$queryType = $this->getQueryType($query);
		if ($this->debug) echo "sql: " . $query . "\n";
	
		try {
			$db = $this->openConnection($this->db_scope);
			$stmt = $db->prepare($query);
	
			if (isset($bindings)) {
				if ($queryType == "INSERT") {
					foreach($bindings as $key => $value) {
						if($key !== "id") {
							if ($this->debug) echo "insert bound key: " . $key . " -> " . $value . "\n";
					
							$stmt->bindValue($key, $value);
						}
					}
				} else if ($queryType == "UPDATE") {
					foreach($bindings as $key => $value) {
						if(!in_array($key, array("createdAt", "createdBy"))) {
							if ($this->debug) echo "update bound key: " . $key . " -> " . $value . "\n";
								
							$stmt->bindValue($key, $value);
						}
					}
				} else if ($queryType == "SELECT") {
					if (isset($bindings)) {
						foreach($bindings as $key => $value) {
							$stmt->bindValue($key, $value);
						}
					}
				} else if ($queryType == "DELETE") {
					if (isset($bindings["id"])) {
						$stmt->bindValue("id", $bindings["id"]);
					} else {
						foreach($bindings as $key => $value) {
							if($key !== "id") {
								if ($this->debug) echo "insert bound key: " . $key . " -> " . $value . "\n";
								
								$stmt->bindValue($key, $value);
							}
						}
					}
				}
			}
	
			$stmt->execute();
			
			if ($queryType == "SELECT") {
				$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
	
				return $objects;
			} else if ($queryType == "INSERT") {
				return $db->lastInsertId();
			} else if ($queryType == "UPDATE") {
			} else if ($queryType == "DELETE") {
				return null;
			}
		} catch(PDOException $e) {
			echo '{"error":{"text": ' . $query . '
' . $e->getMessage() . '(' . $e->getLine() .')
}}
	
';
		}
	}
}
?>