<?php
class OCR_Test extends TestClass {
    var $configs;
    
    function __construct() {
        $this->testobject = new OCR();
    }
    function setConfigs($configs) {
        $this->configs = $configs;
    }
    function prepare() {
        
    }
}

class Document_Test extends TestClass {
	var $configs;
	
	function __construct() {
		$this->testobject = new Document();
	}
	function setConfigs($configs) {
		$this->configs = $configs;
	}
	function prepare() {
		
	}
}
?>