<?php
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
		
		if (isset($data[0])) {
			$object = $this->mapDataToObject($data[0], $class_name, $namespace);
		} else {
			$object = $this->mapDataToObject($data, $class_name, $namespace);
		}
		
		return $object;
	}
	function mapDataToObject($data, $class_name) {
		if (class_exists($class_name)) {
			$object = new $class_name;
		} else {
			if (class_exists($this->baseClass . $class_name)) {
				$object_based = $this->baseClass . $class_name;
				$object = new $object_based;
			} else {
				if (class_exists("\\" . $this->namespace . "\\" . $class_name)) {
					$object_based = "\\" . $this->namespace . "\\" . $class_name;
					$object = new $object_based;
				} else {
					$object = new stdClass();
				}
			}
			
		}
		
		if (is_array($data)) {
			foreach($data as $key => $value) {
				if (class_exists($key) && $this->starts_with_upper($key)) {
					$object->$key =  $this->mapDataToObject($value, $key);
				} else {
					if (is_array($value)) {
						$array = array();
					
						foreach($value as $item) {
							array_push($array, $this->mapDataToObject($item, $this->singularize($key)));
						}
					
						$object->$key = $array;
					} else {
						$object->$key = $value;
					}
				}
			}
		} else if (is_object($data)) {
			$object->$key = $this->mapDataToObject($data, $key);
		}
		
		return $object;
	}
}
?>
