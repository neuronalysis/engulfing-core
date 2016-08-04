<?php
class OntologyProperty extends OntologyProperty_Generated {
	function __construct() {
	}
	function hasLexeme($name) {
		if (isset($this->Lexeme)) {
			if ($this->Lexeme->name == $name) return $this->Lexeme;
		}
		
		
		return false;
	}
	function validateDate($date) {
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') == $date;
	}
	/*function validate($value) {
		if ($this->Type->id === 1) {
			return $this->validateDate(strtotime($value));
		} else if ($this->Type->id === 2) {
			return is_numeric($value);
		} else {
			if (!$this->validationRegularExpression) return true;
			
			$opt = array("options" => array("regexp" => $this->validationRegularExpression));
			
			if(filter_var($value, FILTER_VALIDATE_REGEXP, $opt)) {
				return true;
			} else {
				return false;
			}
		}
	}*/
	function getOntologyClass($className = null) {
		if ($className) {
			//echo "classname: " . $className . "\n";
			$objects = $this->getByNamedFieldValues("OntologyClass", array("name"), array($className));
			
			$result = $objects[0];
		} else {
			$objects = $this->getByNamedFieldValues("RelationOntologyClassOntologyProperty", array("ontologyPropertyID"), array($this->id), false, null, true);
			
			$result = $objects[0]->OntologyClass;
				
			//print_r($result);
		}
		
		return $result;
	}
}
class OntologyPropertyEntity extends OntologyPropertyEntity_Generated {
	
	function __construct() {
	}
	function hasLexeme($name) {
		if (isset($this->Lexeme)) {
			if ($this->Lexeme->name == $name) return $this->Lexeme;
		}


		return false;
	}
	/*function setValue($value) {
		if (isset($this->OntologyProperty)) {
			if ($this->OntologyProperty->isIdentifier) {
				$value = str_replace(" ", "", $value);
			}
			if ($this->OntologyProperty->validate($value)) {
				$this->value = $value;
			}
		} else {
			$this->value = $value;
		}
	}
	function getValue() {
		return $this->value;
	}*/
	function resolvePropertyToClass() {
		//$entity_value = $this->getValue();
		$entity_value = $this->name;
		
		
		//echo "pe.value: " . $entity_value . "\n";
		
		//print_r($this->OntologyProperty);
		
		if (isset($this->OntologyProperty->OntologyClass)) {
			$propentity = $this->OntologyProperty->OntologyClass->getEntityByValue($entity_value);
			
			//print_r($propentity);
			if (isset($propentity)) return $propentity;
		} else {
			/*$rest = new REST();
			$objects = $this->getByNamedFieldValues("RelationOntologyClassOntologyProperty", array("ontologyPropertyID"), array($this->OntologyProperty->id));
			$result = $rest->getById("OntologyClass", $objects[0]->ontologyClassID);
			
			$oce = new OntologyClassEntity();
			$oce->OntologyClass = $result;
			
			return $oce;*/
		}
		
		return false;
	}
	
}
class Type {

	var $id;
	var $name;
	var $text;
	function __construct() {
		
	}
	function initialize() {
		if ($this->id == 0) {
			$this->name = "Text";
		} else if ($this->id == 1) {
			$this->name = "Date";
		} else if ($this->id == 2) {
			$this->name = "Number";
		} else if ($this->id == 3) {
			$this->name = "Boolean";
		}
		
		$this->text = $this->name;
	}
}
?>