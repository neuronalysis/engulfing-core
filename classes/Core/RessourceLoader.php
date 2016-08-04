<?php
trait RessourceLoader {
	
	function RessourceLoader() {
	}
	function loadFromRessourcesByClass($ontologyClass) {
		$iex = new Extraction();
		
		$wiki_search_url = "https://www.wikidata.org/w/api.php?action=wbsearchentities&search=" . $this->name . "&language=en&format=json";
		$wiki_search_ressource = $iex->getRessource($wiki_search_url);
		$wiki_search_json_object = json_decode($wiki_search_ressource->content);
		
		$entityID = $wiki_search_json_object->search[0]->id;
		
		$wiki_entity_url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=" . $entityID . "&props=claims&format=json&languages=en";
		$wiki_entity_ressource = $iex->getRessource($wiki_entity_url);
		$wiki_entity_json_object = json_decode($wiki_entity_ressource->content);
		
		$information_wiki  = $iex->extractInformationFromWikiDataJsonObject($wiki_entity_json_object, $ontologyClass, array("ISIN" => "P946"));
		
		if ($information_wiki) {
			foreach($information_wiki as $key => $value) {
				$sl_key = strtolower($key);
					
				$this->$sl_key = $value;
			}
		}
		
		
		
		$merger = new Merger();
		$relsOCOC = $ontologyClass->getRelationOntologyClassOntologyClasses();
		foreach($relsOCOC as $relOCOC) {
			if ($relOCOC->OntologyRelationType->name === "hasOne") {
				$ressource = $relOCOC->IncomingOntologyClass->getRessource();
					
				if (isset($ressource)) {
					$fieldName = $relOCOC->IncomingOntologyClass->name;
				
					$subclass_information = $iex->extractInformationFromRessourceURL($ressource->url, $ontologyClass, array("symbol", "startDate"), array($this->symbol, "2016-01-20"), $relOCOC->IncomingOntologyClass, $ressource->schemaDefinition);
				
					$this->$fieldName = $subclass_information;
				}
			}
		}
	}
}
?>