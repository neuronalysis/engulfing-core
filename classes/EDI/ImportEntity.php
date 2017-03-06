<?php
class ImportEntity extends Thing_Generated {
	
	var $entityClassName;
	var $entityOntologyName;
	
	function __construct() {
	}
	/*function getPersistables() {
		$persistables = array();
		$objectvars = get_object_vars ( $this );
		
		foreach($objectvars as $key => $value) {
			if (!in_array($key, array("entityClassName", "entityOntologyName", "connectionHost", "connectionUsername", "connectionPassword", "databaseConnections"))) {
				$persistables[$key] = $value;
			}
			
		}
	
		return $persistables;
	}*/
}
?>
