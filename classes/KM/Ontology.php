<?php
class Ontology extends Ontology_Generated {
	
	function __construct() {
	}
	function getOntologyClasses() {
		$oclasses = $this->getByNamedFieldValues("OntologyClass", array("ontologyID"), array($this->id));
		
		return $oclasses;
	}
	function getDataSummary() {
		$datasummary = new DataSummary();
		
		$ontologyClasses = $this->getOntologyClasses();
		//$ontologyProperties = $this->getOntologyProperties();
		//$ontologyRelationTypes = $this->getOntologyRelationTypes();
		
		//$datasummary->ontologies = count($ontologies);
		$datasummary->ontologyClasses = count($ontologyClasses);
		//$datasummary->ontologyProperties = count($ontologyProperties);
		//$datasummary->ontologyRelationTypes = count($ontologyRelationTypes);
		
	
		return $datasummary;
	}
	function getAccessSummary() {
		$accesssummary = new AccessSummary();
	
		$monitoring = new Monitoring();
		$requests = $monitoring->getRequests(strtolower($this->name));
	
	
		$tmp_requests = array();
	
		foreach($requests as $request) {
			if (isset($tmp_requests[$request->refererUrl])) {
				$tmp_requests[$request->refererUrl]++;
			} else {
				$tmp_requests[$request->refererUrl] = 1;
			}
				
		}
	
		foreach($tmp_requests as $key => $value) {
			if (strpos($key, "#") !== false) {
				$dest = new AccessDestination();
				
				if ($this->isLocalRequest()) {
					$dest->url = "http://localhost.ontologydriven/" . $key;
				} else {
					$dest->url = "http://www.ontologydriven.com/" . $key;
				}
				
				$dest->visits = $value;
				$dest->title = $accesssummary->getTitleByURL($dest->url);
				
				array_push($accesssummary->AccessDestinations, $dest);
			}
		}
	
		$this->sort_on_field($accesssummary->AccessDestinations, "visits", "DESC", "num");
	
	
	
		return $accesssummary;
	}
	function getOntologyData() {
		$desc = "";
		if (!file_exists("../engulfing/")) {
			$desc = "../";
			if (!file_exists($desc . "../engulfing/")) {
				$desc .= "../";
			}
		}
		
		$ontologyJSON = "";
		
		if (file_exists("../" . strtolower($this->name) . "/data/ontology.json")) {
			$ontologyJSON = file_get_contents ( "../" . strtolower($this->name) . "/data/ontology.json" );
		}
		
		$ontologyData = json_decode($ontologyJSON);
		
		return $ontologyData;
	}
	function getContent() {
		$ontologyData = $this->getOntologyData();
		
		$content = new stdClass();
		
		if (isset($ontologyData)) {
			$content->title = $ontologyData->title . " (" . $ontologyData->shortTitle . ")";
			
			foreach($ontologyData->services as $service) {
				$service->title = $service->title;
					
				if ($service->shortTitle) {
					$service->title .=  " (" . $service->shortTitle . ")";
				}
			}
			$content->services = $ontologyData->services;
			
			$objects = array();
			
			$dataSummary = new stdClass();
			foreach($ontologyData->dataSummary->classes as $class) {
				$objects[$class] = $this->getAllByName($class);
				
				
				$dataSummaryObjects = array();
				
				if ($class === "Ontology") {
					foreach($objects[$class] as $object) {
						$object_classes = $object->getOntologyClasses();
						
						$object->ontologyClasses = $object_classes;
						
						$dataSummaryObject = new stdClass();
						$dataSummaryObject->name = $object->name;
						$dataSummaryObject->amountOfOntologyClasses = count($object_classes);
						$dataSummaryObject->url = "http://" . $_SERVER ['SERVER_NAME'] . "/" . strtolower($this->name) . "/" . strtolower($this->pluralize($class)) . "/#" . $object->id;
						
						array_push($dataSummaryObjects, $dataSummaryObject);
						
						
					}
				}
				
				
				
				$objectsname = $this->pluralize($class);
				
				$dataSummary->$objectsname = $dataSummaryObjects;
			}
			
			$content->dataSummary = $dataSummary;
			
		}
		
		
		return $content;
	}
	
}
?>