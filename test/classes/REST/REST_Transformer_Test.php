<?php
class REST_Transformer_Test extends TestClass {
	var $configs;
	
	function __construct() {
		$this->testobject = new REST_Transformer2();
	}
	function setConfigs($configs) {
		$this->configs = $configs;
	}
	function prepare() {
		
	}
	function deserialize_JSON() {
		$asserts = array();
		
		try {
			$objconv = new ObjectConverter();
			$xmlconv = new XMLConverter("ALTO");
			
			$assertJSON = file_get_contents(__DIR__ . "/../../mocks/rest_transformer_deserialize_json_1.json");
			$assertJSON = mb_convert_encoding($assertJSON, 'HTML-ENTITIES', "UTF-8");
			
			$alto = $this->testobject->deserialize_JSON($assertJSON, "alto", false, "ALTO");
			
			$altoXML = $objconv->convertToDOMDocument($alto->alto);
			
			$currentALTO = $xmlconv->convertToObjectTree($altoXML);
			
			$resultedJSON = json_encode($currentALTO, JSON_PRETTY_PRINT);
			
			$assert = $this->assertString("deserialize_JSON", $assertJSON, $resultedJSON);
			
			$assert->input = array("rest_transformer_deserialize_json_1.json");
			
			array_push($asserts, $assert);
			
		} catch ( Exception $e ) {
			$assert = $this->plottError("deserialize_JSON", $e);
		}
		
		return $asserts;
	}
}
?>