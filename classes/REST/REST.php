<?php
class REST {
	use Helper;
	
	protected $config;
	
	public static $instance;
	
	function __construct() {
		$this->orm = ORM::getInstance();
		
		$rc = new \ReflectionClass(get_class($this));
		
		$this->app = new Slim\Slim(array(
				'debug' => true
		));
		
		$this->app->contentType('application/json; charset=utf-8');
		$this->app->error(function (\Exception $e) {
			$this->app->render('error.php');
		});
		
		self::$instance = $this;
	}
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	function run() {
		$this->checkAuthorization($this->app);
		
		$this->app->run();
		
		$this->logRequest($this->app, date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']));
	}
	function addAPIClass($classname) {
		$this->$classname = new $classname;
		$this->$classname->db = $this->db;
		
	}
	function isLogged() {
		$auth = Authentication::getInstance();
		if(!$auth->isLogged()) {
			return false;
		} else if (!isset($_COOKIE['UserID'])) {
			return false;
		} else {
			return $_COOKIE['UserID'];
		}
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
	function registerEndpoint($endpoint, $methodType, $action) {
		$action_exp = explode(":", $action);
		$action_class = $action_exp[0];
		$action_method = $action_exp[1];
		
		switch ($methodType) {
			case 'get':
				break;
			case 'post':
				$this->app->post($endpoint, function() {
					$action_obj = new $action_class();
					
					$this->handleResult(
						$action_obj->$action_method()
					);
				});
				
				break;
			default:
				break;
		}
	}
	//TODO gebastel. mix aus generalisierung und spezialfällen...
	function loadRoutes($resourceRoot = null) {
		$scopeName = $this->getScopeName();
		
		if (strlen($scopeName) < 3) {
			$classScopeName = strtoupper($scopeName);
		} else {
			$classScopeName = ucfirst($scopeName);
		}
		
		if ($scopeName !== "") {
			if (!$resourceRoot) {
				$resourceRoot = __DIR__;
			}
			
			$contents = glob($resourceRoot  . '/resources/' . $scopeName . '/' . '*.*');
				
			foreach ($contents as $file_name) {
				if (strpos($file_name, "task_") === false && strpos($file_name, ".json") === false) {
					require_once $file_name;
				}
			}
		}
		
		//authentication routes
		//TODO
		$app = $this->app;
		
		$this->app->get('/authentication/roles/:id',	'REST_Controller:get');
		$this->app->post('/authentication/roles', 'REST_Controller:add');
		$this->app->put('/authentication/roles/:id', 'REST_Controller:update');
		$this->app->delete('/authentication/roles/:id',	'REST_Controller:delete');
		$this->app->get('/authentication/roles', function () use($app) {
			$callback = $app->request()->get('callback');
			
			$rc = new REST_Controller();
			
			if (!$callback) {
				$rc->get(null);
			} else {
				callback_getObjects($callback);
			}
		});
		$this->app->get('/authentication/users/:id',	'REST_Controller:get');
		$this->app->get('/authentication/users/:id/watchlists/',	'User:getWatchlists');
		$this->app->post('/authentication/users', 'USER:add');
		$this->app->put('/authentication/users/:id', 'USER:update');
		$this->app->delete('/authentication/users/:id',	'REST_Controller:delete');
		$this->app->get('/authentication/users', function () use($app) {
			$callback = $app->request()->get('callback');
			
			$rc = new REST_Controller();
			
			if (!$callback) {
				$rc->get(null);
			} else {
				callback_getObjects($callback);
			}
		});
			
		$this->app->post('/authentication/login', '\Authentication:login');
		$this->app->get('/authentication/logout', '\Authentication:logout');
		$this->app->get('/authentication/recovery', '\Authentication:recoverPassword');
		$this->app->post('/authentication/recovery', '\Authentication:resetPassword');
		$this->app->post('/authentication/recovery', '\Authentication:resetPassword');
		$this->app->post('/authentication/signup', '\Authentication:signupUser');
		
		
		//domain logic related routes
		if (class_exists("KM")) {
			$km = new KM();
			
			$ontology = $km->getOntologyByName($scopeName);
			
			if ($ontology) {
				
				$scope = strtolower($ontology->name);
				
				if ($scope !== "news") {
					$classes = $km->getOntologyClassesByOntologyId($ontology->id);
						
					foreach ($classes as $class) {
						$resourceName = strtolower($this->pluralize($class->name));
						
						$this->app->get('/' . $scope . '/' . $resourceName . '/:id',	'REST_Controller:get');
						
						$this->app->get('/' . $scope . '/' . $resourceName . '/:id/observations',	'REST_Controller:getObservations');
						
						$this->app->post('/' . $scope . '/' . $resourceName . '', 'REST_Controller:add');
						$this->app->put('/' . $scope . '/' . $resourceName . '/:id', 'REST_Controller:update');
						$this->app->delete('/' . $scope . '/' . $resourceName . '/:id',	'REST_Controller:delete');
						$app = $this->app;
						$this->app->get('/' . $scope . '/' . $resourceName . '', function () use($app) {
							$callback = $app->request()->get('callback');
							
							$rc = new REST_Controller();
							
							if (!$callback) {
								$rc->get(null);
							} else {
								$rc->callback_getObjects($callback);
							}
						});
						
						
						if (method_exists($class->name, "getValuation")) {
							$this->app->get('/' . $scope . '/' . $resourceName . '/:id/valuation',	'REST_Controller:getValuation');
						}
					}
				}
			}
		}
		
		
		if (isset($scope)) {
			if ($scope === "wiki") {
				$this->app->get('/wiki/articles/:id',	'getWikiArticle');
			} else if ($scope === "news") {
				$this->app->get('/news/:topic',	'getNewsByTopic');
			}
		}
	}
	function logRequest($app, $request_date) {
		if (stripos($app->request->getUri(), "monitoring") !== false) return null;
	
		if (class_exists("Request")) {
			$request = new Request();
			$request->method = $app->request->getMethod();
			
			if (stripos($app->request->getUri(), "/km") !== false && $request->method === "GET") return null;
			
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
	
		if (isset($extract->resource)) $filtered->resource = $extract->resource;
		if (isset($extract->language)) $filtered->resource->language = $extract->language;
		if (isset($extract->information)) $filtered->information = $extract->information;
		if (isset($extract->Words)) $filtered->Words = $extract->Words;
		if (isset($extract->fragments)) $filtered->fragments = $extract->fragments;
	
		if (isset($filtered->resource)) {
			foreach($filtered->resource as $key => $value) {
				if ($key != 'type' && $key != 'size' && $key != 'page' && $key != 'size' && $key != 'language') {
					unset($filtered->resource->$key);
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
				$granted = $mon->getAccessPermissionByClientAndScope($this->isLogged(), $app->request->getIp(), $scopeName);
			
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
	function handleResult($result) {
		$response = new Response();
		
		if ($result instanceof ErrorException) {
	        //print_r($result);
    	    $response->error = $result->getTraceAsString();
    	    
    	    $response->message = $result->getMessage();
    	    
    	    
    	    $this->app->response->setBody(json_encode ( $response, JSON_PRETTY_PRINT ));
	    
	    } else if ($result instanceof Exception) {
		    $response->error = $result->getTraceAsString();
		    
		    $result->traceMsg = $response->error;
		    $this->app->response->setStatus(400);
		    $this->app->response->headers->set('Content-Type', 'application/json');
		    $this->app->response->setBody(json_encode ( $result, JSON_PRETTY_PRINT ));
		    
		} else if ($result instanceof PDOException) {
		    $this->app->response->setBody(json_encode ( $result, JSON_PRETTY_PRINT ));
		} else {
		    $this->app->response->setBody(json_encode ( $result, JSON_PRETTY_PRINT ));
		}
	}
}
class Response {
	function __construct() {
		
	}
}

?>