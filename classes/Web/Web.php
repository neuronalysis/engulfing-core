<?php
include_once __DIR__ . '/../DM/DM.php';
include_once __DIR__ . '/../Core/FileIO.php';
include_once __DIR__ . '/../Core/Helper.php';
include_once __DIR__ . '/../REST/REST.php';
include_once __DIR__ . '/../Authentication/Authentication.php';

include_once __DIR__ . '/../../../../engulfing/engulfing-generated/classes/things/Things_Generated.php';
include_once __DIR__ . '/../../../../engulfing/engulfing-generated/classes/web/Web_Generated.php';

include_once ("WebsiteScript.php");
include_once ("WebsiteNavigation.php");
include_once ('Webpage.php');
include_once ("Website.php");
include_once ('Websites/Website_Grid.php');
include_once ("Websites/Website_ConvertedPDF.php");

class Web extends Web_Generated {
	use Helper;
	
	var $classes = array("Website", "Webpage", "Ontology");
	
	var $entities = '{}';
	
	function __construct() {
		$this->orm = new ORM();
	}
	function getWebsiteByName($name) {
		if (!$name) return null;
		
		$objects = $this->orm->getByNamedFieldValues("Website", array("name"), array($name), false, null, false, true, null, array());
		
		if(!$objects) return null;
		
		return $objects[0];
	}
	function getWebsiteById($id) {
		$rest = new REST();
		$result = $rest->orm->getById("Website", $id);
	
		return $result;
	}
}
?>