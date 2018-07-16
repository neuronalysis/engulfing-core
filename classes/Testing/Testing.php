<?php
class Testing {
    use Helper;
    
	function __construct() {
	}
	function plottResults($results) {
	    $str = "";
	    
	    foreach($results as $key => $class_item) {
	        $str .= "\n\n\nclass: " . $key . "\n";
	        foreach($class_item->methodAsserts as $assert_item) {
	            $str .= $assert_item;
	        }
	    }
	    
	    return $str;
	}
}
class TestAssert {
    var $outcomeExpected;
    var $outcomeActual;
    
    var $result;
    
    function __construct($outcomeExpected, $outcomeActual) {
        $this->outcomeExpected = $outcomeExpected;
        $this->outcomeActual = $outcomeActual;
    }
    
    function __toString() {
        $str = "";
        
        if (is_array($this->input)) {
            $str .= "  input:           " . join("; ", $this->input) . "\n";
        } else {
            $str .= "  input:           " . $this->input . "\n";
        }
        
        if (is_object($this->outcomeActual)) {
            $outcome_actual_string = print_r($this->outcomeActual, true);
            $outcome_expected_string = print_r($this->outcomeExpected, true);
        } else {
            $outcome_actual_string= $this->outcomeActual;
            $outcome_expected_string= $this->outcomeExpected;
        }
        
        $str .= "  outcome - \n";
        $str .= "     - expected:   " . $outcome_expected_string . "\n";
        $str .= "     - actual:     " .  $outcome_actual_string . "\n";
        $str .= "\n";
        
        foreach($this->result as $key => $value) {
            if ($value) {
                $str .= "  test passed\n";
            } else {
                $str .= "  test failed\n";
            }
        }
        
        $str .= "\n\n";
        
        return $str;
    }
}
class TestClass {
	use ObjectHelper;
	
	var $rest;
	
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
	    $assert = new TestAssert($expected, $actual);
	    
	    $assert->result = (object) array(
				$method => (($expected == $actual) ? true : false)
		);
	
		return $assert;
	}
	function plottError($method, $e) {
	    $error = new Error ();
		$error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
		
        $assert = (object) array(
            $method => $error->details
		);
		
		return $assert;
	}
	function assertObject($method, $expected, $actual) {
	    $comp = new Comparator();
	    
	    $assert = new TestAssert($expected, $actual);
	    
	    $compResult = $comp->compareTwoObjects($expected, $actual, true, true, null);
	    
		$assert->result = (object) array(
		    $method => $comp->compareTwoObjects($expected, $actual, true, true, null)
		);
		
		if (!$assert->$method) {
			echo "actual:\n";
			print_r($actual);
			echo "expected:\n";
			print_r($expected);
		}
		
		return $assert;
	}
	/*function assertJson($method, $result, $classname, $id = null) {
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
	}*/
	function assertJson($method, $expected, $actual) {
	    $expectedDecoded = json_decode($expected);
	    $actualDecoded = json_decode($actual);
	    
	    $expectedEncoded = json_encode($expectedDecoded, JSON_PRETTY_PRINT);
	    $actualEncoded = json_encode($actualDecoded, JSON_PRETTY_PRINT);
	    
	    
	    return $this->assertString($method, $expectedEncoded, $actualEncoded);
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
			if (!in_array($method->name, array("__construct", "arrayContains"))) {
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
}
?>