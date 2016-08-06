<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/Integrity.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ObjectHelper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ConnectionManager.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/QueryBuilder.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/Loader.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORMConverter.php");

trait ORM {
	use Integrity, ObjectHelper, ConnectionManager, QueryBuilder, Loader, ORMConverter;
	
	protected $debug = false;
	
	protected $saved = array();
	
	function ORM() {
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
	function getAllCountByName($object_name) {
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$db_scope = $this->getOntologyScope($object_name);
		
		$sql = "select COUNT(id) FROM " . $tableName;
		
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
	function getAllByQuery($sql, $object_name, $explicitFields = null) {
		$db_scope = $this->getOntologyScope($object_name);
		
		//echo "scope: " . $object_name . ": " . $db_scope .  "; getallbyquery-sql: " . $sql . "\n";
		if ($this->debug) echo "getAllByQuery-sql: " . $sql . "\n";
		
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
			
			$stmt->execute();
			$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
				
			
			$db = null;
				
			$objects = $this->convertStdClassesToObjects($objects, $object_name, $explicitFields);
			
			//print_r(array_slice($objects, 0, 10));
				
			return $objects;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getNextInsertId($object_name) {
		$db_scope = $this->getOntologyScope($object_name);
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$sql = "SELECT AUTO_INCREMENT
			FROM  INFORMATION_SCHEMA.TABLES
			WHERE TABLE_NAME   = '" . $tableName . "' AND TABLE_SCHEMA = '" . $this->getDatabaseName($db_scope) . "'";
		
		echo $sql . "\n";
		
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
	function getAllByName($object_name, $noPaging = false, $orderby = null, $limit = null, $explicitFields = null, $includingProtected = false) {
		$tableName = $this->getTableNameByObjectName($object_name, false);
		
		$db_scope = $this->getOntologyScope($object_name);
		
		$sql_paging = "";
		
		if (!$noPaging) $sql_paging = $this->getPaging($object_name, $orderby, $limit);
		
		$sql = "select * FROM " . $tableName . " " . $sql_paging;
		
		//echo "getallbyname-sql: " . $sql . "\n";
		
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
		
			$stmt->execute();
			$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			$db = null;
			
			$objects = $this->convertStdClassesToObjects($objects, $object_name, $explicitFields, $includingProtected);
			
			return $objects;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getAllByNameLight($object_name, $fields, $offset) {
		$tableName = $this->getTableNameByObjectName($object_name, false);
	
		$db_scope = $this->getOntologyScope($object_name);
	
		$sql_select_fields = implode(",", $fields );
		
		
		$sql = "select " . $sql_select_fields . " FROM " . $tableName . " WHERE id >= " . $offset;
	
		echo "getallbyname-sql: " . $sql . "\n";
	
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
	
			$stmt->execute();
			$objects = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
			$db = null;
				
			return $objects;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getByNamedId($object_name, $id, $eager = false, $referencedby = null, $overwrite_referencedby_name = null) {
		if (!$referencedby) $referencedby = $this;
	
		$db_scope = $this->getOntologyScope($object_name);
		
		if (!$referencedby_name = $this->isSelfRelation($object_name)) {
			$referencedby_name = $referencedby->getOntologyClassName();
			
			$referencedby_name = str_ireplace("Outgoing", "", str_ireplace("Incoming", "", $referencedby_name));
			//echo $referencedby_name . "\n";
			$referencing_field = $referencedby_name . "ID";
		} else {
			$referencing_field = "Outgoing" . $referencedby_name . "ID";
		}
		
		
		$sql = "select * FROM " . $this->getTableNameByObjectName($object_name);
		$sql .= " WHERE ";
		$sql .= lcfirst($referencing_field) . "=:" . $referencing_field;
	
		if ($this->debug) echo "getbynamedid-sql: " . $sql . " of " . $object_name . " with id: " . $id . "\n";
		
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
	
			$stmt->bindParam ( $referencing_field, $id );
	
			$stmt->execute();
			$objects = $stmt->fetchAll(PDO::FETCH_OBJ);
			
			
			$objects_converted = $this->convertStdClassesToObjects($objects, $object_name);
			
			$db = null;
				
			return $objects_converted;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getByNamedFieldValues($object_name, $fields, $values = null, $like = false, $paging = null, $eager = false, $noPaging = false, $cascades = null, $order = null, $limit = null) {
		//if (apc_exists($object_name)) return apc_fetch($object_name);
		
		$db_scope = $this->getOntologyScope($object_name);
		//echo "db-scope: " . $db_scope . "\n";
		
		$sql = "SELECT * FROM " . $this->getTableNameByObjectName($object_name);
		
		
		$sql_add = "";
		
		$a=0;
		if (count($fields) > 0) {
			foreach($fields as $field_name) {
				if ($field_name !== "order" && $field_name !== "sort_by" && $field_name !== "page" && $field_name !== "per_page" && (isset($this->$field_name) || isset($values))) {
					if ($a > 0) $sql_add .= " AND ";
					
					if ($like) {
						$sql_add .= $field_name . " LIKE :" . $field_name;
					} else if (substr($field_name, -2, 2) == "At") {
						if (strlen($values[array_search("sentAt", $fields)]) == 10) {
							$sql_add .= "DATEDIFF(" . $field_name . ",:" . $field_name . ") = 0";
						}
					} else {
						$sql_add .= $field_name . "=:" . $field_name;
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
		
		
		if ($this->debug) echo "getbynamedfield-sql: " . $sql . "\n";
		//echo "db-scope: " . $db_scope . "\n";
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
				foreach($objects as $objectIndex => $object) {
					if ($eager) {
						if (class_exists (get_class($object) . "_Generated")) {
							$classvars = get_class_vars ( get_class($object) . "_Generated" );
						} else {
							$classvars = get_class_vars ( get_class($object) );
						}
							
						$objectvars = get_object_vars ( $object );
						foreach($objectvars as $key => $value) {
								
							if (array_key_exists($key, $classvars)) {
								if ($nesting = $this->isNestedObjectsKey($key)) {
									if ($nesting == "one") {
										$nestedobjectid_name = lcfirst($key) . "ID";
										
										if (property_exists($key, "createdAt")) {
											$object->$key = $this->getById($key, $stdObjects[$objectIndex]->$nestedobjectid_name, true);
										} else {
											$key_object = new $key;
											$key_object->id = $stdObjects[$objectIndex]->$nestedobjectid_name;
											$key_object->initialize();
												
											$object->$key = $key_object;
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
				
					} else if ($cascades) {
						if (class_exists (get_class($object) . "_Generated")) {
							$classvars = get_class_vars ( get_class($object) . "_Generated" );
						} else {
							$classvars = get_class_vars ( get_class($object) );
						}
							
						$objectvars = get_object_vars ( $object );
						foreach($objectvars as $key => $value) {
							if (array_key_exists($key, $classvars) && in_array($key, $cascades)) {
								if ($nesting = $this->isNestedObjectsKey($key)) {
									if ($nesting == "one") {
										$nestedobjectid_name = lcfirst($key) . "ID";
							
										if (property_exists($key, "createdAt")) {
											$object->$key = $this->getById($key, $stdObjects[$objectIndex]->$nestedobjectid_name, true);
										} else {
											$key_object = new $key;
											$key_object->id = $stdObjects[$objectIndex]->$nestedobjectid_name;
											$key_object->initialize();
							
											$object->$key = $key_object;
										}
									}
								}
							} else {
								if (isset($cascades[$key])) {
									if ($nesting = $this->isNestedObjectsKey($key)) {
										if ($nesting == "one") {
											$nestedobjectid_name = lcfirst($key) . "ID";
												
											if (property_exists($key, "createdAt")) {
												if (count($cascades[$key]) == 0) {
													$object->$key = $this->getById($key, $stdObjects[$objectIndex]->$nestedobjectid_name, false);
												} else {
													$object->$key = $this->getById($key, $stdObjects[$objectIndex]->$nestedobjectid_name, true, $cascades[$key]);
												} 
											} else {
												$key_object = new $key;
												$key_object->id = $stdObjects[$objectIndex]->$nestedobjectid_name;
												$key_object->initialize();
													
												$object->$key = $key_object;
											}
										}
									}
								}
							}
						}
					}
				
				}
				
				
			} else {
				$objects = $stdObjects;
			}
			
				
			$db = null;
			
			/*if (!apc_exists($object_name)) {
				apc_store($object_name, $objects);
			}*/
			
			//if (!array_key_exists($object_name, $this->loaded)) $this->loaded[$object_name] = $objects;
			
			return $objects;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getById($object_name, $id, $eager = true, $cascades = null) {
		$object_name = $this->getPersistanceClassName($object_name);
		
		//if (apc_exists($object_name . "_" . $id)) return apc_fetch($object_name . "_" . $id);
		
		$db_scope = $this->getOntologyScope($object_name);
		
		//echo "db_scope: " . $db_scope . "\n";
		
		if (!$id) return null;
		
		//$object_name = str_replace("Incoming", "", str_replace("Outgoing", "", $object_name));
		
		
		$sql = "SELECT * FROM " . $this->getTableNameByObjectName($object_name) . " WHERE id=:id";
		if ($this->debug) echo "getbyid-sql: " . $sql . "(" . $object_name . "_" . $id . ")" . "\n";
		//echo "id: " . $id . "\n";
		try {
			$db = $this->openConnection($db_scope);
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
			
			$stmt->execute();
			
			//echo $object_name . "\n";
			if (!class_exists($object_name)) {
				//echo "fuck it\n";
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
											/*$nestedobjects = $this->getByNamedId($nesting, $object->id, false, $object, null);
									
											if (stripos($nesting, "Observation") === false) {
												$nestedobjects_populated = array();
												foreach($nestedobjects as $object_item) {
													$object_item = $this->getById($nesting, $object_item->id, true);
													array_push($nestedobjects_populated, $object_item);
												}
									
												$object->$key = $nestedobjects_populated;
											}*/
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
								} else {
									/*if ($nesting = $this->isNestedObjectsKey($key)) {
										echo $key . " - " . $nesting . "\n";
									}*/
									//echo $key . "\n";
								}
								
							} else {
								if ($nesting = $this->isNestedObjectsKey($key)) {
									if ($nesting === "one") {
										$nestedobjectid_name = lcfirst($key) . "ID";

										if (property_exists($key, "createdAt")) {
											if (!isset($object->loadingMode[$key])) {
												//echo $key . "\n";
												//print_r($object);
												if (isset($object->$nestedobjectid_name)) {
													$keyValue = $this->getById($key, $object->$nestedobjectid_name, true);
													$object->$key = $keyValue;
												}
											}
										} else {
											if (isset($object->$nestedobjectid_name)) {
												$key_object = new $key;
												$key_object->id = $object->$nestedobjectid_name;
												
												if (method_exists($key_object, "initialize")) {
													$key_object->initialize();
												}
								
												$object->$key = $key_object;
											}
										}
								
									} else {
										//echo "key: " . $key . "\n";
											
										//echo $nesting . "\n";
										$nestedobjects = $this->getByNamedId($nesting, $object->id, false, $object, null);
										
										if (stripos($nesting, "Observation") === false) {
											$nestedobjects_populated = array();
										
											if (isset($nestedobjects)) {
												foreach($nestedobjects as $object_item) {
													//echo $nesting . "\n";
													$object_item = $this->getById($nesting, $object_item->id, false, $object_item->getCascades());
													array_push($nestedobjects_populated, $object_item);
												}
										
												if (in_array($key, array("Financials", "Quotes"))) {
													if (isset($nestedobjects_populated[0])) $object->$key = $nestedobjects_populated[0];
												} else {
													$object->$key = $nestedobjects_populated;
												}
												
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
							
						} else {
							if ($nesting = $this->isNestedObjectsKey($key)) {
								//echo "nesting: " . $nesting . "\n";
								if ($nesting === "one" && substr($key, 0, 8) !== "Outgoing" && $key !== "OntologyClass" && $key !== "Entity") {
									$nestedobjectid_name = lcfirst($key) . "ID";
										
									if (property_exists($key, "createdAt")) {
										if (!isset($object->loadingMode[$key])) {
											//echo $key . "\n";
											//print_r($object);
											$object->$key = $this->getById($key, $object->$nestedobjectid_name, false);
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
									if ($key === "Indicators") {
										//echo $nesting . "\n";
										//echo get_class($object) . "; " . $object->id . "\n";
										//$nestedobjects = $this->getByNamedId($nesting, $object->id, false, $object, null);
										/* 	
										if (stripos($nesting, "Observation") === false) {
											$nestedobjects_populated = array();
											foreach($nestedobjects as $object_item) {
												$object_item = $this->getById($nesting, $object_item->id, false);
												array_push($nestedobjects_populated, $object_item);
											}
												
											$object->$key = $nestedobjects_populated;
										}*/
									}
								}
							}
						}
						
						
					}
				}
				foreach($objectvars as $key => $value) {
					if (!array_key_exists($key, $classvars)) {
						//echo "key-to-be-removed: " . $key . "\n";
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
							
							//$result = $this->getById($ocRelation['forward']->IncomingOntologyClass->name);
						}
						
							
						//print_r($ocRelation);
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
		
		
		/*if (!apc_exists($object_name . "_" . $id)) {
			apc_store($object_name . "_" . $id, $object);
		}*/
		
		return $object;
	}
	function deleteById($object_name, $id) {
		if (!$id) return null;
		
		$sql = "DELETE FROM " . $this->pluralize(strtolower($object_name)) . " WHERE id=:id";

		$db_scope = $this->getOntologyScope($object_name);
		
		try {
			$db = $this->openConnection ($db_scope);
			
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
			$stmt->execute();
		
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function setRefererOnNestedObject($object, $direction = null) {
		$classname_referer = $this->getOntologyClassName();
		$classname_nested = $object->getOntologyClassName();
		
		if ($direction) {
			$dbfieldname = $this->getDBFieldName($classname_nested, strtolower($classname_referer) . "_" . $direction . "_id");
		} else {
			$dbfieldname = $this->getDBFieldName($classname_nested, strtolower($classname_referer)) . "ID";
		}
		
		$fieldname = strtolower($classname_referer) . "ID";
		$object->$fieldname = $this->id;
		
		return $object;
	}
	function isOneToOneObject($name) {
		if (class_exists($name)) return true;
		
		return false;
	}
	function saveOneToManyField($key, $value, $referer) {
		$referer_name = get_class($referer);
		
		$referer_objectVars = get_object_vars($referer);
		
		if (is_array($value)) {
			foreach($referer_objectVars as $referer_key => $referer_value) {
				if ($referer_key != "id") unset($referer->$referer_key);
					
			}
			
			foreach($value as $object_item) {
				$persistables = array();
				$persistables[lcfirst($referer_name) . "ID"] = $referer->id;
					
				$object_item->save($persistables);
			}
		} else {
			//print_r($referer);
			$legacy_objects = $this->getByNamedId($this->singularize($key), $referer->id, $referer);
				
			foreach($legacy_objects as $object_item) {
				$object_item->$referer_name = null;
				$object_item->save();
			}
		}
		
				
		return $value;
	}
	function saveManyToOneField($key, $value, $referer, $saved = array()) {
		$referer_name = get_class($referer);
	
		$referer_objectVars = get_object_vars($referer);
	
		if (is_array($value)) {
			foreach($referer_objectVars as $referer_key => $referer_value) {
				if ($referer_key !== "id") unset($referer->$referer_key);
					
			}
				
			foreach($value as $object_item) {
				$outgoing_referer_name = "Outgoing" . $referer_name;
				$incoming_referer_name = "Incoming" . $referer_name;
				if (isset($object_item->$outgoing_referer_name)) {
					$object_item->$outgoing_referer_name = $referer;
				} else {
					$object_item->$referer_name = $referer;
				}
	
				$object_item->save(null, array(), array(), $saved);
			}
		}
	
		return $value;
	}
	function saveManyToManyField($key, $value, $referer) {
		$referer_name = get_class($referer);
		
		$referer_objectVars = get_object_vars($referer);
		
		
		if (is_array($value)) {
			foreach($value as $object_item) {
				$outgoing_referer_name = "Outgoing" . $referer_name;
				$incoming_referer_name = "Incoming" . $referer_name;
				if (isset($object_item->$outgoing_referer_name)) {
					$object_item->$outgoing_referer_name = $referer;
				} else {
					$object_item->$referer_name = $referer;
				}
				
				if (get_class($object_item) === "RelationOntologyClassOntologyClass") {
					unset($object_item->OutgoingOntologyClass->RelationOntologyClassOntologyClasses);
				}
				
				if (get_class($object_item) === "RelationOntologyClassOntologyProperty") {
					unset($object_item->OntologyClass->RelationOntologyClassOntologyProperties);
				}
				//echo get_class($object_item) . "\n";
				//echo $key . "\n";
				
				$object_item->save(null, array(), array());
			}
		}
	
	
		return $value;
	}
	function savePersistableField($stmt, $key, $value, $unprotectables = array()) {
		//if (get_class($this) !== "Request") echo "key: " . $key . "\n";
		
		
		if (property_exists($this, $key)) {
			$rp = new ReflectionProperty($this,$key);
			if ($rp->isProtected()) {
				if (!in_array($key, $unprotectables) && !in_array($key, array("createdAt", "createdBy", "updatedBy", "updatedAt"))) {
					return null;
				}
			}
		}
		
		if ($value === "[]") $value = "";
		
		if (is_bool($value) || (substr($key, 0, 2) == "is" && ctype_upper(substr($key, 2, 1)) && strlen($key) > 2)) {
			$value = (bool)$value;
		}
		
		if ($value === null) {
			if(substr($key, -2, 2) == "ID") {
				$idNamed = ucfirst(substr($key, 0, -2));
				
				$nullIDObject = $this->$idNamed;
				
				//echo "idnamed: " . $idNamed . "\n";
				if ($this->cascade && is_object($nullIDObject) && !in_array($idNamed, array("Entity", "Language", "Owner", "Type"))) {
					$nullIDObject->save(null, array(), array());
				}
				//echo "nullobject:\n";
				//print_r($nullIDObject);
				
				if (isset($nullIDObject->id)) {
					//echo "isset\n";
					$stmt->bindParam ( $key, $nullIDObject->id );
					
					$this->$key = $nullIDObject->id;
				} else {
					//echo "isset\n";
					//echo $key . " issnotset\n";
					$stmt->bindValue(':' . $key , null, PDO::PARAM_NULL);
				}
			} else {
				$stmt->bindValue(':' . $key , null, PDO::PARAM_NULL);
			}
		} else {
			if(substr($key, -2, 2) == "ID") {
				$idNamed = ucfirst(substr($key, 0, -2));
			
				$nullIDObject = $this->$idNamed;
				
				if (is_object($this->cascade && $nullIDObject) && !in_array($idNamed, array("Entity", "Language", "Owner", "Type"))) {
					//echo "save-object\n";
					$nullIDObject->save(null, array(), array());
				}
					
				if (isset($nullIDObject->id)) {
					//echo "isset\n";
					//echo "key1: " . $key . "\n";
					$stmt->bindParam ( $key, $nullIDObject->id );
				
					$this->$key = $nullIDObject->id;
				} else {
					if ($key === "password" && strlen($value) < 5) {
							
					} else {
						//echo "isset\n";
						$stmt->bindParam ( $key, $value );
					}
					
				}
			
				
			} else {
				if ($key === "password" && strlen($value) < 5) {
					
				} else {
					if (is_string($value) || is_numeric($value)) {
						//echo "isset\n";
						//echo "key2: " . $key . "\n";
						$stmt->bindParam ( $key, $value );
					} else if (is_bool($value)) {
						$stmt->bindParam ( $key, $value );
					}
				}
				
			}
		}
	}
	function setModificationInfo($UserID) {
		$class_name = $this->getOntologyClassName();
		
		//if ($class_name !== "Request") echo "class_name: " . $class_name . "\n";
		
		if ($this->isNew()) {
			//if ($class_name !== "Request") echo "userid: " . $UserID . "; " . "was new\n";
			if (isset($UserID)) $this->createdBy = $UserID;
			$this->createdAt = date('Y-m-d H:i:s', time());
		}

		if (isset($UserID)) $this->updatedBy = $UserID;
		$this->updatedAt = date('Y-m-d H:i:s', time());
	}
	function tableExists() {
		$class_name = get_class($this);
		
		if ($class_name === "ImportEntity") {
			$tableName = $this->getTableNameByObjectName($this->entityClassName, false);
			
			$db_scope = strtolower($this->entityOntologyName);
		} else {
			$tableName = $this->getTableNameByObjectName($class_name);
			
			$db_scope = $this->getOntologyScope($this);
			
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
	function countByObjectAndId($object_name, $whereKeyValue) {
		$tableName = $this->getTableNameByObjectName($object_name, false);
		$where = "";
		
		$db_scope = $this->getOntologyScope($object_name);
		
		$sql = "select COUNT(id) FROM " . $tableName . "";
		
		foreach($whereKeyValue as $key => $value) {
			$where .= " " . $key . " = " . $value;
		}
		
		if ($where !== "") {
			$sql .= " WHERE" . $where;
			
		}
		
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
	function createTableByOntologyClass($OntologyClass) {
		$class_name = get_class($this);
		
		$codegen_database = new Generator_DataBase();
		$sqlCreateTable = $codegen_database->generate_Create_Table($OntologyClass);
		
		if ($class_name === "ImportEntity") {
			$db_scope = strtolower($this->entityOntologyName);
		} else {
			$db_scope = $this->getOntologyScope($this);
		}
		
		$db = $this->openConnection ($db_scope);
		
		$stmt = $db->prepare ( $sqlCreateTable );
		$stmt->execute ();
	}
	function save($persistables = null, $unprotectables = array(), $nosaves = array(), $ediReferrer = false, $excludeAllButUnProtectables = false) {
		$class_name = get_class($this);
		
		if (!$this->isNew()) {
			//if (apc_exists("saved_" . $class_name . "_" . $this->id)) return $this;
		}
		
		
		if (stripos($class_name, "user") !== false && isset($this->recoveryToken)) {
		} else if (stripos($class_name, "request") !== false) {
		} else {
			if (!$UserID = isLogged()) {
				if (stripos($class_name, "user") !== false && $this->isNew()) {
				} else {
					//echo "save for " . $class_name . " not possible; not logged\n";
					if (!$ediReferrer) {
						return null;
					}
				}
				
			}
		}
		
		if ($UserID = isLogged()) {
			$this->setModificationInfo($UserID);
		} else if ($ediReferrer) {
			$this->setModificationInfo(23);
		}
		
		if ($class_name === "ImportEntity") {
			$tableName = $this->getTableNameByObjectName($this->entityClassName, false);
			
			$db_scope = strtolower($this->entityOntologyName);
		} else {
			$tableName = $this->getTableNameByObjectName(strtolower( str_ireplace("_Generated", "", $class_name)));
			
			$db_scope = $this->getOntologyScope($this);
			
		}
		
		if (!$persistables) $persistables = $this->getPersistables($unprotectables, $excludeAllButUnProtectables);
		
		//if ($class_name !== "Request") print_r($persistables);
		
		$onetomanyFields = $this->getOneToManyFields();
		$manytomanyFields = $this->getManyToManyFields();
		$manytooneFields = $this->getManyToOneFields();
		
		
		//echo "dbscope: " . $db_scope . "\n";
		//if ($class_name !== "Request") echo "before save of " . $class_name . " id: " . $this->id . "\n";
		
		if ($this->isNew()) {
			//echo "is new \n";
			
			if ($this->isEmpty($persistables)) return null;
			
			if (count($manytooneFields) > 0) {
				foreach($manytooneFields as $manytooneField) {
					if (is_object($manytooneField) && get_class($manytooneField) !== "Entity" && get_class($manytooneField) !== "Language" && get_class($manytooneField) !== "Type") {
						if ($manytooneField->isNew()) {
							$manytooneField->Ontology = $this->OntologyClass->Ontology;
							$manytooneField->save(null, array(), array());
							
							if (get_class($manytooneField) === "OntologyProperty") {
								$language = new Language();
								$language->id = 0;
								$language->initialize();
								
								$lexeme = new Lexeme();
								$lexeme->name = $manytooneField->name;
								$lexeme->Language = $language;
								$lexeme->OntologyProperty = $manytooneField;
								$lexeme->save(null, array(), array(), $saved);
								
								$word = new Word();
								$word->name = $manytooneField->name;
								$word->Language = $language;
								$word->Lexeme = $lexeme;
								$word->save(null, array(), array(), $saved);
								
								array_push($lexeme->Words, $word);
								
								array_push($manytooneField->Lexemes, $lexeme);
							}
							
							
							
							
						}
					}
				}
			}
			
			if ($doublicate = $this->checkUniqueConstraints()) {
				return $doublicate;
			}

			$insert_fields = array();
			$insert_values = array();
			foreach($persistables as $key => $value) {
				array_push($insert_fields, "`" . $key . "`");
				array_push($insert_values, ":" . $key);
			}
				
			$fields = implode($insert_fields, ", ");
			$values = implode($insert_values, ", ");
			//echo "save\n";
			$sql = "INSERT INTO " . $tableName . " (" . $fields . ") VALUES (" . $values . ")";
					
			if ($class_name !== "Request") echo $sql . "\n";
			try {
				$db = $this->openConnection ($db_scope);
				$stmt = $db->prepare ( $sql );
			
				//echo $class_name . "\n";
				
				if ($class_name === "User") {
					$persistables['createdBy'] = $db->lastInsertId();
					$persistables['createdAt'] = date('Y-m-d H:i:s', time());
					$persistables['updatedBy'] = $db->lastInsertId();
					$persistables['updatedAt'] = date('Y-m-d H:i:s', time());
				}
				
				foreach($persistables as $key => $value) {
					//echo "key: " . $key . "\n";
					$this->savePersistableField($stmt, $key, $value, $unprotectables);
				}
				
				
				
				$stmt->execute ();
			
				$this->id = $db->lastInsertId();
				
				//echo "id: " . $this->id . "\n";
				
				$db = null;
			} catch ( PDOException $e ) {
				if ($e->errorInfo[0] === "23000") {
					echo '{"error":{"message": "' . $e->errorInfo[2] . '"}}';
				} else {
					echo '{"error":{"text": "' . $e->getMessage () . '"}}';
				}
				
				return null;
			}
		} else {
			$keys = array();
			foreach($persistables as $key => $value) {
				array_push($keys, $key . " = :" . $key);
			}
				
			$setters = implode($keys, ", ");
				
			$sql = "UPDATE " . $tableName . " SET " . $setters . " WHERE id=:id";
				
			if ($this->debug) echo $sql . "\n";
			//echo $sql . "\n";
			//echo $db_scope . "\n";
			try {
				$db = $this->openConnection ($db_scope);
				$stmt = $db->prepare ( $sql );
			
				$stmt->bindParam ( "id", $this->id );
				//print_r($persistables);
			
				foreach($persistables as $key => $value) {
					$this->savePersistableField($stmt, $key, $value, $unprotectables);
				}
					
				$stmt->execute ();
					
				$db = null;
					
			} catch ( PDOException $e ) {
				if ($e->errorInfo[0] === "HY093") {
					echo '{"error":{"message": "' . $e->getMessage () . '"}}';
				} else {
					echo '{"error":{"text": "' . $e->getMessage () . '"}}';
				}
				
				return null;
			}
			
		}
		
		if ($this->cascade) {
			foreach($onetomanyFields as $key => $value) {
				//echo "o2m-key: " . $key . "\n";
				//$this->$key = $this->saveOneToManyField($key, $value, $this);
			}
			foreach($manytooneFields as $key => $value) {
				//echo "m2o-key: " . $key . "\n";
				$this->$key = $this->saveManyToOneField($key, $value, $this);
			}
			
			foreach($manytomanyFields as $key => $value) {
				//echo "m2m-key: " . $key . "\n";
				$this->$key = $this->saveManyToManyField($key, $value, $this);
			}
		}
		
		/*if (!apc_exists("saved_" . $class_name . "_" . $this->id)) {
			apc_store("saved_" . $class_name . "_" . $this->id, true);
		}*/
		
		return $this;
	}
	function bulkInsert_ImportEntities($entities, $truncate = false, $start = 0, $stackSize = 8000, $ignoreConstraints = true) {
		try {
			if (isset($entities[0])) {
				$db_scope = strtolower($entities[0]->entityOntologyName);
			} else {
				
			}
			
			$db = $this->openConnection($db_scope);
			
			$db->beginTransaction(); // also helps speed up your inserts.
			
			if ($db_scope === "search") {
				$fields = $this->prepareFields(false);
					
				$array = $this->prepareArray($start, $stackSize, false);
			} else {
				$fields = $this->prepareFields();
					
				$array = $this->prepareArray($start, $stackSize);
			}
			
				
			//print_r($array);
			
			if ($db_scope === "search" && $this->entities[0]->entityClassName === "index") {
				$tableName = "index";
			} else {
				$tableName = $this->getTableNameByObjectName($this->entities[0]->entityClassName, false);
			}
			
			if ($truncate) $db->query("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE `" . $tableName . "` SET FOREIGN_KEY_CHECKS = 1;");
				
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
	function bulkInsert_Array($array, $table, $fields) {
		$rest = new REST();
	
		//if (!$UserID = isLogged()) return null;
	
		try {
			$db_scope = strtolower($this->getOntologyScope($rest->singularize($table)));
				
			$db = $this->openConnection($db_scope);
	
			$db->beginTransaction(); // also helps speed up your inserts.
			$insert_values = array();
	
			$qm = '('  . $this->placeholders('?', count($fields)) . ')';
	
			$question_marks = array_fill(0, count($array) / count($fields), $qm);
	
			$sql = "INSERT IGNORE INTO " . $rest->pluralize($table) . " (" . implode(",", $fields ) . ") VALUES " . implode(',', $question_marks);
	
			
			$stmt = $db->prepare ($sql);
	
			$stmt->execute($array);
	
			$db->commit();
			$db = null;
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
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
	
	function getNestedObjectName($object_name) {
		if (substr($object_name, 0, 9) == "relation_") {
			$object_name_exp = explode("_", $object_name);
			if (class_exists($object_name_exp[2])) return $object_name_exp[2];
				
		}
		
		return null;
	}
	function isManyToManyObject($object_name) {
		if (substr($object_name, 0, 8) !== "relation") return false;
		
		$class_name = strtolower(get_class($this));
		if ($class_name == "rest") $class_name = $this->getOntologyClassName();
		
		$class_name_prepared = str_replace("Relation", "", $this->resolveClassName($class_name));
		$class_name_abbrevated = $this->abbrevate($class_name_prepared);
		
		$property_name = str_replace($class_name . "_", "", $object_name);
		
		$onetomanyobject = $property_name;
		
		$onetomanyobject = $this->singularize($onetomanyobject);
		
		$onetomanyobject = $this->deabbrevate($onetomanyobject);
		
		$classname_to_check = str_ireplace("relation_" . strtolower($class_name_abbrevated) . "_", "", $onetomanyobject );
		
		if (class_exists($onetomanyobject)) return $onetomanyobject;
		if (class_exists($classname_to_check)) return $classname_to_check;
		
		return false;
	}
	function isOneToManyObject($object_name) {
		if ($this->isManyToManyObject($object_name)) return false;
		
		$OntologyClassname = strtolower(get_class($this));
		if ($OntologyClassname == "rest") $OntologyClassname = $this->getOntologyClassName();
		
		$property_name = str_replace($OntologyClassname . "_", "", $object_name);

		if ($property_name == $this->singularize($property_name)) return false;
		
		$onetomanyobject = $this->singularize($property_name);
	
		if (class_exists($onetomanyobject)) return $onetomanyobject;
	
		return false;
	}
	function getTotalAmount($object_name) {
		$db_scope = $this->getOntologyScope($object_name);
		
		$db = $this->openConnection($db_scope);
		$sql = "select COUNT(*) as totalAmount FROM " . $this->pluralize($object_name);
		$stmt = $db->prepare ( $sql );
	
		$stmt->execute ();
		$amount = $stmt->fetchAll ( PDO::FETCH_OBJ );
	
		return intval($amount[0]->totalAmount);
	}
	function getDataServiceEntityByObject($object) {
		$className = get_class($object);
		
		$km = new KM();
		$ontologyClass = $km->getOntologyClassByName($className);
		
		$dsEntity = $this->getByNamedFieldValues("DataServiceEntity", array("ontologyClassID", "internalKey"), array($ontologyClass->id, $object->id));
		
		return $dsEntity;
	}
}
?>