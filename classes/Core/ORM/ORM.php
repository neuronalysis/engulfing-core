<?php
class ORM {
	use Integrity, ObjectHelper, ConnectionManager, QueryBuilder, Loader, ORMConverter, Caching, AccessControl, TransactionManager, Helper;
	
	var $convert = true;
	
	protected $config;
	protected $debug = false;
	
	public static $instance;
	
	function __construct($init = array()) {
		foreach($init as $key => $value) {
			$this->$key = $value;
		}
		
		self::$instance = $this;
	}
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	//function getAllByQuery($sql, $object_name, $explicitFields = null) {
	function getAllByQuery($sql, $object_name, $explicitFields = null, $db_scope = null) {
		if (!$db_scope) $db_scope = $this->getOntologyScope($object_name);
		
		//$objects = $this->executeQuery($sql, $object_name, null, true, $explicitFields);
		$objects = $this->executeQuery($sql, $object_name, null, $db_scope);
		
		return $objects;
	}
	function getNextInsertId($object_name) {
		$db_scope = $this->getOntologyScope($object_name);
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$sql = "SELECT AUTO_INCREMENT
			FROM  INFORMATION_SCHEMA.TABLES
			WHERE TABLE_NAME   = '" . $tableName . "' AND TABLE_SCHEMA = '" . $this->getDatabaseName($db_scope) . "'";
		
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
		
			$stmt->execute();
			$objects = $stmt->fetch(PDO::FETCH_NUM);
			
			$db = null;
			
			return $objects[0];
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getByNamedFieldValues(ORM_Request $request) {
	    if (!$request->dbScope) $request->dbScope = $this->getOntologyScope($request->objectName);
	    
	    if ($request->explicitFields) {
	        $sql_select_fields = "*, " . implode(",", $request->explicitFields);
	    } else {
	        $sql_select_fields = "*";
	    }
	    
	    $sql = "SELECT " . $sql_select_fields . " FROM " . $this->getTableNameByObjectName($request->objectName);
	    
	    if ($request->keyValues) {
	        $fields = array_keys($request->keyValues);
	        $values = array_values($request->keyValues);
	        
	        $sql .= $this->buildWhereClause($request->keyValues, $request->noPaging, $request->order, $request->objectName, $request->limit, $request->like, $request->keyOperators);
	    }
	    
	    if ($this->debug) echo "in db " . $request->dbScope . " execute getbynamedfield-sql: " . $sql . "\n";
	    
	    try {
	        $db = $this->openConnection($request->dbScope);
	        $stmt = $db->prepare($sql);
	        
	        if (isset($fields)) {
	            for($i=0; $i<count($fields); $i++) {
	                $field_name = $fields[$i];
	                
	                if ($request->like) {
	                    $stmt->bindValue(":" . $field_name, "%" . $values[$i] . "%");
	                } else {
	                    $stmt->bindParam ( $field_name, $values[$i] );
	                }
	            }
	        }
	        
	        
	        $stmt->execute();
	        
	        $stdObjects = $stmt->fetchAll(PDO::FETCH_OBJ);
	        
	        
	        if (class_exists($request->objectName)) {
	            $objects = $this->convertStdClassesToObjects($stdObjects, $request->objectName, $request->explicitFields, $request->includeProtectedFields);
	            
	            if ($request->explicitFields) {
	                foreach($request->explicitFields as $explicitField_item) {
	                    foreach($objects as $key => $object_item) {
	                        if ($stdObjects[$key]->$explicitField_item) {
	                            $object_item->$explicitField_item = $stdObjects[$key]->$explicitField_item;
	                        }
	                    }
	                }
	            }
	        } else {
	            $objects = $stdObjects;
	        }
	        
	        $db = null;
	        
	        return $objects;
	    } catch(PDOException $e) {
	        if ($this->debug) echo '{"error":{"text":'. $e->getMessage() .'}}';
	        
	        throw $e;
	    }
	}
	function getAllByName(ORM_Request $request) {
	    $tableName = $this->getTableNameByObjectName($request->objectName, false);
	    
	    $sql_paging = "";
	    
	    if (!$request->noPaging) $sql_paging = $this->getPaging($request->objectName, $request->order, $request->limit);
	    
	    if ($request->explicitFields) {
	        $sql_select_fields = "*, " . implode(",", $request->explicitFields);
	    } else {
	        $sql_select_fields = "*";
	    }
	    
	    $sql = "SELECT " . $sql_select_fields . " FROM " . $tableName . " " . $sql_paging;
	    
	    //$objects = $this->executeQuery($sql, $object_name, null, true, $explicitFields, $includingProtected);
	    $objects = $this->executeQuery($sql, $request->objectName, null, $request->dbScope);
	    
	    if (!$request->explicitFields|| $this->convert) {
	        $objects = $this->convertStdClassesToObjects($objects, $request->objectName, $request->explicitFields);
	    }
	    
	    
	    $this->storeObjectsArray($objects, array());
	    
	    return $objects;
	}
	protected function getAllByName_Dep($object_name, $noPaging = false, $orderby = null, $limit = null, $explicitFields = null, $includingProtected = false, $db_scope = null) {
		//if ($objects = $this->isLoadedObjectsArray($object_name, array())) return $objects;
		
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$sql_paging = "";
		
		if (!$noPaging) $sql_paging = $this->getPaging($object_name, $orderby, $limit);
		
		if ($explicitFields) {
			$sql_select_fields = "*, " . implode(",", $explicitFields );
		} else {
			$sql_select_fields = "*";
		}
		
		$sql = "SELECT " . $sql_select_fields . " FROM " . $tableName . " " . $sql_paging;
		
		//$objects = $this->executeQuery($sql, $object_name, null, true, $explicitFields, $includingProtected);
		$objects = $this->executeQuery($sql, $object_name, null, $db_scope);
		
		
		if (!$explicitFields || $this->convert) {
			$objects = $this->convertStdClassesToObjects($objects, $object_name, $explicitFields);
		}
		
		$this->storeObjectsArray($objects, array());
		
		return $objects;
	}
	function getAllByNameLight($object_name, $fields, $offset) {
		if ($objects = $this->isLoadedObjectsArray($object_name, array())) return $objects;
		
		$tableName = $this->getTableNameByObjectName($object_name, false);
	
		$sql_select_fields = implode(",", $fields );
		
		$sql = "SELECT " . $sql_select_fields . " FROM " . $tableName . " WHERE id >= " . $offset;
	
		$objects = $this->executeQuery($sql, $object_name);
		
		return $objects;
	}
	//TODO reduce amount of parameters
	//TODO document cascade/explicitFields processing
	protected function getByNamedFieldValues_Dep($object_name, $fields, $values = null, $like = false, $paging = null, $eager = false, $noPaging = false, $cascades = null, $order = null, $limit = null, $keyOperators = null, $explicitFields = null, $db_scope = null) {
		$keyValues = $this->mergeFieldsAndValues($fields, $values);
		$keyValues = $this->filterPersistableKeyValues($keyValues);
		
		if ($objects = $this->isLoadedObjectsArray($object_name, $keyValues)) return $objects;
		
		if (!$db_scope) $db_scope = $this->getOntologyScope($object_name);
		
		if ($explicitFields) {
			$sql_select_fields = "*, " . implode(",", $explicitFields );
		} else {
			$sql_select_fields = "*";
		}
		
		$sql = "SELECT " . $sql_select_fields . " FROM " . $this->getTableNameByObjectName($object_name);
		
		$fields = array_keys($keyValues);
		$values = array_values($keyValues);
		
		$sql .= $this->buildWhereClause($keyValues, $noPaging, $order, $object_name, $limit, $like, $keyOperators);
		
		if ($this->debug) echo "in db " . $db_scope . " execute getbynamedfield-sql: " . $sql . "\n";
		
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
		
			for($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i];
				
				if ($like) {
					$stmt->bindValue(":" . $field_name, "%" . $values[$i] . "%");
				} else {
					$stmt->bindParam ( $field_name, $values[$i] );
				}
			}
			
			$stmt->execute();
			
			$stdObjects = $stmt->fetchAll(PDO::FETCH_OBJ);
				
			if (class_exists($object_name)) {
				$objects = $this->convertStdClassesToObjects($stdObjects, $object_name);
			} else {
				$objects = $stdObjects;
			}
			
			for($i=0; $i<count($objects); $i++) {
			    //if(isset($cascades) && isset($explicitFields)) {
			    if(isset($cascades)) {
					$j = 0;
					
					foreach($cascades as $cascade) {
					    if (is_array($cascade)) {
					        print_r($cascade);
					    } else {
					        $objectName = $this->getPersistanceClassName($cascade);
					        echo "objName: " . $objectName . "\n";
					        
					        $objectIdFieldName = $this->getObjectIdFieldName($cascade);
					        echo $objectIdFieldName . "\n";
					        $objects[$i]->$cascade = $this->getById($objectName, $stdObjects[$i]->$objectIdFieldName, true, array(), $db_scope);
					    }
					    
						$j++;
					}
				}
			}
			
			$db = null;
			
			$this->storeObjectsArray($objects, array());
				
			return $objects;
		} catch(PDOException $e) {
			if ($this->debug) echo '{"error":{"text":'. $e->getMessage() .'}}';
			
			throw $e;
		}
	}
	function getById($object_name, $id, $eager = true, $excludes = array(), $db_scope = null) {
		//$this->startLoading($object_name, $id);
		//if ($object = $this->isLoadedObject($object_name, $id)) return $object;
			
		if (!$this->isPersistableObject($object_name)) return $this->loadNonPersistableObject($object_name, $id);
		
		$sql = "SELECT * FROM " . $this->getTableNameByObjectName($object_name) . " WHERE id=:id";
		
		if (!$eager) {
			$objects = $this->executeQuery($sql, $object_name, array("id" => $id), $db_scope);
			
			if (isset($objects[0])) {
				$object = $objects[0];
			} else {
				$object = null;
			}
		} else {
			$objects = $this->executeQuery($sql, $object_name, array("id" => $id), $db_scope);
			
			if (isset($objects[0])) {
				$object = $objects[0];
				
				//TODO Evil
				if ($object_name == "RelationIndicatorImpactFunction") {
					$indicator = $this->getById("Indicator", $object->indicatorID, false);
					if (isset($objects[0]->impactFunctionID)) {
						$impactfunctions = $this->executeQuery(
								"SELECT * FROM impactfunctions WHERE id=:id",
								"ImpactFunction", array("id" => $objects[0]->impactFunctionID));
							
						$instrument = $this->getById("Instrument", $impactfunctions[0]->instrumentID, false);
							
						$impactFunction = $this->convertStdClassToObject($impactfunctions, "ImpactFunction");
					
						$impactFunction->Instrument = $instrument;
					
						$object->ImpactFunction = $impactFunction;
					}
						
					$object->Indicator = $indicator;
				} else if ($object_name == "Indicator") {
					$referencedObjects = $this->loadReferencedObjects($object, $object_name, $excludes);
					
					foreach($referencedObjects as $nestedToOneObjectKey => $nestedToOneObjectValue) {
						$object->$nestedToOneObjectKey = $nestedToOneObjectValue;
						
						/*if ($nestedToOneObjectKey == "Release") {
							foreach($object->$nestedToOneObjectKey->Indicators as $releaseIndicator) {
								unset($releaseIndicator->RelationIndicatorImpactFunctions);
							}
						}*/
						
					}
					
					$relationimpactfunctions = $this->executeQuery(
							"SELECT * FROM relationindicatorimpactfunctions WHERE indicatorID=:indicatorID",
							"RelationIndicatorImpactFunction", array("indicatorID" => $object->id));
					
					foreach($relationimpactfunctions as $relationImpactFunctionItem) {
						$relImpFunction = $this->getById("RelationIndicatorImpactFunction", $relationImpactFunctionItem->id);
						
						$ImpFunction = $this->getById("ImpactFunction", $relationImpactFunctionItem->impactfunctionID);
						unset($ImpFunction->RelationIndicatorImpactFunctions);
						$relImpFunction->ImpactFunction = $ImpFunction;
						$relImpFunction->ImpactFunction->name = $relImpFunction->ImpactFunction->formula;
						
						unset($relImpFunction->Indicator);
						
						array_push($object->RelationIndicatorImpactFunctions, $relImpFunction);
					}
					
					unset($object->Release->Indicators);
					
				} else if ($object_name == "Underlying") {
					$sector = $this->getById("Sector", $object->sectorID, false);
					
					$object->Sector = $sector;
				} else {
					$referencedObjects = $this->loadReferencedObjects($object, $object_name, $excludes, $db_scope);
						
					foreach($referencedObjects as $nestedToOneObjectKey => $nestedToOneObjectValue) {
						$object->$nestedToOneObjectKey = $nestedToOneObjectValue;
					}
				}
			} else {
				$object = null;
			}
		}
		
		if ($object) {
			$object = $this->convertStdClassToObject($object, $object_name);
			$this->endLoading($object_name, $id);
			$this->storeObject($object);
			
			return $object;
		} else {
			$this->endLoading($object_name, $id);
			
			return null;
		}
	}
	function deleteById($object_name, $id, $cascade = true, $db_scope = null) {
		if (!$id) return null;
		
		$sql = "DELETE FROM " . $this->pluralize(strtolower($object_name)) . " WHERE id=:id";

		$this->executeQuery($sql, $object_name, array("id" => $id), $db_scope);
	}
	function deleteByNamedFieldValues($object_name, $fields, $values) {
		$objects = $this->getByNamedFieldValues($object_name, $fields, $values);
		
		foreach($objects as $object) {
			$this->deleteById($object_name, $object->id);
		}
	}
	//TODO db-scope handling
	function insert($object, $db_scope = null) {
		$this->setModificationInfo($object);
		
		$query = $this->prepareInsertQueryByObject($object);
		
		$bindings = $this->getBindingsFromObject($object);
		
		$lastInsertId = $this->executeQuery($query, get_class($object), $this->getBindingsFromObject($object), $db_scope);
		
		return intval($lastInsertId);
	}
	//TODO db-scope handling
	function update($object, $db_scope = null) {
		$this->setModificationInfo($object);
		
		$query = $this->prepareUpdateQueryByObject($object);
		
		$bindings = $this->getBindingsFromObject($object);
		
		$this->executeQuery($query, get_class($object), $this->getBindingsFromObject($object), $db_scope);
	}
	//TODO db-scope handling
	function save($object, $db_scope = null) {
		if ($this->hasVersionning($object)) {
			$object->version += 1;
			$object->id = null;
			
			return $this->insert($object, $db_scope);
		} else {
			if ($this->isNew($object, $db_scope)) {
				if ($doublicate = $this->checkUniqueConstraints($object)) {
					return $this->replace($object, $db_scope);
				} else {
					return $this->insert($object, $db_scope);
				}
			} else {
				$this->update($object, $db_scope);
			}
		}
	}
	function restore($object, $version, $db_scope = null) {
		$object_name = $this->getNameWithoutNamespace(get_class($object));
		
		$ormRequest = new ORM_Request($object_name, array("number" => $object->number));
		$ormRequest->order = "number DESC";
		
		$versions = $this->getByNamedFieldValues($ormRequest);
		
		$restoredVersion = null;
		
		foreach($versions as $versionItem) {
			if ($versionItem->version > $version) {
				$this->deleteById($object_name, $versionItem->id);
			} else if ($versionItem->version == $version) {
				$restoredVersion = $versionItem;
			}
		}
			
		return $versionItem;
	}
	function replace($object, $db_scope = null) {
		$persistableObjectVars = $this->filterPersistableFields($object);
		
		$this->deleteByNamedFieldValues(get_class($object), array_keys(array_filter($persistableObjectVars)), array_values(array_filter($persistableObjectVars)));
		
		return $this->insert($object, $db_scope);
	}
	function insertImportEntitiesBulk($entities, $truncate = false, $start = 0, $stackSize = 8000, $ignoreConstraints = true) {
		try {
			if (isset($entities[0])) {
				$db_scope = strtolower($entities[0]->entityOntologyName);
			}
			
			$db = $this->openConnection($db_scope);
			
			$db->beginTransaction(); // also helps speed up your inserts.
			
			if ($db_scope === "search") {
				$fields = $this->prepareFields($entities, false);
					
				$array = $this->prepareArray($entities, $start, $stackSize, false);
			} else {
				$fields = $this->prepareFields($entities);
					
				$array = $this->prepareArray($entities, $start, $stackSize);
			}
						
			
			if ($db_scope === "search" && $entities[0]->entityClassName === "index") {
				$tableName = "index";
			} else {
				$tableName = $this->getTableNameByObjectName($entities[0]->entityClassName, false);
			}
				
			if ($truncate)  {
				$sql_truncate = "SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE `" . $tableName . "` SET FOREIGN_KEY_CHECKS = 1;";
				
				if ($this->debug) echo $sql_truncate . "\n";
				
				$db->query($sql_truncate);
			}
				
			$qm = '('  . $this->placeholders('?', count($fields)) . ')';
	
			$question_marks = array_fill(0, count($array) / count($fields), $qm);
	
			$sql = "INSERT IGNORE INTO `" . $tableName . "` (" . implode(",", $fields ) . ") VALUES " . implode(',', $question_marks);
	
			if ($this->debug) echo $sql . "\n";
			
			$stmt = $db->prepare ($sql);
	
			$stmt->execute($array);
	
			$db->commit();
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
		
		return $this;
	}
	function insertArrayBulk($array, $table, $fields) {
		try {
			$db_scope = strtolower($this->getOntologyScope($this->singularize($table)));
				
			$db = $this->openConnection($db_scope);
	
			$db->beginTransaction(); // also helps speed up your inserts.
			$insert_values = array();
	
			$qm = '('  . $this->placeholders('?', count($fields)) . ')';
	
			$question_marks = array_fill(0, count($array) / count($fields), $qm);
	
			$sql = "INSERT IGNORE INTO " . $this->pluralize(strtolower($table)) . " (" . implode(",", $fields ) . ") VALUES " . implode(',', $question_marks);
	
			//echo $sql . "\n";
			
			$stmt = $db->prepare ($sql);
	
			$stmt->execute($array);
	
			$db->commit();
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getTotalAmount($object_name, $whereKeyValue = null) {
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$sql = "SELECT COUNT(*) as totalAmount FROM " . $tableName;
		
		if ($whereKeyValue) {
			foreach($whereKeyValue as $key => $value) {
				$where .= " " . $key . " = " . $value;
			}
				
			if ($where !== "") {
				$sql .= " WHERE" . $where;
			}
		}
		
		$objects = $this->executeQuery($sql, $object_name);
		
		return intval($objects[0]->totalAmount);
	}
	function getDataServiceEntityByObject($object) {
		$className = get_class($object);
		
		$km = new KM();
		$ontologyClass = $km->getOntologyClassByName($className);
		
		$dsEntity = $this->orm->getByNamedFieldValues("DataServiceEntity", array("ontologyClassID", "internalKey"), array($ontologyClass->id, $object->id));
		
		return $dsEntity;
	}
}
class ORM_Request {
    var $objectName;
    var $keyValues;
    var $explicitFields = null;
    var $limit = null;
    var $order = null;
    var $noPaging = true;
    var $like = null;
    var $keyOperators = null;
    var $dbScope = null;
    var $includeProtectedFields = false;
    
    use QueryBuilder;
    
    function __construct($objectName, $keyValues = null, $explicitFields = null) {
        $this->objectName = $objectName;
        if ($keyValues) $this->keyValues = $this->filterPersistableKeyValues($keyValues);
        $this->explicitFields = $explicitFields;
    }
    function filterPersistableKeyValues($keyValues) {
        $filtered = array();
        
        foreach($keyValues as $key => $value) {
            if (!is_object($value)) {
                $filtered[$key] = $value;
            }
        }
        
        return $filtered;
    }
    function setKeyValuesByFieldsAndValues($fields, $values) {
    	$this->keyValues = $this->filterPersistableKeyValues($this->mergeFieldsAndValues($fields, $values));
    }
}
?>