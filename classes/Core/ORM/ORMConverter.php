<?php
trait ORMConverter {
	function __construct() {
	}
	function convertStdClassToObject($stdClass, $object_name, $includingProtected = true, $explicitFields = null) {
		if (class_exists($object_name)) {
			$object = new $object_name;
		} else {
			$object = new stdClass();
		}
		
		
		if (is_array($stdClass)) {
			if (count($stdClass) > 0) {
				$stdClass = $stdClass[0];
			}
			
		}
		
		if (class_exists($object_name) && isset($stdClass)) {
			foreach($stdClass as $key => $value) {
				$corrected = $key;
				if (stripos($key, $object_name) !== false) {
					$corrected = str_ireplace($object_name . "_", "", $key);
				}
				
				if (property_exists($object, $corrected)) {
					$rp = new ReflectionProperty($object,$corrected);
					if (!$rp->isProtected()) {
						if (substr($corrected, 0, 2) == "is" && $this->starts_with_upper($corrected, 2)) $value = boolval($value);
						if ($corrected == "id") $value = intval($value);
						if (is_numeric($value)) $value = 1 * $value;
						$object->$corrected = $value;
					} else {
						if ($includingProtected) {
							$setterMethod = "set" . ucfirst($corrected);
							if (method_exists($object, $setterMethod)) {
								$object->$setterMethod($value);
							}
						} else {
							if ($corrected === "createdBy")	$object->setCreatedBy($stdClass->createdBy);
							if ($corrected === "createdAt")	$object->setCreatedAt($stdClass->createdAt);
							//if ($corrected === "updatedBy")	$object->setUpdatedBy($stdClass->updatedBy);
							//if ($corrected === "updatedAt")	$object->setUpdatedAt($stdClass->updatedAt);
						}
					}
				} else {
					if ($explicitFields) {
						foreach($explicitFields as $fieldItem) {
							if ($fieldItem === "ontologyClassID") {
								$object->$fieldItem = $value;
							} else if ($fieldItem === "releaseID") {
								$object->$fieldItem = $value;
							} else if ($fieldItem === "indicatorID") {
								$object->$fieldItem = $value;
							} else if ($fieldItem === "instrumentID") {
								$object->$fieldItem = $value;
							}
								
						}
					
					}
					
					
				}
					
			}
		} else {
			if (isset($stdClass)) {
				foreach($stdClass as $key => $value) {
					$corrected = $key;
					if (stripos($key, $object_name) !== false) {
						$corrected = str_ireplace($object_name . "_", "", $key);
					}
				
					if (!in_array($corrected, array("createdAt", "createdBy", "updatedAt", "updatedBy"))) {
						$object->$corrected = $value;
					}
				}
			}
		}
		
		return $object;
	}
	function convertStdClassesToObjects($stdClasses, $object_name, $explicitFields = null, $includingProtected = false) {
		$objects = array();
		
		foreach($stdClasses as $stdClass_item) {
			array_push($objects, $this->convertStdClassToObject($stdClass_item, $object_name, $includingProtected, $explicitFields));
		}
		
		return $objects;
	}
	function convertStdClassesToMultipleObjects($stdClasses, $object_names) {
		$objects = array();
	
		$rest = REST::getInstance();
		
		foreach($stdClasses as $stdClass_item) {
			if (count($object_names) == 2) {
				$obj_first_name = $object_names[0];
				if ($obj_first_name === "Lexeme") {
					$obj_first = $rest->orm->getById("Lexeme", $stdClass_item->lexeme_id, true);
				} else {
					$obj_first = $this->convertStdClassToObject($stdClass_item, $object_names[0]);
				}
				
				
				
				
				$obj_second = $this->convertStdClassToObject($stdClass_item, $object_names[1]);
				$obj_second->$obj_first_name = $obj_first;
				
				array_push($objects, $obj_second);
			}
		}
	
		return $objects;
	}
}
?>