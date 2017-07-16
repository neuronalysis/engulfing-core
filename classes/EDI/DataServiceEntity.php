<?php
class DataServiceEntity extends Thing {
	
	var $DataService;
	var $OntologyClass;
	var $externalKey;
	var $internalKey;
	
	function DataService() {
	}
	
	function save($persistables = null, $unprotectables = array(), $nosaves = array(), $ediReferrer = false, $excludeAllButUnProtectables = false) {
		if (!$this->internalKey) {
			throw new Exception('internalKey missing');
		} else {
			parent:save($persistables, $unprotectables, $nosaves, $ediReferrer, $excludeAllButUnProtectables);
		}
	}
	
}
?>
