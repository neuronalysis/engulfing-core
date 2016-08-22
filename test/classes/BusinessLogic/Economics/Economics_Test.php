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
	
	function __construct() {
	}
	function getById() {
		$rest = new REST();
		
		$oc = $rest->getById("OntologyClass", 148);
		
		return $oc;
	}
	function prepare() {
		
	}
	function test() {
		$this->prepare();
		
		$assert = new stdClass();
		$assert->Economics = array();
		
		
		return $assert;
		
	}
}
?>