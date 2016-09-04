<?php
include_once (__DIR__ . "/../../../engulfing-generated/classes/things/Things_Generated.php");
include_once (__DIR__ . "/../../../engulfing-generated/classes/dm/DM_Generated.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/Helper.php");

include_once ("Element.php");
include_once ("Document.php");

class DM extends DM_Generated {
	use Helper;
	
	var $Ontologies = array();
	var $classes = array("Document");
	
	var $entities = '{}';
	
	
	function __construct() {
	}
	
}
?>
