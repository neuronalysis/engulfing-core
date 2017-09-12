<?php
class Language {
	
	var $isoCode;
	var $name;
	
	function __construct() {
	}
    function initialize() {
    	if (isset($this->id)) {
    		if ($this->id == 0) {
    			$this->isoCode 	= "en";
    			$this->name 	= "English";
    		} else if ($this->id == 1) {
    			$this->isoCode 	= "de";
    			$this->name 	= "German";
    		}
    	} else {
    		if ($this->name == "English") {
    			$this->id = 0;
    			$this->isoCode= "en";
    		} else if ($this->name  == "German") {
    			$this->id 	= 1;
    			$this->isoCode= "de";
    		}
    	}
    	
    }
}
?>