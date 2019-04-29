<?php
class ImportProcess extends Thing {
	
	var $id;
	
	var $name;
	
	var $DataService;
	
	var $processing;
	
	function __construct() {
	}
	function loadDomainClassesBySchema() {
		$km = new KM();
		
		$desc = "";
		if (!file_exists("../engulfing/")) {
			$desc = "../";
			if (!file_exists($desc . "../engulfing/")) {
				$desc .= "../";
			}
		}
			
		$schema = json_decode($this->DataService->schemaDefinition);
		$schemavars = get_object_vars($schema);
			
		
		foreach($schemavars as $key => $val) {
			if (isset($val[0]->ontologyClass)) {
				//echo $val[0]->ontologyClass . "\n";
				$oClass = $km->getOntologyClassByName($val[0]->ontologyClass, true);
					
				if (!$oClass) $oClass = $km->getOntologyClassByName($this->singularize($val[0]->ontologyClass), true);
				
				$km->loadDomainClassesByOntology($oClass->Ontology, true);
			} else if (isset($val[2])) {
				foreach($val[2] as $field => $fieldMapped) {
					$oClass = $km->getOntologyClassByName($field, true);
					
					if (!$oClass) $oClass = $km->getOntologyClassByName($this->singularize($field), true);
					
					$km->loadDomainClassesByOntology($oClass->Ontology, true);
				}
			}
			
		}
	}
	function getUrls() {
		$rest = \REST::getInstance();
		
		$request_urls = array();
		
		$schema = json_decode($this->DataService->schemaDefinition);
		$schemavars = get_object_vars($schema);
		
		
		if (isset($schemavars['parameters'][0]->ontologyClass)) {
			$ontologyName = $rest->orm->getOntologyName($schemavars['parameters'][0]->ontologyClass);
		
			$parameterObjects = $rest->orm->getAllByName($schemavars['parameters'][0]->ontologyClass);
		
			/*foreach($parameterObjects as $paramObj) {
				$parameters = "";
		
				$parameterField = $schemavars['parameters'][0]->field;
				//$parameterValue = $dsEntity[0]->externalKey;
				
				$parameters .= "&" . $parameterField . "=" . $parameterValue;
				
				$request_url = $this->DataService->url . $parameters . "&" . "api_key=" . $this->DataService->DataProvider->apiKey;
				
				array_push($request_urls, $request_url);
			}*/
		}
		
		return array_slice($request_urls, 4, 10);
	}
	function importObjects($objects, $internalKeys) {
		$km = new KM();
		$iex = new Extraction();
		$edi = new EDI();
		
		$edi->userID = 23;
			
		$ontologyClass = $km->getOntologyClassByName(get_class($objects[0]), true);
		
		$objects[0]->OntologyClass = $ontologyClass;
		$edi->importObjects($objects, $this->DataService->schemaDefinition, $internalKeys);
	}
	function getUrl($internalKey = null, $explicitParameterKeyValues = null) {
		$rest = \REST::getInstance();
		
		$parameters = "";
		$apiKeyParameter = "";
		$urlString = $this->DataService->url;
		
		$schema = json_decode($this->DataService->schemaDefinition);
		
		if ($schema) {
			$schemavars = get_object_vars($schema);
			
			if (isset($schemavars['parameters'][0]->ontologyClass)) {
				$ontologyName = $rest->orm->getOntologyName($schemavars['parameters'][0]->ontologyClass);
				
				$orm_req = new ORM_Request("OntologyClass", array("name" => $schemavars['parameters'][0]->ontologyClass));
				
				$ontologyClasses = $rest->orm->getByNamedFieldValues($orm_req);
				
				if ($internalKey) {
					$dsEntity = $rest->orm->getById($schemavars['parameters'][0]->ontologyClass, $internalKey);
					
					$parameterField = $schemavars['parameters'][0]->field;
					
					if (strpos($this->DataService->url, "{" . $parameterField . "}") !== false) {
						$urlString = str_ireplace("{" . $parameterField . "}", $dsEntity->externalKey, $urlString);
					} else {
						if (isset($dsEntity)) {
							$parameters .= "&" . $parameterField . "=" . $dsEntity->externalKey;
						} else {
							//print_r(array($ontologyClasses[0]->id, $internalKey));
						}
						
					}
				}
				
				if ($this->DataService->DataProvider->apiKey) {
					if (isset($schemavars['api'][0]->keyName)) {
						$apiKeyParameter = "&" . $schemavars['api'][0]->keyName . "=" . $this->DataService->DataProvider->apiKey;
					} else {
						$apiKeyParameter = "&" . "api_key=" . $this->DataService->DataProvider->apiKey;
					}
				}
			} else {
				if ($this->DataService->DataProvider->apiKey) {
					$apiKeyParameter = "&" . "api_key=" . $this->DataService->DataProvider->apiKey;
				}
			}
		} else {
			$dsEntity = $rest->orm->getById("Instrument", $internalKey);
			
			$parameterField = "Symbol";
			
			if (strpos($this->DataService->url, "{" . $parameterField . "}") !== false) {
				$urlString = str_ireplace("{" . $parameterField . "}", $dsEntity->externalKey, $urlString);
			} else {
				if (isset($dsEntity)) {
					$parameters .= "&" . $parameterField . "=" . $dsEntity->externalKey;
				} else {
					//print_r(array($ontologyClasses[0]->id, $internalKey));
				}
				
			}
		}
		
		
		if ($explicitParameterKeyValues) {
			foreach($explicitParameterKeyValues as $key => $value) {
				if (strpos($this->DataService->url, "{" . $key. "}") !== false) {
					$urlString = str_ireplace("{" . $key. "}", $value, $urlString);
				} else {
					$parameters .= "&" . $key . "=" . $value;
				}
				
			}
		}
		if ($parameters) {
			$request_url = $urlString . $parameters . $apiKeyParameter;
		} else {
			$request_url = $urlString . $apiKeyParameter;
		}
		
		return $request_url;
	}
	function needsParameter() {
		$schema = json_decode($this->DataService->schemaDefinition);
		$schemavars = get_object_vars($schema);
		
		if (isset($schemavars['parameters'][0]->ontologyClass)) {
			return true;
		}
		
		return false;
	}
	function start() {
		$rest = \REST::getInstance();
		$km = new KM();
		$edi = new EDI();
		
		$schema = json_decode($this->DataService->schemaDefinition);
		$schemavars = get_object_vars($schema);
		
		
		//echo $request_url . "\n";
		$response = $rest->request($request_url);
		
		$result = json_decode($response);
			
		
		
		if (isset($parameterField)) $result->$parameterField = $parameterValue;
		
		$schema = json_decode($this->DataService->schemaDefinition);
			
		$converted = $edi->convertJSONToObjectsBySchema($result, $schema);
		
		return $converted;
	}
}
?>
