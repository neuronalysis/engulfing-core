<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/Testing/Testing.php");

class ORM_Test extends TestClass {
	
	function __construct() {
	}
	function getById() {
		$rest = new REST();
		
		$oc = $rest->getById("OntologyClass", 148);
		
		return $oc;
	}
}
?>