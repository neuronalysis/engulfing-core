<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/Testing/Testing.php");

class ORM_Test extends TestClass {
	
	function __construct() {
	}
	function prepare() {
		$rest = new REST();
		
		$rest->deleteByNamedFieldValues("Release", array("name"), array("nameTest"));
		$rest->deleteByNamedFieldValues("Release", array("name"), array("nameTestUpdated"));
		
		$rest->deleteByNamedFieldValues("Release", array("name"), array("forDelete"));
		$fordelete = new Release();
		$fordelete->name = "forDelete";
		$fordelete->externalKey = "forDelete";
		$fordelete->save(null, array(), array(), true);
		
		$rest->deleteByNamedFieldValues("Release", array("name"), array("forGetById"));
		$fordelete = new Release();
		$fordelete->name = "forGetById";
		$fordelete->externalKey = "forGetById";
		$fordelete->save(null, array(), array(), true);
		
		$rest->deleteByNamedFieldValues("Release", array("name"), array("forGetByNamedFieldValues"));
		$fordelete = new Release();
		$fordelete->name = "forGetByNamedFieldValues";
		$fordelete->externalKey = "forGetByNamedFieldValues";
		$fordelete->save(null, array(), array(), true);
		
		$rest->deleteByNamedFieldValues("Release", array("externalKey"), array("forTotalAmount"));
		$fordelete = new Release();
		$fordelete->name = "forTotalAmount1";
		$fordelete->externalKey = "forTotalAmount";
		$fordelete->save(null, array(), array(), true);
		$fordelete = new Release();
		$fordelete->name = "forTotalAmount2";
		$fordelete->externalKey = "forTotalAmount";
		$fordelete->save(null, array(), array(), true);
		$fordelete = new Release();
		$fordelete->name = "forTotalAmount3";
		$fordelete->externalKey = "forTotalAmount";
		$fordelete->save(null, array(), array(), true);
		
	}
	function test() {
		$this->prepare();
		
		$coverage = parent::getTestCoverage();
		
		$check = new stdClass();
		$check->ORM = new stdClass();
		$check->ORM->coverage = $coverage;
		$check->ORM->methodAsserts = array();
		
		foreach($coverage->methods as $method => $coverage) {
			if ($coverage[0]) {
				echo "method: " . $method . "; " . $coverage[0] . "\n";
				$asserts = $this->$method();
				if (is_array($asserts)) {
					$check->ORM->methodAsserts = array_merge($check->ORM->methodAsserts, $asserts);
				} else {
					array_push($check->ORM->methodAsserts, $asserts);
				}
			}
			
		}
		
		
		return $check;
	}
	function getByNamedFieldValues() {
		try {
			$rest = new REST();
			$restTransformer = new REST_Transformer ();
				
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_getbynamedfieldvalues_' . strtolower("Release") . '.json');
				
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
				
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forGetByNamedFieldValues"));
			
			$assert = $this->assertJson("getByNamedFieldValues", $objects[0], "Release");
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;
	}
	function getById() {
		try {
			$rest = new REST();
			$restTransformer = new REST_Transformer ();
	
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_getbyid_' . strtolower("Release") . '.json');
	
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
	
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forGetById"));
			
			$object = $rest->getById("Release", $objects[0]->id);
			$assert = $this->assertJson("getById", $object, "Release");
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
	
			$assert = $error;
		}
	
		return $assert;
	}
	function deleteById() {
		try {
			$rest = new REST();
		
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forDelete"));
			
			$rest->deleteById("Release", $objects[0]->id);
			
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$assert = $this->assertNumerics("deleteById", 0, count($objects));
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;
	}
	function getAllByName() {
		try {
			$rest = new REST();
				
			$objects = $rest->getAllByName("Release", true);
	
			$assert = $this->assertNumerics("getAllByName", 6, count($objects));
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
	
			$assert = $error;
		}
	
		return $assert;
	}
	function getAllByNameLight() {
		try {
			$rest = new REST();
	
			$objects = $rest->getAllByNameLight("Release", array("name"), 0);
	
			$assert = $this->assertNumerics("getAllByNameLight", 6, count($objects));
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
	
			$assert = $error;
		}
	
		return $assert;
	}
	function getAllByQuery() {
		try {
			$rest = new REST();
			
			$sql = "SELECT releases.id, releases.name
				FROM releases
				WHERE releases.externalKey = 'forTotalAmount'
				ORDER BY name DESC";
			
			$objects = $rest->getAllByQuery($sql, "Release", array("name"));
				
			$assert = $this->assertNumerics("getAllByQuery", 3, count($objects));
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;
	}
	function deleteByNamedFieldValues() {
		try {
			$rest = new REST();
	
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$rest->deleteByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$objects = $rest->getByNamedFieldValues("Release", array("name"), array("forDelete"));
	
			$assert = $this->assertNumerics("deleteByNamedFieldValues", 0, count($objects));
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
	
			$assert = $error;
		}
	
		return $assert;
	}
	function save_new_simple() {
		try {
			$rest = new REST();
			$restTransformer = new REST_Transformer ();
			
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_new_simple_' . strtolower("Release") . '.json');
			
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
			
			$object = $result->save(null, array(), array(), true);
			
			$assert = $this->assertJson("save_new_simple", $object, "Release");
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;		
	}
	function save_existing_simple() {
		try {
			$rest = new REST();
			$restTransformer = new REST_Transformer ();
				
			$existing = $rest->getByNamedFieldValues("Release", array("name"), array("nameTest"));
			$existing[0]->name = "nameTestUpdated";
			$existing[0]->externalKey = 191;
				
			$object = $existing[0]->save(null, array(), array(), true);
				
			$assert = $this->assertJson("save_existing_simple", $object, "Release");
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
	
			$assert = $error;
		}
	
		return $assert;
	}
	function save() {
		$asserts = array();
	
		$assert = $this->save_new_simple("Release");
		array_push($asserts, $assert);
		
		$assert = $this->save_existing_simple("Release");
		array_push($asserts, $assert);
		
		return $asserts;
		
	}
	function getPaging() {
		try {
			$rest = new REST();
			
			$existing = $rest->getPaging("Release", "name", 50, "asc");
			
			$assert = $this->assertString("getPaging", "ORDER BY name asc LIMIT 15", $existing);
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;
	}
	function getTotalAmount() {
		try {
			$rest = new REST();
		
			$objects = $rest->getTotalAmount("Release");
				
			$assert = $this->assertNumerics("getTotalAmount", 6, $objects);
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getLine();
		
			$assert = $error;
		}
		
		return $assert;
	}
}
?>