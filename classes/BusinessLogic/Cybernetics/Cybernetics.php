<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/cybernetics/Cybernetics_Generated.php");

class Cybernetics extends Cybernetics_Generated {
	
	function __construct() {
	}
	
	function getPrognosis() {
		$prognosis = new Prognosis();
		
		return $prognosis;
	}
	
}

class Prognosis {
	var $nextInstrumentObservation;
	var $inputFactors;
	
	function __construct() {
		$this->nextInstrumentObservation = $this->getNextInstrumentObservation();
	}
	function getNextInstrumentObservation() {
		$instrumentobservation = new InstrumentObservation();
		$instrumentobservation->date = '2020-01-01';
		$instrumentobservation->value = 2500;
	
		return $instrumentobservation;
	}
}
?>