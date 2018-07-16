<?php
trait ResourceLoader {
	
	function ResourceLoader() {
	}
	function loadFromResourcesByClass($ontologyClass) {
		$iex = new Extraction();
		
		$wiki_search_url = "https://www.wikidata.org/w/api.php?action=wbsearchentities&search=" . $this->name . "&language=en&format=json";
		$wiki_search_resource = $iex->getResource($wiki_search_url);
		$wiki_search_json_object = json_decode($wiki_search_resource->content);
		
		$entityID = $wiki_search_json_object->search[0]->id;
		
		$wiki_entity_url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=" . $entityID . "&props=claims&format=json&languages=en";
		$wiki_entity_resource = $iex->getResource($wiki_entity_url);
		$wiki_entity_json_object = json_decode($wiki_entity_resource->content);
		
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
				$resource = $relOCOC->IncomingOntologyClass->getResource();
					
				if (isset($resource)) {
					$fieldName = $relOCOC->IncomingOntologyClass->name;
				
					$subclass_information = $iex->extractInformationFromResourceURL($resource->url, $ontologyClass, array("symbol", "startDate"), array($this->symbol, "2016-01-20"), $relOCOC->IncomingOntologyClass, $resource->schemaDefinition);
				
					$this->$fieldName = $subclass_information;
				}
			}
		}
	}
}
?>