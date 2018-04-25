<?php
class ObjectConverter extends Converter {
	use ObjectHelper;
	
	function convertToDOMDocument($object, $stickToClass = true, $stickToNamespace = true, $addNameSpacePrefix = false) {
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		
		$element = $this->convertToElement($object, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
			
		$dom->appendChild($element);
		
		return $dom;
	}
	//TODO function feels bloated. try to simplify/reduce
	function convertToElement($object, $dom, $stickToClass, $stickToNamespace = true, $addNameSpacePrefix = false) {
		$className = get_class($object);
		$classNameWithoutNS = $this->getNameWithoutNamespace(get_class($object));
		
		//echo $className . "; " . $classNameWithoutNS . "\n";
		$classNameForTagName = null;
		$classNameWithoutNSForTagName = null;
		
		if (get_parent_class($className) === "owl\\NamedIndividual") {
		    $classNameForTagName = "owl\\NamedIndividual";
		    $classNameWithoutNSForTagName= "NamedIndividual";
		} else {
		    $classNameForTagName = $className;
		    $classNameWithoutNSForTagName= $classNameWithoutNS;
		}
		
		if ($addNameSpacePrefix) {
		    $reflection = new ReflectionClass($classNameForTagName);
		    $nsName = $reflection->getNamespaceName();
		    
		    if($classNameWithoutNS == "ALTOString") {
		    	$element = $dom->createElement($nsName . ":String");
		    } else if ($classNameWithoutNS == "owlClass") {
		        $element = $dom->createElement($nsName . ":Class");
		    } else {
		        if ($classNameWithoutNSForTagName) {
		            $element = $dom->createElement($nsName . ":" . $classNameWithoutNSForTagName);
		        } else {
		            $element = $dom->createElement($nsName . ":" . $classNameWithoutNS);
		        }
		    }
		} else {
		    if($classNameWithoutNS == "ALTOString") {
		    	$element = $dom->createElement("String");
		    } else if ($classNameWithoutNS == "owlClass") {
		        $element = $dom->createElement("Class");
		    } else {
		        if ($classNameWithoutNSForTagName) {
		            $element = $dom->createElement($classNameWithoutNSForTagName);
		        } else {
		            $element = $dom->createElement($classNameWithoutNS);
		        }
		    }
		}
		
		
		if ($stickToClass) {
			$reflection = new ReflectionClass($className);
			$classvars = $reflection->getDefaultProperties();
			
			$tidied = null;
			
			if ($reflection->hasMethod("tidy")) {
				$tidied = $object->tidy();
			}
			
			if ($tidied) {
				foreach($classvars as $key => $value) {
					if (is_string($object->$key)) {
						$element->setAttribute($key, $object->$key);
					}
				}
				foreach($tidied as $key => $value) {
				    $childElement = $this->convertToElement($value, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
					
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
					
					if (isset($object->$key)) {
						if (is_object($object->$key)) {
					        
					        if (class_exists("ALTO\\" . $readKey)) {
					        	$childElement = $this->convertToElement($object->$readKey, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
								
								$element->appendChild($childElement);
							} else {
								if ($stickToNamespace) {
								    if (is_string($object->$readKey)) {
								        if ($addNameSpacePrefix) {
								            $element->setAttribute($nsName . ":" . $readKey, $object->$readKey);
								        } else {
								            $element->setAttribute($readKey, $object->$readKey);
								        }
										
									} else if (is_object($object->$readKey)) {
									    if (isset($object->$readKey->value)) {
									        if (is_string($object->$readKey->value)) {
									            if ($addNameSpacePrefix) {
									                $attributeRC = new ReflectionClass($object->$readKey);
									                
									                $nsName = $attributeRC->getNamespaceName();
									                
									                $element->setAttribute($nsName . ":" . $readKey, $object->$readKey->value);
									            } else {
									                $element->setAttribute($readKey, $object->$readKey->value);
									            }
									        } else {
									            if ($addNameSpacePrefix) {
									                $childElement = $this->convertToElement($object->$readKey, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
									                
									                $element->appendChild($childElement);
									            }
									        }
									    } else {
									        if ($addNameSpacePrefix) {
									            $childElement = $this->convertToElement($object->$readKey, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
									            
									            $element->appendChild($childElement);
									        }
									    }
									    
									    
									}
								} else {
								    if (class_exists($readKey)) {
								        $childElement = $this->convertToElement($object->$readKey, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
										
										$element->appendChild($childElement);
									} else {
									    if ($addNameSpacePrefix) {
									        $element->setAttribute($nsName . ":" . $readKey, $object->$readKey);
									    } else {
									        $element->setAttribute($readKey, $object->$readKey);
									    }
										
									}
								}
							}
						} else if (is_string($object->$key)) {
							if (class_exists("ALTO\\" . $key)) {
								$childElement = $dom->createElement($key, $object->$readKey);
								
								$element->appendChild($childElement);
							} else {
								if ($stickToNamespace) {
								    if ($addNameSpacePrefix) {
								        if (class_exists($readKey)) {
								            $attributeRC = new ReflectionClass($readKey);
								            
								            $nsName = $attributeRC->getNamespaceName();
								            
								            $element->setAttribute($nsName . ":" . $key, $object->$readKey);
								        } else {
								            
								        }
								        
								    } else {
								        $element->setAttribute($key, $object->$readKey);
								    }
								} else {
									if (class_exists($readKey)) {
										$childElement = $dom->createElement($key, $object->$readKey);
										
										$element->appendChild($childElement);
									} else {
										$element->setAttribute($key, $object->$readKey);
									}
								}
							}
						} else if (is_array($object->$key)) {
						    
							if ($this->isAssoc($object->$key)) {
							    if ($key === "keyValues") {
							        foreach($object->$readKey as $aKey => $aValue) {
							            $childElement = $dom->createElement($aKey, $aValue);
							            
							            $element->appendChild($childElement);
							        }
							    } else {
							    	foreach($object->$key as $aKey => $aValue) {
							            if ($aKey== "Strings") {
							                $readKey = "ALTOStrings";
							            } else {
							                $readKey = $aKey;
							            }
							            
							            
							            if (class_exists("ALTO\\" . $readKey)) {
							                $childElement = $this->convertToElement($aValue, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
							                
							                $element->appendChild($childElement);
							            } else {
							                if ($stickToNamespace) {
							                    $element->setAttribute($aKey, $aValue);
							                } else {
							                    if (class_exists($readKey)) {
							                        $childElement = $this->convertToElement($aValue, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
							                        
							                        $element->appendChild($childElement);
							                    } else {
							                        $element->setAttribute($aKey, $aValue);
							                    }
							                }
							            }
							        }
							    }
								
							} else {
								foreach($object->$key as $aValue) {
							        $childElement = $this->convertToElement($aValue, $dom, $stickToClass, $stickToNamespace, $addNameSpacePrefix);
									
									$element->appendChild($childElement);
								}
							}
							
						}
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
