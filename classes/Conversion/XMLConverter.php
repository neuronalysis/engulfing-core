<?php
class XMLConverter extends Converter {
	
	var $scope;
	var $xpath;
	
	function __construct($scope, $xpath = null) {
		$this->scope = $scope;
		$this->xpath = $xpath;
	}
	function convertToObjectTree($dom) {
		$stree = simplexml_import_dom($dom);
		
		$tree = $this->convertSimpleObject($stree);
		
		return $tree;
	}
	function convertSimpleObject($sObject) {
		$objectName = $sObject->getName();
		
		if (class_exists("\\" . $this->scope . "\\" . $objectName)) {
			$objectName = "\\" . $this->scope . "\\" . $objectName;
		} else if (class_exists("\\" . $this->scope . "\\" . $this->scope . $objectName)) {
			$objectName = "\\" . $this->scope . "\\" . $this->scope . $objectName;
		} else {
			echo "unknown object: " . $objectName . "\n";
			
		}
		
		$object = new $objectName;
		
		
		
		$sObjVars = get_object_vars($sObject);
		
		foreach($sObjVars as $sKey => $sValue) {
			//echo " -- " . $sKey . "\n";
			
			if ($sKey == "@attributes") {
				foreach($sValue as $sAttributeKey => $sAttributeValue) {
					if (property_exists($objectName, $sAttributeKey)) {
						$object->$sAttributeKey = $sAttributeValue;
					}
					
				}
			} else {
				if (property_exists($objectName, $sKey)) {
					if (is_object($sValue)) {
						//echo $sKey . " exists on " . $objectName . " and has object as value " . "\n";
							
						$childObject = $this->convertSimpleObject($sObject->$sKey);
							
						$object->$sKey = $childObject;
							
							
					} else if (is_string($sValue)) {
						$object->$sKey = $sValue;
					}
				
				
				} else {
					//echo $sKey . "\n";
					if (is_array($sValue)) {
						$sKeyPlural = $this->pluralize($sKey);
						if (property_exists($objectName, $sKeyPlural)) {
							$valueArray = array();
				
							foreach($sValue as $sValueItem) {
								array_push($valueArray, $this->convertSimpleObject($sValueItem));
							}
				
							$object->$sKeyPlural = $valueArray;
						}
					} else {
						$sKeyPlural = $this->pluralize($sKey);
						//echo $sKeyPlural . "\n";
						if (property_exists($objectName, $sKeyPlural)) {
							$valueArray = array();
								
							if (is_object($sValue)) {
								$childObject = $this->convertSimpleObject($sValue);
									
								array_push($valueArray, $childObject);
							}
				
							$object->$sKeyPlural = $valueArray;
						}
					}
				
				}
			}
			
		}
		
			
		return $object;
	}
}
?>
