<?php
$desc = "";
if (!file_exists("../engulfing/")) $desc = "../";
include_once ($desc . "../engulfing/engulfing-core/classes/Core/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-extensions/generated/sandbox/Sandbox_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

include_once ("TestObject.php");
include_once ("TestLocation.php");

class Sandbox extends Sandbox_Generated {
	use ORM;
	use Helper;
	
	var $Ontologies = array();
	var $lexicon;
	var $classes = array("TestObject", "TestLocation");
	
	var $entities = '{}';
	
	
	function Sandbox() {
	}
}
?>
