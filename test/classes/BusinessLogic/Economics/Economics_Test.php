<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/Testing/Testing.php");

class Economics_Test extends TestClass {
	
	function prepare($method = null, $object = null) {
		$mockRelease = new Release(array("name" => "forImpactFunctions", "externalKey" => "forImpactFunctions"));
		$mockRelease->id = $this->orm->replace($mockRelease);
		
		$mockIndicator = new Indicator(array("name" => "forImpactFunctions", "externalKey" => "forImpactFunctions"));
		$country = $this->orm->getById("Country", 233);
		$mockIndicator->countryID = $country->id;
		$mockIndicator->Release = $mockRelease;
		
		print_r($mockIndicator);
		$indicatorID = $this->orm->replace($mockIndicator);
		
	}
	function getById() {
		$rest = new REST();
		
		$oc = $rest->orm->getById("OntologyClass", 148);
		
		return $oc;
	}
	function test() {
		$this->prepare();
		
		$assert = new stdClass();
		$assert->Economics = array();
		
		
		return $assert;
		
	}
}
?>