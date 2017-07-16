<?php
$desc = "";
if (!file_exists("../engulfing/")) $desc = "../";
include_once ($desc . "../engulfing/engulfing-core/classes/Things/Things.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/sandbox/Sandbox_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

include_once ("TestObject.php");
include_once ("TestLocation.php");

class Sandbox extends Thing {
	
	use Helper;
	
	var $Ontologies = array();
	var $lexicon;
	var $classes = array("TestObject", "TestLocation");
	
	var $entities = '{}';
	
	
	function Sandbox() {
	}
}
?>
