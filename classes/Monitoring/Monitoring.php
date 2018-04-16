<?php
class Monitoring {
	var $requests = array();
	
	function __construct() {
		$this->orm = new ORM();
	}
	function getAccessSummaryByTopic($topic) {
		if ($topic === "km") {
			$km = new KM();
	
			$accesssummary = $km->getAccessSummary();
	
			$accesssummary->AccessDestinations = array_slice($accesssummary->AccessDestinations, 0, 10);
		} else if ($topic === "wiki") {
			$km = new KM();
	
			$ontology = $km->getOntologyByName("Wiki");
	
			$accesssummary = $ontology->getAccessSummary();
	
			$accesssummary->AccessDestinations = array_slice($accesssummary->AccessDestinations, 0, 10);
		}
	
	
		return $accesssummary;
	}
	function getRequestsByUserAndScope($userID, $scope) {
		$objects = $this->orm->getByNamedFieldValues("Request", array("UserID", "url"), array($userID, "/api" . $scope), false, null, false, true);
	
		return $objects;
	}
	function isEligibleScopeForProtection($scope) {
		if ($scope == "/extraction/information" || $scope == "/extraction/knowledge" || $scope == "/ocr/documents/37/pages/4") {
			return true;
		}
		
		return false;
	}
	function getRequests($ontologyName = null) {
		if ($ontologyName) {
		    $requests = $this->orm->getByNamedFieldValues(new ORM_Request("Request", array("OntologyName" => $ontologyName)));
		} else {
			$requests = $this->orm->getAllByName("Request", true);
		}
		
	
		return $requests;
	}
	function getAccessPermissionByClientAndScope($userID, $clientIP, $scope) {
		if (isset($userID)) {
		    $objects = $this->orm->getByNamedFieldValues(new ORM_Request("Request", array("UserID" => $userID, "url" => "/api" . $scope, "sentAt" => date("Y-m-d"))));
			
			if (count($objects) < 500) return true;
		} else {
		    $objects = $this->orm->getByNamedFieldValues(new ORM_Request("Request", array("clientIP" => $clientIP, "url" => "/api" . $scope, "sentAt" => date("Y-m-d"))));
			
			if (count($objects) < 500) return true;
		}
		
		return false;
	}
}
class Request {
	
	var $sentAt;
	var $OntologyName;
	var $method;
	var $url;
	var $refererUrl;
	var $clientIP;
	var $User;
	var $ResponseStatistics;
	
	function __construct() {
	}

}
class RequestStatistics {
		var $date;
	var $value;
	
	function __construct() {
	}

}
class Report {
	var $ReportStatistics;
	
	function __construct() {
		$this->ReportStatistics = new ReportStatistics();
	}

}
class ReportStatistics {
	var $AmountOfRequests;
	
	function __construct() {
	}

}
class Metrics {
	/*var $fragments_retrieved;
	 var $fragments_relevant = array();
	 var $fragments_processed = array();
	 var $fragments_mandatory = array();
	 var $fragments_processed_amount;
	 var $fragments_processed_mandatory_amount;
	 var $fragments_processed_mandatory = array();
	 var $fragments_processed_of_relevant_pct;
	 var $fragments_processed_of_mandatory_pct;*/
	//var $fragments_valid;

	function __construct() {
	}

	function getRetrievedFragments($fragments) {
		return count($fragments);
	}
	function getProcessedWords($fragments) {
		$processed = 0;

		for ($i=0; $i<count($fragments); $i++) {
			for ($j=0; $j<count($fragments[$i]->Words); $j++) {
				if ($fragments[$i]->Words[$j]->Lexeme->id) {
					$processed = $processed + 1;
				}
			}
		}

		return $processed;
	}
	function getRelevantFragments($fragments) {
		$f=0;

		for ($i=0; $i<count($fragments); $i++) {
			if (isset($fragments[$i]->Ontology)){
				$f++;
			}
		}

		return $f;
	}
	function getMandatoryFragments($fragments) {
		$f=0;

		for ($i=0; $i<count($fragments); $i++) {
			if (isset($fragments[$i]->Ontology)) {

				$f++;
			}
		}

		return $f;
	}
	function getProcessedFragments($fragments) {
		$f=0;

		for ($i=0; $i<count($fragments); $i++) {
			if (isset($fragments[$i]->Ontology)) $f++;
		}

		return $f;
	}
	function getProcessedMandatoryFragments($fragments) {
		$f=0;

		for ($i=0; $i<count($fragments); $i++) {
			if (isset($fragments[$i]->Ontology)) $f++;
		}

		return $f;
	}
}
?>
