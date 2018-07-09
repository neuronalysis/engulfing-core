<?php
class Website_Test extends TestClass {
    var $configs;
    
    function __construct() {
        $this->testobject = new Website();
    }
    function setConfigs($configs) {
        $this->configs = $configs;
    }
    function prepare() {
        
    }
    function getScriptSource() {
        $asserts = array();
        
        try {
            $this->testobject->setConfig($this->configs['dev']);
            
            
            $scriptsource = $this->testobject->getScriptSource("coverage", "js/main_coverage.js", "C:\\xampp\\htdocs\\extraction");
            $assert = $this->assertString("getScriptSource", "js/main_coverage.js", $scriptsource);
            
            $assert->input = array("coverage", "js/main_coverage.js", "C:\\xampp\\htdocs\\extraction");
            
            array_push($asserts, $assert);
            
            
            $scriptsource = $this->testobject->getScriptSource("coverage", "js/main_coverage.js", "C:\\xampp\\htdocs\\extraction\\coverage");
            $assert = $this->assertString("getScriptSource", "../js/main_coverage.js", $scriptsource);
            
            $assert->input = array("coverage", "js/main_coverage.js", "C:\\xampp\\htdocs\\extraction\\coverage");
            
            array_push($asserts, $assert);
            
            
            $scriptsource = $this->testobject->getScriptSource("engulfing", "engulfing-core/vendor/engulfing.vendor.min.js", "/home/engulfin/extract-info.com/coverage");
            $assert = $this->assertString("getScriptSource", "http://localhost.engulfing/engulfing-core/vendor/engulfing.vendor.min.js", $scriptsource);
            
            $assert->input = array("coverage", "js/main_coverage.js", "/home/engulfin/extract-info.com/coverage");
            
            array_push($asserts, $assert);
            
            $scriptsource = $this->testobject->getScriptSource("engulfing", "engulfing-core/vendor/engulfing.vendor.min.js", "C:\\xampp\\htdocs\\extraction");
            $assert = $this->assertString("getScriptSource", "http://localhost.engulfing/engulfing-core/vendor/engulfing.vendor.min.js", $scriptsource);
            
            $assert->input = array("engulfing", "engulfing-core/vendor/engulfing.vendor.min.js", "C:\\xampp\\htdocs\\extraction");
            
            array_push($asserts, $assert);
            
            
            
            $this->testobject->setConfig($this->configs['live']);
            
            $scriptsource = $this->testobject->getScriptSource("coverage", "js/main_coverage.js", "/home/engulfin/extract-info.com/coverage");
            $assert = $this->assertString("getScriptSource", "../js/main_coverage.js", $scriptsource);
            
            $assert->input = array("coverage", "js/main_coverage.js", "/home/engulfin/extract-info.com/coverage");
            
            array_push($asserts, $assert);
            
            $scriptsource = $this->testobject->getScriptSource("engulfing", "engulfing-core/vendor/engulfing.vendor.min.js", "/home/engulfin/extract-info.com/coverage");
            $assert = $this->assertString("getScriptSource", "../engulfing/engulfing-core/vendor/engulfing.vendor.min.js", $scriptsource);
            
            $assert->input = array("coverage", "engulfing-core/vendor/engulfing.vendor.min.js", "/home/engulfin/extract-info.com/coverage");
            
            array_push($asserts, $assert);
            
        } catch ( Exception $e ) {
            $assert = $this->plottError("getScriptSource", $e);
        }
        
        return $asserts;
    }
}
?>