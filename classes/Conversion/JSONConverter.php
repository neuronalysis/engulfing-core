<?php
class JSONConverter extends Converter {
	
	var $scope;
	var $baseClass;
	
	function __construct($scope, $class_name = null) {
		$this->scope = $scope;
		$this->baseClass = $class_name;
		
	}
	function convertToObjectTree($json) {
		$object = json_decode($json, TRUE);
		
		
		return $object;
	}
	
	/*function convertToObjectTree($json) {
		if (class_exists($dom->documentElement->tagName)) {
			$tree = new $dom->documentElement->tagName;
			
			foreach($dom->documentElement->childNodes as $childNode) {
				if($childNode->nodeType == 1) {
					$objectName = $childNode->tagName;
					
					$object = $this->convertToObject($childNode);
					
					$tree->$objectName = $object;
				}
			}
		} else if (class_exists("\\" . $this->scope . "\\" . $dom->documentElement->tagName)) {
			$treeName = "\\" . $this->scope . "\\" . $dom->documentElement->tagName;
			
			$tree = new $treeName;
				
			foreach($dom->documentElement->childNodes as $childNode) {
				if($childNode->nodeType == 1) {
					$objectName = $childNode->tagName;
						
					$object = $this->convertToObject($childNode);
						
					$tree->$objectName = $object;
				}
			}
		}
		
		return $tree;
	}
	function convertToObject($node) {
		if (class_exists($node->tagName)) {
			$nodeObjectName = ucfirst($node->tagName);
		} else if (class_exists($this->scope . $node->tagName)) {
			$nodeObjectName = ucfirst($this->scope . $node->tagName);
		} else if (class_exists("\\" . $this->scope . "\\" . $node->tagName)) {
			$treeName = "\\" . $this->scope . "\\" . $node->tagName;
			
			$nodeObjectName = ucfirst($treeName);
		} else if (class_exists("\\" . $this->scope . "\\" . $this->scope . $node->tagName)) {
			$treeName = "\\" . $this->scope . "\\" . $this->scope . $node->tagName;
				
			$nodeObjectName = ucfirst($treeName);
		} else {
			return null;
		}
		
		$object = new $nodeObjectName;
			
		foreach($node->childNodes as $childNode) {
			if($childNode->nodeType == 1) {
				$childObjectName = ucfirst($childNode->tagName);
				
				$childObject = $this->convertToObject($childNode);
				
				if ($childNode->hasAttributes()) {
					foreach ($childNode->attributes as $attr) {
						$name = strtolower($attr->nodeName);
						$value = $attr->nodeValue;
						
						if (class_exists(get_class($childObject))) {
							if (get_class($childObject) == "XMLConverter") {
								echo $childNode->tagName . "\n";
							}
							
							if(property_exists($childObject, $name)) {
								$childObject->$name = $value;
							}
						}
						
						
					}
				}
				
				if(property_exists($object, $childObjectName)) {
					
					$object->$childObjectName = $childObject;
				} else if (property_exists($object, $this->pluralize($childObjectName))) {
					$objectPropertyName = ucfirst($this->pluralize($childObjectName));
		
					if (!is_array($object->$objectPropertyName)) $object->$objectPropertyName = array();
		
					array_push($object->$objectPropertyName, $childObject);
				}
			}
		}
			
		return $object;
		
		
	}
	*/
}
?>
