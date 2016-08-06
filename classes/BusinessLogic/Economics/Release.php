<?php
class Release extends Release_Generated {
	
	function __construct() {
	}
	
	function getById($ontologyClassName, $id, $eager = false, $cascades = NULL) {
		//your code here...
		//first get Release in non eager mode from parent::getById
		//second get Indicators from parent::getByNamedFieldValues function
		
		return $result;
	}
	
}
?>