<?php
class Testing {
	function __construct() {
	}
}
class TestClass {
	use ObjectHelper;
	
	var $rest;
	
	var $results = array();
	
	function __construct() {
		//$this->orm = new ORM();
		
		//$classname = str_ireplace("_Test", "", get_class($this));
		
		//$this->testobject = new $classname();
	}
	function test() {
		$this->prepare();
	
		$coverage = $this->getTestCoverage();
	
		$classname = str_ireplace("_Test", "", get_class($this));
	
		$check = new stdClass();
		$check->$classname = new stdClass();
		$check->$classname->coverage = $coverage;
		$check->$classname->methodAsserts = array();
	
	
		foreach($coverage->methods as $method => $coverage) {
			if ($coverage[0]) {
				$asserts = $this->$method();
				if (is_array($asserts)) {
					$check->$classname->methodAsserts = array_merge($check->$classname->methodAsserts, $asserts);
				} else {
					array_push($check->$classname->methodAsserts, $asserts);
				}
			}
				
		}
	
		return $check;
	}
	function testMethod($method) {
		if (!method_exists($this, $method)) return null;
		
		$this->prepare($method);
	
		$coverage = $this->getTestCoverage();
	
		$classname = str_ireplace("_Test", "", get_class($this));
		
		$check = new stdClass();
		$check->$classname = new stdClass();
		$check->$classname->coverage = $coverage;
		$check->$classname->methodAsserts = array();
	
		
		$asserts = $this->$method();
		if (is_array($asserts)) {
			$check->$classname->methodAsserts = array_merge($check->$classname->methodAsserts, $asserts);
		} else {
			array_push($check->$classname->methodAsserts, $asserts);
		}
	
		return $check;
	}
	function testMethodAndObject($method, $object) {
		$this->prepare($method, $object);
	
		$coverage = $this->getTestCoverage();
	
		$classname = str_ireplace("_Test", "", get_class($this));
	
		$check = new stdClass();
		$check->$classname = new stdClass();
		$check->$classname->coverage = $coverage;
		$check->$classname->methodAsserts = array();
	
		$asserts = $this->$method($object);
		if (is_array($asserts)) {
			$check->$classname->methodAsserts = array_merge($check->$classname->methodAsserts, $asserts);
		} else {
			array_push($check->$classname->methodAsserts, $asserts);
		}
	
		return $check;
	}
	function regression() {
		$assert = $this->prepare();
		$assert = $this->test();
		
		return $assert;
	}
	function assertNumerics($method, $expected, $actual, $operator = null) {
		if ($operator) {
			if ($operator == ">") {
				$assert = (object) array(
						$method => (($actual > $expected) ? true : false)
				);
			}
		} else {
			$assert = (object) array(
					$method => ((($expected - $actual) == 0) ? true : false)
			);
		}
		
		return $assert;
	}
	function assertString($method, $expected, $actual) {
		$assert = (object) array(
				$method => (($expected == $actual) ? true : false)
		);
	
		return $assert;
	}
	function plottError($method, $e) {
		$error = new Error ();
		$error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
		
		$assert = (object) array(
				$method => $error
		);
		
		return $assert;
	}
	function assertObject($method, $expected, $actual) {
		$assert = (object) array(
				$method => $this->compareTwoObjects($expected, $actual)
		);
		
		if (!$assert->$method) {
			echo "actual:\n";
			print_r($actual);
			echo "expected:\n";
			print_r($expected);
		}
		
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
		
		$assert = (object) array(
			$method => $this->compareTwoStrings($assert, $result)
		);
		
		return $assert;
	}
	function getFunctionReferencesByProject($methodName, $projectNames = array("engulfing/engulfing-core")) {
		$directory = getcwd() . "../../" . $projectNames[0];
		
		$count = 0;
				
		if (file_exists($directory)) {
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
	function arrayContains($array, $string) {
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
		$classname = get_class($this);
		
		$class = new ReflectionClass(str_ireplace("_Test", "", $classname));
		$methods = $class->getMethods();
		$classTest = new ReflectionClass($classname);
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
		
		$coverage->overAll = $coveredAmount / count($methods);
		
		return $coverage;
	}
	//TODO improvements necessary. bad approach to save results in class-variable
	//TODO most probably not robust for general purpose (which is intended); contains app-specific code to be cleaned up.
	function compareTwoObjects($a,$b, $ignoreID = true, $convertNumericStrings = true) {
		if(is_object($a) && is_object($b)) {
			if(get_class($a)!=get_class($b))
				return false;
				foreach($a as $key => $val) {
					if(!$this->compareTwoObjects($val,$b->$key) && (!in_array($key, array("id")) || !$ignoreID)) {
						if (!is_array($val) && !is_array($b->$key)) {
							if (!is_string($key)) $key= print_r($key, true);
							if (!is_string($val)) $key= print_r($val, true);
							
							$classNameWithoutNS = $this->getNameWithoutNamespace($key);
							
							if($classNameWithoutNS == "ALTOString") {
								$bVal = $b->String;
								$hpos = $b->HPOS;
								$vpos = $b->VPOS;
								$width = $b->WIDTH;
								$height = $b->HEIGHT;
							} else {
								if (isset($b->$classNameWithoutNS)) {
									$bVal = $b->$classNameWithoutNS;
									$hpos = $b->HPOS;
									$vpos = $b->VPOS;
									$width = $b->WIDTH;
									$height = $b->HEIGHT;
								}
								
							}
							
							if (isset($bVal)) {
								if (!is_string($bVal)) $bVal= print_r($bVal, true);
								
								$delta = new \OCR\Difference();
								$delta->key = $key;
								
								$delta->before = $val;
								$delta->after = $bVal;
								$delta->HPOS = $hpos;
								$delta->VPOS = $vpos;
								$delta->WIDTH = $width;
								$delta->HEIGHT = $height;
								
								unset($delta->Page);
								array_push($this->results, $delta);
								
								//echo "delta key " . $key . ": " . $val . "; " . $bVal. "\n";
							}
							
						} else {
							//echo "delta key " . $key . "\n";
						}
						//return false;
					}
				}
				return true;
		}
		else if(is_array($a) && is_array($b)) {
			while(!is_null(key($a)) && !is_null(key($b))) {
				if (key($a)!==key($b) || !$this->compareTwoObjects(current($a),current($b)))
					return false;
					next($a); next($b);
			}
			return is_null(key($a)) && is_null(key($b));
		}
		else
			if (!is_string($a)) $a = print_r($a, true);
			if (!is_string($b)) $b = print_r($b, true);
			
			return "" . $a === "" . $b;
	}
}
?>