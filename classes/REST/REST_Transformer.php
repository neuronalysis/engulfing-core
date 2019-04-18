<?php
class REST_Transformer {
	use Helper;
	
	var $baseClass;
	var $namespace;
	
	function __construct() {
	}
	function deserialize_JSON($json, $class_name = null) {
		$data = json_decode($json, FALSE);
		
		$object = $this->mapDataToObject($data, $class_name);
		
		return $object;
	}
	function mapDataToObject($data, $class_name) {
		if (is_array($data)) {
			if ($this->isAssoc($data)) {
				$mapped = $this->mapAssocData(new $class_name, $data);
			} else {
				$mapped = array();
				
				foreach($data as $data_item) {
					array_push($mapped, $this->mapDataToObject($data_item, $class_name));
				}
			}
		} else {
			$mapped = $this->mapAssocData(new $class_name, $data);
		}
		
		return $mapped;
	}
	function mapAssocData($mapped, $data) {
		foreach($data as $key => $value) {
			if ($value instanceof stdClass) {
				$mapped->$key = $this->mapDataToObject($value, $key);
			} else {
				if (property_exists($mapped, $key)) {
					$rp = new ReflectionProperty($mapped,$key);
					if ($rp->isProtected()) {
						$setterMethodName = "set" . ucfirst($key);
						
						if (method_exists($mapped, $setterMethodName)) $mapped->$setterMethodName($value);
					} else {
						$mapped->$key = $value;
					}
				}
			}
		}
		
		return $mapped;
	}
	function isAssoc(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>
