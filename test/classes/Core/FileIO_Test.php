<?php
class FileIO_Test extends TestClass {
    var $configs;
    
    function __construct() {
        $this->testobject = new FileIO();
    }
    function setConfigs($configs) {
        $this->configs = $configs;
    }
    function prepare() {
        
    }
    function translateAbsolutePathToRelative() {
        $asserts = array();
        
        try {
            $config = $this->configs['live'];
            
            
            $relpath = $this->testobject->translateAbsolutePathToRelative("/home/engulfin/extract-info.com/coverage", $config['framework']['path']);
            $assert = $this->assertString("translateAbsolutePathToRelative", "../engulfing/", $relpath);
            
            $assert->input = array("/home/engulfin/extract-info.com/coverage", $config['framework']['path']);
            
            array_push($asserts, $assert);
            
            
            
        } catch ( Exception $e ) {
            $assert = $this->plottError("translateAbsolutePathToRelative", $e);
        }
        
        return $asserts;
    }
}
?>