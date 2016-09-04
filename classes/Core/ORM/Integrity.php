<?php
trait Integrity {
	protected $validationRules = array();
	protected $encryptions = array();
	
	function __construct() {
	}
	function checkConstraints() {
		$this->checkUniqueConstraints();
	}
	function checkUniqueConstraints($object) {
		if (!method_exists($object, "getConstraintsUnique")) return false;
		
		$constraintsUnique = $object->getConstraintsUnique();
		
		if (is_array($constraintsUnique)) {
			foreach($constraintsUnique as $constraintUnique) {
				$class_name = strtolower($this->getOntologyClassName($object));
				$fields = array();
				$values = array();
				foreach($constraintUnique as $item_name) {
					if ($this->isToOneField($item_name)) {
						array_push($fields, $item_name . "ID");
						array_push($values, $object->$item_name);
					} else {
						array_push($fields, $item_name);
						array_push($values, $object->$item_name);
					}
				}
				
				$result = $this->getByNamedFieldValues($class_name, $fields, $values);
				
				if ($result) return $result;
			}
		}
		
		return false;
	}
}
?>