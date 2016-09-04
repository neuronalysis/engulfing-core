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
	
	function prepare($method = null, $object = "Release") {
		if ($method && $object) {
			$this->orm->deleteByNamedFieldValues($object, array("name"), array(""));
			$this->orm->deleteByNamedFieldValues($object, array("name"), array($method . "_" . $object));
			$this->orm->deleteByNamedFieldValues($object, array("name"), array($method . "_" . $object . "_updated"));
		} else {
			$this->orm->deleteByNamedFieldValues("Indicator", array("name"), array("Total Consumer Credit Owned and Securitized, Outstanding"));
			$this->orm->deleteByNamedFieldValues("Indicator", array("name"), array("Total Consumer Credit Owned and Securitized"));
			$this->orm->deleteByNamedFieldValues("Indicator", array("name"), array("Total Consumer Loans Owned by Credit Unions, Outstanding"));
			
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array(""));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("saveSimple"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("saveSimpleUpdated"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("save_new_simple_Release"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("save_new_nested_Release"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("save_existing_simple_Release_updated"));
			$this->orm->deleteByNamedFieldValues("Indicator", array("name"), array("save_new_nested_Indicator"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("saveNestedReleaseUpdated"));
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("saveNestedIndicatorUpdated"));
			
			$mock = new Release(array("name" => "forDelete", "externalKey" => "forDelete"));
			$this->orm->replace($mock);
			
			$mock = new Release(array("name" => "forGetById", "externalKey" => "forGetById"));
			$this->orm->replace($mock);
			
			$mock = new Release(array("name" => "forGetByNamedFieldValues", "externalKey" => "forGetByNamedFieldValues"));
			$this->orm->replace($mock);
			
			$mock = new Release(array("name" => "forTotalAmount1", "externalKey" => "forTotalAmount"));
			$this->orm->replace($mock);
			
			$mock = new Release(array("name" => "forTotalAmount2", "externalKey" => "forTotalAmount"));
			$this->orm->replace($mock);
			
			$mock = new Release(array("name" => "forTotalAmount3", "externalKey" => "forTotalAmount"));
			$this->orm->replace($mock);
		}
	}
	function getByNamedFieldValues() {
		try {
			$restTransformer = new REST_Transformer ();
				
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_getbynamedfieldvalues_' . strtolower("Release") . '.json');
				
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
				
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forGetByNamedFieldValues"));
			
			$assert = $this->assertObject("getByNamedFieldValues", $objects[0], $result);
		} catch ( Exception $e ) {
			$assert = $this->plottError("getByNamedFieldValues", $e);
		}
		
		return $assert;
	}
	function getById() {
		try {
			$restTransformer = new REST_Transformer ();
	
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_getbyid_' . strtolower("Release") . '.json');
	
			$result = $restTransformer->deserialize_JSON ( $inputJson, "Release" );
	
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forGetById"));
			
			$object = $this->orm->getById("Release", $objects[0]->id);
			$assert = $this->assertObject("getByNamedFieldValues", $object, $result);
		} catch ( Exception $e ) {
			$assert = $this->plottError("getById", $e);
		}
	
		return $assert;
	}
	function deleteById() {
		try {
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forDelete"));
			
			$this->orm->deleteById("Release", $objects[0]->id);
			
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$assert = $this->assertNumerics("deleteById", 0, count($objects));
		} catch ( Exception $e ) {
			$assert = $this->plottError("deleteById", $e);
		}
		
		return $assert;
	}
	function getAllByName() {
		try {
			$objects = $this->orm->getAllByName("Release", true);
	
			$assert = $this->assertNumerics("getAllByName", 6, count($objects));
		} catch ( Exception $e ) {
			$assert = $this->plottError("getAllByName", $e);
		}
	
		return $assert;
	}
	function getAllByNameLight() {
		try {
			$objects = $this->orm->getAllByNameLight("Release", array("name"), 0);
	
			$assert = $this->assertNumerics("getAllByNameLight", 6, count($objects));
		} catch ( Exception $e ) {
			$assert = $this->plottError("getAllByNameLight", $e);
		}
	
		return $assert;
	}
	function getAllByQuery() {
		try {
			$sql = "SELECT releases.id, releases.name
				FROM releases
				WHERE releases.externalKey = 'forTotalAmount'
				ORDER BY name DESC";
			
			$objects = $this->orm->getAllByQuery($sql, "Release", array("name"));
				
			$assert = $this->assertNumerics("getAllByQuery", 3, count($objects));
		} catch ( Exception $e ) {
			$assert = $this->plottError("getAllByQuery", $e);
		}
		
		return $assert;
	}
	function deleteByNamedFieldValues() {
		try {
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$this->orm->deleteByNamedFieldValues("Release", array("name"), array("forDelete"));
				
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("forDelete"));
	
			$assert = $this->assertNumerics("deleteByNamedFieldValues", 0, count($objects));
		} catch ( Exception $e ) {
			$assert = $this->plottError("deleteByNamedFieldValues", $e);
		}
	
		return $assert;
	}
	function save_new_simple($classname = "Release") {
		try {
			$restTransformer = new REST_Transformer ();
			
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_new_simple_' . strtolower($classname) . '.json');
			
			$result = $restTransformer->deserialize_JSON ( $inputJson, $classname );
			
			$this->orm->save($result);
			$objects = $this->orm->getByNamedFieldValues($classname, array("name"), array("save_new_simple_" . $classname));
			
			$assert = $this->assertObject("save_new_simple", $objects[0], $result);
		} catch ( Exception $e ) {
			$assert = $this->plottError("save_new_simple", $e);
		}
		
		return $assert;		
	}
	/*function save_new_nested($classname = "Release") {
		try {
			$restTransformer = new REST_Transformer ();
			
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_new_nested_' . strtolower($classname) . '.json');
			$resultForSave = $restTransformer->deserialize_JSON ( $inputJson, $classname );
			
			$this->orm->save($resultForSave);
			
			$objects = $this->orm->getByNamedFieldValues($classname, array("name"), array("save_new_nested_" . $classname));
			
			if (count($objects) == 1) {
				$object = $this->orm->getById($classname, $objects[0]->id, true);
			}
			$actual = $object;
			
			$resultForExpected = $restTransformer->deserialize_JSON ( $inputJson, $classname );
				
			$assert = $this->assertObject("save_new_nested_" . $classname , $resultForExpected, $actual);
		} catch ( Exception $e ) {
			$assert = $this->plottError("save_new_nested_" . $classname, $e);
		}
		
		return $assert;		
	}*/
	function save_existing_simple($classname = "Release") {
		try {
			$restTransformer = new REST_Transformer ();
				
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_new_simple_' . strtolower($classname) . '.json');
			$result = $restTransformer->deserialize_JSON ( $inputJson, $classname );
			$this->orm->save($result);
				
			
			
			$assertJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_existing_simple_' . strtolower($classname) . '.json');
				
			$existing = $this->orm->getByNamedFieldValues($classname, array("name"), array("save_new_simple_" . $classname));
			$existing[0]->name = "save_existing_simple_" . $classname . "_updated";
			$existing[0]->externalKey = "191";
			$this->orm->save($existing[0]);
			
			$objects = $this->orm->getByNamedFieldValues($classname, array("name"), array("save_existing_simple_" . $classname . "_updated"));
			if (count($objects) == 1) {
				$object = $this->orm->getById($classname, $objects[0]->id, true);
			}
			
			$resultForExpected = $restTransformer->deserialize_JSON ( $assertJson, $classname );
				
			$assert = $this->assertObject("save_existing_simple", $resultForExpected, $object);
		} catch ( Exception $e ) {
			$assert = $this->plottError("save_existing_simple", $e);
		}
	
		return $assert;
	}
	/*function save_existing_nested($classname = "Release") {
		try {
			$restTransformer = new REST_Transformer ();
			
			$newJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_new_nested_' . strtolower($classname) . '.json');
			$resultForSave = $restTransformer->deserialize_JSON ( $newJson, $classname );
			$this->orm->save($resultForSave);
				
			
			$inputJson = file_get_contents('../engulfing/engulfing-core/test/asserts/orm_save_existing_nested_' . strtolower($classname) . '.json');
				
			$existing = $this->orm->getByNamedFieldValues($classname, array("name"), array("save_new_nested_" . $classname));
			$existing[0]->name = "save_existing_nested_" . $classname . "_updated";
			$existing[0]->externalKey = "1919";
			$existing[0]->ReleasePublications = null;
				
			//$object = $existing[0]->save(null, array(), array(), true);
			$this->orm->save($existing[0]);
			$objects = $this->orm->getByNamedFieldValues("Release", array("name"), array("save_existing_nested_" . $classname . "_updated"));
			if (count($objects) == 1) {
				$object = $this->orm->getById($classname, $objects[0]->id, true);
			}
			
			//print_r($object);
				
			$resultForExpected = $restTransformer->deserialize_JSON ( $inputJson, $classname );
				
			//print_r($resultForExpected);
				
			$assert = $this->assertObject("save_existing_nested", $resultForExpected, $object);
		} catch ( Exception $e ) {
			$assert = $this->plottError("save_existing_nested", $e);
		}
	
		return $assert;
	}*/
	function save() {
		$asserts = array();
	
		$assert = $this->save_new_simple("Release");
		array_push($asserts, $assert);
		
		$assert = $this->save_existing_simple("Release");
		array_push($asserts, $assert);
		
		//$assert = $this->save_new_nested("Release");
		//array_push($asserts, $assert);
		
		//$assert = $this->save_new_nested("Indicator");
		//array_push($asserts, $assert);
		
		//$assert = $this->save_existing_nested("Release");
		//array_push($asserts, $assert);
		
		return $asserts;
		
	}
	function getPaging() {
		try {
			$existing = $this->orm->getPaging("Release", "name", 50, "asc");
			
			$assert = $this->assertString("getPaging", "ORDER BY name asc LIMIT 15", $existing);
		} catch ( Exception $e ) {
			$assert = $this->plottError("getPaging", $e);
		}
		
		return $assert;
	}
	function getTotalAmount() {
		try {
			$objects = $this->orm->getTotalAmount("Release");
				
			$assert = $this->assertNumerics("getTotalAmount", 6, $objects);
		} catch ( Exception $e ) {
			$assert = $this->plottError("getTotalAmount", $e);
		}
		
		return $assert;
	}
}
?>