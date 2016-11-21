<?php
//XXX clean up class
class Website extends Website_Generated {
	use Helper;
	use DOMHelper;
	
	use WebsiteScript;
	use WebsiteNavigation;
	
	var $website_url;
    var $website_source;
	var $html;
	var $Ontology;
	var $auth;
	
	var $elements = array();
	
	var $title;
	
	var $baseurl;
	var $ressource;
	var $website_document;
	var $website_directory;
	var $website_sections;
	
	var $generated = false;
	
	var $activescope_OntologyClass;
	var $activescope_Ontology;
	var $activescope_admin = false;
	var $activescope_usermanagement = false;
	var $activescope_monitoring = false;
	var $Ontologies = array();
	
	var $levels = array ();
	
	var $isShop;
	
	var $debug = true;
	
	var $accessRestrictions;
	
	var $usedOntologyClasses = array();
	
	var $object_class_names = array('Head_Website', 'Body_Website');
	
	function __construct($Ontologies = array(), $title = null) {
		$this->title = $title;
		
		$this->Ontologies = $Ontologies;
	}
	function isAllowed($ontology) {
		if (isset($this->accessRestrictions[$ontology->name])) {
			if ($this->auth->isLogged()) {
				if (isset($this->accessRestrictions[$ontology->name][$_COOKIE['UserRoleID']])) {
					if ($this->accessRestrictions[$ontology->name][$_COOKIE['UserRoleID']] === true) {
						return true;
					}
				}
			}
		} else {
			return true;
		}
		
		return false;
	}
	function getDescription() {
    	
    	
    	$scopename = $this->getScopeName();
    	if ($scopename === "") {
    		$exploded = explode("/", $_SERVER ['DOCUMENT_ROOT']);
    			
    		$scopename = end($exploded);
    	}
    		 
    	$ontologyJSON = "";
    
    	if (file_exists("../" . strtolower($scopename) . "/data/ontology.json")) {
    		$ontologyJSON = file_get_contents ( "../" . strtolower($scopename) . "/data/ontology.json" );
    	}
    
    	$ontologyData = json_decode($ontologyJSON);
    
    	if (isset($ontologyData->description)) {
    		$description = $ontologyData->description;
    	} else {
    		$description = "";
    	}
    	
    	return $description;
    }
    function getTitle() {
    	if (!$this->title) {
    		$scopename = $this->getScopeName();
    		if ($scopename === "") {
    			$exploded = explode("/", $_SERVER ['DOCUMENT_ROOT']);
    			
    			$scopename = end($exploded);
    		}
    		 
    		$ontologyJSON = "";
    		
    		if (file_exists("../" . strtolower($scopename) . "/data/ontology.json")) {
    			$ontologyJSON = file_get_contents ( "../" . strtolower($scopename) . "/data/ontology.json" );
    		}
    		
    		$ontologyData = json_decode($ontologyJSON);
    		
    		if (isset($ontologyData->title)) $this->title = $ontologyData->title;
    	}
    	
    	if (!$this->title) {
    		$this->title = str_replace("Website_", "", get_class($this));
    	}
    	
    	return $this->title;
    }
    function getHomeUrl() {
    	$topDomain = $this->getTopDomain();
    	 
    	if ($this->isLocalRequest()) {
    		$home_url = "";
    		if ($this->generated) {
    			$home_url = "http://localhost." . $topDomain . "/";
    		} else {
    			$home_url = "http://localhost." . $topDomain . "/";
    		}
    	} else {
    		$home_url = "";
    		if ($this->generated) {
    			$home_url = "http://www." . $topDomain . ".com/";
    		} else {
    			$home_url = "http://www." . $topDomain . ".com/";
    		}
    	}
    	
    	return $home_url;
    }
    function getHomeTitle() {
    	$depth = $this->getScopeDepth();
    	
    	$topDomain = $this->getTopDomain();
    	
    	$scopename = $this->getScopeName();
    	
    	$ontologyJSON = "";
    	
    	if ($this->isLocalRequest()) {
    		if ($depth == 0) {
    			if (file_exists("/data/ontology.json")) {
    				$ontologyJSON = file_get_contents ( "data/ontology.json" );
    			}
    		} else if ($depth == 1) {
    			if (file_exists("../" . $topDomain . "/data/ontology.json")) {
    				$ontologyJSON = file_get_contents ( "../" . $topDomain . "/data/ontology.json" );
    			}
    		} else if ($depth == 2) {
    			if (file_exists("../../" . $topDomain . "/data/ontology.json")) {
    				$ontologyJSON = file_get_contents ( "../../" . $topDomain . "/data/ontology.json" );
    			}
    		}
    	} else {
    		$before_chdir = getcwd();
    		
    		if ($depth == 0) {
    			$ontologyJSON = file_get_contents ( getcwd() . "/data/ontology.json" );
    		} else if ($depth == 1) {
    			chdir('../');
    			$ontologyJSON = file_get_contents ( getcwd() . "/data/ontology.json" );
    			chdir($scopename);
    		} else if ($depth == 2) {
    			chdir('../../');
    			$ontologyJSON = file_get_contents ( getcwd() . "/data/ontology.json" );
    		} else if ($depth == 3) {
    			chdir('../../../');
    			$ontologyJSON = file_get_contents ( getcwd() . "/data/ontology.json" );
    		}
    		chdir($before_chdir);
    		
    	}
    	
    	$ontologyData = json_decode($ontologyJSON);
    	if ($ontologyData) {
    		return $ontologyData->shortTitle;
    	} else {
    		return $this->getShortTitle();
    	}
    }
    function getShortTitle() {
    	$scopename = $this->getScopeName();
    	if ($scopename === "") {
    		$tmp = explode("/", $_SERVER ['DOCUMENT_ROOT']);
    		$scopename = end($tmp);
     	}
    	 
    	$ontologyJSON = "";
        if (file_exists("../" . strtolower($scopename) . "/data/ontology.json")) {
    		$ontologyJSON = file_get_contents ( "../" . strtolower($scopename) . "/data/ontology.json" );
    	} else {
    		if (file_exists("../../" . strtolower($scopename) . "/data/ontology.json")) {
    			$ontologyJSON = file_get_contents ( "../../" . strtolower($scopename) . "/data/ontology.json" );
    		}
    	}
    
    	$ontologyData = json_decode($ontologyJSON);
       	if ($ontologyData) {
       		return $ontologyData->shortTitle;
       	} else {
       		return $this->getTitle();
       	}
    }
    function loadOntologiesFromSiteMap($siteMap) {
    	$ontologies = $siteMap->Ontologies;
    	 
    	foreach($ontologies as $ontology) {
    		foreach($ontology->OntologyClasses as $ontologyclass) {
    			$ontologyclass->Ontology = $ontology;
    		}
    	}
    	
    	$this->Ontologies = $ontologies;
    }
    function init_forRendering() {
    	$rest = new REST_Transformer();
    	
    	$this->title = $this->getTitle();
    	
    	$this->auth = new Authentication();
    	
    	$web = new Web();
    	$loadedWebsite = $web->getWebsiteByName($this->title);
    	if ($loadedWebsite) {
    		$this->siteMapDefinition = $loadedWebsite->siteMapDefinition;
    		
    		$this->isShop = $loadedWebsite->isShop;
    	}
    	
    	if (!class_exists("KM")) {
	    	$siteMap = $rest->deserialize_JSON($this->siteMapDefinition);
	    	$this->loadOntologiesFromSiteMap($siteMap);
	    }
	    
    	$this->accessRestrictions = array(
    			"EDI" => array(
    				1 => true
    			),
    			"NLP" => array(
    				1 => true
    			),
    			"CodeGeneration" => array(
    				1 => true
    			),
    			"LMS" => array(
    				1 => true
    			)
    	);
    	
    	$this->website_url = $_SERVER['REQUEST_URI'];
    	
    	$url_parsed = parse_url ( $this->website_url );
    	
    	$this->levels = explode ( "/", $url_parsed ['path'] );
    	 
    	$this->init_activeScope_Ontology();
    	
    	$this->init_activeScope_OntologyClass();
    }
    function init_activeScope_Ontology() {
    	foreach($this->Ontologies as $Ontology_item) {
    		if (isset($Ontology_item)) {
    			if (strpos($this->website_url, strtolower($Ontology_item->name)) !== false) {
    				$this->activescope_Ontology = $Ontology_item;
    			}
    		}
		}
    	if (!$this->activescope_Ontology) {
    		foreach($this->Ontologies as $Ontology_item) {
    			if (isset($Ontology_item->OntologyClasses)) {
    				foreach($Ontology_item->OntologyClasses as $OntologyClass_item) {
    					foreach ($this->levels as $level_item) {
    						if ($level_item == strtolower($OntologyClass_item->name) || $level_item == $this->pluralize(strtolower($OntologyClass_item->name))) {
    							$this->activescope_Ontology = $OntologyClass_item->Ontology;
    						}
    					}
    				}
    			}
    		}
    	}
    	
    	if (strpos($this->website_url, "usermanagement") !== false) {
    		$this->activescope_usermanagement = true;
    	}
    	
    	if (strpos($this->website_url, "monitoring") !== false) {
    		$this->activescope_monitoring = true;
    	}
    }
    function init_activeScope_OntologyClass() {
    	foreach($this->Ontologies as $Ontology_item) {
    		if (isset($Ontology_item)) {
    			if (isset($Ontology_item->OntologyClasses)) {
	    			foreach($Ontology_item->OntologyClasses as $OntologyClass_item) {
	    				foreach ($this->levels as $level_item) {
	    					if ($level_item == strtolower($OntologyClass_item->name) || $level_item == $this->pluralize(strtolower($OntologyClass_item->name))) {
	    						$this->activescope_OntologyClass = $OntologyClass_item;
	    					}
	    				}
	    			}
	    		}
	    		
	    		foreach($this->usedOntologyClasses as $ocName) {
    				foreach ($this->levels as $level_item) {
    					if ($level_item == strtolower($ocName) || $level_item == $this->pluralize(strtolower($ocName))) {
    						$this->activescope_OntologyClass = $ocName;
    					}
    				}
				}
	    	}
    	}
    	
    	if (!isset($this->activescope_OntologyClass)) {
    		foreach ($this->levels as $level_item) {
    			if ($level_item == "admin") {
    				$this->activescope_admin = true;
    			}
    		}
    	}
    }
    function renderHTML() {
    	$this->init_forRendering();
    	
    	
    	$html = '';
		
		$html .= '<!DOCTYPE html>
<html>';
		
		$html .= $this->renderHTML_Head();

  		$html .= '
	<body>';
  		
  		$html .= $this->renderHTMLScripts_Analytics();
  		
  		$html .= $this->renderHTMLNavigation();
  			
  		if ($this->auth->isLogged()) {
  			if (isset($this->activescope_OntologyClass)) {
  				$html .= $this->renderHTML_ContentContainer();
   			} else {
  				
  				if (isset($this->activescope_Ontology)) {
  					if ($this->activescope_admin) {
  						$html .= $this->renderHTML_ContentContainer();
  					} else {
  						$html .= $this->renderHTML_ContentContainer();
  					}
  					
  				} else {
  					$html .= $this->renderHTML_ContentContainer();
  				}
  			}
  		} else {
  			if (isset($this->activescope_Ontology)) {
  				if (isset($this->activescope_OntologyClass)) {
  					$html .= $this->renderHTML_ContentContainer();
  				} else {
  					$html .= $this->renderHTML_ContentContainer();
  				}
  				
  			} else {
  				$html .= $this->renderHTML_ContentContainer();
  			}
  			
  				
  		}
  		
  		$html .= $this->renderHTML_Footer();
  		
  		$html .= $this->renderHTMLScripts();
  		

   		$html .= '
  		<script type="text/javascript">
  				$("#quicksearch").select2(select2QSConfig.get(odBase + "api/search/query", "Search...")).on("change", function (e) {
    			if (typeof window[e.added.ontologyClassName] !== "undefined") {
	  				model_object = window[e.added.ontologyClassName].findOrCreate({
						id: e.added.id,
						name: e.added.text
					});
  					
   					if (e.added.ontologyClassName === "OntologyClass") {
   						window.location.href = odBase + "km/ontologyclasses/#" + model_object.id;
   					} else if (e.added.ontologyClassName === "Ontology") {
   						window.location.href = odBase + "km/ontologies/#" + model_object.id;
   					}
   				
  					
    			} else {
  					model_object = window["Article"].findOrCreate({
						id: e.added.id,
						name: e.added.text
					});
  			
  					window.location.href = odBase + "wiki/articles/#" + model_object.get("name").replaceAll("/", "_");
  				}
  			
  			
  			
  			
}).on("select", function (e) {
    console.log("select");
  			
});;
  				</script>';
  		
  		
  		
  		$html .= '
  		<script type="text/javascript">$( document ).ready(function() {
				$(\'.arrow, [class^=arrow-]\').bootstrapArrows();
			});</script>
    			';
	    
  		$html .= '
	</body>
</html>';
		
		return $html;
    }
    function renderHTML_Head_Description() {
    	$desc = $this->getDescription();
    	
    	$html = '';
    	if ($desc) {
    		$html .= '
    		<meta name="description" content="' . $this->getDescription() . '">';
    	}
    	
    	return $html;
    }
    function renderHTML_ContentContainer() {
    	$html = '';
    	 
    	if (!isset($this->activescope_OntologyClass)) {
    	//if (!isset($this->activescope_OntologyClass) && !isset($this->activescope_Ontology)) {
    		$html .= '
		<div class="container">
			<br>
    		<div class="page-header" style="display: none;">
	  			<h1 id="title"></h1>
			</div>
	  		<div class="row-fluid">
	  			<div class="row">
	  				<div class="col-lg-12"></div>
  				</div>
			</div>
			<div class="row-fluid">
				<div class="controls" id="alerts"></div>
    		
	  			<div class="row">
	  				<div id="content" class="col-lg-12"></div>
  				</div>
			</div>
		</div>';
    	} else {
    		$html .= '
		<div class="container">
			<br>
    		<div class="page-header" style="display: none;">
	  			<h1 id="title"></h1>
			</div>
	  		<div class="row-fluid">
	  			<div class="row">
	  				<div class="col-lg-12">';
    				
    		if ($this->generated) {
    			$html .= '<div id="ontologyInformation" class="pull-right"></div>';
    		} else {
    			$html .= '<div id="concreteInformation" class="pull-right"></div>';
    		}
  						
  			$html .= '<br /><br />
  					</div>
	  			</div>
			</div>
			<div class="row-fluid">
				<div class="controls" id="alerts"></div>
    		
	  			<div class="row">
	  				<div id="content" class="col-lg-12"></div>
    			</div>
			</div>
		</div>';
    	}
    				
    	
    	 
    	return $html;
    }
    function renderHTML_Head() {
    	$html = '';
    	
    	$html .= '
	<head>
		<title>' . $this->getTitle() . '</title>';
    	
    	$html .= $this->renderHTML_Head_Description();
    	
    	$html .= '
		<meta name="viewport" content="width=device-width, initial-scale=1">';
    	$html .= '
		<link href="' . $this->getScriptSource('engulfing', 'engulfing-core/images/favicon.ico') . '" rel="shortcut icon" type="image/x-icon">
		<link href="' . $this->getScriptSource('engulfing', 'engulfing-core/images/favicon192.png') . '" rel="icon" sizes="192x192">';
    	
    	$html .= '
		<link href="' . $this->getScriptSource('engulfing', 'engulfing-core/vendor/engulfing.vendor.min.css') . '" rel="stylesheet">
		<link href="' . $this->getScriptSource('engulfing', 'engulfing-core/vendor/twbs/bootstrap/dist/css/ie10-viewport-bug-workaround.css') . '" rel="stylesheet">
		<link href="' . $this->getScriptSource('engulfing', 'engulfing-core/css/styles.css') . '" rel="stylesheet">';
    	
    	$html .= '
		<style>
			body { padding-top: 45px; }
		</style>
	</head>';
    	
    	return $html;
    }
    function renderHTML_Footer() {
    	$html = '';
    	 
    	$html .= '
		<footer class="footer">
			<div class="container">
				<p class="text-muted text-center">&copy; 2015-2016 Ontology Driven - Zurich, Switzerland - <a href="mailto:info@ontologydriven.com"><span class="glyphicon glyphicon-envelope" /></a></p>
			</div>
		</footer>';
  		
    	 
    	return $html;
    }
    function parse($page = 1) {
    	$this->processDOM($page);
    	 
    	unset($this->dom);
    	unset($this->xpath);
    }
    function convertFromHTML() {
    	//$this->body->setContainers();
    	$this->processHTMLElements($this->elements);
    	$this->processURL();
    	
   		$this->body->setType(true);
    	
   		$this->body->clean();
    }
    function simplify() {
    	$this->body->simplify();
    	
    	
    }
	function processDOM($page = null) {
		if (!$this->dom) {
			return;
		}
		
		$this->xpath = new DOMXPath($this->dom);
		
		
		$css = $this->getCSS();
		
		foreach($this->object_class_names as $item_object_class_name) {
			
			$template_class_object = new $item_object_class_name($page);
	
			$class_object_entity_nodes = $this->xpath->query($template_class_object->xpath_self);
	
	
			foreach($class_object_entity_nodes as $item_class_object_entity_node) {
				$class_object_entity = new $item_object_class_name($page);
				
				$class_object_entity->xpath = $this->xpath;
				$class_object_entity->node = $item_class_object_entity_node;
				$class_object_entity->css = $css;
				
				
				$class_object_entity->processDOMElement($page);
				
				unset($class_object_entity->xpath);
				unset($class_object_entity->node);
				unset($class_object_entity->object_class_names);
				
				array_push($this->elements, $class_object_entity);
			}
		}
	}
	function extractInformationByOntology($Ontology) {
		//echo "classes: " .count($Ontology->OntologyClasses) . "\n";
		$classes_entities = array();
	
		for ($oc=0; $oc<count($Ontology->OntologyClasses); $oc++) {
			//echo $Ontology->OntologyClasses[$oc]->name . "\n";
			
			unset($class_entity);
	
			if ($Ontology->OntologyClasses[$oc]->hasIdentifier() == true) {
				$identifier = $Ontology->OntologyClasses[$oc]->getIdentifier();
				 
				echo "idenifier: " . $Ontology->OntologyClasses[$oc]->name . "\n";
				 
				//echo count($identifier->Lexeme->Words) . "\n";
				 
				$value = null;
				for ($w=0; $w<count($identifier->Lexeme->Words); $w++) {
					$name = $identifier->Lexeme->Words[$w]->name;
					//echo $name . "\n";
	
					$amt_parent_entities = $this->countValuesByKey($name);
					if ($amt_parent_entities[0] > 0) {
						if (count($amt_parent_entities[0]) == 1) {
							$class_entity = new OntologyClassEntity();
							$class_entity->OntologyClass = $Ontology->OntologyClasses[$oc];
	
	
						}
						//echo count($amt_parent_entities[0]) . "\n";
						break;
					}
				}
			}
	
			for ($op=0; $op<count($Ontology->OntologyClasses[$oc]->OntologyClass_properties); $op++) {
				//echo $Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op]->name . "\n";
				
				$prop_entity[$op] = new OntologyPropertyEntity();
				$prop_entity[$op]->OntologyProperty = $Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op];
				 
				 
				//unset($prop_entity[$op]->OntologyProperty->OntologyClass);
				 
				$value = null;
				for ($w=0; $w<count($Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op]->Lexeme->Words); $w++) {
					$name = $Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op]->Lexeme->Words[$w]->name;
					
					$value = $this->searchValueByKey($name);
					
					if ($value != null) {
						echo "value: " . $value . "\n";
						if ($Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op]->isIdentifier) {
							$value = trim(str_replace(" ", "", $value));
						} else if ($Ontology->OntologyClasses[$oc]->OntologyClass_properties[$op]->isDate) {
							$value = date("Y-m-d H:i:s e O P T", strtotime($value));
						}
	
						$prop_entity[$op]->setValue($value);
							
						if (!isset($class_entity)) {
							$class_entity = $this->extractInformationByNamedEntity($Ontology, $prop_entity[$op]);
	
							if (isset($class_entity)) {
								for ($pp=0; $pp<count($class_entity->OntologyClass_propertyentities); $pp++) {
									//echo $class_entity->OntologyClass_propertyentities[$pp]->value . "\n";
								}
							} else {
								$class_entity = new OntologyClassEntity();
								$class_entity->OntologyClass = $Ontology->OntologyClasses[$oc];
									
								if (isset($class_entity->OntologyClass_propertyentities)) {
									array_push($class_entity->OntologyClass_propertyentities, $prop_entity[$op]);
								}
						
							}
						} else {
							if (isset($class_entity->OntologyClass_propertyentities)) {
								array_push($class_entity->OntologyClass_propertyentities, $prop_entity[$op]);
							}
						}
							
							
							
						break;
					}
				}
			}
	
			if (!isset($class_entity)) {
				for ($oce=0; $oce<count($Ontology->OntologyClasses[$oc]->entities); $oce++) {
					for ($ope=0; $ope<count($Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities); $ope++) {
						$class_entity = $this->extractInformationByNamedEntity($Ontology, $Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities[$ope]);
						$value = $this->searchValueByPropertyEntity($Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities[$ope]);
							
						if ($value != null) {
							$p_entity = clone $Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities[$ope];
	
							$p_entity->setValue(trim($value));
	
							if (!isset($class_entity)) {
								$class_entity = $this->extractInformationByNamedEntity($Ontology, $p_entity);
							}
	
						}

						if (isset($class_entity)) {
							//echo $class_entity->OntologyClass->name . "\n";
						}
					}
				}
			}
	
	
			if (isset($class_entity)) array_push($classes_entities, $class_entity);
	
		}
		 
		return $classes_entities;
		 
	}
	function getCSS() {
		$style_tags = array();
	
		$css = new Website_CSS();
	
		$xpath = '//style[3]';
	
		$xml_content = $this->xpath->query($xpath);
	
		if (isset($xml_content->item)) {
			$style_string = $xml_content->item(0)->textContent;
	
			$css->website_css_attributes = $this->parseCSS($style_string);
	
		} else {
			$style_string = "";
			foreach($xml_content as $xml_content_node) {
				$style_string .= $xml_content_node->nodeValue;
			}
				
			$css->website_css_attributes = $this->parseCSS($style_string);
		}
	
		return $css;
	}
    function parseCSS($css){
		$css_array = array();
		
		
        $file = $css;
        $element = explode('}', $css);

        foreach ($element as $element) {
            $a_name = explode('{', $element);
            $name = str_replace(".", "", trim($a_name[0]));

            $a_styles = explode(';', $element);

            $a_styles[0] = str_replace($name . '{', '', $a_styles[0]);

            for ($a=0; $a<count($a_styles); $a++) {
                if (trim($a_styles[$a]) != '') {
                    $a_key_value = explode(':', $a_styles[$a]);
                    $a_key_value[0] = trim($a_key_value[0]);
                    if (isset($a_key_value[1])) $a_key_value[1] = trim($a_key_value[1]);

                    if (!isset($css_array[$name][trim($a_key_value[0])])) {
                    	$css_array[$name][trim($a_key_value[0])] = trim($a_key_value[1]);
                    }
                   
                }
            }               
        }

       
        return $css_array;
    }
    function parseByOntology() {
		if (!$this->dom) return;
		
		$this->xpath = new DOMXPath($this->dom);
		
		
    	for ($i=0; $i<count($this->Ontology->Ontology_classs); $i++) {
    		for ($j=0; $j<count($this->Ontology->Ontology_classs[$i]->entities); $j++) {
    			
    			$string = $this->Ontology->Ontology_classs[$i]->entities[$j]->OntologyClassentity_name;
    			
    			$xpath = "//*[text() = '" . $string ."']";
				$xml_content = $this->xpath->query($xpath);
				
				$nodepath = $xml_content->item(1)->getNodePath();
				
				//print_r($nodepath);
				
    		}
    	}
    }
}
class Website_Document {
	var $website_css;
	var $website_pages;
	
	function __construct() {
	}
	function getCSSValueByKey($key) {
		//print_r($this->website_css->website_css_attributes);
		
		return str_replace("px", "", str_replace("pt", "", $this->website_css->website_css_attributes[$key]));
	}
}
class Website_CSS {
	var $website_css_attributes = array();
	
	function __construct() {
	}
	
}
class Website_Page {
	var $website_divs;
	var $website_sections;
	
	function __construct() {
	}
}
class Website_DIV {
	var $content;
	var $content_nodes = array();
	var $class;
	var $position;
	var $bottom;
	var $left;
	var $height;
	var $width;
	
	function __construct() {
	}
}
?>