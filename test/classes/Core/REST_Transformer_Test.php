<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/Testing/Testing.php");

class REST_Transformer_Test extends TestClass {
	
	function prepare() {
		$mock = new Release(array("name" => "forDeserialize_JSON", "externalKey" => "forDeserialize_JSON"));
		$this->orm->replace($mock);
	}
	function test() {
		$this->prepare();
		
		$coverage = parent::getTestCoverage();
		
		$check = new stdClass();
		$check->REST_Transformer = new stdClass();
		$check->REST_Transformer->coverage = $coverage;
		$check->REST_Transformer->methodAsserts = array();
		
		
		foreach($coverage->methods as $method => $coverage) {
			if ($coverage[0]) {
				$asserts = $this->$method();
				if (is_array($asserts)) {
					$check->REST_Transformer->methodAsserts = array_merge($check->REST_Transformer->methodAsserts, $asserts);
				} else {
					array_push($check->REST_Transformer->methodAsserts, $asserts);
				}
			}
			
		}
		
		return $check;
	}
	function deserialize_JSON() {
		try {
			$restTransformer = new REST_Transformer ();
			
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forDeserialize_JSON"));
	
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/rest_transformer_deserialize_json_' . strtolower("Release") . '.json');
				
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
			
			print_r($objects[0]);
			print_r($result);
			$assert = $this->assertObject("deserialize_JSON", $objects[0], $result);
		} catch ( Exception $e ) {
			$assert = $this->plottError("deserialize_JSON", $e);
		}
		
		return $assert;
	}
}
?>