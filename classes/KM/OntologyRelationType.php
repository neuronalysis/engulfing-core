<?php
class OntologyRelationType extends OntologyRelationType_Generated {
	function __construct() {
	}
	function hasLexeme($name) {
		if (isset($this->Lexeme)) {
			if ($this->Lexeme->name == $name) return $this->Lexeme;
		}
	
	
		return false;
	}
	function getParent() {
		$onto = new OntologyAnalyser();
		
		$parent_relation = $onto->getOntologyTypeRelationByTypeOut(4, $this->id);
		
		if (isset($parent_relation)) {
			$parent = $this->rest_get($parent_relation->relation_oror_relation_ococ_incoming_id	);

			return $parent;
		}
		
		return null;
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
}
?>
