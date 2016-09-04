<?php
class Language {
	
	var $isoCode;
	var $name;
	
	function __construct() {
	}
    function initialize() {
    	if ($this->id == 0) {
    		$this->isoCode 	= "en";
    		$this->name 	= "English";
    	} else if ($this->id == 1) {
    		$this->isoCode 	= "de";
    		$this->name 	= "German";
    	}
    }
}
?>