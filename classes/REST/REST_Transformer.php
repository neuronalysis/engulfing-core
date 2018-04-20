<?php
class REST_Transformer {
	use Helper;
	
	var $baseClass;
	var $namespace;
	
	function __construct() {
	}
	function deserialize_JSON($json, $class_name = null, $sticktoclass = false, $namespace = null, $enforceList = false, $enforceAttributes = false) {
		$this->baseClass = $class_name;
		$this->namespace = $namespace;
		
		$data = json_decode($json, TRUE);
		
		//print_r($data);
		
		if (isset($data[0])) {
			if ($enforceList) {
				$objects = array();
				
				foreach($data as $item) {
				    array_push($objects, $this->mapDataToObject($item, $class_name, $enforceAttributes));
				}
				
				return $objects;
			} else {
			    $object = $this->mapDataToObject($data[0], $class_name, $enforceAttributes);
			}
			
		} else {
		    $object = $this->mapDataToObject($data, $class_name, $enforceAttributes);
		}
		
		return $object;
	}
	function mapDataToObject($data, $class_name, $enforceAttributes = false) {
		//echo "\\" . $this->namespace . "\\" . $this->baseClass . $class_name . "\n";
		if (class_exists("\\" . $this->namespace . "\\" . $this->baseClass . $class_name)) {
			$object_based = "\\" . $this->namespace . "\\" . $this->baseClass . $class_name;
			$object = new $object_based;
		} else {
			if (class_exists("\\" . $this->namespace . "\\" . $this->namespace . $class_name)) {
				$object_based = "\\" . $this->namespace . "\\" . $this->namespace . $class_name;
				$object = new $object_based;
			} else {
				if (class_exists("\\" . $this->namespace . "\\" . $class_name)) {
					$object_based = "\\" . $this->namespace . "\\" . $class_name;
					$object = new $object_based;
				} else {
					if (class_exists($this->baseClass . $class_name)) {
						$object_based = $this->baseClass . $class_name;
						$object = new $object_based;
					} else {
						if (class_exists($class_name)) {
							$object = new $class_name;
						} else {
							$object = new stdClass();
						}
					}
				}
			}
		}
		
		if (is_array($data)) {
			foreach($data as $key => $value) {
				if ((class_exists($key)) || (class_exists("\\" . $this->namespace . "\\" . $key))) {
				    $object->$key =  $this->mapDataToObject($value, $key, $enforceAttributes);
				} else {
					if ($key == "Strings") $key = "ALTOStrings";
					if (is_array($value)) {
						if ($this->isAssoc($value)) {
							foreach($value as $itemKey => $itemValue) {
							    $object->$itemKey = $this->mapDataToObject($itemValue, $this->singularize($itemKey), $enforceAttributes);
							}
						} else {
							$array = array();
							
							if (count($value) > 0) {
								foreach($value as $item) {
								    array_push($array, $this->mapDataToObject($item, $this->singularize($key), $enforceAttributes));
								}
								
								$object->$key = $array;
							}
								
							
						}
					} else {
						if (property_exists($class_name, $key) || property_exists("\\" . $this->namespace . "\\" . $class_name, $key)) {
							$rp = new ReflectionProperty($object,$key);
							if ($rp->isProtected()) {
								$setterMethodName = "set" . ucfirst($key);
								
								if (method_exists($object, $setterMethodName) || property_exists($object, $setterMethodName)) $object->$setterMethodName($value);
							} else {
								$object->$key = $value;
							}
						} else {
						    if ($key === "id" || $enforceAttributes) {
								$object->$key = $value;
							}
						}
					}
				}
			}
		} else if (is_object($data)) {
		    $object->$key = $this->mapDataToObject($data, $key, $enforceAttributes);
		} else if (is_string($data)) {
			$object = $data;
		}
		
		return $object;
	}
	function isAssoc(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>
