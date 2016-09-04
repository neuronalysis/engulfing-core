<?php
trait TransactionManager {
	function executeQuery($query, $object_name, $bindings = null) {
		$db_scope = $this->getOntologyScope($object_name);
		
		$queryType = $this->getQueryType($query);
		if ($this->debug) echo "sql: " . $query . "\n";
	
		try {
			$db = $this->openConnection($db_scope);
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
					foreach($bindings as $key => $value) {
						$stmt->bindValue($key, $value);
					}
				} else if ($queryType == "DELETE") {
					$stmt->bindValue("id", $bindings["id"]);
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