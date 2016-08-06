<?php
trait ObjectHelper {
	function ObjectHelper() {
	}
	function isNew() {
		if (isset($this->id) && $this->id !== -99) return false;
		return true;
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
		if (class_exists($objectName)) {
			if (substr(get_parent_class($objectName), -10, 10) === "_Generated") {
				$persistanceClassName = $objectName;
			} else {
				if (substr(get_parent_class(get_parent_class($objectName)), -10, 10) === "_Generated") {
					//echo "pers-classname-getter: " . get_parent_class($objectName) . "\n";
					$persistanceClassName = get_parent_class($objectName);
				} else {
					$persistanceClassName = $objectName;
				}
			}
		} else {
			$persistanceClassName = $objectName;
		}
		
		return $persistanceClassName;
	}
	function getTableNameByObjectName($objectName, $checkExistance = true) {
		//echo "tablenamegetter: " . $objectName . "\n";
		if ($objectName . "_Generated" !== get_parent_class($objectName)) {
			$checkExistance = true;
		}
		
		if ($checkExistance) {
			$persistanceClassName = $this->getPersistanceClassName($objectName);
			//echo "persistanceclassname: " . $persistanceClassName . "\n";
			
		} else {
			$persistanceClassName = $objectName;
		}
		
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
	function isNestedObjectsKey($key) {
		//echo "key: " . $key . "\n";
		if (class_exists($key) && substr($key, -11, 11) !== "Observation") {
			if (substr($key, 0, 1) == strtoupper(substr($key, 0, 1)) && !in_array($key, array("Financials", "Quotes", "RelationIndicatorImpactFunction"))) return "one";
		}
		$key_singularized = $this->singularize(strtolower($key));
		//echo "key: " . $key . " key singularized: " . $key_singularized . "\n";
		
		if (class_exists($key_singularized)) {
			if (substr(get_parent_class($key_singularized), -10, 10) == "_Generated") {
				return $key_singularized;
			} else {
				if (substr(get_parent_class(get_parent_class($key_singularized)), -10, 10) == "_Generated") {
					return $key_singularized;
				}
			}
			
		} else {
			if (class_exists($key_singularized . "_Generated")) {
				return $key_singularized;
			}
		}
		
		return false;
	}
	function getManyToOneFields() {
		$class_name = get_class($this);
		
		
		if (class_exists ($class_name . "_Generated")) {
			$classvars = get_class_vars ( $class_name . "_Generated" );
		} else {
			$classvars = get_class_vars ( $class_name );
		}
		
		$objectvars = get_object_vars ( $this );
		
		$manytooneFields = array();
		
		foreach($objectvars as $key => $value) {
			if (array_key_exists($key, $classvars)) {
				if ($nesting = $this->isNestedObjectsKey($key)) {
					if ($nesting == "one" && substr($key, 0, 8) !== "Relation") {
						$manytooneFields[$key] = $this->$key;
					}
				}
			}
		}
		
		return $manytooneFields;
	}
	function getManyToManyFields() {
		$class_name = get_class($this);
	
		
		if (class_exists ($class_name . "_Generated")) {
			$classvars = get_class_vars ( $class_name . "_Generated" );
		} else {
			$classvars = get_class_vars ( $class_name );
		}
		
		$objectvars = get_object_vars ( $this );
	
		$manytomanyFields = array();
	
		foreach($objectvars as $key => $value) {
			if (array_key_exists($key, $classvars)) {
				if ($nesting = $this->isNestedObjectsKey($key)) {
					if ($nesting !== "one" && substr($key, 0, 8) == "Relation") {
						$manytomanyFields[$key] = $this->$key;
					}
				}
			}
		}
	
		return $manytomanyFields;
	}
	function getOneToManyFields() {
		$class_name = get_class($this);
		
		if (class_exists ($class_name . "_Generated")) {
			$classvars = get_class_vars ( $class_name . "_Generated" );
		} else {
			$classvars = get_class_vars ( $class_name );
		}
		
		$objectvars = get_object_vars ( $this );
		
		$onetomanyFields = array();
		
		foreach($objectvars as $key => $value) {
			if (array_key_exists($key, $classvars)) {
				if ($nesting = $this->isNestedObjectsKey($key)) {
					if ($nesting !== "one" && substr($key, 0, 8) !== "Relation") {
						$onetomanyFields[$key] = $this->$key;
					}
				}
			}
		}
		
		return $onetomanyFields;
	}
	function getPersistables($unprotectables = array(), $excludeAllButUnProtectables = false) {
		$class_name = get_class($this);
	
		if (class_exists ($class_name . "_Generated")) {
			$classvars = get_class_vars ( $class_name . "_Generated" );
		} else {
			$classvars = get_class_vars ( $class_name );
		}
		
		
		foreach($classvars as $classvarKey => $classvarValue) {
			if (property_exists($this, $classvarKey)) {
				$rp = new ReflectionProperty($this,$classvarKey);
				
				if ($rp->isProtected()) {
					if (!in_array($classvarKey, $unprotectables) && !in_array($classvarKey, array("createdAt", "createdBy", "updatedBy", "updatedAt"))) {
						unset($classvars[$classvarKey]);
					}
				} else {
					if ($excludeAllButUnProtectables) {
						if (!in_array($classvarKey, $unprotectables) && !in_array($classvarKey, array("createdAt", "createdBy", "updatedBy", "updatedAt"))) {
							unset($classvars[$classvarKey]);
						}
					}
				}
			}
		}
		
		$reflection = new ReflectionClass("ConnectionManager");
		$classvars_connectionManager = $reflection->getdefaultProperties();
		
		$objectvars = get_object_vars ( $this );
		
		$classvars_modification = array();
		if (!$this->isNew()) {
			unset($classvars['createdBy']);
			unset($classvars['createdAt']);
		}
		
		if ($class_name === "Request") {
			unset($classvars['createdBy']);
			unset($classvars['createdAt']);
			unset($classvars['updatedBy']);
			unset($classvars['updatedAt']);
		}
		
		unset($classvars['saved']);
		unset($classvars['cascades']);
		unset($classvars['loaded']);
		unset($classvars['debug']);
		
		$persistables = array();
		
		foreach($objectvars as $key => $value) {
			if (array_key_exists($key, $classvars) && $key != "constraints_unique" && $key != "validationRules" && $key !== "ontologyName" && $key !== "encryptions" && $key !== "className" && !array_key_exists($key, $classvars_connectionManager)) {
				if ($nesting = $this->isNestedObjectsKey($key)) {
					//echo "getpers: " . $key . "\n";
					if ($nesting == "one") {
						if ($key === "User" && $class_name === "Request") {
							$persistables["userID"] = $this->userID;
						} else {
							if (isset($this->$key->id)) {
								//echo "key: " . $key . ": " . $this->$key->id . "\n";
								$persistables[lcfirst($key) . "ID"] = $this->$key->id;
							} else {
								if (isset($this->$key)) {
									if (is_object($this->$key) && $key !== "Language" && $key !== "Type") {
										$nestedObject = $this->convertStdClassToObject($this->$key, $key);
										$nestedObject->save();
											
										$persistables[lcfirst($key) . "ID"] = $nestedObject->id;
									}
							
								} else {
									$persistables[lcfirst($key) . "ID"] = null;
								}
							}
						}
					}
				} else if ($key != "id" && $key != "userID") {
					$persistables[$key] = $value;
				}
			}
		}
		
		return $persistables;
	}
	function getOntologyClassName() {
		$OntologyClassname = get_class($this);
		
		if (strtolower($OntologyClassname) !== "rest") return $OntologyClassname;
	
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
	
		//echo $url_parsed ['path'] . "\n";
		
		if (strpos($url_parsed ['path'], "localhost") !== false) {
			if (strpos($url_parsed ['path'], "/api/") !== false) {
				$OntologyClassname = $levels[3];
			} else {
				$OntologyClassname = $levels[1];
			}
			
		} else if (strpos($url_parsed ['path'], "/api/") !== false) {
			if (isset($levels[3])) {
				if ($levels[2] === "api") {
					$OntologyClassname = $levels[4];
				} else {
					$OntologyClassname = $levels[3];
				}
				
			} else {
				$OntologyClassname = $levels[2];
			}
			
		} else {
			$OntologyClassname = $levels[1];
		}
	
		if (!class_exists($OntologyClassname)) {
			$OntologyClassname = $this->singularize($OntologyClassname);
		}
		
		return $OntologyClassname;
	}
	function getOntologyName($object = null) {
		if (is_object($object)) {
			$object_name = strtolower(get_class($object));
		} else {
			$object_name = $object;
		}
		
		//echo "obj-name: " . $object_name . "\n";
		
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
		//echo "obj-name: " . $object_name . "\n";
		
		if (is_object($object_name)) $object_name = get_class($object_name);
		
		if (class_exists($object_name)) {
			$reflection = new ReflectionClass($object_name);
			$directory = dirname($reflection->getFileName());
			
			if (strpos($directory, "\\") !== false) {
				$directoryArray = explode("\\", $directory);
			} else if (strpos($directory, "/") !== false) {
				$directoryArray = explode("/", $directory);
			}
			
			$scope = end($directoryArray);
		} else {
			$km = new KM();
			
			//echo $object_name . "\n";
			$ontologyClass = $km->getOntologyClassByName($object_name, true);
			
			$scope = $ontologyClass->Ontology->name;
		}
		
		
		//echo "namespace of " . $object_name . "; " . ": " . end(explode("\\", $directory))  . "\n";
		
		/*
		if (is_object($object)) {
			$object_name = strtolower(get_class($object));
		} else {
			$object_name = $object;
		}
		
		if (!class_exists($object_name)) {
			$desc = "";
			if (!file_exists("../engulfing/")) {
				$desc = "../";
				if (!file_exists($desc . "../engulfing/")) {
					$desc .= "../";
				}
			}
			
			$km = new KM();
			
			$oClass = $km->getOntologyClassByName($object_name);
			
			if (!$oClass) {
				$oClass = $km->getOntologyClassByName($this->singularize($object_name));
				
				if ($oClass->ontologyID) {
					$ontology = $km->getOntologyById($oClass->ontologyID);
				}
			} else {
				if ($oClass->ontologyID) {
					$ontology = $km->getOntologyById($oClass->ontologyID);
				}
			}
			
			
			
			$ontologyName = $ontology->name;
		} else {
			$ontologyName = $this->getOntologyName($object);
		}
		
		*/
		
		return strtolower($scope);
	}
}
?>