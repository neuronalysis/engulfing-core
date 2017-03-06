<?php
class OntologyConverter extends Converter {
	function convertObjectToOntology($object) {
		
	}
	function convertToObjects($oes) {
		$objects = array();
	
		//print_r($oes);
		for ($i=0; $i<count($oes); $i++) {
			//echo "oc-name: " . $oes[$i]->OntologyClass->name . "\n";
			if ($oes[$i]->OntologyClass->name != "") {
				//print_r($oes[$i]);
	
				$class_name = str_ireplace(" ", "_", $oes[$i]->OntologyClass->name);
					
				if (substr(strtolower($oes[$i]->OntologyClass->name), -1, 1) == "y") {
					$obj_name_tmp = strtolower(str_replace("", "", $oes[$i]->OntologyClass->name));
	
					$objects_name = substr($obj_name_tmp, 0, strlen($obj_name_tmp) -1 ) . "ies";
				} else {
					$objects_name = $this->pluralize(strtolower($oes[$i]->OntologyClass->name));
				}
	
	
				//echo "objects-name: " . $objects_name . "\n";
				if (!isset($objects[$objects_name])) $objects[$objects_name] = array();
					
				$object[$i] = new stdClass();
	
				//print_r($oes[$i]);
				for ($j=0; $j<count($oes[$i]->RelationOntologyClassOntologyPropertyEntities); $j++) {
					if ($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->isIdentifier && $oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->isMandatory) {
						$property_name = lcfirst($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->name);
	
						//echo "property-name: " . $property_name . "\n";
						if ($property_name) {
							$object[$i]->$property_name = $oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->name;
						}
					}
				}
	
				for ($j=0; $j<count($oes[$i]->RelationOntologyClassOntologyPropertyEntities); $j++) {
					if ($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->isIdentifier && !$oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->isMandatory) {
						$property_name = lcfirst($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->name);
						//echo $property_name . "\n";
						//print_r($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]);
	
						if ($property_name) {
							$object[$i]->$property_name = $oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->name;
						}
					}
				}
	
				for ($j=0; $j<count($oes[$i]->RelationOntologyClassOntologyPropertyEntities); $j++) {
					if (!$oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->isIdentifier) {
						//print_r($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity);
						$property_name = lcfirst($oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->OntologyProperty->name);
	
						//print_r($oes[$i]);
						if ($property_name) {
							$object[$i]->$property_name = $oes[$i]->RelationOntologyClassOntologyPropertyEntities[$j]->OntologyPropertyEntity->name;
						}
					}
	
				}
					
	
				for ($j=0; $j<count($oes[$i]->RelationOntologyClassOntologyClassEntities); $j++) {
					if ($oes[$i]->RelationOntologyClassOntologyClassEntities[$j]->OntologyRelationType->name === "hasOne" || $oes[$i]->RelationOntologyClassOntologyClassEntities[$j]->OntologyRelationType->name === "isTradedAt") {
						$rel_object = $this->convertToObject($oes[$i]->RelationOntologyClassOntologyClassEntities[$j]->IncomingOntologyClassEntity);
							
						$rel_object_name = lcfirst($oes[$i]->RelationOntologyClassOntologyClassEntities[$j]->IncomingOntologyClassEntity->OntologyClass->name);
							
						$object[$i]->$rel_object_name = $rel_object;
					}
				}
					
				array_push($objects[$objects_name], $object[$i]);
			}
	
		}
	
		//print_r($objects);
		return $objects;
	}
	function convertToObject($o_class_entity) {
		$class_name = $o_class_entity->OntologyClass->name;
		$class_name_generated = $class_name . "_Generated";
		
		if (class_exists($class_name)) {
			$object = new $class_name;
		} else if (class_exists ($class_name_generated)) {
			$object = new $class_name_generated;
		} else {
			$object = new stdClass();
		}
		
		
		if (isset($o_class_entity->RelationOntologyClassOntologyPropertyEntities)) {
			foreach($o_class_entity->RelationOntologyClassOntologyPropertyEntities as $item_propertyentity) {
				if (isset($item_propertyentity->OntologyPropertyEntity->OntologyProperty)) {
					$property_name = $item_propertyentity->OntologyPropertyEntity->OntologyProperty->name;
					
					if (strlen($property_name) <= 5) {
						$property_name = strtolower($property_name);
					} else {
						$property_name = lcfirst($property_name);
					}
					
					$object->$property_name = $item_propertyentity->OntologyPropertyEntity->name;
						
				}
		
			}
			
		}

		if (isset($o_class_entity->RelationOntologyClassOntologyClassEntities)) {
			foreach($o_class_entity->RelationOntologyClassOntologyClassEntities as $item_classentity) {
				if (isset($item_classentity->IncomingOntologyClassEntity->OntologyClass)) {
					$class_name = $item_classentity->IncomingOntologyClassEntity->OntologyClass->name;
		
					$object->$class_name = $this->convertToObject($item_classentity->IncomingOntologyClassEntity);
				}
		
			}
		}
		
		return $object;
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
}
?>
