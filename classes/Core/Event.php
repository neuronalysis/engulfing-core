<?php
class Event {
    function Event() {
    }
	function getObjectid($inputs) {
		return $this->getFieldValueByName($inputs, "objectid");
	}
	function getViewName($inputs) {
		return $this->getFieldValueByName($inputs, "viewName");
	}
	function getObjectType($inputs) {
		return $this->getFieldValueByName($inputs, "object");
	}
	function getFieldValueByName($inputs, $name) {
		$exp = explode(";", $inputs);
		for ($i=0; $i<count($exp); $i++) {
			$split = explode("=", $exp[$i]);
			
			if ($split[0] == $name) {
				return $split[1];
			}
		}
		
		return false;
	}
	function mapInputs($map = null, $inputs, &$object) {
		$inputs_ready = array();
		$exp = explode(";", $inputs);
		for ($i=0; $i<count($exp); $i++) {
			$split = explode("=", $exp[$i]);
			
			$inputs_ready[$split[0]] = $split[1];
		}
		if (!$map) {
			$map = array();
			for ($i=0; $i<count($exp); $i++) {
				$split = explode("=", $exp[$i]);
				
				if (substr($split[0], 0, 4) == "obj_") {
					$map[str_replace("obj_", "", $split[0])] = $split[0];
					
				}
			}
		}
		$objKeys 	= array_keys ( get_object_vars ($object) );
		$mapKeys 	= array_keys ( $map );
		for ($i=0; $i<count($mapKeys); $i++) {
			$object->$mapKeys[$i] = $inputs_ready[$map[$mapKeys[$i]]];
		}
	}
}
?>
