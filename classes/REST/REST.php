<?php
include_once ('REST_Transformer.php');
include_once ('REST_TaskExecutor.php');

include_once (__DIR__ . "/../Core/Helper.php");

class REST {
	use Helper;
	
	function __construct() {
		$this->orm = new ORM();
	}
	//TODO
	function get($id = null, $app = null) {
		$this->orm->db_scope = $this->getScopeName();
		
		$ontologyClassName = $this->orm->getOntologyClassName();
		
		if ($app) {
			
			if (isset($_GET['page'])) {
				
				$namedfieldParameters = $_GET;
				unset($namedfieldParameters['page']);
				unset($namedfieldParameters['per_page']);
				unset($namedfieldParameters['total_pages']);
				unset($namedfieldParameters['total_entries']);
					
				$oclass = null;
				if (class_exists("KM")) {
					$km = new KM();
						
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
					$result_paged = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($namedfieldParameters), array_values($namedfieldParameters));
		
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
					if ($ontologyClassName === "ReleasePublication") {
						$economics = new Economics();
						$result_paged = $economics->getNextReleasePublications();
					} else {
						$result_paged = $this->orm->getAllByName($oclass->name);
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
						$result = $this->orm->getAllByName($ontologyClassName);
					}
					
					
				}
			}
		
		} else {
			
			if ($id) {
				$obj = new $ontologyClassName();
				
				if ($ontologyClassName === "indicator") {
					$result = $this->orm->getById($ontologyClassName, $id);
						
					unset($result->Release->Indicators);
					unset($result->Release->ReleasePublications);
				} else if ($ontologyClassName === "Instrument") {
					$result = $this->orm->getById("Instrument", $id);
					
					$result->ImpactFunctions = $this->orm->getByNamedFieldValues("ImpactFunction", array("instrumentID"), array($result->id));
					foreach($result->ImpactFunctions as $if_item) {
						$if_item->RelationIndicatorImpactFunctions = $this->orm->getByNamedFieldValues("RelationIndicatorImpactFunction", array("impactFunctionID"), array($if_item->id), false, null, false, false, null, null, null, null, array("indicatorID"));
						
						foreach($if_item->RelationIndicatorImpactFunctions as $rel_item) {
							print_r($rel_item);
							$rel_item->Indicator = $this->orm->getByNamedFieldValues("Indicator", array("id"), array($rel_item->indicatorID));
						}
					}
					
				} else if ($ontologyClassName === "\\OCR\\Document") {
					$result = $this->orm->getById("\\OCR\\Document", $id, false);
					
					$doc = new DOMDocument();
					$doc->loadXML($result->Pages[0]->altoXML);
						
					$xmlconv = new XMLConverter("ALTO");
					$alto = $xmlconv->convertToObjectTree($doc);
					$result->Pages[0]->ALTO = $alto;
					
					//print_r($result);
					
					unset($result->Pages[0]->altoXML);
				} else {
					$result = $this->orm->getById($ontologyClassName, $id);
				}
			} else if (count($_GET) > 0) {
				$result = $this->orm->getByNamedFieldValues($ontologyClassName, array_keys($_GET), array_values($_GET));
			} else {
				$result = $this->orm->getAllByName($ontologyClassName);
			}
		}
		
		$this->cleanObjects($result);
		return $result;
	}
	function getObservations($id = null, $app = null) {
		$ontologyClassName = $this->getScopeObjectName();
		
		$limit = null;
		
		/*if (isset($_GET['limit'])) {
			$limit = $_GET['limit'];
		}*/
	
		if ($ontologyClassName === "indicator") {
			$observations = $this->orm->getByNamedFieldValues($ontologyClassName . "Observation", array(lcfirst($ontologyClassName) . "ID", "date"), array($id, "2014-01-01"), false, null, false, false, null, "date ASC", $limit, array(lcfirst($ontologyClassName) . "ID" => "=", "date" => ">="));
		} else if ($ontologyClassName === "instrument") {
			$observations = $this->orm->getByNamedFieldValues($ontologyClassName . "Observation", array(lcfirst($ontologyClassName) . "ID", "date"), array($id, "2015-01-01"), false, null, false, false, null, "date ASC", $limit, array(lcfirst($ontologyClassName) . "ID" => "=", "date" => ">="));
		}
		
		foreach($observations as $item) {
			unset($item->id);
			unset($item->$ontologyClassName);
		}
		
		$result = new stdClass();
		$result->items = $observations;
		
		return $result;
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
		
		$this->cleanObjects($result);
		
		return $result;
	}
	function addAPIClass($classname) {
		$this->$classname = new $classname;
		$this->$classname->db = $this->db;
		
	}
	function request($uri, $method = "GET", $object = null, $fields = null) {
		$objects = array();
		$objects[0] = $object;
		
		$server['REQUEST_METHOD'] = $method;

		$uri = str_replace(" ", "+", $uri);
		
		if ($objects[0]) $request_body = $this->getJSONAll($objects);
		
		$ch = curl_init();
		if ($method == "GET") {
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$output = curl_exec($ch);
		} else if ($method == "PUT") {
			curl_setopt($ch, CURLOPT_URL, $uri . "");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($request_body)
			));
			
			
			$output = curl_exec($ch);
		} else if ($method == "POST") {
			if ($fields != null) {
				$request_body = "";
				//url-ify the data for the POST
				$request_body = $fields;
			}
			
			curl_setopt($ch, CURLOPT_URL, $uri);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'Content-Length: '.strlen($request_body)
			));
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
			
			$output = curl_exec($ch);
		}

		return $output;
	}
	function getScopeObjectName($path = null) {
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
	
		if ($path) {
			$pathToUse = str_ireplace("http://", "", $path);
		} else {
			$pathToUse = $url_parsed ['path'];
		}
	
		$levels = explode ( "/", $pathToUse );
	
		$objectname = "";
		
		if (strpos($pathToUse, "localhost") !== false) {
			if (isset($levels[2])) $objectname = $levels[2];
		} else if (strpos($pathToUse, "/api/") !== false) {
			$apiIndex = array_search("api", $levels);
			
			$objectname = $levels[$apiIndex+2];
		} else {
			if (isset($levels[2])) $objectname = $levels[2];
		}
	
		return $this->singularize($objectname);
	}
	function loadRoutes($app, $ressourceRoot = null) {
		$scopeName = $this->getScopeName();
	
		if (strlen($scopeName) < 3) {
			$classScopeName = strtoupper($scopeName);
		} else {
			$classScopeName = ucfirst($scopeName);
		}
		
		if ($scopeName !== "" && !in_array(strtolower($scopeName), array("edi"))) {
			
			if (!$ressourceRoot) {
				$ressourceRoot = __DIR__;
			}
			
			if (file_exists($ressourceRoot . '/../../../../engulfing/engulfing-core/classes/' . $classScopeName . '/')) {
				require_once $ressourceRoot . '/../../../../engulfing/engulfing-core/classes/' . $classScopeName . '/' . $classScopeName . '.php';
			} else if (file_exists($ressourceRoot . '/../../../../engulfing/engulfing-extensions/classes/' . $classScopeName . '/')) {
				require_once $ressourceRoot . '/../../../../engulfing/engulfing-extensions/classes/' . $classScopeName . '/' . $classScopeName . '.php';
			} else {
				if (file_exists($ressourceRoot . '/../../../../engulfing/engulfing-core/classes/BusinessLogic/' . $classScopeName . '/')) {
					require_once $ressourceRoot . '/../../../../engulfing/engulfing-core/classes/BusinessLogic/' . $classScopeName . '/' . $classScopeName . '.php';
				}
			}
				
			$contents = glob($ressourceRoot  . '/ressources/' . $scopeName . '/' . '*.*');
				
			foreach ($contents as $file_name) {
				//echo $file_name . "\n";
				if (strpos($file_name, "task_") === false && strpos($file_name, ".json") === false) {
					require_once $file_name;
				}
			}
		}
		
		if (class_exists("KM")) {
			$km = new KM();
			
			$ontology = $km->getOntologyByName($scopeName);
			
			if ($ontology) {
					
				$scope = strtolower($ontology->name);
					
				if ($scope !== "news") {
			
					$classes = $km->getOntologyClassesByOntologyId($ontology->id);
						
					foreach ($classes as $class) {
						$ressourceName = strtolower($this->pluralize($class->name));
			
						$app->get('/' . $scope . '/' . $ressourceName . '/:id',	'get');
						$app->get('/' . $scope . '/' . $ressourceName . '/:id/detailed',	'getDetailed');
						$app->get('/' . $scope . '/' . $ressourceName . '/:id/observations',	'getObservations');
			
						$app->post('/' . $scope . '/' . $ressourceName . '', 'add');
						$app->put('/' . $scope . '/' . $ressourceName . '/:id', 'update');
						$app->delete('/' . $scope . '/' . $ressourceName . '/:id',	'delete');
						$app->get('/' . $scope . '/' . $ressourceName . '', function () use($app) {
							$callback = $app->request()->get('callback');
			
							if (!$callback) {
								get(null, $app);
							} else {
								callback_getObjects($callback);
							}
						});
						
						
						if (method_exists($class->name, "getValuation")) {
							$app->get('/' . $scope . '/' . $ressourceName . '/:id/valuation',	'getValuation');
						}
					}
				}
			}
		}
		
		if (isset($scope)) {
			if ($scope === "wiki") {
				$app->get('/wiki/articles/:id',	'getWikiArticle');
			} else if ($scope === "news") {
				$app->get('/news/:topic',	'getNewsByTopic');
			}
		}
	}
	function getValuation($id = null, $app = null) {
		$ontologyClassName = $this->orm->getOntologyClassName();
		
		if ($id) {
			$result = $this->orm->getById($ontologyClassName, $id);
		}
		
		return $result->getValuation();
	}
	function logRequest($app, $request_date) {
		if (stripos($app->request->getResourceUri(), "monitoring") !== false) return null;
	
		if (class_exists("Request")) {
			$request = new Request();
			$request->method = $app->request->getMethod();
			
			if (stripos($app->request->getResourceUri(), "/km") !== false && $request->method === "GET") return null;
			
			$rest = new REST ();
			$restTransformer = new REST_Transformer ();
			
			$OntologyName = $rest->orm->getScopeName();
			
			
			$result = $restTransformer->deserialize_JSON ( $app->response->getBody (), "Extract");
			
			if (isset($result->name)) {
				if (strlen($result->name) <= 4) $result->name = strtoupper($result->name);
				$request->refererUrl = str_ireplace("http://www.ontologydriven.com/", "", str_ireplace("http://localhost.ontologydriven/", "", $app->request->headers->get('Referer') . "#" . $result->name));
			} else {
				$request->refererUrl = str_ireplace("http://www.ontologydriven.com/", "", str_ireplace("http://localhost.ontologydriven/", "", $app->request->headers->get('Referer')));
			}
			$request->url = $app->request->getRootUri() . $app->request->getResourceUri();
			$request->sentAt = $request_date;
			$request->OntologyName = $OntologyName;
			$request->clientIP = $app->request->getIp();
			$request->userID = isLogged();
			
			if (isset($result->processing)) {
				$request->ResponseStatistics = json_encode($result->processing, JSON_PRETTY_PRINT);
			}
			
			$this->orm->save($request);
		}
		
	}
	function filterFields($extract) {
		$filtered = new Extract();
	
		if (isset($extract->ressource)) $filtered->ressource = $extract->ressource;
		if (isset($extract->language)) $filtered->ressource->language = $extract->language;
		if (isset($extract->information)) $filtered->information = $extract->information;
		if (isset($extract->Words)) $filtered->Words = $extract->Words;
		if (isset($extract->fragments)) $filtered->fragments = $extract->fragments;
	
		if (isset($filtered->ressource)) {
			foreach($filtered->ressource as $key => $value) {
				if ($key != 'type' && $key != 'size' && $key != 'page' && $key != 'size' && $key != 'language') {
					unset($filtered->ressource->$key);
				}
			}
		}
	
		if (isset($filtered->information['structuredproducts'])) {
			foreach($filtered->information['structuredproducts'] as $sp_item) {
				foreach($sp_item as $key => $value) {
					if ($key != 'isin' && $key != 'symbol') {
						unset($sp_item->$key);
					}
	
					if ($key == 'isin' && $value == '') {
						unset($filtered->information);
					}
				}
			}
		}
	
		if (isset($extract->error)) $filtered->error = $extract->error;
	
		return $filtered;
	}
	function removeNullValues($withnull) {
		foreach($withnull as $key => $value) {
			if ($value == null) {
				unset($withnull->$key);
			}
		}
	
		if (isset($filtered->information)) {
			foreach($withnull->information['structuredproducts'] as $sp_item) {
				foreach($sp_item as $key => $value) {
					if ($value == null) {
						unset($sp_item->$key);
					}
				}
			}
		}
	
		return $withnull;
	}
	function checkAuthorization($app) {
		if (class_exists("Monitoring")) {
			$mon = new Monitoring();
			
			$scopeName = $app->request->getResourceUri();
			if ($mon->isEligibleScopeForProtection($scopeName)) {
				$granted = $mon->getAccessPermissionByClientAndScope(isLogged(), $app->request->getIp(), $scopeName);
			
				if (!$granted) {
					$extract = new Extract ();
					$extract->error = new Error ();
					$extract->error->message = "Extraction Failure.";
					$extract->error->details = "Not Authorized. Daily Limit Exeeded.";
			
					$extract = filterFields ( $extract );
					$extract = removeNullValues ( $extract );
						
					echo json_encode ( $extract, JSON_PRETTY_PRINT );
					exit ();
				}
			
				return true;
			}
		}
	}
	function getContent($id = null, $app = null) {
		$km = new KM();
	
		$ontology = $km->getOntologyById($id);
	
		if (isset($ontology)) {
			$content = $ontology->getContent();
	
			echo json_encode ( $content, JSON_PRETTY_PRINT );
		}
	}
}
class Response {
	var $message;
	
	function __construct() {
		
	}
}

?>