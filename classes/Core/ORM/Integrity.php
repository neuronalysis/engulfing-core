<?php
trait Integrity {
	protected $constraints_unique = array();
	
	protected $validationRules = array();
	protected $encryptions = array();
	
	function Integrity() {
	}
	function checkConstraints() {
		$this->checkUniqueConstraints();
	}
	function checkUniqueConstraints() {
		if (isset($this->constraints_unique)) {
			foreach($this->constraints_unique as $constraint_unique_item) {
				$class_name = strtolower($this->getOntologyClassName());
				$fields = array();
				$values = array();
				foreach($constraint_unique_item as $item_name) {
					if ($this->isOneToOneObject($item_name)) {
						array_push($fields, $item_name . "ID");
						array_push($values, $this->$item_name);
					} else {
						array_push($fields, $item_name);
						array_push($values, $this->$item_name);
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