<?php

class ObjectTreeAccessor {
	use Helper;
	
	var $object;
	/*
	* Konstruktor
	*/
	function __construct() {
	}
	function getAllObjectsOfTypeFromObject($type, $object) {
		$objects = array();
		
		foreach($object as $key => $value) {
			if (is_object($object)) {
				if (property_exists($object, $this->pluralize($type))) {
					$rp = new ReflectionProperty($object,$this->pluralize($type));
					if ($rp->isProtected()) {
						$getterMethodName = "get" . ucfirst($this->pluralize($type));
						
						if (method_exists($object, $getterMethodName)) {
							$value = $object->$getterMethodName();
						} else {
							echo get_class($object) . "-" . $getterMethodName . "\n";
							
						}
					}
				}
			}
			if (is_object($value)) {
				if (get_class($value) === $type) {
					array_push($objects, $value);
				}
				
				$objects = array_merge($objects, $this->getAllObjectsOfTypeFromObject($type, $value));
			} else if (is_array($value)) {
				foreach ($value as $item) {
					if (is_object($item)) {
						if (get_class($item) === $type) {
							array_push($objects, $item);
						}
					}
					
					$objects = array_merge($objects, $this->getAllObjectsOfTypeFromObject($type, $item));
				}
			}
		}
		
		return $objects;
	}
	
}
?>