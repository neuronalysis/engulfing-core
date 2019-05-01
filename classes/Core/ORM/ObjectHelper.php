<?php
trait ObjectHelper {
	var $entities = array();
	var $entityClasses = array();
	var $entityProperties = array();
	
	function __construct() {
	}
	function isNew($object) {
		if (isset($object->id) && $object->id !== -99) return false;
		return true;
	}
	function hasVersionning($object) {
		if (property_exists(get_class($object), "version")) {
			return true;
		}
		
		return false;
	}
	function isEmpty($persistables) {
		foreach($persistables as $key => $value) {
			if ($key !== "createdBy" && $key !== "createdAt" && $key !== "updatedAt" && $key !== "updatedBy") {
				if ($value !== null && $value !== "") {
					return false;
				}
			}
		}
		
		return true;
	}
	function isClassField($field_name) {
		$class_name = $this->getOntologyClassName();
	
		if (in_array($field_name, array("createdBy", "createdAt", "updatedBy", "updatedAt"))) return true;
	
		return property_exists(get_class($this), $field_name);
	}
	function getPersistanceClassName($objectName) {
		$persistanceClassName = "";
		
		$objectName = str_replace("Outgoing", "", $objectName);
		$objectName = str_replace("Incoming", "", $objectName);
		
		if (class_exists($objectName)) {
			if (substr(get_parent_class($objectName), -10, 10) === "_Generated") {
				$persistanceClassName = $objectName;
			} else {
				if (substr(get_parent_class(get_parent_class($objectName)), -10, 10) === "_Generated") {
					$persistanceClassName = get_parent_class($objectName);
				} else {
					$persistanceClassName = $objectName;
				}
			}
		} else {
			$persistanceClassName = $objectName;
		}
		
		$persistanceClassName = $this->getNameWithoutNamespace($persistanceClassName);
		
		return $persistanceClassName;
	}
	function getNameWithoutNamespace($objectName) {
		$nsSplit = explode("\\", $objectName);
		
		return $nsSplit[count($nsSplit)-1];
	}
	function getNamespaceByObjectName($objectName) {
		$nsSplit = explode("\\", $objectName);
	
		if (count($nsSplit) > 1) {
			return $nsSplit[1];
		}
		
		return null;
	}
	function setModificationInfo($object) {
		$auth = Authentication::getInstance();
		
		if (!property_exists($object, 'createdBy')) return null;
		
		if (!$UserID = $auth->isLogged()) {
			$UserID = 23;
		}
	
		$class_name = $this->getOntologyClassName($object);
	
		if ($this->isNew($object)) {
			if (isset($UserID)) $object->setCreatedBy($UserID);
			$object->setCreatedAt(date('Y-m-d H:i:s', time()));
		}
	
		if (isset($UserID)) $object->setUpdatedBy($UserID);
		$object->setUpdatedAt(date('Y-m-d H:i:s', time()));
	}
	function tableExists($object) {
	    if (is_object($object)) {
	        $class_name = get_class($object);
	    } else {
	        $class_name = $object;
	    }
		
	
		if ($class_name === "ImportEntity") {
			$tableName = $this->getTableNameByObjectName($object->entityClassName, false);
				
			$db_scope = strtolower($object->entityOntologyName);
		} else {
			$tableName = $this->getTableNameByObjectName($class_name);
				
			$db_scope = $this->getOntologyScope($object);
				
		}
		
		if ($this->debug) {
			echo "table-exists - db_scope: " . $db_scope . "\n";
		}
		$db = $this->openConnection ($db_scope);
	
		$stmt = $db->prepare ( "SHOW TABLES LIKE '$tableName'" );
		$stmt->execute ();
		$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		if(count($objects) > 0){
			return true;
		} else {
			return false;
		}
	}
	function getTableNameByObjectName($objectName, $checkExistance = true) {
		if ($objectName . "_Generated" !== get_parent_class($objectName)) {
			$checkExistance = true;
		}
		
		
		if ($checkExistance) {
		    $persistanceClassName = $this->getPersistanceClassName($objectName);
		} else {
		    $persistanceClassName = $objectName;
		}
		
		$exp = explode("_", $persistanceClassName);
		
		$persistanceClassName = $exp[0];
		
		return $this->pluralize(strtolower($persistanceClassName));
	}
	function isSelfRelation($name) {
		$relation = str_ireplace("entity", "", str_ireplace("relation", "", $name));
	
		if (strlen($relation) % 2 == 0) {
			if (substr($relation, 0, strlen($relation) / 2) == substr($relation, strlen($relation) / 2, strlen($relation) / 2)) {
				if (stripos($name, "entity") !== false) {
					return substr($relation, 0, strlen($relation) / 2) . "Entity";
				} else {
					return substr($relation, 0, strlen($relation) / 2);
						
					
				}
			}
		}
	
		return false;
	}
	function resolveClassName($classname_unresolved) {
		if (class_exists($classname_unresolved)) {
			$temp_object = new $classname_unresolved;
			$classname = get_class($temp_object);
			unset($temp_object);
	
			return $classname;
		}
	}
	function isObjectReference($key) {
		if ($this->starts_with_upper($key)) return true;
		
		return false;
	}
	function isToOneField($field) {
		if (class_exists($field)) return true;
		
		return false;
	}
	function isToManyField($field) {
		if (class_exists($this->singularize($field)) && $field !== $this->singularize($field)) return true;
		
		return false;
	}
	function getObjectIdFieldName($field) {
		$idFieldName = lcfirst($field) . "ID";
		
		return $idFieldName;
	}
	function filterPersistableFields($object) {
		$objectvars = get_object_vars($object);
		
		return $this->filterPersistableKeyValues($objectvars);
	}
	function loadNonPersistableObject($object, $id) {
		if (is_string($object)) {
			$object_name = $object;
		} else if (is_object($object)) {
			$object_name = get_class($object);
		}
		
		$nonpersistableObject = new $object_name();
		$nonpersistableObject->id = $id;
		
		if (method_exists($nonpersistableObject, "initialize")) {
			$nonpersistableObject->initialize();
		}
		
		return $nonpersistableObject;
	}
	function isPersistableObject($object) {
		if (is_string($object)) {
			if (property_exists($object, "id")) return true;
		} else if (is_object($object)) {
			if (property_exists(get_class($object), "id")) return true;
		}
		
		return false;
	}
	function getFieldsByRelationType($object, $relationshipType) {
		
		
		$filtered = array();
		
		$reflection = new ReflectionClass(get_class($object));
		$classvars = $reflection->getDefaultProperties();
		
		foreach($classvars as $key => $value) {
			if (is_object($value) || is_array($value)) {
				if ($this->getRelationshipType($object, $key) == $relationshipType) array_push($filtered, $key);
			}
		}
		
		return $filtered;
	}
	function loadReferencedObjects($stdClass, $object_name, $excludes, $db_scope = null) {
		$referencedObjects = array();
		
		$object = new $object_name();
		
		$reflection = new ReflectionClass($object_name);
		$classvars = $reflection->getDefaultProperties();
		
		foreach($classvars as $key => $value) {
			$rp = new ReflectionProperty($object,$key);
				
			if ($this->isObjectReference($key) && !in_array($key, $excludes)) {
				$relationshipType = $this->getRelationshipType($object, $key);
				
				if ($relationshipType == "toOne") {
					$idFieldname = lcfirst($key) . "ID";
					
					$refObject = $this->getById($key, $stdClass->$idFieldname, true, array(), $db_scope);
					if (isset($refObject)) {
						if ($refObject->id == null) {
							$refObject = null;
						}
						$referencedObjects[$key] = $refObject;
					}
				} else if ($relationshipType == "toOneFromRecursive") {
					$idFieldname = lcfirst($key) . "ID";
					$objectFieldname = str_ireplace("Incoming", "", $key);
					
					$exclusions = $this->getFieldsByRelationType(new $objectFieldname(), "toManyRecursive");
					
					$refObject = $this->getById($objectFieldname, $stdClass->$idFieldname, true, $exclusions);
						
					if ($refObject->id == null) {
						$refObject = null;
					}
					$referencedObjects[$key] = $refObject;
				} else if ($relationshipType == "toMany") {
					$idFieldname = lcfirst($this->getNameWithoutNamespace($object_name)) . "ID";
					
					$refObjectsTotalAmount = $this->getTotalAmount($this->singularize($key));
					
					$refObjectName = $this->singularize($key);
					
					if (class_exists($refObjectName)) {
						if ($refObjectsTotalAmount > 15) {
							$refObject = new $refObjectName();
							$ormRequest = new ORM_Request($refObjectName, array($idFieldname=> $stdClass->id));
							
							$ormRequest->order = $refObject->getDefaultOrder();
							
							$refObjects = $this->getByNamedFieldValues($ormRequest);
						}
					} else {
						$ns = $this->getNamespaceByObjectName($object_name);
						if ($ns) {
							$refObjectName = "\\" . $ns . "\\" . $refObjectName;
						}
						
						$refObject = new $refObjectName();
						
						$ormRequest = new ORM_Request($refObjectName, array($idFieldname=> $stdClass->id));
						$ormRequest->order = $refObject->getDefaultOrder();
						
						$refObjects = $this->getByNamedFieldValues($ormRequest);
					}
					
					$manyObjects = array();
				
					if (isset($refObjects)) {
						foreach($refObjects as $refObjectItem) {
							$pulled = $this->getById(get_class($refObjectItem), $refObjectItem->id, true, array($object_name));
							
							array_push($manyObjects, $pulled);
						}
					}
					
					$referencedObjects[$key] = $manyObjects;
				} else if ($relationshipType == "toObservations") {
					$referencedObjects[$key] = null;
				} else if ($relationshipType == "toManyRecursive") {
					$idFieldname = lcfirst("Outgoing" . $object_name) . "ID";
					
					$ormRequest = new ORM_Request($this->singularize($key), array($idFieldname=> $stdClass->id));
					
					$refObjects = $this->getByNamedFieldValues($ormRequest);
					
					$manyObjects = array();
					foreach($refObjects as $refObjectItem) {
						$pulled = $this->getById(get_class($refObjectItem), $refObjectItem->id, true, array("Outgoing" . $object_name));
						array_push($manyObjects, $pulled);
					}
					$referencedObjects[$key] = $manyObjects;
				}
			}
		}
		
		return $referencedObjects;
	}
	function getRelationshipType($object, $key) {
		$singular = $this->singularize($key);
		$plural = $this->pluralize($key);
		
		//echo $key . "; "  . $singular . "; " . $plural . "; " . get_class($object) . "\n";
		if ($key == $singular && property_exists($object, $key)) {
			if (substr($key, 0, 8) == "Incoming") {
				$relationshipType = "toOneFromRecursive";
			} else {
				$relationshipType = "toOne";
			}
		} else if ($key == $plural && property_exists($object, $key)) {
			if (substr_count($key, get_class($object)) == 2) {
				$relationshipType = "toManyRecursive";
			} else if (substr($key, -12, 11) == "Observation") {
				$relationshipType = "toObservations";
			} else {
				$relationshipType = "toMany";
			}
		}
		
		return $relationshipType;
	}
	function getOntologyClassName($object = null) {
		if ($object) {
			$OntologyClassname = get_class($object);
			
			return $OntologyClassname;
		} else {
			$declaredClasses = get_declared_classes();
			
			$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
			
			$levels = explode ( "/", $url_parsed ['path'] );
			
			$last = end($levels);
			
			if(is_numeric($last)) $last = $levels[count($levels)-2];
			
			//print_r($declaredClasses);
			
			foreach($declaredClasses as $item) {
				if (strtolower($item) === $this->singularize(strtolower($last))) {
					return $item;
				}
			}
			
			foreach($declaredClasses as $item) {
				if (stripos($item, $this->singularize($last)) !== false) {
					$pre_part = str_ireplace($this->singularize($last), "", $item);
					
					//echo $item . "\n";
					
					//echo $url_parsed ['path'] . " ; " . $pre_part . "\n";
					
					if (stripos($url_parsed ['path'], $pre_part) !== false) {
						//echo "found\n";
						return $item;
					}
				}
			}
		}
		
		return null;
	}
	function getOntologyName($object = null) {
		if (is_object($object)) {
			$object_name = strtolower(get_class($object));
		} else {
			$object_name = $object;
		}
		
		if ($object_name === "rest") {
			$OntologyClassname = $object->getOntologyClassName();
			$object_name = $OntologyClassname;
	
			$object_name = $this->resolveClassName($object_name);
		}
	
		if (!class_exists($object_name)) return null;
		
		$helloReflection = new ReflectionClass($object_name);
		$filename = $helloReflection->getFilename();
	
		if (strpos($filename, "\\") !== false) {
			$exp_filename = explode("\\", $filename);
		} else if (strpos($filename, "/") !== false) {
			$exp_filename = explode("/", $filename);
		}
		
		$ontologyName = $exp_filename[count($exp_filename)-2];
		
		return $ontologyName;
	}
	function getOntologyScope($object_name) {
	    $rest = REST::getInstance();
	    
		if (is_object($object_name)) {
			$object_name = get_class($object_name);
		}
		
		if (class_exists($object_name) && $object_name !== "stdClass") {
			$reflection = new ReflectionClass($object_name);
			$directory = dirname($reflection->getFileName());
			
			if (strpos($directory, "\\") !== false) {
				$directoryArray = explode("\\", $directory);
			} else if (strpos($directory, "/") !== false) {
				$directoryArray = explode("/", $directory);
			}
			
			$scope = end($directoryArray);
		} else {
			if (class_exists("KM")) {
				$km = new KM();
				$km->orm = $rest->orm;
				
				$ontologyClass = $km->getOntologyClassByName($object_name, true);
				
				if (isset($ontologyClass)) {
					if ($ontologyClass->Ontology) {
						$scope = $ontologyClass->Ontology->name;
					}
				} else {
					$scope = $this->getScopeName();
				}
			} else {
				$scope = $this->getScopeName();
			}
		}
		
		return strtolower($scope);
	}
	function prepareArray($entities, $start, $stackSize, $inclusiveModification = true) {
		$array = array();
	
	
		$entities = array_slice ($entities, $start, $stackSize);
	
		foreach($entities as $entity) {
			/*if (isset($entity->name)) {
			 foreach($this->entityClasses as $class) {
			 $classname = $class->name;
			 if ($this->entities[0]->entityClassName === $class->name) {
			 array_push($array, $entity->name);
			 }
	
			 }
				}*/
				
			foreach($this->entityProperties as $prop) {
				$propname = $prop->name;
	
				array_push($array, $entity->$propname);
			}
				
			if ($inclusiveModification) {
				array_push($array, $entity->createdBy);
				array_push($array, $entity->createdAt);
				array_push($array, $entity->updatedBy);
				array_push($array, $entity->updatedAt);
			}
				
				
		}
	
		//print_r($array);
	
		return $array;
	}
	function prepareFields($entities, $inclusiveModification = true) {
		$fields = array();
	
		foreach($this->entityClasses as $class) {
			if (isset($entities[0]->name)) {
				if ($entities[0]->entityClassName === $class->name) {
					array_push($fields, "name");
				}
			}
		}
	
		//print_r($this->entityProperties);
	
		foreach($this->entityProperties as $prop) {
			if (!in_array($prop->name, $fields)) {
				array_push($fields, $prop->name);
			}
		}
	
		if ($inclusiveModification) {
			array_push($fields, "createdBy");
			array_push($fields, "createdAt");
			array_push($fields, "updatedBy");
			array_push($fields, "updatedAt");
		}
	
		return $fields;
	}
}
?>