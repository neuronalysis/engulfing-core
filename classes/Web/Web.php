<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/web/Web_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

//include_once ("Html/Html.php");

include_once ("WebsiteScript.php");
include_once ("WebsiteNavigation.php");
include_once ('Webpage.php');
include_once ("Website.php");
include_once ('Websites/Website_Grid.php');
include_once ("Websites/Website_ConvertedPDF.php");

class Web extends Web_Generated {
	use Helper;
	use ORM;
	
	var $classes = array("Website", "Webpage", "Ontology");
	
	var $entities = '{}';
	
	function __construct() {
	}
	function getWebsiteByName($name) {
		$objects = $this->getByNamedFieldValues("Website", array("name"), array($name), false, null, false, true, null, array());
		
		if(!$objects) return null;
		
		return $objects[0];
	}
	function getWebsiteById($id) {
		$rest = new REST();
		$result = $rest->getById("Website", $id);
	
		return $result;
	}
}
?>