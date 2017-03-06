<?php
use ForceUTF8\Encoding;

class REST_Transformer {
	use Helper;
	
	var $baseClass;
	var $namespace;
	
	function __construct() {
	}
	function deserialize_JSON($json, $class_name = null, $sticktoclass = false, $namespace = null) {
		$this->baseClass = $class_name;
		$this->namespace = $namespace;
		
		$data = json_decode($json, TRUE);
		
		//print_r($data);
		
		if (isset($data[0])) {
			$object = $this->mapDataToObject($data[0], $class_name);
		} else {
			$object = $this->mapDataToObject($data, $class_name);
		}
		
		return $object;
	}
	function mapDataToObject($data, $class_name) {
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
					$object->$key =  $this->mapDataToObject($value, $key);
				} else {
					if (is_array($value)) {
						if ($this->isAssoc($value)) {
							foreach($value as $itemKey => $itemValue) {
								$object->$itemKey = $this->mapDataToObject($itemValue, $this->singularize($itemKey));
							}
						} else {
							$array = array();
							
							foreach($value as $item) {
								array_push($array, $this->mapDataToObject($item, $this->singularize($key)));
							}
								
							$object->$key = $array;
						}
					} else {
						$object->$key = $value;
					}
				}
			}
		} else if (is_object($data)) {
			$object->$key = $this->mapDataToObject($data, $key);
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
