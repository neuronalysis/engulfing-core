<?php
class Release extends Release_Generated {
	
	function getById($ontologyClassName, $id, $eager = false, $cascades = NULL) {
		$result = $this->orm->getById($ontologyClassName, $id, $eager, $cascades);
		
		if ($result) $result->Indicators = $this->orm->getByNamedFieldValues("Indicator", array("releaseID"), array($id), false, null, false, true, null, "popularity DESC", 10);
		
		return $result;
	}
	
}
?>