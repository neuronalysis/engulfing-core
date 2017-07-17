<?php
include_once __DIR__ . '/../DM/DM.php';
include_once __DIR__ . '/../Core/FileIO.php';
include_once __DIR__ . '/../Core/Helper.php';
include_once __DIR__ . '/../REST/REST.php';
include_once __DIR__ . '/../Authentication/Authentication.php';

include_once __DIR__ . '/WebsiteScript.php';
include_once __DIR__ . '/WebsiteNavigation.php';
include_once __DIR__ . '/Webpage.php';
include_once __DIR__ . '/Website.php';
include_once __DIR__ . '/Websites/Website_Grid.php';
include_once __DIR__ . '/Websites/Website_ConvertedPDF.php';

class Web {
	use Helper;
	
	var $classes = array("Website", "Webpage", "Ontology");
	
	var $entities = '{}';
	
	function __construct() {
		$this->orm = new ORM();
	}
	function getWebsiteByName($name) {
		if (!$name) return null;
		
		try {
			$objects = $this->orm->getByNamedFieldValues("Website", array("name"), array($name), false, null, false, true, null, array());
		} catch (PDOException $e) {
			return null;
		}
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