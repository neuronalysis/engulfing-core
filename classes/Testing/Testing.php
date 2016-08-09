<?php

class Testing {
	
	function __construct() {
		
	}
	
}
class TestClass {
	function __construct() {
		
	}
	function test() {
		$assert = new stdClass();
		
		$objectName = get_class($this);
		
		$assert->$objectName = array();
		
		
		$class_methods = get_class_methods($this);
		
		foreach ($class_methods as $method_name) {
			if (!in_array($method_name, array("test", "__construct", "assertJson"))) {
				$result = $this->$method_name();
				
				array_push($assert->$objectName, $this->assertJson($result));
			
			}
		}
		
		return $assert;
	}
	function assertJson($result) {
		$objectName = str_ireplace("_Test", "", get_class($this));
		
		$assert = file_get_contents('../engulfing/engulfing-core/test/asserts/' . strtolower($objectName) . '_getbyid.json');
			
		$result =  json_encode ( $result, JSON_PRETTY_PRINT );
		
		if (strcmp($assert, $result) ) {
			return true;
		} else {
			return false;
		}
	}
}
?>