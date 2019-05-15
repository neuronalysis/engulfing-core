<?php
class REST_Transformer {
	use Helper;
	
	var $baseClass;
	var $namespace;
	
	function __construct() {
	}
	function deserialize_JSON($json, $class_name = 'stdClass', $sticktoclass = false, $namespace = null) {
		$this->namespace = $namespace;
		
		$data = json_decode($json, FALSE);
		
		$object = $this->mapDataToObject($data, $this->getClassName($class_name));
		
		return $object;
	}
	function getClassName($class_name) {
		$class_name_sing = $this->singularize($class_name);
		
		if (class_exists("\\" . $this->namespace . "\\" . $this->namespace . $class_name)) {
			$class_name = "\\" . $this->namespace . "\\" . $this->namespace . $class_name;
		} else {
			if (class_exists("\\" . $this->namespace . "\\" . $class_name)) {
				$class_name = "\\" . $this->namespace . "\\" . $class_name;
			} else {
				if (class_exists("\\" . $this->namespace . $class_name)) {
					$class_name = "\\" . $this->namespace . $class_name;
				} else {
					if (class_exists("\\" . $class_name)) {
						$class_name = "\\" . $class_name;
					} else {
						if (class_exists("\\" . $this->namespace . "\\" . $this->namespace . $class_name_sing)) {
							$class_name = "\\" . $this->namespace . "\\" . $this->namespace . $class_name_sing;
						} else {
							if (class_exists("\\" . $this->namespace . "\\" . $class_name_sing)) {
								$class_name = "\\" . $this->namespace . "\\" . $class_name_sing;
							} else {
								if (class_exists("\\" . $this->namespace . $class_name_sing)) {
									$class_name = "\\" . $this->namespace . $class_name_sing;
								} else {
									if (class_exists("\\" . $class_name_sing)) {
										$class_name = "\\" . $class_name_sing;
									} else {
										$class_name = '\stdClass';
									}
								}
							}
						}
					}
				}
			}
		}
		
		return $class_name;
	}
	function mapDataToObject($data, $class_name) {
		if (is_array($data)) {
			if ($this->isAssoc($data)) {
				$mapped = $this->mapAssocData(new $class_name, $data);
			} else {
				$mapped = array();
				
				foreach($data as $data_item) {
					array_push($mapped, $this->mapDataToObject($data_item, $this->getClassName($class_name)));
				}
			}
		} else {
			$mapped = $this->mapAssocData(new $class_name, $data);
		}
		
		return $mapped;
	}
	function mapAssocData($mapped, $data) {
		if (is_array($data) || is_object($data)) {
			foreach($data as $key => $value) {
				if ($key === "namespaceDefinitions") {
					//don't change it
				} else {
					if (is_array($value)) {
						foreach($value as $data_item) {
							if (!isset($mapped->$key)) {
								if (property_exists($mapped, $key)) {
									$mapped->$key = array($this->mapDataToObject($data_item, $this->getClassName($key)));
								}
								
							} else {
								array_push($mapped->$key, $this->mapDataToObject($data_item, $this->getClassName($key)));
							}
						}
					} else {
						if ($value instanceof stdClass) {
							$mapped->$key = $this->mapDataToObject($value, $this->getClassName($key));
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
				}
				
			}
		} else {
			//echo "no valid array-value\n";
			//print_r($data);
		}
		
		
		return $mapped;
	}
	function isAssoc(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>
