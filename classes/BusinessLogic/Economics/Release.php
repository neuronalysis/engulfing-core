<?php
class Release extends Release_Generated {
	
	function __construct() {
	}
	
	function getById($ontologyClassName, $id, $eager = false, $cascades = NULL) {
		$result = parent::getById($ontologyClassName, $id, $eager, $cascades);
		
		if ($result) $result->Indicators = $this->getByNamedFieldValues("Indicator", array("releaseID"), array($id), false, null, false, true, null, "popularity DESC", 10);
		
		return $result;
	}
	
}
?>