<?php
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
			$objects = $this->orm->getByNamedFieldValues(new ORM_Request("Website", array("name" => $name)));
		} catch (PDOException $e) {
			return null;
		}
		if(!$objects) return null;
		
		return $objects[0];
	}
	function getWebsiteById($id) {
	    $rest = REST::getInstance();
	    $result = $rest->orm->getById("Website", $id);
	
		return $result;
	}
}
?>