<?php
class HTMLConverter extends XMLConverter {
	
	function __construct() {
	}
	function convertPDFHTMLToEDOM($resource) {
	    libxml_use_internal_errors(true);
	    
	    $doc = new Website_ConvertedPDF();
	    $doc->resource_path = $resource->url;
	    $doc->dom = new DOMDocument();
	    $doc->dom->loadHTML($resource->content);
	    $doc->document_type = $resource->Type;
	    
	    $stree = simplexml_import_dom($doc->dom);
		
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
			if ($sKey == "@attributes") {
				foreach($sValue as $sAttributeKey => $sAttributeValue) {
					if (property_exists($objectName, $sAttributeKey)) {
						$object->$sAttributeKey = $sAttributeValue;
					}
				}
			} else {
				if (property_exists($objectName, $sKey)) {
					if (is_object($sValue)) {
						$childObject = $this->convertSimpleObject($sObject->$sKey);
							
						$object->$sKey = $childObject;
					} else if (is_string($sValue)) {
						$object->$sKey = $sValue;
					}
				} else {
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
