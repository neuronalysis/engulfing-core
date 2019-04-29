<?php
class EDI extends Thing {
    protected $config;
    
    var $classes = array("Schedule", "ImportProcess", "DataProvider", "DataService", "Resource", "RelationDataServiceOntologyClass", "DataSource", "OntologyClass");
	
	var $entities = '{}';
	
	var $debugMode = true;
	var $userID;
	
	function __construct() {
	}
	function getImportProcesses() {
		
	}
	function importData() {
		$rest = REST::getInstance();
		$fio = new FileIO();
		
		$ext = new Extraction();
		$ext->setConfig($rest->getConfig());
		$ext->apiMode = true;
		$this->debugMode = false;
		$this->debugMode = false;
		$this->setConfig($rest->getConfig());
		
		$processing = new Processing();
		
		try {
			$config = $rest->getConfig();
			
			$task = new Task ( "uploading" );
			
			$ip = $this->getImportProcessById(10);
			
			$methodName = "import" . $this->pluralize($ip->DataService->OntologyClass->name);
			
			$ontologyName = $ip->DataService->OntologyClass->Ontology->name;
			
			$ontology = new $ontologyName;
			
			if ($methodName == "importInstrumentObservations") {
				$objectName = "Instrument";
				
				$methodName = "importInstrumentObservationsByInstrument";
			}
			
			if (method_exists($ontology, $methodName)) {
				if ($methodName == "importInstrumentObservationsByInstrument") {
					$instrument = $ontology->getInstrumentByID(1);
					
					$resource = $ontology->$methodName($instrument, $ip);
				} else {
					$resource = $ontology->$methodName($ip);
				}
			}
			
			
			if (method_exists($ontology, $methodName)) {
				if ($methodName == "importInstrumentObservationsByInstrument") {
					$instrument = $ontology->getInstrumentByID(2);
					
					$resource = $ontology->$methodName($instrument, $ip);
				} else {
					$resource = $ontology->$methodName($ip);
				}
			}
			
			
			if (method_exists($ontology, $methodName)) {
				if ($methodName == "importInstrumentObservationsByInstrument") {
					$instrument = $ontology->getInstrumentByID(3);
					
					$resource = $ontology->$methodName($instrument, $ip);
				} else {
					$resource = $ontology->$methodName($ip);
				}
			}
			
			if (method_exists($ontology, $methodName)) {
				if ($methodName == "importInstrumentObservationsByInstrument") {
					$instrument = $ontology->getInstrumentByID(4);
					
					$resource = $ontology->$methodName($instrument, $ip);
				} else {
					$resource = $ontology->$methodName($ip);
				}
			}
		} catch ( Exception $e ) {
			$error = new Error ();
			$error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
			
			echo json_encode ( $error, JSON_PRETTY_PRINT );
			exit ();
		}
	}
	function testXpath() {
		$filename = __DIR__ . '/../../../data/ocr/altoxmls/bulletin_ocr_1968_1_alto_xml.xml';
		
		$doc = new DOMDocument();
		$doc->load($filename);
		
		$xpathObj = new DOMXPath($doc);
		$rootNamespace = $doc->lookupNamespaceUri($doc->namespaceURI);
		echo $rootNamespace. "\n";
		$xpathObj->registerNamespace("alto", $rootNamespace);
		$xpath_pages = '//*';
		
		$page_nodes = $xpathObj->query($xpath_pages);
		
		echo "length: " . $page_nodes->length . "\n";
		
		$xquery = '//alto:Page';
		$elements = $xpathObj->query($xquery);
		
		$i = 0;
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				if ($i < 10) {
					echo $element->nodeName . ": " . $element->getNodePath() . "\n";
					
					$i++;
				}
			}
		}
	}
	function uploadAltoXMLFile() {
		$rest = REST::getInstance();
		$config = $rest->getConfig();
		
		$edi = new EDI();
		$fio = new FileIO();
		$conv = new TIFFConverter();
		
		$scopename = $rest->getScopeName();
		if (! isset ( $_FILES ['file_data'] )) {
			return "No files uploaded!!";
		} else {
			$processing = new Processing();
			
			try {
				$task = new Task ( "uploading" );
				
				$uploaded = array ();
				
				$files = $_FILES ['file_data'];
				$cnt = count ( $files ['name'] );
				
				//remove existing files from target-upload-directory to avoid confusion
				$fio_removed = $fio->rmdirr($config['frontend']['work'] . 'ocr/altoxmls', true);
				
				if ($cnt === 0) {
					$error = new Error ();
					$error->details = "no files in scope of upload";
					
					return $error;
					exit ();
				} else if ($cnt === 1) {
					if ($files ['error'] === 0) {
						if (move_uploaded_file ( $files ['tmp_name'], $config['frontend']['work'] . 'ocr/altoxmls/' . $files ['name'] ) === true) {
							$uploaded [] = array (
									'url' => $config['frontend']['work'] . 'ocr/altoxmls/',
									'name' => $files ['name']
							);
						} else {
							$error = new Error ();
							$error->details = "file was not moved from " . $files ['tmp_name'] . " to " . $config['frontend']['work'] . 'ocr/altoxmls/' . $files ['name'];
							
							return $error;
							exit ();
						}
					} else {
						$error = new Error ();
						$error->details = print_r($_FILES, true);
						
						return $error;
						exit ();
					}
				} else {
					for($i = 0; $i < $cnt; $i ++) {
						if ($files ['error'] [$i] === 0) {
							if (move_uploaded_file ( $files ['tmp_name'] [$i],  $config['frontend']['work'] . 'ocr/altoxmls/' . $files ['name'] [$i] ) === true) {
								$uploaded [] = array (
										'url' => '/uploads/' . $name,
										'name' => $files ['name'] [$i]
								);
							} else {
								$error = new Error ();
								$error->details = "no files were transferred from uploaded to working-directory";
								
								return $error;
								exit ();
							}
						} else {
							$error = new Error ();
							$error->details = $files ['error'] [$i];
							
							return $error;
							exit ();
						}
					}
				}
				
				$processing->addTask ( $task );
				
			} catch ( Exception $e ) {
				$error = new Error ();
				$error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
				
				return $error;
				exit ();
			}
			
			
			//TODO avoid importing all directories content. only process uploaded file.
			//TODO don't do shits with specific filenames for filtering (whitelist instead of blacklist)
			try {
				$task = new Task ( "importing" );
				
				if (file_exists( $config['frontend']['work'] . 'ocr/altoxmls/')) {
					$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator (  $config['frontend']['work'] . 'ocr/altoxmls/' ) );
					foreach ( $directory_iterator as $filename => $path_object ) {
						if ($path_object->getFilename() != '.' && $path_object->getFilename() != '..') {
							if(is_file($filename)) {
								$filetype = pathinfo($filename, PATHINFO_EXTENSION);
								if ($filetype === "zip") {
									$zip = new ZipArchive ();
									$res = $zip->open ( $filename);
									if ($res === TRUE) {
										if ($extracted = $zip->extractTo ( $filename. '_unzipped/' )) {
											$zip->close ();
										} else {
											$error = new Error ();
											$error->details = print_r($extracted, true);
											
											return $error;
											exit ();
										}
										
										
										$xmlFileName = $filename. '_unzipped/' . str_ireplace('.zip', '', basename($filename))  . '/' . str_ireplace('.zip', '', basename($filename)). '.xml.xml';
										$imgDirectoryName = $filename. '_unzipped/' . str_ireplace('.zip', '', basename($filename))  . '/' . str_ireplace('.zip', '', basename($filename)). '_images';
										
										if(is_file($xmlFileName)) {
											$xmlObject = $edi->importXMLFile($xmlFileName, "Document", "//Page", "OCR");
										} else {
											$error = new Error ();
											$error->details = $xmlFileName . " is no valid xml file";
											
											return $error;
											exit ();
										}
										
										if (file_exists($imgDirectoryName)) {
											
											$fio->cpy($imgDirectoryName, $config['frontend']['work'] . 'ocr/images/');
											
											$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $config['frontend']['work'] . 'ocr/images/') );
											foreach ( $directory_iterator as $imgFileName=> $path_object ) {
												if(is_file($imgFileName)) {
													$pageNumber = substr(basename($imgFileName, ".tif"), -4, 4);
													rename($imgFileName, str_ireplace(basename($imgFileName), $xmlObject->name . "_" . $pageNumber . ".tif", $imgFileName));
												}
											}
										}
									} else {
										$error = new Error ();
										$error->details = "zip file could not be processed";
										
										return $error;
										exit ();
									}
								} else if ($filetype === "xml") {
									if (basename($filename) != "README" && basename($filename) != "CONTENTS" && basename($filename) != "cats.txt") {
										$xmlObject = $edi->importXMLFile($filename, "Document", "//Page", "OCR");
									}
								} else {
									$error = new Error ();
									$error->details = "no xml-files were in scope of upload";
									
									return $error;
									exit ();
								}
							} else {
								$error = new Error ();
								$error->details = $filename . " is no valid file" . "\n" . print_r($path_object, true);
								
								return $error;
								exit ();
							}
						}
						
					}
				} else {
					$error = new Error ();
					$error->details = "files did not exist for import in " . $config['frontend']['work'] . 'ocr/altoxmls/';
					
					return $error;
					exit ();
				}
				
				$processing->addTask ( $task );
				
				
				$savedDocumentID = $rest->orm->save($xmlObject);
				
				foreach($xmlObject->Pages as $pageItem) {
					$pageItem->documentID = $savedDocumentID;
					
					$rest->orm->save($pageItem);
				}
				$extract = new stdClass();
				$extract->processing = $processing;
				$extract->uploaded = $uploaded;
				
				
				return $extract;
				
			} catch ( Exception $e ) {
				$extract = new stdClass();
				$extract->error = new Error ();
				$extract->error->details = $e->getMessage () . "\n" . $e->getFile() . " - " . $e->getLine();
				
				return $extract;
				exit ();
			}
		}
	}
	function getImportProcessByID($importprocessID) {
		$rest = \REST::getInstance();
		
		$result = $rest->orm->getById("ImportProcess", $importprocessID);
		
		
		$orm_req = new ORM_Request("DataService", array("id" => $result->DataService->id), array("dataProviderID"));
		
		$ds = $rest->orm->getByNamedFieldValues($orm_req);
		
		$ds[0]->DataProvider = $rest->orm->getById("DataProvider", $ds[0]->dataProviderID);
		
		
		
		$schema = json_decode($ds[0]->schemaDefinition);
		
		if ($schema) {
			$schemavars = get_object_vars($schema);
			
			if (isset($schemavars['parameters'][0]->ontologyClass)) {
				$orm_req = new ORM_Request("OntologyClass", array("name" => $schemavars['observations'][0]->ontologyClass), array("ontologyID"));
				
				$ontologyClasses = $rest->orm->getByNamedFieldValues($orm_req);
				
				$ontologyClasses[0]->Ontology = $rest->orm->getById("Ontology", $ontologyClasses[0]->ontologyID);
				
				$ds[0]->OntologyClass = $ontologyClasses[0];
			}
			
			$result->DataService = $ds[0];
		}
		
		
		return $result;
	}
	function getResources($urls) {
		$resources = array();
		
		foreach($urls as $url) {
			array_push($resources, $this->getResource($url));
		}
		
		return $resources;
	}
	function getResourceByContent($content) {
	    $resource = new Resource();
	    $resource->loadByContent($content);
	    
	    return $resource;
	}
	//TODO
	function getResource($url, $noDownload = false, $enforcedType = null, $save = false, $headers = null) {
		$rest = REST::getInstance();
		$config = $rest->getConfig();
		
	    try {
	        $resource = new Resource($url);
			
	        if (!$this->is_connected() || $this->debugMode) {
			    $noDownload = true;
			    $resource->url = $config['frontend']['path'] . "../work/extraction/testresource.pdf";
				
				//$enforcedType = "application/pdf; charset=binary";
				$resource->load($noDownload, $enforcedType);
				$fio = new FileIO();
				$fio->saveStringToFile($resource->content, $config['frontend']['path'] . "../work/extraction/testresource.pdf");
			} else {
				$resource->load($noDownload, $enforcedType, $headers);
			    
			    $resource->name = basename($url);
			    
			    $exp_basename = explode("?", basename($url));
			    
			    $fio = new FileIO();
			    $fio->saveStringToFile($resource->content, $config['frontend']['work'] . "download/" . str_ireplace("?", "", $exp_basename[0]));
			}
			
			return $resource;
		}
		catch (Exception $e) {
			throw $e;
		}
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
	function importObjects($objects, $schema, $internalKeys) {
		$auth = Authentication::getInstance();
		
		$UserID = $auth->isLogged();
		
		$km = new KM();
		$rest = REST::getInstance();
		
		$entities = array();
		$entityClasses = array();
		$entityProperties = array();
		
		$bulkValues = array();
		$bulkFields = array();
		
		$ontologyClass = $objects[0]->OntologyClass;
		
		if (isset($ontologyClass)) {
			array_push($entityClasses, $ontologyClass);
			
			foreach($ontologyClass->RelationOntologyClassOntologyProperties as $rocop) {
				array_push($entityProperties, $rocop->OntologyProperty);
			}
			
			foreach($ontologyClass->RelationOntologyClassOntologyClasses as $rococ) {
				if ($rococ->OntologyRelationType->name === "hasOne") {
					$ocName = $rococ->IncomingOntologyClass->name;
					$ocNameIdied = lcfirst($ocName) . "ID";
						
					if (isset($objects[0]->$ocName->id)) {
						$op = new OntologyProperty();
						$op->name = $ocNameIdied;
							
						array_push($entityProperties, $op);
					} else {
						//echo $ocName . "; fuuuck\n";
						//print_r($objects[0]);
					}
					
				}
				
			}
			
			foreach($objects as $objectItem) {
				$entity = new stdClass();
					
				foreach($ontologyClass->RelationOntologyClassOntologyProperties as $rocop) {
					$opName = $rocop->OntologyProperty->name;
					
					$entity->$opName = $objectItem->$opName;
					
					//TODO modification info missing on observations?
					/*if ($UserID) {
						$entity->setModificationInfo($UserID);
					} else {
						$entity->setModificationInfo(23);
					}*/
				}
				
				foreach($ontologyClass->RelationOntologyClassOntologyClasses as $rococ) {
					if ($rococ->OntologyRelationType->name === "hasOne") {
						$ocName = $rococ->IncomingOntologyClass->name;
						
						$ocNameIdied = lcfirst($ocName) . "ID";
				
				
						$entity->$ocNameIdied = $internalKeys[$ocNameIdied];
					}
				}
							
				array_push($entities, $entity);
			}
		}
		
		foreach($entityProperties as $entityPropertyItem) {
			$keyName = $entityPropertyItem->name;
			array_push($bulkFields, $keyName);
		}
			
		foreach($entities as $entityItem) {
			foreach($entityProperties as $entityPropertyItem) {
				$keyName = $entityPropertyItem->name;
				array_push($bulkValues, $entityItem->$keyName);
			}
		}
		
		$rest->orm->insertArrayBulk($bulkValues, $ontologyClass->name, $bulkFields);
	}
	function importDataServiceEntities($dataserviceentities) {
		$auth = Authentication::getInstance();
		
		if (!$UserID = $auth->isLogged()) {
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
		
		//echo file_get_contents($filename);
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
		
		$object->name = basename($filename, ".xml");
		
		
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
								//echo $tagged[0]->Lexeme->OntologyClass->name . "\n";
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
