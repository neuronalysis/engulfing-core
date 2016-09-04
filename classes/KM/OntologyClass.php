<?php
class OntologyClass extends OntologyClass_Generated {
	//protected $relations = array("Lexemes", "Ontology", "Ressource", "RelationOntologyClassOntologyClass" => array("IncomingOntologyClass", "OntologyRelationType"), "RelationOntologyClassOntologyProperty" => array("OntologyProperty"));
	//var $loadingMode = array("RelationOntologyClassOntologyClasses" => "lazy", "RelationOntologyClassOntologyProperties" => "lazy");
	
	//use OntologyClass_ORM;
	
    function __construct() {
	}
	function inherit() {
		if ($superclass = $this->hasSuperClass()) {
			$inherited_properties = array();
			
			foreach($superclass->properties as $item_ontoproperty) {
				$inherited_property = clone $item_ontoproperty;
				
				$inherited_property->name = str_replace(strtolower($superclass->name), strtolower($this->name), $inherited_property->name);
				
				$inherited_property->OntologyClass = $this;
				
				array_push($inherited_properties, $inherited_property);
			}
			
			$inherited_properties = array_reverse($inherited_properties);
			foreach($inherited_properties as $item_inherited) {
				if (!$this->hasProperty($item_inherited->name)) {
					array_unshift($this->properties, $item_inherited);
				}
				
			}
		}
	}
	function getOntology() {
		$o = $this->orm->getByNamedFieldValues("Ontology", array("ontologyID"), array($this->ontologyID));
		
		return $o;
	}
	function getRessource() {
		if (isset($this->ressourceID)) {
			$r = $this->orm->getById("Ressource", $this->ressourceID);
		} else {
			$r = null;
		}
		
	
		return $r;
	}
	function getOntologyClassEntitiesByObjects($objects, $object_name = null, $onlyNameOnRelated = false) {
		$oces = array();
		
		if (count($objects) > 5) {
			
			$objects = array_slice($objects, 0, 5);
		}
		
		foreach($objects as $item_object) {
			$oce = new OntologyClassEntity();
			$oce->OntologyClass = $this;
			
			if (!method_exists($item_object, "initialize")) {
				$oce->id = $item_object->id;
			}
			
			if (!$this->RelationOntologyClassOntologyProperties) {
				$relOCOPs = $this->getRelationOntologyClassOntologyProperties();
			} else {
				$relOCOPs = $this->RelationOntologyClassOntologyProperties;
			}
			if (!$this->RelationOntologyClassOntologyClasses) {
				$relOCOCs = $this->getRelationOntologyClassOntologyClasses();
			} else {
				$relOCOCs = $this->RelationOntologyClassOntologyClasses;
			}
			
			$oce->OntologyClass->RelationOntologyClassOntologyProperties = $relOCOPs;
			$oce->OntologyClass->RelationOntologyClassOntologyClasses = $relOCOCs;
				
			$oclassProperties = $this->getOntologyProperties();
			
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
			
			$oclassIncomingClasses = $this->getIncomingOntologyClasses();
			
			foreach($oclassIncomingClasses as $oclassIncomingClass) {
				$iocName = $oclassIncomingClass->name;
				
				$relationType = $this->getRelationTypeFromIOCByName($iocName);
				
				$iocNamePluralized = $this->pluralize($iocName);
				
				if ($relationType->name === "hasMany") {
					if (isset($item_object->$iocName)) {
						$sub_oces = $oclassIncomingClass->getOntologyClassEntitiesByObjects(array($item_object->$iocName), null, true);
							
						$oce->setRelatedClassEntity($sub_oces[0], "hasOne");
					} else if (isset($item_object->$iocNamePluralized)) {
						$sub_oces = $oclassIncomingClass->getOntologyClassEntitiesByObjects($item_object->$iocNamePluralized, null, true);
							
						foreach($sub_oces as $subOceItem) {
							$oce->setRelatedClassEntity($subOceItem, "hasOne");
						}
					}
				} else {
					if (isset($item_object->$iocName)) {
						$sub_oces = $oclassIncomingClass->getOntologyClassEntitiesByObjects(array($item_object->$iocName));
							
						$oce->setRelatedClassEntity($sub_oces[0], "hasOne");
					} else if (isset($item_object->$iocNamePluralized)) {
						$sub_oces = $oclassIncomingClass->getOntologyClassEntitiesByObjects($item_object->$iocNamePluralized);
							
						foreach($sub_oces as $subOceItem) {
							$oce->setRelatedClassEntity($subOceItem, "hasOne");
						}
					}
				}
				
			}
			
			array_push($oces, $oce);
		}
		
		return $oces;
	}
	function getRelationTypeFromIOCByName($name) {
		foreach($this->RelationOntologyClassOntologyClasses as $ioc) {
			if ($ioc->IncomingOntologyClass->name === $name) return $ioc->OntologyRelationType;
		}
		
		return null;
	}
	function getOntologyClassEntities($stdobjects = null) {
		if ($this->isPersistedConcrete) {
			if (!$stdobjects) $stdobjects = $this->orm->getAllByName($this->name, true);
			
			//$stdobjects = array_slice($stdobjects, 0, 10);
			
			$objects = $this->getOntologyClassEntitiesByObjects($stdobjects, $this->name);
		} else {
			$oces = $this->orm->getByNamedFieldValues("OntologyClassEntity", array("ontologyClassID"), array($this->id));
			
			$objects = array();
			foreach($oces as $item_oce) {
				$item_oce = $this->orm->getById("OntologyClassEntity", $item_oce->id);
					
				array_push($objects, $item_oce);
			}
		}
		

		return $objects;
	}
	function hasSubClass() {
		/*foreach($this->RelationOntologyClassOntologyClasses_outgoing as $item_ococ_outgoing) {
			if ($item_ococ_outgoing->relation_ococ_Ontologyrelationtype->name == "extendedBy") {
				return $item_ococ_outgoing->relation_ococ_incomingOntologyClass;
			}
		}*/
				
		return false;
	}
	function hasSuperClass() {
		foreach($this->RelationOntologyClassOntologyClasses_outgoing as $item_ococ_outgoing) {
			if ($item_ococ_outgoing->relation_ococ_Ontologyrelationtype->name == "extends") {
				$superclass = $item_ococ_outgoing->relation_ococ_incomingOntologyClass;
				
				$superclass->inherit();
				
				return $superclass;
			}
		}
		
		return false;
	}
	function hasIdentifier() {
		for ($i=0; $i<count($this->properties); $i++) {
			if ($this->properties[$i]->isIdentifier == true) return true;
		}
		
		return false;
	}
	function hasOneDateField() {
		$amt_datefields = 0;
		
		for ($i=0; $i<count($this->properties); $i++) {
			if ($this->properties[$i]->isDate == true) $amt_datefields++;
		}
		
		if ($amt_datefields == 1) {
			return true;
		} else {
			return false;
		}
	}
	function getDateField() {
		for ($i=0; $i<count($this->properties); $i++) {
			if ($this->properties[$i]->isDate == true) return $this->properties[$i];
		}
	
		return null;
	}
	function getIdentifier() {
		for ($i=0; $i<count($this->properties); $i++) {
			if ($this->properties[$i]->isIdentifier == true) return $this->properties[$i];
		}
		
		return null;
	}
	function setRelatedClass($oclass, $relationtype_name, $wordnames = array(), $language = "en") {
		$or_ococ = new RelationOntologyClassOntologyClass();
		
		$or_ococ->relation_ococ_outgoingOntologyClass = $this;
		$or_ococ->relation_ococ_incomingOntologyClass = $oclass;

		$or_ococ->setOntologyRelationType($relationtype_name, $wordnames, $language);
		
		array_push($this->RelationOntologyClassOntologyClasses_outgoing, $or_ococ);
	}
	function hasProperty($name) {
		if (!isset($this->properties)) return false;
		
		foreach($this->properties as $item_property) {
			if ($item_property->name == $name) return $item_property;
		}
		
		return false;
	}
	function getPropertyIndex($name) {
		$i=0;
		foreach($this->properties as $item_property) {
			if ($item_property->name == $name) return $i;
			$i++;
		}
	}
	function setValidation($name, $regexp) {
		for ($i=0; $i<count($this->properties); $i++) {
			if ($this->properties[$i]->name == $name) {
				$this->properties[$i]->validationRegularExpression = $regexp;
			}
		}
	}
	function setMandatories($fields) {
		for ($i=0; $i<count($fields); $i++) {
			for ($j=0; $j<count($this->properties); $j++) {
				if ($this->properties[$j]->name == $fields[$i]) {
					$this->properties[$j]->isMandatory = true;
				}
			}
		}
	}
	function setOntologyProperty($name, $wordarray, $isIdentifier = false, $isDate = false) {
		if (!isset($this->properties)) $this->properties = array();
		
		if (!$Ontology_property = $this->hasProperty($name)) {
			$Ontology_property = new OntologyProperty();
			$Ontology_property->OntologyClass = $this;
			$Ontology_property->name = $name;
			$Ontology_property->isDate = $isDate;
			$Ontology_property->isIdentifier = $isIdentifier;

			if (!$lexeme = $Ontology_property->hasLexeme($name)) {
				$lexeme = new Lexeme();
				$lexeme->name = $name;
				$lexeme->OntologyClass = $this;
			}
			
			
			$i=0;
			foreach($wordarray as $language => $names) {
				foreach($names as $name) {
					if (!$words[$i] = $lexeme->hasWord($name, $language)) {
						$words[$i] = new Word();
						$words[$i]->name = $name;
						$words[$i]->Language = $language;
						$words[$i]->Lexeme = $lexeme;
						$i++;
					}
				}
			}
				
			if (isset($words)) $lexeme->Words = $words;
			
			$Ontology_property->Lexeme = $lexeme;
				
			array_push($this->properties, $Ontology_property);
		} else {
			if (!$lexeme = $Ontology_property->hasLexeme($name)) {
				$lexeme = new Lexeme();
				$lexeme->name = $name;
				$lexeme->OntologyClass = $this;
			}
				
				
			$i=0;
			foreach($wordarray as $language => $names) {
				foreach($names as $name) {
					if (!$words[$i] = $lexeme->hasWord($name, $language)) {
						$words[$i] = new Word();
						$words[$i]->name = $name;
						$words[$i]->Language = $language;
						$words[$i]->Lexeme = $lexeme;
						$i++;
					}
				}
			}
			
			$lexeme->Words = $words;
				
			$Ontology_property->Lexeme = $lexeme;
			
			array_splice($this->properties, $prop_idx, 1, array($Ontology_property));
		}
	}
	function getTopParent() {
		$parent = $this->getParent();
		if ($parent != null) {
			$parent_top = $parent->getParent();
			if ($parent_top != null) {
				$parent_top = $parent_top->getParent();
			} else {
				return $parent;
			}
		} else {
			return $this;
		}
		
		return $parent_top;
	}
	function getOntologyPropertyByName($name, $createIfMissing = false) {
		if ($this->RelationOntologyClassOntologyProperties[0]->OntologyProperty) {
			foreach($this->RelationOntologyClassOntologyProperties as $item_relocop) {
				if ($item_relocop->OntologyProperty->name === $name) return $item_relocop->OntologyProperty;
			}
		} else {
			$rest = new REST();
				
			$relocop = $rest->orm->getByNamedFieldValues("RelationOntologyClassOntologyProperty", array("ontologyClassID"), array($this->id), false, null, false, false, array("OntologyProperty"));
			
			foreach($relocop as $item_relocop) {
				if ($item_relocop->OntologyProperty->name === $name) return $item_relocop->OntologyProperty;
			}
		}
	}
	function getEntityByValue($value) {
		for ($i=0; $i<count($this->entities); $i++) {
			for ($j=0; $j<count($this->entities[$i]->propertyentities); $j++) {
				for ($w=0; $w<count($this->entities[$i]->propertyentities[$j]->Lexeme->Words); $w++) {
					if ($this->entities[$i]->propertyentities[$j]->Lexeme->Words[$w]->name == $value) {
						return $this->entities[$i];
					}
				}
			}
		}
		
		return null;
	}
	function getRelationOntologyClassOntologyProperties() {
		$relocop = $this->orm->getByNamedFieldValues("RelationOntologyClassOntologyProperty", array("ontologyClassID"), array($this->id), false, null, false, false, array("OntologyProperty"));
		
		if(isset($relocop[0])) $relocop[0]->setDataBaseConnections($this->databaseConnections);
			
		return $relocop;
	}
	function getOntologyProperties() {
		$properties = array();
		
		if ($this->RelationOntologyClassOntologyProperties[0]->OntologyProperty) {
			foreach($this->RelationOntologyClassOntologyProperties as $item_relocop) {
				array_push($properties, $item_relocop->OntologyProperty);
			}
		} else {
			$rest = new REST();
			
			$relocop = $rest->orm->getByNamedFieldValues("RelationOntologyClassOntologyProperty", array("ontologyClassID"), array($this->id), false, null, false, false, array("OntologyProperty"));
			
			foreach($relocop as $item_relocop) {
				array_push($properties, $item_relocop->OntologyProperty);
			}
		}
		
		return $properties;
	}
	function getIncomingOntologyClasses() {
		$classes = array();
		
		if ($this->RelationOntologyClassOntologyClasses[0]->IncomingOntologyClass) {
			foreach($this->RelationOntologyClassOntologyClasses as $item_relococ) {
				array_push($classes, $item_relococ->IncomingOntologyClass);
			}
		} else {
			$rest = new REST();
			
			$relococ = $rest->orm->getByNamedFieldValues("RelationOntologyClassOntologyClass", array("outgoingOntologyClassID"), array($this->id), false, null, false, false, array("IncomingOntologyClass", "OntologyRelationType"));
			
			foreach($relococ as $item_relococ) {
				array_push($classes, $item_relococ->IncomingOntologyClass);
			}
		}
		
		
		return $classes;
	}
	function getRelationOntologyClassOntologyClasses($twoWay = false, $byReverse = false) {
		$relococ = $this->orm->getByNamedFieldValues("RelationOntologyClassOntologyClass", array("outgoingOntologyClassID"), array($this->id), false, null, false, false, array("IncomingOntologyClass" => array("Ressource"), "OntologyRelationType"));
		
		$relations = array();
		
		foreach($relococ as $item_relococ) {
			$item_relococ->setDataBaseConnections($this->databaseConnections);
			
			$item_relococ->IncomingOntologyClass->setDataBaseConnections($this->databaseConnections);
				
			if ($twoWay) {
				$twoWayRelation = array();
					
				$twoWayRelation['forward'] = $item_relococ;
					
				$relococwayBack = $item_relococ->IncomingOntologyClass->getRelationOntologyClassOntologyClasses();
					
				foreach($relococwayBack as $item_relococWayBack) {
					if (isset($item_relococWayBack->IncomingOntologyClass)) {
						if ($item_relococWayBack->IncomingOntologyClass->name === $this->name) {
							$item_relococWayBack->setDataBaseConnections($this->databaseConnections);
								
							$twoWayRelation['backward'] = $item_relococWayBack;
						}
					}
					
				}
			
				array_push($relations, $twoWayRelation);
			} else {
				if ($item_relococ->IncomingOntologyClass->name === "EarningsAnnouncement") {
					$item_relococ->IncomingOntologyClass->RelationOntologyClassOntologyClasses = $item_relococ->IncomingOntologyClass->getRelationOntologyClassOntologyClasses();
				}
				
				$item_relococ->IncomingOntologyClass->RelationOntologyClassOntologyProperties = $item_relococ->IncomingOntologyClass->getRelationOntologyClassOntologyProperties();
				
				array_push($relations, $item_relococ);
			}
		}
		
		if ($byReverse) {
			$relococ = $this->orm->getByNamedFieldValues("RelationOntologyClassOntologyClass", array("incomingOntologyClassID"), array($this->id), false, null, false, false, array("OutgoingOntologyClass" => array("Ressource"), "OntologyRelationType"));
			
			$km = new KM();
			
			foreach($relococ as $item_relococ) {
				if ($item_relococ->OntologyRelationType->name === "hasMany" && !$this->hasRelation($relations, $item_relococ->OutgoingOntologyClass->name)) {
					$reversedRelOCOC = new RelationOntologyClassOntologyClass();
					$reversedRelOCOC->OutgoingOntologyClass = $item_relococ->IncomingOntologyClass;
					$reversedRelOCOC->IncomingOntologyClass = $item_relococ->OutgoingOntologyClass;
					$reversedRelOCOC->OntologyRelationType = $km->getOntologyRelationTypeByName("hasOne");
						
					array_push($relations, $reversedRelOCOC);
				}
				//$item_relococ->IncomingOntologyClass->RelationOntologyClassOntologyProperties = $item_relococ->OutgoingOntologyClass->getRelationOntologyClassOntologyProperties();
		
				
			}
			
		}
		
		return $relations;
	}
	function hasRelation($relations, $name) {
		foreach($relations as $relItem) {
			if ($relItem->IncomingOntologyClass->name === $name) return true;
		}
		
		return false;
	}
	function getParent() {
		$onto = new OntologyAnalyser();
		
		$parent_relation = $onto->getOntologyRelationByTypeOut(4, $this->id);
		
		if (isset($parent_relation)) {
			if (isset($parent_relation->incomingOntologyClass_id)) {
				$parent = $this->orm_get($parent_relation->incomingOntologyClass_id);
					
				return $parent;
			}
			
		}
		
		return null;
	}
}
class OntologyClassEntity extends OntologyClassEntity_Generated {
	function __construct() {
	}
	function getPropertyEntityIndex($name) {
		$i=0;
		if (isset($this->propertyentities)) {
			foreach($this->propertyentities as $item_propertyentity) {
				if ($item_propertyentity->OntologyProperty->name == $name) return $i;
				$i++;
			}
		}
	}
	function getOntologyClass() {
		$rest = new REST();
		
		if (!isset($this->ontologyClassID)) return null;
		
		$oclass = $rest->orm->getById("OntologyClass", $this->ontologyClassID);
		
		return $oclass;
	}
	function getOntologyPropertyEntities() {
		$rest = new REST();
		
		$relocope = $rest->orm->getByNamedFieldValues("RelationOntologyClassOntologyPropertyEntity", array("ontologyClassEntityID"), array($this->id), false, null, false, false, array("OntologyPropertyEntity"));
		
		foreach($relocope as $item_relocop) {
			if (isset($item_relocop->OntologyPropertyEntity->ontologyPropertyID)) {
				$item_relocop->OntologyPropertyEntity->OntologyProperty = $this->orm->getById("OntologyProperty", $item_relocop->OntologyPropertyEntity->ontologyPropertyID);
				
			}
		}
		
		return $relocope;
	}
	function getIdentifierValue() {
		if ($ope = $this->hasIdentifierPropertyEntity()) {
			return $ope->OntologyPropertyEntity->name;
		}
	
		return null;
	}
	function getName() {
		if ($ope = $this->hasPropertyEntity("name")) {
			return $ope->OntologyPropertyEntity->name;
		}
		
		return null;
	}
	function hasSubClass($scope = null, $oclasses = null) {
		if (isset($scope)) {
			$scope_keys = array();
			for ($i=0; $i<count($scope); $i++) {
				$scope_keys[$scope[$i]->OntologyClass->name] = $scope[$i];
			}
		}
		if (isset($this->OntologyClass->RelationOntologyClassOntologyClasses_outgoing)) {
			foreach($this->OntologyClass->RelationOntologyClassOntologyClasses_outgoing as $item_ococ_outgoing) {
				if ($item_ococ_outgoing->relation_ococ_Ontologyrelationtype->name == "extendedBy") {
					$subclass = $item_ococ_outgoing->relation_ococ_incomingOntologyClass;
			
					if (!isset($scope_keys[$subclass->name])) {
						if (!isset($oclasses[$subclass->name])) {
							return false;
						} else {
							if ($sub_subclass = $subclass->hasSubClass($scope, $oclasses)) {
								$subclass = $sub_subclass;
							}
						}
					}
			
					return $subclass;
			
				}
			}
		}
	
		return false;
	}
	function getBottomChild($scope = null, $oclasses = null) {
		if (!isset($this->OntologyClass)) return null;
		
		if (!$subclass = $this->OntologyClass->hasSubClass($scope, $oclasses)) {
			$bottomChild = $this->OntologyClass;
		} else {
			if (!$sub_sub_class = $subclass->hasSubClass($scope, $oclasses)) {
				$bottomChild = $subclass;
			} else {
				if (!$sub_sub_sub_class = $sub_sub_class->hasSubClass($scope, $oclasses)) {
					$bottomChild = $sub_sub_class;
				} else {
					if (!$sub_sub_sub_sub_class = $sub_sub_sub_class->hasSubClass($scope, $oclasses)) {
						$bottomChild = $sub_sub_sub_class;
					} else {
						$bottomChild = $sub_sub_sub_sub_class;
					}
				}
			}
		}
		
		return $bottomChild;
	}
	function hasSuperClass($scope = null, $oclasses = null) {
		if (isset($scope)) {
			$scope_keys = array();
			for ($i=0; $i<count($scope); $i++) {
				$scope_keys[$scope[$i]->OntologyClass->name] = $scope[$i];
			}
		}
		
		if (isset($this->OntologyClass->RelationOntologyClassOntologyClasses_outgoing)) {
			foreach($this->OntologyClass->RelationOntologyClassOntologyClasses_outgoing as $item_ococ_outgoing) {
				if ($item_ococ_outgoing->relation_ococ_Ontologyrelationtype->name == "extends") {
					$superclass = $item_ococ_outgoing->relation_ococ_incomingOntologyClass;
					
					if (!isset($scope_keys[$superclass->name])) {
						if (!isset($oclasses[$superclass->name])) {
							return false;
						} else {
							if ($super_superclass = $superclass->hasSuperClass($scope, $oclasses)) {
								$superclass = $super_superclass;
							}
						}
					}
			
					$superclass->inherit();
			
					return $superclass;
				}
			}
		}

		return false;
	}
	function getOntologyPropertyEntityByName($name) {
		for ($i=0; $i<count($this->propertyentities); $i++) {
			if ($this->propertyentities[$i]->OntologyProperty->name == $name) {
				return $this;
			}
		}

		return null;
	}
	function hasPropertyEntity($name) {
		if (isset($this->RelationOntologyClassOntologyPropertyEntities)) {
			foreach($this->RelationOntologyClassOntologyPropertyEntities as $item_propertyentity) {
				if (isset($item_propertyentity->OntologyPropertyEntity->OntologyProperty)) {
					if ($item_propertyentity->OntologyPropertyEntity->OntologyProperty->name == $name) {
						return $item_propertyentity;
					}
				}

			}
		}

		return false;
	}
	function hasIdentifierPropertyEntity() {
		if (isset($this->RelationOntologyClassOntologyPropertyEntities)) {
			foreach($this->RelationOntologyClassOntologyPropertyEntities as $item_propertyentity) {
				if (isset($item_propertyentity->OntologyPropertyEntity->OntologyProperty)) {
					if ($item_propertyentity->OntologyPropertyEntity->OntologyProperty->isIdentifier) return $item_propertyentity;
				}
				
	
			}
		}
	
		return false;
	}
	function setRelatedClassEntity($oclass_entity, $relationtype_name) {
		$km = new KM();
		
		//print_r($oclass_entity);
		
		$ort = $km->getOntologyRelationTypeByName($relationtype_name);
		
		$ore_ococ = new RelationOntologyClassOntologyClassEntity();
		$ore_ococ->OntologyRelationType = $ort;

		//$ore_ococ->OutgoingOntologyClassEntity = $this;
		$ore_ococ->IncomingOntologyClassEntity = $oclass_entity;

		array_push($this->RelationOntologyClassOntologyClassEntities, $ore_ococ);
	}
	function setOPEntity($name, $value, $wordnames = array(), $language = "en") {
		if (!isset($this->OntologyClass)) return null;
		
		if (!$relpropertyentity = $this->hasPropertyEntity($name)) {
			$relpropertyentity = new RelationOntologyClassOntologyPropertyEntity();
			
			$propertyentity = new OntologyPropertyEntity();
				
			$ontologyProperty = $this->OntologyClass->getOntologyPropertyByName($name, true);
			
			$propertyentity->OntologyProperty = $ontologyProperty;
			$propertyentity->name = $value;

			$relpropertyentity->OntologyPropertyEntity = $propertyentity;
			
			if (!$lexeme = $propertyentity->hasLexeme($name)) {
				$lexeme = new Lexeme();
				$lexeme->name = $name;
				//$lexeme->OntologyClassentity = $this;
			}


			for ($i=0; $i<count($wordnames); $i++) {
				if (!$words[$i] = $lexeme->hasWord($name, $language)) {
					$words[$i] = new Word();
					$words[$i]->name = $wordnames[$i];
					$words[$i]->language = $language;
					$words[$i]->Lexeme = $lexeme;
				}
					
				array_push($lexeme->Words, $words[$i]);
			}

			$propertyentity->Lexeme = $lexeme;

			array_push($this->RelationOntologyClassOntologyPropertyEntities, $relpropertyentity);
		} else {
			$prop_idx = $this->getPropertyEntityIndex($name);
			
			if (isset($propertyentity)) {
				if (!$lexeme = $propertyentity->hasLexeme($name)) {
					$lexeme = new Lexeme();
					$lexeme->name = $name;
					//$lexeme->OntologyClass = $this;
				}
				
				for ($i=0; $i<count($wordnames); $i++) {
					if (!$words[$i] = $lexeme->hasWord($name, $language)) {
						$words[$i] = new Word();
						$words[$i]->name = $wordnames[$i];
						$words[$i]->language = $language;
						$words[$i]->Lexeme = $lexeme;
					}
						
					array_push($lexeme->Words, $words[$i]);
				}
				
				$propertyentity->Lexeme = $lexeme;
				
				array_splice($this->propertyentities, $prop_idx, 1, array($propertyentity));
			}
		}
	}
}
class IncomingOntologyClass extends OntologyClass {
	protected $cascades = null;
	
}
class IncomingOntologyClassEntity extends OntologyClassEntity {

}
class OutgoingOntologyClass extends OntologyClass {

}
class OutgoingOntologyClassEntity extends OntologyClassEntity {

}
?>
