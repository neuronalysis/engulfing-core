<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/dm/DM_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

include_once ("Element.php");
include_once ("Document.php");

class DM extends DM_Generated {
	use ORM;
	use Helper;
	
	var $Ontologies = array();
	var $classes = array("Document");
	
	var $entities = '{}';
	
	
	function __construct() {
	}
	
}
?>
