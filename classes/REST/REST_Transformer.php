<?php
class REST_Transformer {
	use Helper;
	
	function __construct() {
	}
	function deserialize_JSON($json, $class_name = null, $sticktoclass = false) {
		if ($json == '{"data": [], "statistics" : "blabla"}') return null;
		if ($json == '[]') return null;
		
		//echo $json;
		//echo stripslashes($json);
		//echo $class_name . "\n";
		
		$data = json_decode($json, TRUE);
		
		if (isset($data['data'])) $data = $data['data'];
		
		//echo $class_name . "\n";
		if (class_exists($class_name)) {
			$class_name_rtrim = $class_name;
		} elseif (class_exists($this->singularize($class_name))) {
			$class_name_rtrim = $this->singularize($class_name);
		} else {
			$class_name_rtrim = "stdClass";
		}
		
		//echo $class_name_rtrim . "\n";
		if (isset($data[0])) {
			
			for ($i=0; $i<count($data); $i++) {
				if (!is_array($data)) return "[]";
				
				//echo $class_name_rtrim . "\n";
				if ($class_name_rtrim == "element") {
					foreach($data[$i] as $key => $val) {
						//echo $key . "\n";
						if ($key === "type") {
							if (class_exists($val)) {
								$object[$i] = new $val;
							} else {
								$object[$i] = new $class_name_rtrim;
							}
							
						}
					}
				} else {
					$object[$i] = new $class_name_rtrim;
					
					
				}
				
				if (!empty($data[$i])) {
					foreach($data[$i] as $key => $val) {
						if (class_exists($this->singularize($key))) {
							//echo $key . "\n";
							//print_r($object[$i]);
							if (property_exists($object[$i],$key)) {
								$rp = new ReflectionProperty($object[$i],$key);
								if (!$rp->isProtected()) {
									$object[$i]->$key = $this->deserialize_JSON(json_encode($val), $key);
								} else {
									$functionName = "set" . ucfirst($key);
									if (method_exists($object[$i], $functionName) && $functionName !== "setSaved") $object[$i]->$functionName($this->deserialize_JSON(json_encode($val), $key));
								}
							}
							
							
							
						} else {
							//echo $this->singularize($key) . "\n";
							//$corr_key = str_replace(strtolower(str_replace("", "", $class_name_rtrim)) . "_", "", $key);
							$corr_key = str_replace(strtolower($class_name_rtrim) . "_", "", $key);
							
							if (property_exists($class_name_rtrim, $corr_key) && $corr_key !== "saved") {
								$rp = new ReflectionProperty($object[$i],$corr_key);
								if (!$rp->isProtected()) {
									$object[$i]->$corr_key = $this->deserialize_JSON(json_encode($val), $corr_key);
								} else {
									$functionName = "set" . ucfirst($corr_key);
									if (method_exists($object[$i], $functionName) && $functionName !== "setSaved") $object[$i]->$functionName($this->deserialize_JSON(json_encode($val), $corr_key));
								}
							}
							
							
							/*
							if (property_exists($class_name_rtrim, $corr_key) && $corr_key !== "saved") {
									
								$object[$i]->$corr_key = $val;
							} else if (property_exists($class_name_rtrim, $key) && $key !== "saved") {
									
								$object[$i]->$key = $val;
							} else {
								if (!$sticktoclass  && $key !== "saved") {
					
									$object[$i]->$key = $val;
								}
							}*/
					
					
						}
					}
				}
				
				
				//echo "i: " . $i . "\n";
				//print_r($object[$i]);
			}
		} else {
			//echo $class_name_rtrim . "\n";
			
			//print_r($data);
			
			if (!is_array($data)) return "[]";
			
			if ($class_name_rtrim == "element") {
				foreach($data as $key => $val) {
					if ($key === "type") {
						$object = new $val;
					}
				}
			} else {
				
				$object = new $class_name_rtrim;
			}
			
			foreach($data as $key => $val) {
				if (isset($object)) {
					//echo "check-key-class-exists: " . $key . "\n";
					if (property_exists($object,$key)) {
						$rp = new ReflectionProperty($object,$key);
						if (!$rp->isProtected()) {
							$object->$key = $val;
						} else {
							$functionName = "set" . ucfirst($key);
							if (method_exists($object, $functionName) && $functionName !== "setSaved") $object->$functionName($val);
						}
					} else {
						$object->$key = $val;
					}
					
					if (class_exists($this->singularize($key))) {
						//echo $key . "\n";
						$rp = new ReflectionProperty($object,$key);
						if (!$rp->isProtected()) {
							$object->$key = $this->deserialize_JSON(json_encode($val), $key);
						} else {
							$functionName = "set" . ucfirst($key);
							if (method_exists($object, $functionName)) $object->$functionName($this->deserialize_JSON(json_encode($val), $key));
						}
					} else {
						//echo $class_name_rtrim . "\n";
						$corr_key = str_replace("_incoming", "", str_replace("_outgoing", "", str_replace(strtolower($class_name_rtrim) . "_", "", $key)));
						//echo "corr-key: " . $class_name_rtrim . "; " . $corr_key . "\n";
						
						if (class_exists($this->singularize($corr_key))) {
							$object->$key = $this->deserialize_JSON(json_encode($val), $corr_key);
						} else {
							if (property_exists($class_name_rtrim, $corr_key)) {
								$rp = new ReflectionProperty($class_name_rtrim,$corr_key);
								if (!$rp->isProtected()) {
									$object->$corr_key = $val;
								} else {
									$functionName = "set" . ucfirst($corr_key);
									if (method_exists($object, $functionName) && $functionName !== "setSaved") $object->$functionName($val);
								}
								
							} else if (property_exists($class_name_rtrim, $key)) {
								
								$object->$key = $val;
							} else {
								if (!$sticktoclass) {
									
									$object->$key = $val;
								}
							}
						}
						
					}
				} else {
					$object = null;
				}
				
			}
		}
		
		return $object;
	}
	
}
?>
