<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/geo/GEO_Generated.php");


include_once ('Country.php');
include_once ('City.php');


class GEO extends GEO_Generated {
	var $classes = array("Country", "City");
	
	var $entities = '{}';
	
	function __construct() {
	}
	
}
?>