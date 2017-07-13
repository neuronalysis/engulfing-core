<?php
class ObjectConverter extends Converter {
	use ObjectHelper;
	
	function convertToDOMDocument($object, $stickToClass = true) {
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		$element = $this->convertToElement($object, $dom, $stickToClass);
			
		$dom->appendChild($element);
		
		return $dom;
	}
	function convertToElement($object, $dom, $stickToClass) {
		/*if (strlen(get_class($object)) > 4 && substr(get_class($object), 0, 4) == "ALTO") {
			$className = str_replace("ALTO", "", get_class($object));
		} else {
			$className = get_class($object);
		}*/
		$className = get_class($object);
		
		$classNameWithoutNS = $this->getNameWithoutNamespace(get_class($object));
		//echo $classNameWithoutNS. "\n";
		
		
		if($classNameWithoutNS == "ALTOString") {
			$element = $dom->createElement("String");
		} else {
			$element = $dom->createElement($classNameWithoutNS);
		}
		
		
		if ($stickToClass) {
			$reflection = new ReflectionClass($className);
			$classvars = $reflection->getDefaultProperties();
			
			$tidied = null;
			
			if ($reflection->hasMethod("tidy")) {
				$tidied = $object->tidy();
			}
			//print_r($classvars);
			
			if ($tidied) {
				foreach($classvars as $key => $value) {
					if (is_string($object->$key)) {
						$element->setAttribute($key, $object->$key);
					}
				}
				foreach($tidied as $key => $value) {
					$childElement = $this->convertToElement($value, $dom, $stickToClass);
					
					$element->appendChild($childElement);
				}
			} else {
				foreach($classvars as $key => $value) {
					if ($key == "Strings") {
						$readKey = "ALTOStrings";
					} else {
						$readKey = $key;
					}
					
					str_ireplace("XML", "", $key, $rplCount);
					if ($rplCount == 1) $key = strtoupper($key);
					
					$sKey = $this->singularize($key);
					
					if (isset($object->$readKey)) {
						if (is_object($object->$readKey)) {
							$childElement = $this->convertToElement($object->$readKey, $dom, $stickToClass);
							
							$element->appendChild($childElement);
						} else if (is_string($object->$readKey)) {
							if (class_exists("ALTO\\" . $key)) {
								$childElement = $dom->createElement($key, $object->$readKey);
								
								$element->appendChild($childElement);
							} else {
								if (class_exists($readKey)) {
									$childElement = $dom->createElement($key, $object->$readKey);
									
									$element->appendChild($childElement);
								} else {
									$element->setAttribute($key, $object->$readKey);
								}
							}
						} else if (is_array($object->$readKey)) {
							//echo $key . "; " . $sKey . "\n";
							
							foreach($object->$readKey as $item) {
								$childElement = $this->convertToElement($item, $dom, $stickToClass);
								
								$element->appendChild($childElement);
							}
						} else {
							echo "fuck\n";
						}
					} else {
						$sKey = $this->singularize($key);
						
						if (class_exists("ALTO\\" . $sKey)) {
							$childElement = $dom->createElement($sKey, null);
							
							//$element->appendChild($childElement);
						} else {
							if (class_exists($readKey)) {
								$childElement = $dom->createElement($key, null);
								
								//$element->appendChild($childElement);
							} else {
								if ($key == "ID") {
									//$element->setAttribute($key, null);
								}
							}
						}
						//echo "fuck : " . $className . " - > " . $key . "; " . $sKey . "\n";
						//print_r($object);
					}
				}
			}
			
		}
		
		return $element;
	}
	function convertToOntology($source) {
		if (is_array($source)) {
			$target = array();
			
			foreach ($source as $object) {
				$oce = $this->convertToOntologyClassEntities($object);
				
				array_push($target, $oce);
			}
			
			return $target;
		} else {
			$target = $this->convertToOntologyClassEntities(array($source));
			
			return $target[0];
		}
	}
	function convertToOntologyClassEntities($objects, $object_name = null, $onlyNameOnRelated = false) {
		$oces = array();
	
		$km = new KM();
		if (!isset($objects[0])) {
			return null;
		}
		$ontologyClass = $km->getOntologyClassByName(get_class($objects[0]), true);
			
		if (!$ontologyClass->RelationOntologyClassOntologyProperties) {
			$relOCOPs = $ontologyClass->getRelationOntologyClassOntologyProperties();
		} else {
			$relOCOPs = $ontologyClass->RelationOntologyClassOntologyProperties;
		}
		if (!$ontologyClass->RelationOntologyClassOntologyClasses) {
			$relOCOCs = $ontologyClass->getRelationOntologyClassOntologyClasses();
		} else {
			$relOCOCs = $ontologyClass->RelationOntologyClassOntologyClasses;
		}
		
		$ontologyClass->RelationOntologyClassOntologyProperties = $relOCOPs;
		$ontologyClass->RelationOntologyClassOntologyClasses = $relOCOCs;
		
		$oclassProperties = $ontologyClass->getOntologyProperties();
		
		$oclassIncomingClasses = $ontologyClass->getIncomingOntologyClasses();
		
			
		
		if (count($objects) > 5) {
				
			$objects = array_slice($objects, 0, 5);
		}
	
		foreach($objects as $item_object) {
			$oce = new OntologyClassEntity();
			$oce->OntologyClass = $ontologyClass;
				
			if (!method_exists($item_object, "initialize")) {
				$oce->id = $item_object->id;
			}
				
			foreach($oclassProperties as $oclassProperty) {
				$opName = lcfirst($oclassProperty->name);
				if (!isset($item_object->$opName)) {
					if (strlen($oclassProperty->name < 5)) {
						$opName = strtolower($oclassProperty->name);
					}
				}
					
				if ($onlyNameOnRelated) {
					if (isset($item_object->$opName) && $opName === "name") {
						$oce->setOPEntity($oclassProperty->name, $item_object->$opName);
					}
				} else {
					if (isset($item_object->$opName)) {
						$oce->setOPEntity($oclassProperty->name, $item_object->$opName);
					}
				}
	
			}
				
			foreach($oclassIncomingClasses as $oclassIncomingClass) {
				$iocName = $oclassIncomingClass->name;
	
				$relationType = $ontologyClass->getRelationTypeFromIOCByName($iocName);
	
				$iocNamePluralized = $this->pluralize($iocName);
	
				if ($relationType->name === "hasMany") {
					if (isset($item_object->$iocName)) {
						$sub_oces = $this->convertToOntologyClassEntities(array($item_object->$iocName), null, true);
							
						$oce->setRelatedClassEntity($sub_oces[0], "hasOne");
					} else if (isset($item_object->$iocNamePluralized)) {
						$sub_oces = $this->convertToOntologyClassEntities($item_object->$iocNamePluralized, null, true);
						
						if ($sub_oces) {
							foreach($sub_oces as $subOceItem) {
								$oce->setRelatedClassEntity($subOceItem, "hasOne");
							}
						}
					}
				} else {
					if (isset($item_object->$iocName)) {
						$sub_oces = $this->convertToOntologyClassEntities(array($item_object->$iocName));
							
						$oce->setRelatedClassEntity($sub_oces[0], "hasOne");
					} else if (isset($item_object->$iocNamePluralized)) {
						$sub_oces = $this->convertToOntologyClassEntities($item_object->$iocNamePluralized);
						
						if (isset($sub_oces)) {
							foreach($sub_oces as $subOceItem) {
								$oce->setRelatedClassEntity($subOceItem, "hasOne");
							}
						}
						
					}
				}
	
			}
				
			array_push($oces, $oce);
		}
	
		return $oces;
	}
	function convertConcreteEntityToOntologyClassEntity($concretes) {
		$oces = $this->getOntologyClassEntitiesByObjects($concretes, null, true);
		
		return $oces[0];
	}
	function hasObject($Ontologies, $property_name) {
		foreach ($Ontologies as $item_Ontology) {
			$i=0;
			foreach ($item_Ontology as $item_oc_entity) {
				if (strtolower($item_oc_entity->OntologyClass->name) == substr($property_name, strlen($property_name) - strlen($item_oc_entity->OntologyClass->name), strlen($item_oc_entity->OntologyClass->name))) {
					$object = $this->convertToObject($item_oc_entity);
					$object->index = $i;
	
					return $object;
				}
				
				$i++;
			}
		}
	
		return false;
	}
	function isAssoc(array $arr) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}
?>
