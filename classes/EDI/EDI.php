<?php
include_once (__DIR__ . "/../../../engulfing-generated/classes/things/Things_Generated.php");
include_once (__DIR__ . "/../../../engulfing-generated/classes/edi/EDI_Generated.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/Helper.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/FileIO.php");

include_once ('DataService.php');
include_once ('EDI_Relations.php');
include_once ('ProcessScheduler.php');
include_once ('Schedule.php');
//include_once ('DataServiceEntity.php');
include_once ('Ressource.php');
include_once ('DataSource.php');
include_once ('DataProvider.php');
include_once ('ImportProcess.php');
include_once ('ImportEntity.php');
include_once ('ImportHelper.php');



class EDI extends EDI_Generated {
	var $classes = array("Schedule", "ImportProcess", "DataProvider", "DataService", "Ressource", "RelationDataServiceOntologyClass", "DataSource", "OntologyClass");
	
	var $entities = '{}';
	
	var $debugMode = true;
	var $userID;
	
	function __construct() {
		$this->orm = new ORM();
	}
	function getImportProcesses() {
		
	}
	function getImportProcessByID($importprocessID) {
		$result = $this->orm->getById("ImportProcess", $importprocessID);
		
		return $result;
	}
	function getRessources($urls) {
		$ressources = array();
		
		foreach($urls as $url) {
			array_push($ressources, $this->getRessource($url));
		}
		
		return $ressources;
	}
	function getRessource($url, $noDownload = false, $enforcedType = null) {
		$ressource = new Ressource($url);
		if ($this->debugMode) {
			//$noDownload = true;
			//$enforcedType = "application/pdf; charset=binary";
		}
		$ressource->load($noDownload, $enforcedType);
		 
		return $ressource;
	}
	/*function convertObjectsToDataServiceEntitiesByDataService($objects, $dataservice) {
		$dses = array();
		
		foreach($objects as $objectItem) {
			
			$dse = new DataServiceEntity();
			$dse->OntologyClass = $objectItem->OntologyClass;
			$dse->DataService = $dataservice;
			$dse->internalKey = $objectItem->id;
			$dse->externalKey = $objectItem->externalKey;
			
			array_push($dses, $dse);
		}
		
		return $dses;
	}*/
	function importPendings($type = null) {
		$economics = new Economics();
		
		$pendings = $economics->getPendings($type);
	}
	function importObjects($objects, $schema) {
		$UserID = isLogged();
		
		$imphelper = new ImportHelper();
		
		$km = new KM();
		$km->setDataBaseConnections($this->databaseConnections);
		
		$ontologyClass = $km->getOntologyClassByName(get_class($objects[0]), true);
		
		if (isset($ontologyClass)) {
			$ontologyClass->RelationOntologyClassOntologyProperties = $ontologyClass->getRelationOntologyClassOntologyProperties();
			$ontologyClass->RelationOntologyClassOntologyClasses = $ontologyClass->getRelationOntologyClassOntologyClasses(false, true);
		
			array_push($imphelper->entityClasses, $ontologyClass);
		
			foreach($ontologyClass->RelationOntologyClassOntologyProperties as $rocop) {
				array_push($imphelper->entityProperties, $rocop->OntologyProperty);
			}
			
			foreach($ontologyClass->RelationOntologyClassOntologyClasses as $rococ) {
				if ($rococ->OntologyRelationType->name === "hasOne") {
					$ocName = $rococ->IncomingOntologyClass->name;
					$ocNameIdied = lcfirst($ocName) . "ID";
						
					if (isset($objects[0]->$ocName->id)) {
						$op = new OntologyProperty();
						$op->name = $ocNameIdied;
							
						array_push($imphelper->entityProperties, $op);
					} else {
						//echo $ocName . "; fuuuck\n";
						//print_r($objects[0]);
					}
					
				}
				
			}
			
			$imphelper->OntologyClass = $ontologyClass;
		
			foreach($objects as $objectItem) {
				$entity = new ImportEntity();
				$entity->entityClassName = $ontologyClass->name;
				$entity->entityOntologyName = $ontologyClass->Ontology->name;
					
				foreach($ontologyClass->RelationOntologyClassOntologyProperties as $rocop) {
					$opName = $rocop->OntologyProperty->name;
		
					$entity->$opName = $objectItem->$opName;
					
					if ($UserID) {
						$entity->setModificationInfo($UserID);
					} else {
						$entity->setModificationInfo(23);
					}
				}
				
				foreach($ontologyClass->RelationOntologyClassOntologyClasses as $rococ) {
					if ($rococ->OntologyRelationType->name === "hasOne") {
						$ocName = $rococ->IncomingOntologyClass->name;
						$ocNameIdied = lcfirst($ocName) . "ID";
				
				
						$entity->$ocNameIdied = $objectItem->$ocName->id;
					}
				}
							
				array_push($imphelper->entities, $entity);
			}
		}
		
		//print_r($imphelper->entities[0]);
		
		$imphelper->bulkInsert_ImportEntities($imphelper->entities, true);
	}
	function importDataServiceEntities($dataserviceentities) {
		if (!$UserID = isLogged()) {
			$UserID = 23;
		}
		
		$entitiesData = array();
		
		$dataserviceentities[0]->setModificationInfo($UserID);
		
		foreach($dataserviceentities as $entity) {
			$entity->setModificationInfo($UserID);
				
			if (!$entity->externalKey) throw new Exception('externalKey missing');
			if (!$entity->internalKey) throw new Exception('internalKey missing');
			
			array_push($entitiesData, $entity->externalKey);
			array_push($entitiesData, $entity->internalKey);
			array_push($entitiesData, $entity->DataService->id);
			array_push($entitiesData, $entity->OntologyClass->id);
			
			array_push($entitiesData, $dataserviceentities[0]->createdBy);
			array_push($entitiesData, $dataserviceentities[0]->createdAt);
			array_push($entitiesData, $dataserviceentities[0]->updatedBy);
			array_push($entitiesData, $dataserviceentities[0]->updatedAt);
		}

		$this->bulkInsert_Array($entitiesData, "dataserviceentities", array("externalKey", "internalKey", "dataServiceID", "ontologyClassID", "createdBy", "createdAt", "updatedBy", "updatedAt"));
	}
	function convertJSONToOntology($jsonObject) {
		$objectvars = get_object_vars($jsonObject);
	
		$ontologyClasses = array();
		$ontologyClassEntities = array();
		$ontologyProperties = array();
		$ontologyPropertyEntities = array();
	
		foreach($objectvars as $key => $val) {
	
			if (is_object($val)) {
			} else if (is_array($val)) {
				$ontologyClassEntities = mapArrayToOntologyClassEntities($key, $val, $ontologyClasses, $ontologyClassEntities);
			} else {
				$ontologyProperty = mapFieldToOntologyPropertyEntity($key, $val, $ontologyProperties, $ontologyPropertyEntities);
			}
	
		}
	
		return $ontologyClassEntities;
	}
	function mapObjectToOntology($object) {
	
	}
	function mapFieldToOntologyPropertyEntity($key, $value, $ontologyProperties, $ontologyPropertyEntities) {
		if (isset($ontologyProperties[$key])) {
			return $ontologyProperties[$key];
		} else {
			$km = new KM();
			$ontologyProperty = $km->getOntologyPropertyByName($key);
	
			return $ontologyProperty;
		}
	}
	function mapArrayToObjects($array, $schema) {
	
	}
	
	function mapArrayToOntologyClassEntities($key, $value, $ontologyClasses, $ontologyClassEntities) {
		$ocEntities = array();
	
		$tokenizedKey = explode("_", $key);
		if (count($tokenizedKey) == 1) {
	
		} else if (count($tokenizedKey) == 2) {
			$km = new KM();
	
			$ontologyClass = $km->getOntologyClassByName($tokenizedKey[0]);
			$ontologyClass = $km->convertStdClassToObject($ontologyClass, "OntologyClass");
	
	
			foreach($value as $array_item) {
				$ocEntity = new OntologyClassEntity();
				$ocEntity->OntologyClass = $ontologyClass;
					
				foreach($array_item as $propKey => $propVal) {
					$tokenizedProperty = explode("_", $propKey);
					if (count($tokenizedProperty) > 1) {
						$ontologyProperty = $km->getOntologyPropertyByName($tokenizedProperty[1]);
							
					} else {
						$ontologyProperty = $km->getOntologyPropertyByName($tokenizedProperty[0]);
							
					}
					if ($ontologyProperty) {
						$ontologyProperty = $km->convertStdClassToObject($ontologyProperty, "OntologyProperty");
							
						$opEntity = new OntologyPropertyEntity();
						$opEntity->OntologyProperty = $ontologyProperty;
						$opEntity->name = $propVal;
						$relationOCOPEntity = new RelationOntologyClassOntologyPropertyEntity();
						$relationOCOPEntity->OntologyPropertyEntity = $opEntity;
							
						array_push($ocEntity->RelationOntologyClassOntologyPropertyEntities, $relationOCOPEntity);
					}
	
				}
					
	
				//print_r($relationOCOPEntity);
					
				array_push($ocEntities, $ocEntity);
			}
	
		}
	
		return $ocEntities;
	
	}
	function placeholders($text, $count=0, $separator=","){
		$result = array();
		if($count > 0){
			for($x=0; $x<$count; $x++){
				$result[] = $text;
			}
		}
	
		return implode($separator, $result);
	}
	function importXMLFile($filename, $objectName, $xPathQuery, $scope = null) {
		$doc = new DOMDocument();
		$doc->loadXML(file_get_contents($filename));
		
		$baseDoc = clone $doc;
		
		$xpathObj = new DOMXPath($baseDoc);
		$xpathObj->registerNamespace("alto", $baseDoc->lookupNamespaceUri($baseDoc->namespaceURI));
		$layout_node = $xpathObj->query('//alto:Layout');
		
		
		while ($layout_node[0]->hasChildNodes()) {
			$layout_node[0]->removeChild($layout_node[0]->firstChild);
		}
		
		$layoutNode = $layout_node[0];
		
		if ($scope) {
			if (class_exists("\\" . $scope . "\\" . $objectName)) {
				$objectName = "\\" . $scope . "\\" . $objectName;
			} else if (class_exists("\\" .$scope . "\\" . $scope . $objectName)) {
				$objectName = "\\" . $scope . "\\" . $scope . $objectName;
			}
			
			$object = new $objectName;
		} else {
			$object = new $objectName;
		}
		
		$object->name = $filename;
		
		
		$xpathObjPage = new DOMXPath($doc);
		$xpathObjPage->registerNamespace("alto", $doc->lookupNamespaceUri($doc->namespaceURI));
		$page_nodes = $xpathObjPage->query('//alto:Page');
		
		
		if (!is_null($page_nodes)) {
			$pages = array();
			foreach ($page_nodes as $pageNode) {
				$page = new OCR\Page();
				
				$page->number = str_ireplace("Page", "", $pageNode->attributes->getNamedItem('ID')->value);
				
				$docPageNode = $baseDoc->importNode($pageNode, TRUE);
				
				$layoutNode->appendChild($docPageNode);
				
				$page->altoXML = $baseDoc->saveXML();
				
				$layoutNode->removeChild($layoutNode->firstChild);
				
				array_push($pages, $page);
			}
			
			$object->Pages = $pages;
		}
		
		return $object;
	}
	function importFile($filename, $ontologyClass = null) {
		$nlp = new NLP();
		$edi = new EDI();
		$ie = new Extraction();
	
		$imphelper = new ImportHelper();
		if (isset($ontologyClass)) {
			$imphelper->OntologyClass = $ontologyClass;
		}
	
		$csvOntologyInfo = array();
		$delimiter = "";
		//print_r($ontologyClass);
		//$ontologyClassProperties = $ontologyClass->getOntologyProperties();
	
		$corpora = array();
		$words_tagged = array();
		$words = array();
	
		$i=0;
	
	
		$handle = fopen($filename, "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				if ($i==0) {
					$ontologyproperties = array();
	
					$line = str_replace('"', '', $line);
	
					if (stripos($line, ";") !== false) {
						$delimiter = ";";
					} else if (stripos($line, ",") !== false) {
						$delimiter = ",";
					}
					if ($delimiter !== "") {
						$header = explode($delimiter, $line);
					} else {
						$header[0] = $line;
					}
	
	
					$header = cleanCSVHeader($header);
	
					//print_r($header);
	
					$c=0;
					foreach($header as $header_item) {
						$tagged = $ie->postagText($header_item);
							
						//print_r($tagged);
							
						if (isset($tagged[0]->Lexeme)) {
							//echo "fuck\n";
							$tagged[0]->Lexeme->loadOntology();
								
							if (isset($tagged[0]->Lexeme->OntologyProperty)) {
								array_push($ontologyproperties, $tagged[0]->Lexeme->OntologyProperty);
								//echo $tagged[0]->Lexeme->OntologyProperty->name . "\n";
								$csvOntologyInfo[$tagged[0]->Lexeme->OntologyProperty->name] = $c;
	
								array_push($imphelper->entityProperties, $tagged[0]->Lexeme->OntologyProperty);
							} else if (isset($tagged[0]->Lexeme->OntologyClass)) {
								//array_push($ontologyproperties, $tagged[0]->Lexeme->OntologyProperty);
								echo $tagged[0]->Lexeme->OntologyClass->name . "\n";
								$csvOntologyInfo[$tagged[0]->Lexeme->OntologyClass->name] = $c;
									
								array_push($imphelper->entityClasses, $tagged[0]->Lexeme->OntologyClass);
							}
						}
							
						$c++;
					}
	
	
	
	
				} else {
					//print_r($csvOntologyInfo);
					if ($delimiter !== "") {
						$attributes = explode($delimiter, $line);
					} else {
						$attributes[0] = $line;
					}
	
					//print_r($csvOntologyInfo);
					//print_r($attributes);
	
					if (isset($ontologyClass)) {
						$entity = new ImportEntity();
						$entity->entityClassName = $ontologyClass->name;
						$entity->entityOntologyName = $ontologyClass->Ontology->name;
	
						foreach($ontologyClass->RelationOntologyClassOntologyProperties as $rocop) {
							//print_r($rocop);
							$opName = $rocop->OntologyProperty->name;
							//echo $opName . "\n";
							//echo $opName . ": " . $csvOntologyInfo[$opName] . "\n";
							if (!isset($csvOntologyInfo[$opName]) && $opName === "name") {
								$idxName = $ontologyClass->name;
							} else {
								$idxName = $opName;
							}
							if (isset($csvOntologyInfo[$idxName])) {
								$value = trim(preg_replace("/ {2,}/", " ", preg_replace( "/\r|\n/", "", preg_replace('/\s+\t+/', '', $attributes[$csvOntologyInfo[$idxName]]))), " \:\n\r");
									
								$value = str_ireplace('"', '', $value);
									
								$entity->$opName = $value;
							}
						}
							
						//print_r($entity);
							
						array_push($imphelper->entities, $entity);
					}
				}
					
					
				$i++;
			}
	
			fclose($handle);
	
			//echo "count.entities: " . count($imphelper->entities) . "\n";
	
			if(!$imphelper->entities[0]->tableExists()) {
				$imphelper->entities[0]->createTableByOntologyClass($imphelper->OntologyClass);
			}
	
			/*foreach($imphelper->entities as $impentity) {
				if (isset($imphelper->entities[0])) {
				if (count($imphelper->entities[0]->databaseConnections) === 1) {
				$impentity->databaseConnections = $imphelper->entities[0]->databaseConnections;
				}
				}
					
				$impentity->save();
				}*/
	
			//print_r($imphelper);
			$imphelper->bulkInsert_ImportEntities();
			//bulkInsert_Array($words, "word", array("word_name", "word_tagBrown", "word_type", "word_language"));
		} else {
			// error opening the file.
		}
	}
	function cleanCSVHeader($header) {
		$clean = array();
	
		foreach ($header as $header_item) {
			if (stripos($header_item, "_") !== false) {
				$split = split("_", $header_item);
				if (strlen($split[0]) <= 2 && strlen($split[1]) > 2) {
					if ($split[1] !== "") {
						array_push($clean, $split[1]);
					}
				}
			} else {
				//echo "[" . $header_item . "]\n";
				if (trim($header_item) !== "") {
					array_push($clean, trim($header_item));
				}
			}
		}
	
		return $clean;
	}
}
?>
