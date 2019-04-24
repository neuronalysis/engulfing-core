<?php
class REST_Controller {
	function __construct() {
		$this->rest = REST::getInstance();
	}
	function deleteById($object_name, $id) {
		$this->rest->orm->deleteById($object_name, $id);
	}
	function add() {
		$className = $this->rest->singularize($this->rest->orm->getOntologyClassName());
		
		if (strtolower($className) === "user") {
			$request = $this->rest->app->request ();
			
			$auth = Authentication::getInstance();
			
			$object = $auth->signupUser($request);
			
			if ($object) {
				$rest->cleanObjects($object);
				
				echo json_encode ( $object, JSON_PRETTY_PRINT );
			}
			
		} else {
			$this->save();
		}
	}
	function save($id = null) {
		$className = $this->rest->singularize($this->rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $this->rest->isLogged()) return null;
		}
		
		$request = $this->rest->app->request ();
		
		$restTransformer = new \REST_Transformer ();
		$object = $restTransformer->deserialize_JSON ( $request->getBody (), $className );
		
		if (!in_array($className, array("watchlistitem")) && isset($object->OntologyClass) && isset($object->OntologyClass->isPersistedConcrete) && $object->OntologyClass->isPersistedConcrete) {
			$converter = new Converter();
			$object = $converter->convertToObject($object);
			
			//TODO symbol???
			$existingObject = $rest->orm->getByNamedFieldValues($object->OntologyClass->name, array("symbol"), array($object->symbol));
			
			if ($existingObject) {
				$object->id = $existingObject[0]->id;
			}
			
			$object = $rest->orm->save($object, "ocr");
			
			$object = $converter->convertConcreteEntityToOntologyClassEntity($object->OntologyClass, array($object));
		} else {
			try {
				$saveResp = $this->rest->orm->save($object);
				
				if (intval($saveResp)) {
					$object->id = $saveResp;
				}
			} catch ( Exception $e ) {
				$extract = new stdClass();
				$extract->error = new stdClass();
				$extract->error->message = "Database Transaction Failure";
				
				$extract->error->details = $e->getMessage();
				$extract->error->file = $e->getFile();
				$extract->error->code = $e->getCode();
				$extract->error->line = $e->getLine();
				
				echo json_encode ( $extract, JSON_PRETTY_PRINT );
				exit ();
			}
			
		}
		
		if ($object) {
			$this->rest->cleanObjects($object);
			
			echo json_encode ( $object, JSON_PRETTY_PRINT );
		}
	}
	function delete($id) {
		$className = $this->rest->singularize($this->rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $this->rest->isLogged()) return null;
		}
		
		$this->deleteById($className, $id, true);
	}
	function update($id) {
		$this->save($id);
	}
	function callback_getObjects($callback) {
		$rest = \REST::getInstance();
		
		$ontologyclassName = $rest->orm->getOntologyClassName();
		$field_name = "name";
		
		header('Content-type: text/javascript');
		
		if ($ontologyclassName == "query") {
			$search = new Search();
			
			$objects = $search->searchByQuery($_GET['query'], "ocr");
		} else if ($ontologyclassName == "ontologyclassentity") {
			if ($_GET['ontologyClassID']) {
				$oclass = $rest->orm->getById("OntologyClass", $_GET['ontologyClassID']);
			}
			
			if ($oclass->isPersistedConcrete) {
				$objects = $rest->orm->getAllByName($oclass->name);
				
				foreach($objects as $object_item) {
					$object_item->id = -99;
					$object_item->text = $object_item->name;
				}
			} else {
				$ontologyclassName = "ontologypropertyentity";
				
				$objects = $rest->orm->getByNamedFieldValues($ontologyclassName, array($field_name), array($_GET['query']), true);
				if (is_array($objects)) {
					
					foreach($objects as $object_item) {
						$relobjects = $rest->orm->getByNamedFieldValues("relationontologyclassontologypropertyentity", array("ontologyPropertyEntityID"), array($object_item->id), true);
						
						if ($relobjects[0]) {
							$object_item->id = $relobjects[0]->ontologyClassEntityID;
							$object_item->text = $object_item->name;
						}
						
					}
				}
			}
		} else {
			$orm_req = new ORM_Request($ontologyclassName, array($field_name=> $_GET['query']));
			$orm_req->like = true;
			
			$objects = $rest->orm->getByNamedFieldValues($orm_req);
			
			if (is_array($objects)) {
				foreach($objects as $object_item) {
					$object_item->text = $object_item->name;
				}
			}
		}
		
		echo "$callback(" . json_encode($objects) . ");";
	}
	//TODO
	function get($id = null) {
		$className = $this->rest->singularize($this->rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $this->rest->isLogged()) return null;
		}
		
		if ($id) {
			$result = $this->rest->orm->getById($className, $id);
		} else {
			if (isset($_GET['page'])) {
				$orm_req = new ORM_Request($className);
				
				$result_paged = $this->rest->orm->getAllByName($orm_req);
		
				$result = new stdClass();
				$result->items = $result_paged;
				$result->total_count = $this->rest->orm->getTotalAmount($className);
			} else {
				$orm_req = new ORM_Request($className);
				$result = $this->rest->orm->getAllByName($orm_req);
			}
		}
		/*if ($this->app) {
		 if (isset($_GET['page'])) {
		 
		 $namedfieldParameters = $_GET;
		 unset($namedfieldParameters['page']);
		 unset($namedfieldParameters['per_page']);
		 unset($namedfieldParameters['total_pages']);
		 unset($namedfieldParameters['total_entries']);
		 
		 $oclass = null;
		 //TODO
		 
		 if (class_exists("KM")) {
		 $km = new KM();
		 $km->orm = $this->orm;
		 
		 //TODO
		 
		 if (isset($namedfieldParameters['ontologyClassID'])) {
		 if ($namedfieldParameters['ontologyClassID']) {
		 $oclass = $km->getOntologyClassById($namedfieldParameters['ontologyClassID']);
		 } else {
		 $oclass = $km->getOntologyClassByName($ontologyClassName);
		 }
		 } else {
		 $oclass = $km->getOntologyClassByName($ontologyClassName);
		 }
		 }
		 
		 
		 if (!$oclass || !$oclass->getIsPersistedConcrete()) {
		 $orm_req = new ORM_Request($ontologyClassName);
		 $orm_req->setKeyValuesByFieldsAndValues(array_keys($namedfieldParameters), array_values($namedfieldParameters));
		 
		 $result_paged = $this->orm->getByNamedFieldValues($orm_req);
		 //$result_paged = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($namedfieldParameters), array_values($namedfieldParameters));
		 
		 foreach($result_paged as $obj_item) {
		 $obj_item->id = intval($obj_item->id);
		 
		 if ($ontologyClassName === "ontologyclassentity") {
		 $relobjects = $this->orm->getByNamedFieldValues("RelationOntologyClassOntologyPropertyEntity", array("ontologyclassentityid"), array($obj_item->id), false);
		 
		 if (isset($relobjects[0])) {
		 $propertyentityobjects = $this->orm->getByNamedFieldValues("ontologypropertyentity", array("id"), array($relobjects[0]->ontologyPropertyEntityID), false);
		 
		 if (isset($propertyentityobjects[0])) $obj_item->name = $propertyentityobjects[0]->name;
		 }
		 
		 }
		 }
		 } else {
		 //TODO
		 if ($ontologyClassName === "ReleasePublication") {
		 $economics = new Economics();
		 $result_paged = $economics->getNextReleasePublications();
		 } else {
		 $orm_req = new ORM_Request($oclass->name);
		 
		 $result_paged = $this->orm->getAllByName($orm_req);
		 }
		 }
		 
		 $result = new stdClass();
		 $result->items = $result_paged;
		 $result->total_count = $this->orm->getTotalAmount($ontologyClassName);
		 } else {
		 if (isset($_GET['name'])) {
		 $result = $this->orm->getByNamedFieldValues($ontologyClassName, array("name"), array($_GET['name']));
		 $result = $this->orm->getById($ontologyClassName, $result[0]->id);
		 } else {
		 //TODO
		 
		 if ($ontologyClassName === "releasepublication") {
		 $result_paged = $this->orm->getAllByName($ontologyClassName, false, null, null, array("releaseID"));
		 
		 foreach($result_paged as $result_item) {
		 $result_item->Release = $this->orm->getById("Release", $result_item->releaseID, false);
		 unset($result_item->releaseID);
		 }
		 
		 $result = new stdClass();
		 $result->items = $result_paged;
		 $result->total_count = $this->getTotalAmount($ontologyClassName);
		 
		 } else {
		 $orm_req = new ORM_Request($ontologyClassName);
		 
		 $result = $this->orm->getAllByName($orm_req);
		 }
		 
		 
		 }
		 }
		 
		 } else {
		 if ($id) {
		 $obj = new $ontologyClassName();
		 //TODO
		 
		 if ($ontologyClassName === "indicator") {
		 $result = $this->orm->getById($ontologyClassName, $id);
		 
		 unset($result->Release->Indicators);
		 unset($result->Release->ReleasePublications);
		 } else if ($ontologyClassName === "Instrument") {
		 $result = $this->orm->getById("Instrument", $id);
		 
		 $result->ImpactFunctions = $this->orm->getByNamedFieldValues("ImpactFunction", array("instrumentID"), array($result->id));
		 foreach($result->ImpactFunctions as $if_item) {
		 $if_item->name = $if_item->formula;
		 
		 $if_item->RelationIndicatorImpactFunctions = $this->orm->getByNamedFieldValues("RelationIndicatorImpactFunction", array("impactFunctionID"), array($if_item->id), false, null, false, false, array("Indicator"), null, null, null, array("indicatorID"));
		 
		 foreach($if_item->RelationIndicatorImpactFunctions as $rel_item) {
		 unset($rel_item->ImpactFunction);
		 
		 unset($rel_item->Indicator->Release);
		 unset($rel_item->Indicator->Frequency);
		 unset($rel_item->Indicator->IndicatorObservations);
		 unset($rel_item->Indicator->Country);
		 
		 unset($rel_item->Indicator->RelationIndicatorImpactFunctions);
		 }
		 
		 unset($if_item->Instrument);
		 }
		 
		 } else if ($ontologyClassName === "\\OCR\\Document") {
		 $result = $this->orm->getById("\\OCR\\Document", $id, true);
		 
		 $doc = new DOMDocument();
		 $doc->loadXML($result->Pages[0]->altoXML);
		 
		 
		 $xmlconv = new XMLConverter("alto");
		 $alto = $xmlconv->convertToObjectTree($doc);
		 $result->Pages[0]->alto= $alto;
		 
		 
		 unset($result->Pages[0]->altoXML);
		 } else {
		 $result = $this->orm->getById($ontologyClassName, $id);
		 }
		 } else if (count($_GET) > 0) {
		 $result = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($_GET), array_values($_GET));
		 } else {
		 $result = $this->orm->getAllByName($ontologyClassName);
		 }
		 }*/
		
		$this->rest->cleanObjects($result);
		
		echo json_encode ( $result, JSON_PRETTY_PRINT );
	}
	function getObservations($id = null, $app = null) {
		$className = $this->rest->singularize($this->rest->orm->getOntologyClassName());
		
		$limit = null;
		
		if ($className === "Indicator") {
			$orm_request = new ORM_Request($className. "Observation", array(lcfirst($className) . "ID" => $id, "date" => "2014-01-01"));
			$orm_request->keyOperators = array(lcfirst($className) . "ID" => "=", "date" => ">=");
			$orm_request->order = "date ASC";
			
			$observations = $this->rest->orm->getByNamedFieldValues($orm_request);
		} else if ($className === "Instrument") {
			$orm_request = new ORM_Request($className. "Observation", array(lcfirst($className) . "ID" => $id, "date" => "2015-01-01"));
			$orm_request->keyOperators = array(lcfirst($className) . "ID" => "=", "date" => ">=");
			$orm_request->order = "date ASC";
			
			$observations = $this->rest->orm->getByNamedFieldValues($orm_request);
		}
		
		foreach($observations as $item) {
			unset($item->id);
			unset($item->$className);
		}
		
		$result = new stdClass();
		$result->items = $observations;
		
		echo json_encode ( $result, JSON_PRETTY_PRINT );
	}
	function getValuation($id = null, $app = null) {
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if ($id) {
			$result = $this->rest->orm->getById($className, $id);
		}
		
		return $result->getValuation();
	}
	function getDetailed($id = null, $app = null) {
		$ontologyClassName = $this->orm->getOntologyClassName();
		
		if ($app) {
			if (isset($_GET['page'])) {
				$namedfieldParameters = $_GET;
				unset($namedfieldParameters['page']);
				unset($namedfieldParameters['per_page']);
				unset($namedfieldParameters['total_pages']);
				unset($namedfieldParameters['total_entries']);
				
				$result_paged = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($namedfieldParameters), array_values($namedfieldParameters));
				
				foreach($result_paged as $obj_item) {
					$obj_item->id = intval($obj_item->id);
					
					if ($ontologyClassName === "ontology") {
						$relobjects = $this->orm->getByNamedFieldValues("RelationOntologyClassOntologyPropertyEntity", array("ontologyclassentityid"), array($obj_item->id), false);
						
						if (isset($relobjects[0])) {
							$propertyentityobjects = $this->orm->getByNamedFieldValues("ontologypropertyentity", array("id"), array($relobjects[0]->ontologyPropertyEntityID), false);
							
							if (isset($propertyentityobjects[0])) $obj_item->name = $propertyentityobjects[0]->name;
						}
					}
				}
				$result = new stdClass();
				$result->items = $result_paged;
				$result->total_count = $this->getTotalAmount($ontologyClassName);
			} else {
				
				
				if (isset($_GET['name'])) {
					$result = $this->orm->getByNamedFieldValues($ontologyClassName, array("name"), array($_GET['name']));
					$result = $this->orm->getById($ontologyClassName, $result[0]->id);
				} else {
					$result = $this->orm->getAllByName($ontologyClassName);
				}
			}
			
		} else {
			if ($id) {
				$result = $this->orm->getById($ontologyClassName, $id);
				
				if ($ontologyClassName === "ontology") {
					$related = $this->orm->getByNamedFieldValues("OntologyClass", array("ontologyID"), array($id), false);
					
					$result->OntologyClasses = $related;
				} else if ($ontologyClassName === "release") {
					$related = $this->orm->getByNamedFieldValues("ReleasePublication", array("releaseID"), array($id), false);
					
					$result->ReleasePublications = $related;
				}
				
			} else if (count($_GET) > 0) {
				$result = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($_GET), array_values($_GET));
			} else {
				$result = $this->orm->getAllByName($ontologyClassName);
			}
		}
		
		$this->rest->cleanObjects($result);
		
		return $result;
	}
	function decodeJSON($cmd_string) {
		$cmd_split = explode(":", $cmd_string);
		
		$obj = new $cmd_split[0];
	}
}
?>