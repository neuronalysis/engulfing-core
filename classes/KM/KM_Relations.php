<?php
class RelationOntologyClassOntologyProperty extends RelationOntologyClassOntologyProperty_Generated {
	
	protected $cascades = array("OntologyProperty");
	
	function __construct() {
	}
}
class RelationOntologyRelationOntologyRelation extends RelationOntologyRelationOntologyRelation_Generated {
	function __construct() {
	}
}
class RelationOntologyClassOntologyClassEntity extends RelationOntologyClassOntologyClassEntity_Generated {
	function __construct() {
		
	}
	
}
class RelationOntologyClassOntologyPropertyEntity extends RelationOntologyClassOntologyPropertyEntity_Generated {
	
	function __construct() {

	}
	
}
class RelationOntologyClassOntologyClass extends RelationOntologyClassOntologyClass_Generated {
	protected $cascades = array("IncomingOntologyClass", "OntologyRelationType");
	
	function __construct() {
	}
	
}
?>