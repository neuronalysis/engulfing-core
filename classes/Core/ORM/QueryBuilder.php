<?php
trait QueryBuilder {
	function __construct() {
	}
	
	function placeholders($text, $count=0, $separator=","){
		$result = array();
		if($count > 0){
			for($x=0; $x<$count; $x++){
				$result[] = $text;
			}
		}
	
		return implode($separator, $result);
	}
	function buildInsertQuery($object, $fields) {
		$insert_fields = array();
		$insert_values = array();
			
		foreach($fields as $key => $value) {
			if (!in_array($key, array("id"))) {
				array_push($insert_fields, "`" . $key . "`");
				array_push($insert_values, ":" . $key);
			}
		}
		
		$fields = implode($insert_fields, ", ");
		$values = implode($insert_values, ", ");
		
		$tableName = $this->getTableNameByObjectName(get_class($object));
			
		$sql = "INSERT INTO " . $tableName . " (" . $fields . ") VALUES (" . $values . ")";
		
		return $sql;
	}
	function buildUpdateQuery($object, $bindings) {
		$keys = array();
		foreach($bindings as $key => $value) {
			if (!in_array($key, array("id", "createdAt", "createdBy"))) array_push($keys, $key . " = :" . $key);
		}
		
		$setters = implode($keys, ", ");
		
		$tableName = $this->getTableNameByObjectName(get_class($object));
			
		$sql = "UPDATE " . $tableName . " SET " . $setters . " WHERE id=:id";
		
		return $sql;
	}
	function filterFields($fields, $filter) {
		$filtered = array();
		
		foreach($fields as $key => $value) {
			if(!in_array($key, $filter)) $filtered[$key] = $value;
		}
		
		return $filtered;
	}
	function getBindingsFromObject($object) {
		return $this->getPersistableFieldsFromObject($object);
	}
	function prepareInsertQueryByObject($object) {
		$fields = $this->getPersistableFieldsFromObject($object);
		
		$sql = $this->buildInsertQuery($object, $fields);
		
		return $sql;
	}
	function prepareUpdateQueryByObject($object) {
		$fields = $this->getPersistableFieldsFromObject($object);
		
		$sql = $this->buildUpdateQuery($object, $fields);
		
		return $sql;
	}
	function getPersistableFieldsFromObject($object, $includingObjects = true) {
		$fields = array();
		
		$reflection = new ReflectionClass(get_class($object));
		$classvars = $reflection->getProperties();
		
		for($i=0; $i < count($classvars); $i++) {
			$key = $classvars[$i]->name;
			
			$rp = new ReflectionProperty($object,$key);
			if (!$this->isObjectReference($key) && !in_array($key, array("cascades", "cascade", "constraintsUnique", "defaultOrder"))) {
				if ($rp->isProtected()) {
					$getterMethodName = "get" . ucfirst($key);
					$fields[$key] = $object->$getterMethodName();
				} else {
					$fields[$key] = $object->$key;
				}
		
		
			} else {
				if ($includingObjects && $this->isObjectReference($key)) {
					if (isset($object->$key->id)) {
						$fields[lcfirst($key) . "ID"] = $object->$key->id;
					}
				}
			}
		}
		
		
		return $fields;
	}
	function getQueryType($sql) {
		if (strpos($sql, 'DELETE') !== false) return "DELETE";
		if (strpos($sql, 'SELECT') !== false) return "SELECT";
		if (strpos($sql, 'INSERT') !== false) return "INSERT";
		if (strpos($sql, 'UPDATE') !== false) return "UPDATE";
	}
	function getPaging($object_name, $sortby = null, $limit = null, $order = "ASC") {
		$sql = "";
	
		if (stripos($object_name, "observation") !== false) {
			$page = null;
			$per_page = 1000;
			$sort_by = "";
		} else {
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
			} else {
				if ($limit) {
					$page = 1;
				} else {
					$page = null;
				}
			}
			if (isset($_GET['per_page'])) {
				$per_page = $_GET['per_page'];
			} else {
				$per_page = 15;
			}
			if (isset($_GET['sort_by'])) {
				$sort_by = "ORDER BY " . $_GET['sort_by'] . " " . $_GET['order'];
			} else {
				if ($sortby) {
					$sort_by = "ORDER BY " . $sortby . " " . $order;
				} else {
					$sort_by = "";
				}
			}
		}
	
		if ($page > 1) {
			$sql .= $sort_by . " LIMIT " . (($page-1) * $per_page) . ", " . $per_page;
		} else {
			$sql .= $sort_by . " LIMIT " . $per_page;
		}
	
		return $sql;
	}
	function mergeFieldsAndValues($fields, $values) {
		$keyValues = array();
		
		for($i=0; $i<count($fields); $i++) {
			$keyValues = array_merge(array($fields[$i] => $values[$i]), $keyValues);
		}
		
		return $keyValues;
	}
	function buildWhereClause($keyValues, $noPaging, $order, $object_name, $limit, $like, $keyOperators) {
		$sql = "";
		
		$a=0;
		if (count($keyValues) > 0) {
			$sql_add = "";
			
			foreach($keyValues as $field_name => $value) {
				//echo $field_name . " : " . $value . "\n";
				
				if ($field_name !== "order" && $field_name !== "sort_by" && $field_name !== "page" && $field_name !== "per_page" && (isset($value))) {
					if ($a > 0) $sql_add .= " AND ";
						
					if ($like) {
						$sql_add .= $field_name . " LIKE :" . $field_name;
					} else if (substr($field_name, -2, 2) == "At") {
						if (strlen($values[array_search("sentAt", $fields)]) == 10) {
							$sql_add .= "DATEDIFF(" . $field_name . ",:" . $field_name . ") = 0";
						}
					} else {
						if ($keyOperators) {
							$sql_add .= $field_name . $keyOperators[$field_name] . ":" . $field_name;
						} else {
							$sql_add .= $field_name . "=:" . $field_name;
						}
						
					}
						
					$a++;
				}
			}
		
			if ($a > 0) {
				$sql = $sql . " WHERE " . $sql_add;
			}
		}
		
		if (!$noPaging && !$order) {
			$sql_paging = $this->getPaging($object_name);
			$sql .= " " . $sql_paging;
		}
		
		if ($order) {
			$orderBy = " ORDER BY " . $order;
				
			$sql .= " " . $orderBy;
		}
		
		if ($limit) {
			$sql .= " LIMIT " . $limit;
		}
		
		//echo $sql . "\n";
		
		return $sql;
	}
}
?>