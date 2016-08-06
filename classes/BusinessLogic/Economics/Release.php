<?php
class Release extends Release_Generated {
	
	function __construct() {
	}
	
	function getById($ontologyClassName, $id, $eager = false, $cascades = NULL) {
		//your code here...
		//first get Release in non eager mode from parent::getById
		//second get Indicators from parent::getByNamedFieldValues function
		
		$result = parent::getById($ontologyClassName, $id, $eager, $cascades);
		
		$result->Indicators = $this->getByNamedFieldValues("Indicator", array("releaseID"), array($id), false, null, false, true, null, "popularity DESC", 10);
		
		return $result;
	}
	
}
?>