<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/Testing/Testing.php");

class KM_Test extends TestClass {
	
	function prepare($method = null, $object = null) {
		
	}
	function getOntologyClassEntities() {
		try {
			$objects = $this->testobject->getOntologyClassEntities();
	
			$assert = $this->assertNumerics("getOntologyClassEntities", 0, count($objects), ">");
		} catch ( Exception $e ) {
			$assert = $this->plottError("getOntologyClassEntities", $e);
		}
	
		return $assert;
	}
	function getNews() {
		try {
			$objects = $this->testobject->getNews("km");
		
			$assert = $this->assertNumerics("getNews", 0, count($objects), ">");
		} catch ( Exception $e ) {
			$assert = $this->plottError("getNews", $e);
		}
		
		return $assert;
	}
}
?>