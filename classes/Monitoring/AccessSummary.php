<?php
class AccessSummary {
	use Helper;

	var $AccessDestinations = array();
	
	//var $Event;

	function __construct() {
	}
	function getTitleByURL($url) {
	    $rest = REST::getInstance();
	    
		if (strpos($url, "#") !== false) {
			$url_exp = explode("#", $url);
			$title = $url_exp[1];
		} else {
			$title = "//" . $rest->orm->getScopeName($url) . "/" . $rest->orm->getScopeObjectName($url);
		}
	
		
		return $title;
	}
}

class AccessDestination {
	use Helper;

	var $url;
	var $title;
	var $visits;
	
	//var $Event;

	function __construct() {
	}
}
?>
