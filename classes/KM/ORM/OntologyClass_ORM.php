<?php
trait OntologyClass_ORM {
	
	function OntologyClass_ORM() {
	}
	/*function getById($id) {
		$eager = false;
		$object_name = "OntologyClass";
		
		$db_scope = $this->getOntologyScope($object_name);
		
		if (!$id) return null;
		
		$object_name = str_replace("Incoming", "", str_replace("Outgoing", "", $object_name));
		
		
		$sql = "SELECT * FROM " . $this->getTableNameByObjectName($object_name) . " WHERE id=:id";
		echo "getbyid-sql: " . $sql . "\n";
		//echo "id: " . $id . "\n";
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
				
			$stmt->execute();
				
			if (!class_exists($object_name)) {
				$stmt->setFetchMode(PDO::FETCH_CLASS, 'Thing_Generated');
		
				$object = $stmt->fetch();
			} else {
				$stmt->setFetchMode(PDO::FETCH_CLASS, $object_name);
		
				$object = $stmt->fetch();
			}
				
			if (class_exists ($object_name . "_Generated")) {
				$classvars = get_class_vars ( $object_name . "_Generated" );
		
			} else if (class_exists(get_parent_class($object_name) . "_Generated")) {
				//echo "asdf";
				$classvars = get_class_vars ( get_parent_class($object_name) . "_Generated" );
			} else if (class_exists ($object_name)) {
				$classvars = get_class_vars ( $object_name );
			} else {
				$classvars = array();
		
				$km = new KM();
				$ontologyClass = $km->getOntologyClassByName($object_name);
		
				//echo "loading ontologyclass " . $object_name . "\n";
		
				$ontologyProperties = $ontologyClass->getOntologyProperties();
				foreach($ontologyProperties as $oProperty) {
					array_push($classvars, $oProperty->name);
				}
		
				$ontologyClasses = $ontologyClass->getIncomingOntologyClasses();
				foreach($ontologyClasses as $oClass) {
					array_push($classvars, $oClass->name);
				}
			}
				
			if (!$object) return null;
				
			if (class_exists($object_name) || class_exists ($object_name . "_Generated")) {
				$objectvars = get_object_vars ( $object );
				foreach($objectvars as $key => $value) {
					if (array_key_exists($key, $classvars)) {
						if ($eager) {
							if ($cascades) {
								if (in_array($key, $cascades)) {
									if ($nesting = $this->isNestedObjectsKey($key)) {
										if ($nesting === "one") {
											$nestedobjectid_name = lcfirst($key) . "ID";
												
											if (property_exists($key, "createdAt")) {
												if (!isset($object->loadingMode[$key])) {
													$object->$key = $this->getById($key, $object->$nestedobjectid_name, true);
												}
											} else {
												if (isset($object->$nestedobjectid_name)) {
													$key_object = new $key;
													$key_object->id = $object->$nestedobjectid_name;
													$key_object->initialize();
														
													$object->$key = $key_object;
												}
											}
												
										} else {
											}
									} else {
										if (substr($key, 0, 2) == "is" && ctype_upper($key{2})) {
											$object->$key = (boolean) $value;
										} else if (is_numeric($value)) {
											$object->$key = (float) $value;
										} else if ($key == "id") {
											$object->$key = (int) $value;
										} else if ($key == "createdAt" || $key == "updatedAt") {
											$datetime = strtotime($value);
											$object->$key = date("Y-m-dTH:i:sZ", $datetime);
										}
									}
								}
		
							} else {
								if ($nesting = $this->isNestedObjectsKey($key)) {
									if ($nesting === "one") {
										$nestedobjectid_name = lcfirst($key) . "ID";
		
										if (property_exists($key, "createdAt")) {
											if (!isset($object->loadingMode[$key])) {
												$keyValue = $this->getById($key, $object->$nestedobjectid_name, true);
												$object->$key = $keyValue;
											}
										} else {
											if (isset($object->$nestedobjectid_name)) {
												$key_object = new $key;
												$key_object->id = $object->$nestedobjectid_name;
												$key_object->initialize();
		
												$object->$key = $key_object;
											}
										}
		
									} else {
										$nestedobjects = $this->getByNamedId($nesting, $object->id, false, $object, null);
		
										if (stripos($nesting, "Observation") === false) {
											$nestedobjects_populated = array();
		
											if (isset($nestedobjects)) {
												foreach($nestedobjects as $object_item) {
													$object_item = $this->getById($nesting, $object_item->id, true, $object_item->getCascades());
													array_push($nestedobjects_populated, $object_item);
												}
		
												$object->$key = $nestedobjects_populated;
		
											}
		
										}
									}
								} else {
									if (substr($key, 0, 2) == "is" && ctype_upper($key{2})) {
										$object->$key = (boolean) $value;
									} else if (is_numeric($value)) {
										$object->$key = (float) $value;
									} else if ($key == "id") {
										$object->$key = (int) $value;
									} else if ($key == "createdAt" || $key == "updatedAt") {
										$datetime = strtotime($value);
										$object->$key = date("Y-m-dTH:i:sZ", $datetime);
									}
								}
							}
								
						}
		
		
					}
				}
				foreach($objectvars as $key => $value) {
					if (!array_key_exists($key, $classvars)) {
						unset($object->$key);
					}
				}
			} else {
				$ocRelations = $ontologyClass->getRelationOntologyClassOntologyClasses(true);
				foreach($ocRelations as $ocRelation) {
					if (isset($ocRelation['backward'])) {
						if($ocRelation['forward']->OntologyRelationType->name === "hasOne" && $ocRelation['backward']->OntologyRelationType->name === "hasMany") {
							$incomingClassName = $ocRelation['forward']->IncomingOntologyClass->name;
								
							$incomingClassNameID = lcfirst($incomingClassName) . "ID";
								
							$result = $this->getById($incomingClassName, $object->$incomingClassNameID);
								
							$object->$incomingClassName = $result;
							unset($object->$incomingClassNameID);
					
						}
		
							
					} else {
						if($ocRelation['forward']->OntologyRelationType->name === "hasOne") {
							$incomingClassName = $ocRelation['forward']->IncomingOntologyClass->name;
								
							$result = $this->getByNamedFieldValues($incomingClassName, array(lcfirst($object_name) . "ID"), array($object->id));
		
							if (isset($result[0])) {
								$object->$incomingClassName = $result[0];
							} else {
								$object->$incomingClassName = null;
							}
								
						}
					}
				}
			}
				
				
				
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
		
		if (method_exists($object, "valuate")) {
			$object->valuate();
		}
		
		return $object;
	}*/
}
?>