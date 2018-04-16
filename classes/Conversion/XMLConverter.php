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
    function extractObjectNameFromNode($node) {
        $objectName = $node->nodeName;
        
        if (strpos($objectName, ':') !== FALSE) {
            $nsObjName = explode(":", $objectName);
            
            if ($nsObjName[1] === "Class") $nsObjName[1] = $nsObjName[0] . $nsObjName[1];
            
            if (class_exists("\\" . $nsObjName[0] . "\\" . $nsObjName[1])) {
                $objectName = "\\" . $nsObjName[0] . "\\" . $nsObjName[1];
            } else if (class_exists("\\" . $this->scope . "\\" . $objectName)) {
                $objectName = "\\" . $this->scope . "\\" . $objectName;
            } else if (class_exists("\\" . $this->scope . "\\" . $this->scope . $objectName)) {
                $objectName = "\\" . $this->scope . "\\" . $this->scope . $objectName;
            } else {
                return false;
            }
        } else {
            if (class_exists("\\" . $this->scope . "\\" . $objectName)) {
                $objectName = "\\" . $this->scope . "\\" . $objectName;
            } else if (class_exists("\\" . $this->scope . "\\" . $this->scope . $objectName)) {
                $objectName = "\\" . $this->scope . "\\" . $this->scope . $objectName;
            } else {
                return false;
            }
        }
        
        return $objectName;
    }
    function extractObjectNameWithoutNSFromNode($node) {
        $objectName = $node->nodeName;
        
        if (strpos($objectName, ':') !== FALSE) {
            $nsObjName = explode(":", $objectName);
            
            if (class_exists("\\" . $nsObjName[0] . "\\" . $nsObjName[1])) {
                $objectName = $nsObjName[1];
            } else if (class_exists("\\" . $nsObjName[0]. "\\" . $nsObjName[0] . $nsObjName[1])) {
                $objectName = $nsObjName[0] . $nsObjName[1];
            } else {
                return false;
            }
        } else {
            if (class_exists("\\" . $this->scope . "\\" . $objectName)) {
                $objectName = $objectName;
            } else if (class_exists("\\" . $this->scope . "\\" . $this->scope . $objectName)) {
                $objectName = $this->scope . $objectName;
            } else {
                return false;
            }
        }
        
        return $objectName;
    }
    function convertNodesToObjects($node) {
        $objectName = $this->extractObjectNameFromNode($node);
        
        $object = new $objectName;
        
        if ($node->attributes) {
            foreach($node->attributes as $attribute_item) {
                $attributeName = $this->extractObjectNameFromNode($attribute_item);
                $attributeNameWithoutNS = $this->extractObjectNameWithoutNSFromNode($attribute_item);
                
                if (class_exists($attributeName)) {
                    $childAttributeObject = $this->convertNodesToObjects($attribute_item);
                    $childAttributeObject->value = $attribute_item->nodeValue;
                    
                    $object->$attributeNameWithoutNS = $childAttributeObject;
                } else {
                    if (property_exists($objectName, $attributeNameWithoutNS)) {
                        $object->$attributeNameWithoutNS = $attribute_item->nodeValue;
                    }
                }
                
                
            }
        }
        
        foreach($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                $childNodeName = $this->extractObjectNameWithoutNSFromNode($childNode);
                $plChildNodeName = $this->pluralize($childNodeName);
                
                if (property_exists($objectName, $childNodeName)) {
                    $childObject = $this->convertNodesToObjects($childNode);
                    
                    $object->$childNodeName = $childObject;
                } else if (property_exists($objectName, $plChildNodeName)) {
                    $childObject = $this->convertNodesToObjects($childNode);
                    
                    array_push($object->$plChildNodeName, $childObject);
                }
                
                
            } else if ($childNode->nodeType === XML_TEXT_NODE) {
            } else if ($childNode->nodeType === XML_COMMENT_NODE) {
            } else {
            }
            
            
        }
        
        return $object;
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
