<?php
class Plotter {
	function __construct() {
		
	}
	
	function plottFragments($fragments) {
		foreach($fragments as $fragment) {
			
			if (isset($fragment->Ontology)) echo "fragment-ontology-" . get_class($fragment->Ontology) . ": " . $fragment->Ontology->name . "\n";
			
			if ($lexeme = $fragment->hasLexeme()) {
				//echo json_encode ( $lexeme, JSON_PRETTY_PRINT ) . "\n\n";
				
				if ($ontology = $lexeme->hasOntology()) {
					echo "fragment\n";
					echo "  - ontology-type: " . get_class($ontology) . " name: " . $ontology->name   . "\n";
					echo "      - ontology-class: " . $ontology->OntologyClass->name  . "\n";
				
					if ($fragment->siblings) {
						foreach($fragment->siblings as $sibling) {
							echo "    sibling\n";
								
							echo "       - ontology-type: " . get_class($sibling->Ontology) . " name: " . $sibling->Ontology->name   . "\n";
								
						}
					}
				} else {
				}
			}
			
			
		}
	}
}
?>
