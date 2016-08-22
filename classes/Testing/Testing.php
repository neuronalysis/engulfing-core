<?php

class Testing {
	
	function __construct() {
		
	}
	
}
class TestClass {
	function __construct() {
		
	}
	function regression() {
		$assert = $this->prepare();
		$assert = $this->test();
		
		return $assert;
	}
	function assertNumerics($method, $expected, $actual) {
		$assert = (object) array(
				$method => ((($expected - $actual) == 0) ? true : false)
		);
		
		return $assert;
	}
	function assertString($method, $expected, $actual) {
		$assert = (object) array(
				$method => (($expected == $actual) ? true : false)
		);
	
		return $assert;
	}
	function assertJson($method, $result, $classname, $id = null) {
		$objectName = str_ireplace("_Test", "", get_class($this));
		
		if ($id) {
			$assert = file_get_contents('../engulfing/engulfing-core/test/asserts/' . strtolower($objectName) . '_' . strtolower($method) . '_' . strtolower($classname) . '_' . strtolower($id) . '.json');
		} else {
			$assert = file_get_contents('../engulfing/engulfing-core/test/asserts/' . strtolower($objectName) . '_' . strtolower($method) . '_' . strtolower($classname) . '.json');
		}
			
		$result =  json_encode ( $result, JSON_PRETTY_PRINT );
		
		//echo $assert . "\n";
		//echo $result . "\n";
		
		$assert = (object) array(
			$method => $this->compareTwoStrings($assert, $result)
		);
		
		return $assert;
	}
	function getFunctionReferencesByProject($methodName, $projectNames = array("engulfing/engulfing-core")) {
		$directory = getcwd() . "../../" . $projectNames[0];
		
		$count = 0;
				
		if (file_exists($directory)) {
			//echo $directory . "\n";
			
			$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $directory ) );
			foreach ( $directory_iterator as $filename => $path_object ) {
				if(is_file($filename) && pathinfo($filename, PATHINFO_EXTENSION) == "php" && pathinfo($filename, PATHINFO_FILENAME ) !== str_ireplace("_Test", "", get_class($this)) . ".php") {
					$filestring = file_get_contents($filename);
					
					$count += substr_count($filestring, $methodName);
				}
			}
		}
		
		return $count;
	}
	function arrayContains($array, $string)
	{
		if ($array === null) return false;
	
		$exploded = explode("\\", $string);
			
		$filename = end($exploded);
		foreach ($array as $name) {
			if (stripos($name, $filename) !== FALSE) {
				return true;
			}
		}
	
		foreach ($array as $name) {
			if (stripos($filename, $name) !== FALSE) {
				return true;
			}
		}
	
		return false;
	}
	function getTestCoverage() {
		$class = new ReflectionClass('ORM');
		$methods = $class->getMethods();
		$classTest = new ReflectionClass('ORM_Test');
		$methodsTest = $classTest->getMethods();
		
		$coverage = new stdClass();
		
		$testmethods = array();
		foreach($methodsTest as $testmethod) {
			array_push($testmethods, $testmethod->name);
		}
		
		$coveredAmount = 0;
		
		$coverage->overAll = null;
		$coverage->methods = array();
		
		foreach($methods as $method) {
			if (!in_array($method->name, array("__construct"))) {
				if (in_array($method->name, $testmethods)) {
					//$coverage->methods[$method->name] = array(true, $this->getFunctionReferencesByProject($method->name));
					$coverage->methods[$method->name] = array(true, null);
						
					$coveredAmount++;
				} else {
					//$coverage->methods[$method->name] = array(false, $this->getFunctionReferencesByProject($method->name));
				}
				
			}
			
		}
		
		//print_r($methods);
		
		//echo count($coverage->methods) . "; " . count($methods) . "\n";
		
		$coverage->overAll = $coveredAmount / count($methods);
		
		return $coverage;
	}
	function compareTwoStrings($string1, $string2) {
		$lines1 = preg_split("/((\r?\n)|(\r\n?))/", $string1);
		$lines2 = preg_split("/((\r?\n)|(\r\n?))/", $string2);
		
		for($i=0; $i<count($lines1); $i++) {
			if (substr($lines2[$i], -1) == ',')	$lines2[$i] = substr($lines2[$i], 0, -1);
			if (substr($lines2[$i], -3, 2) == '[]')	$lines2[$i] = str_ireplace('"[]"', 'null', $lines2[$i]);
			if (substr($lines2[$i], -2, 2) == '[]')	$lines2[$i] = str_ireplace('[]', 'null', $lines2[$i]);
			if (substr($lines1[$i], -1) == ',')	$lines1[$i] = substr($lines1[$i], 0, -1);
			if ($lines1[$i] !== $lines2[$i] && substr($lines2[$i], 5, 2) !== "id" && strpos($lines2[$i], ':') !== false) {
				echo "[" . $lines1[$i] . "]" . " vs. " . "[" . $lines2[$i] . "]" . "\n";
				
				return false;
			}
		}
		
		return true;
	}
}
?>