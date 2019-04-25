<?php
trait WebsiteScript {
	var $desc;
	
	
	function __construct() {
	}
	function renderHTMLScripts() {
		$topdomain = $this->getTopDomain();
		
		$scope = $this->getScopeName();
		
		$html = '';

		$this->combineUnderscoreTemplates();
		
		$html .= $this->renderHTMLScripts_Base($scope, $topdomain);
		$html .= $this->renderHTMLScripts_AppBase($scope);
		$html .= $this->renderHTMLScripts_UserManagement();
		$html .= $this->renderHTMLScripts_Administration();
		
		$html .= $this->renderHTMLScripts_ModelsAndViews($scope);
		
		
		$html .= $this->renderHTMLScripts_Controller($scope);
		
	
		return $html;
	}
	function getScriptPathByScopeAndDirectory($scope, $directory) {
		if ($scope === "engulfing") {
			$scriptPath = str_ireplace("../engulfing/engulfing-core/", "", $directory);
		} else {
			$scriptPath = $directory;
		}
		
		$scriptPath = $directory;
		
		return $scriptPath;
	}
	function getRefererScopeName($scope) {
		if (strpos(getcwd(), "\\") !== false) {
			$explodes = explode("\\", getcwd());
		} else {
			$explodes = explode("/", getcwd());
		}
		
		if ($scope === null) {
			$scopeDepth = $this->getScopeDepth();
				
			if ($scopeDepth === 0) {
				$refererScopeName = end($explodes);
			} else {
				$refererScopeName = $explodes[count($explodes) - $scopeDepth];
			}
		} else {
			$refererScopeName = end($explodes);
		}
		
		$refererScopeName = str_ireplace(".com", "", $refererScopeName);
		
		return $refererScopeName;
	}
	function getScriptSource($scope, $scriptPath, $cwd = null) {
	    $scriptSource = "";
	    
	    if (!$cwd) $cwd = getcwd();
	    
	    $fio = new FileIO();
	    
	    $scriptPath = $this->getScriptPathByScopeAndDirectory($scope, $scriptPath);
	    
	    $relpath = "";
	    
	    if ($scope === "engulfing") {
	        if ($this->config['frontend']['web']['useAbsolutePaths']) {
	            $scriptSource = $relpath . $this->config['framework']['url'] . $scriptPath;
	        } else {
	            $relpath = $fio->translateAbsolutePathToRelative($cwd, $this->config['framework']['path']);
	            
	            $scriptSource = $relpath . $scriptPath;
	        }
	        
	    } else {
	        $relpath = $fio->translateAbsolutePathToRelative($cwd, $this->config['frontend']['path'], true, $scope);
	        
	        $scriptSource = $relpath . $scriptPath;
	    }
	    
	    //echo $this->plottKeyValues(array("scope" => $scope, "scriptPath" => $scriptPath, "cwd" => $cwd, "relpath" => $relpath, "scriptSource" => $scriptSource));
	    
	    if ($scope === "engulfing") {
	    	$scriptSource = str_ireplace("\\", "/", $this->config['framework']['base'] . $scriptPath);
	    } else {
	    	if ($this->config['frontend']['appBase']) {
	    		$scriptSource = $this->config['frontend']['appBase'] . str_ireplace("\\", "/", $scriptPath);
	    	} else {
	    		$scriptSource = "/" . str_ireplace("\\", "/", $scriptPath);
	    	}
	    	
	    }
	    
	    
	    //echo $scriptPath . "; " . $scriptSource . "\n";
	    return $scriptSource;
	}
	function combineUnderscoreTemplates() {
		$html = "";
	
		if (file_exists("../engulfing-core/templates")) {
			$files = array();
		
			array_push($files, new File (null, 'layouts/objectlist.html'));
			array_push($files, new File (null, 'layouts/entitylist.html'));
			array_push($files, new File (null, 'layouts/singleobject.html'));
			array_push($files, new File (null, 'layouts/ontologyinformation.html'));
			array_push($files, new File (null, 'layouts/concreteinformation.html'));
			
			array_push($files, new File (null, 'components/accordiongroup.html'));
			array_push($files, new File (null, 'components/accordionitem.html'));
			array_push($files, new File (null, 'components/backgrid.html'));
			array_push($files, new File (null, 'components/backgrid_actions.html'));
			
			array_push($files, new File (null, 'components/input_datepicker.html'));
			array_push($files, new File (null, 'components/input_textarea.html'));
			array_push($files, new File (null, 'components/input_text.html'));
			array_push($files, new File (null, 'components/input_tags.html'));
			array_push($files, new File (null, 'components/input_highcharts.html'));
			array_push($files, new File (null, 'components/input_locationmap.html'));
			array_push($files, new File (null, 'components/input_datepicker.html'));
			array_push($files, new File (null, 'components/input_checkbox.html'));
			
			array_push($files, new File (null, 'details_codegenerator.html'));
			array_push($files, new File (null, 'details_websitecodegenerator.html'));
			
			
			array_push($files, new File (null, 'layouts/intro.html'));
			
			if (file_exists("../../engulfing/engulfing-core/templates")) {
				$html = $this->combineTemplates($files, "../../engulfing/engulfing-core/templates/", "underscore.html");
				
				$fio = new FileIO();
				$fio->saveStringToFile($html, "..//engulfing/engulfing-core" . "/" . "underscore.html" );
			} else if (file_exists("/engulfing/engulfing-core")) {
				$html = $this->combineTemplates($files, "/engulfing/engulfing-core/", "underscore.html");
				
				$fio = new FileIO();
				$fio->saveStringToFile($html, "/engulfing/engulfing-core" . "/" . "underscore.html" );
			}
		}

	}
	function renderHTMLScripts_Base($scope, $topdomain) {
		$html = "";
		
		$files = array();
		
		
		/*array_push($files, new File (null, 'vendor/jquery/jquery-2.1.3.min.js'));
		array_push($files, new File (null, 'vendor/moment/moment.min.js'));
		array_push($files, new File (null, 'vendor/twbs/bootstrap/dist/js/bootstrap.min.js'));
		array_push($files, new File (null, 'vendor/twbs/bootstrap/dist/js/ie10-viewport-bug-workaround.js'));
		array_push($files, new File (null, 'vendor/bootstrap-arrows/js/bootstrap-arrows.min.js'));
		array_push($files, new File (null, 'vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'));
		array_push($files, new File (null, 'vendor/bootstrap-typeahead/typeahead.bundle.min.js'));
		array_push($files, new File (null, 'vendor/kartik-v/bootstrap-fileinput/js/fileinput.min.js'));
		array_push($files, new File (null, 'vendor/underscore/underscore-1.8.3.min.js'));
		array_push($files, new File (null, 'vendor/backbone/backbone-1.3.2.min.js'));
		array_push($files, new File (null, 'vendor/backbone/backbone-relational-0.10.0.js'));
		array_push($files, new File (null, 'vendor/backbone/backbone-crossdomain.js'));
		array_push($files, new File (null, 'vendor/backgrid/lib/backgrid.min.js'));
		array_push($files, new File (null, 'vendor/backbone/backgrid-paginator.js'));
		array_push($files, new File (null, 'vendor/backbone/backgrid-select-all.min.js'));
		array_push($files, new File (null, 'vendor/backbone/backgrid-filter.min.js'));
		array_push($files, new File (null, 'vendor/backbone/backbone-pageable.min.js'));
		array_push($files, new File (null, 'vendor/highcharts/highcharts.js'));
		array_push($files, new File (null, 'vendor/highcharts-multicolor/js/multicolor_series.min.js'));
		array_push($files, new File (null, 'vendor/select2/select2.min.js'));
		array_push($files, new File (null, 'vendor/various/js/cookie.js'));
		array_push($files, new File (null, 'vendor/various/js/scripts.js'));
		array_push($files, new File (null, 'vendor/various/js/inflection.js'));
		array_push($files, new File (null, 'vendor/various/js/pdfobject.min.js'));

		
		$js = $this->combineJS($files, "../engulfing/engulfing-core/", "engulfing.vendor.min.js");
		if (file_exists("../engulfing/engulfing-core/vendor")) {
			$fio = new FileIO();
			$fio->saveStringToFile($js, "../engulfing/engulfing-core/vendor" . "/" . "engulfing.vendor.min.js" );
		}*/
		
		
		$html .= '
		<script src="' . $this->getScriptSource('engulfing', 'engulfing-core/vendor/engulfing.vendor.min.js') . '"></script>';
		
		
		$html .= $this->renderHTMLScriptByDirectory(
				"engulfing",
				"engulfing-core/js",
				"engulfing.min.js",
				array("core/utils.js", "models/model_master.js", "models/model_user.js", "models/model_content.js", "main.js", "views/base.js", "views/singleobject.js", "views/components/input.js", "views/components/button.js"),
				array("app.min.js", "ontologydriven.admin.min.js", "ontologydriven.wiki.min.js", "ontologydriven.edi.min.js", "ontologydriven.codegeneration.min.js", "ontologydriven.nlp.min.js", "ontologydriven.km.min.js", "engulfing.min.js"),
				null)
		;
		
		
		if (!in_array($scope, array("kokos", "extraction", "knifecatcher", "neuronalysis")) && !in_array($topdomain, array("kokos", "extraction", "knifecatcher", "neuronalysis"))) {
			foreach (array('km', 'nlp', 'codegeneration', 'edi', 'wiki', 'admin') as $scopeItem) {
				$html .= $this->renderHTMLScriptByDirectory(
						$scopeItem,
						"js",
						"ontologydriven." . $scopeItem . ".min.js",
						null,
						array("app.min.js","main", "config", "utils")
						);
			}
		}
		
		return $html;
	}
	function arrayContains($array, $string)
	{
		if ($array === null) return false;
		
		$exploded = explode("\\", $string);
		 
		$filename = end($exploded);
		foreach ($array as $name) {
			if (stripos($name, $filename) !== FALSE) {
				return true;
			}
		}
		
		foreach ($array as $name) {
			if (stripos($filename, $name) !== FALSE) {
				return true;
			}
		}
		
		return false;
	}
	function compileHTMLScriptsIfNecessary($scope, $directory, $target, $ordering = null, $exclusions = null, $targetDirectory = null) {
		if (!$targetDirectory) $targetDirectory = $directory;
		
		$path = $this->getPathForRecursiveDirectoryIterator($scope, $targetDirectory);
		
		if ($this->enforceRecompile || !file_exists($path . "/" . $target)) {
			$js = $this->combineJSFromDirectory($path, $ordering, $exclusions);
			
			if (file_exists($path)) {
				$fio = new FileIO();
				$fio->saveStringToFile($js, $path . "/" . $target );
			}
		}
	}
	function getDirectoryMaxFileTime($directory, $exclusions) {
		$maxfiletime = 0;
		
		if (file_exists($directory)) {
			$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $directory ) );
			foreach ( $directory_iterator as $filename => $path_object ) {
				if(is_file($filename) && stripos($filename, ".json") === false && !$this->arrayContains($exclusions, $filename)) {
					$filetime = filemtime ($filename);
					
					if ($filetime > $maxfiletime) $maxfiletime = $filetime;
				}
			}
		}
		
		return $maxfiletime;
	}
	function renderHTMLScriptByDirectory($scope, $directory, $target, $ordering = null, $exclusions = null, $targetDirectory = null) {
		$html = "";
		$this->compileHTMLScriptsIfNecessary($scope, $directory, $target, $ordering, $exclusions, $targetDirectory);
		
		if ($this->isLocalRequest()) {
			if ($this->debug) {
				$html .= $this->listJSFromDirectory($scope, $directory, $ordering, $exclusions, $targetDirectory);
			} else {
				$html .= '
		<script src="' . $this->getScriptSource($scope, $directory . "/" . $target, $targetDirectory) . '"></script>';
			}
		} else {
			$html .= '
		<script src="' . $this->getScriptSource($scope, $directory . "/" . $target, $targetDirectory) . '"></script>';
		}
		
		return $html;
	}
	function getPathForRecursiveDirectoryIterator($scope, $scriptSource) {
		$cwd = null;
		
		if (!$cwd) $cwd = getcwd();
		
		$fio = new FileIO();
		
		if ($scope === "engulfing") {
			if ($this->config['frontend']['web']['useAbsolutePaths']) {
				$path = $this->config['framework']['path'] . $scriptSource;
			} else {
				$relpath = $fio->translateAbsolutePathToRelative($cwd, $this->config['framework']['path']);
				
				$path = $relpath . $scriptSource;
			}
		} else {
			if ($scope) {
				if ($this->config['frontend']['web']['useAbsolutePaths']) {
					$path = $this->config['frontend']['path'] . $scriptSource;
				} else {
					$relpath = $fio->translateAbsolutePathToRelative($cwd, $this->config['frontend']['path']);
					
					$path = $relpath . $scriptSource;
				}
			} else {
				if (file_exists ( "../" . $scriptSource )) {
					$path = "../" . $scriptSource;
				} else {
					if (file_exists ( $scriptSource )) {
						$path = $scriptSource;
					}
				}
			}
		}
		
		return $path;
	}
	function listJSFromDirectory($scope, $directory, $ordering = null, $exclusions = null, $targetDirectory = null) {
		$html = "";
		
		if ($ordering !== null) {
			foreach($ordering as $orderedfile) {
				$html .= '
		<script src="' . $this->getScriptSource($scope, $directory . "/" . $orderedfile) . '"></script>';
			}
		}
		
		if ($ordering !== null) $exclusions = array_merge($exclusions, $ordering);
		
		$scriptSource = $this->getScriptPathByScopeAndDirectory($scope, $directory);
	
		$pathForIterator = $this->getPathForRecursiveDirectoryIterator($scope, $scriptSource);
		
		$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $pathForIterator ) );
		foreach ( $directory_iterator as $filename => $path_object ) {
		    if(is_file($filename) && stripos($filename, ".json") === false && !$this->arrayContains($exclusions, $filename)) {
		        $exploded = explode($scope . "/", $filename);
				$filename = end($exploded);
				
				$html .= '
		<script src="' . $this->getScriptSource($scope, $filename) . '"></script>';
			}
		}
		
		
		return $html;
	}
	function combineJSFromDirectory($directory, $ordering = null, $exclusions = null) {
		$js = "";
		
		if ($ordering !== null) {
			foreach($ordering as $orderedfile) {
				if (file_exists($directory . "/" . $orderedfile)) {
					$js .= "\n" . file_get_contents ( $directory . "/" . $orderedfile );
				}
			}
		}
		
		if ($ordering !== null) $exclusions = array_merge($exclusions, $ordering);
		
		if (file_exists($directory)) {
			$directory_iterator = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $directory ) );
			foreach ( $directory_iterator as $filename => $path_object ) {
				if(is_file($filename) && stripos($filename, ".json") === false && !$this->arrayContains($exclusions, $filename)) {
					$js .= "\n" . file_get_contents ( $filename );
				}
			}
		}
		
		return $js;
	}
	function combineTemplates($files, $directory, $target) {
		$js = "";
		foreach($files as $file) {
	
			$filepath = $directory . $file->path;
			if (file_exists ( $filepath )) {
				$js .= '<script type="text/template" id="' . $file->path . '">
						';
				
				$js .= "\n" . "\n" . file_get_contents ( $filepath );
				
				$js .= '</script>';
			} else {
				echo "file not found - " . $filepath . "\n";
			}
			//$code = file_get_contents ( getcwd () . '/data/codegeneration/code/database/' . strtolower ( $this->Ontology->name ) . "/code_database_tables.sql" );
	
		}
	
		return $js;
	}
	function combineJS($files, $directory, $target) {
		$js = "";
		foreach($files as $file) {
	
			$filepath = $directory . $file->path;
			if (file_exists ( $filepath )) {
				$js .= "\n" . "\n" . file_get_contents ( $filepath );
			} else {
				echo "file not found - " . $filepath . "\n";
			}
		}
	
		return $js;
	}
	function renderHTMLScripts_UserManagement($scope = null) {
		$html = "";
		if ($this->activescope_usermanagement) {
			$html .= '<script src="' . $this->getScriptSource($scope, 'js/views/detail_register.js') . '"></script>
    			';
			$html .= '<script src="' . $this->getScriptSource($scope, 'js/views/detail_recovery.js') . '"></script>
    			';
			$html .= '<script src="' . $this->getScriptSource($scope, 'js/views/detail_passwordreset.js') . '"></script>
    			';
			$html .= '<script src="' . $this->getScriptSource($scope, 'js/models/model_recovery.js') . '"></script>
    			';
			
			if (file_exists('../js/main_register.js') && $this->getScopeObjectName() == "register") {
				$html .= '<script src="' . $this->getScriptSource($scope, 'js/main_register.js') . '"></script>
    				';
			}
			if (file_exists('../js/main_user.js') && $this->getScopeObjectName() == "users") {
				$html .= '<script src="' . $this->getScriptSource($scope, 'js/main_user.js') . '"></script>
    				';
			}
			if (file_exists('../js/main_role.js') && $this->getScopeObjectName() == "roles") {
				$html .= '<script src="' . $this->getScriptSource($scope, 'js/main_role.js') . '"></script>
    				';
			}
			if (file_exists('../js/main_recovery.js') && $this->getScopeObjectName() == "recovery") {
				$html .= '<script src="' . $this->getScriptSource($scope, 'js/main_recovery.js') . '"></script>
    				';
			}
		}
		
		return $html;
	}
	function renderHTMLScripts_AppVariables() {
	    $fio = new FileIO();
	    
	    $html = "";
	    
	    
	    $html = '
		<script>
var activateSessionStorage = false;
var referrer = document.referrer;
        ';

	    if (isset($this->config['frontend']['accessRestrictions'])) {
	        $html .= '
    var objects = ' . str_replace('"', '', json_encode($this->config['frontend']['accessRestrictions']['objects'], JSON_PRETTY_PRINT)) . ';
        ';
	        
	    }
	    
	    $html .= '
    var activateSessionStorage = false;
    var odBase = ""
    var engulfingBase = "' . $this->config['framework']['base'] . '"
    var siteAdmin = "' . $this->config['frontend']['siteAdmin'] . '"
    var appHost = "' . $this->config['frontend']['appHost'] . '"
    var kmapiHost = "' . $this->config['frontend']['kmapiHost'] . '"
    var apiHost = "' . $this->config['frontend']['apiHost'] . '";';

	    $html .= '
    var sitemap = ' . json_encode($this->config['frontend']['sitemap'], JSON_PRETTY_PRINT). '';
	    

        $html .= '
</script>';

        
	    return $html;
	}
	function renderHTMLScripts_Administration() {
		$html = "";
		if ($this->activescope_monitoring) {
			if (file_exists('../js/views/detail_monitoring.js')) {
				$html .= '<script src="' . $this->getScriptSource(null, 'js/views/detail_monitoring.js') . '"></script>
    				';
			}
			if (file_exists('../js/models/model_monitoring.js')) {
				$html .= '<script src="' . $this->getScriptSource(null, 'js/models/model_monitoring.js') . '"></script>
    				';
			}
			if (file_exists('../js/main_monitoring.js') && $this->getScopeObjectName() == "monitoring") {
				$html .= '<script src="' . $this->getScriptSource(null, 'js/main_monitoring.js') . '"></script>
    				';
			}
		}
	
		return $html;
	}
	function renderHTMLScripts_Analytics() {
		$html = "";
		
		if (!$this->isLocalRequest()) {
			$html = "
		<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			
  ga('create', 'UA-58793893-1', 'auto');
  ga('send', 'pageview');</script>";
		}
		
		return $html;
	}
	function renderHTMLScripts_Controller($scope) {
		$scopeDepth = $this->getScopeDepth();
		$objectName = $this->getRefererScopeName($scope);
		
		$html = "";
		
		if (isset($this->activescope_OntologyClass)) {
			if (isset($this->activescope_OntologyClass->name)) {
				$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . strtolower($this->activescope_OntologyClass->name) . '.js') . '"></script>
    					';
			} else {
				$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . strtolower($this->activescope_OntologyClass) . '.js') . '"></script>
    					';
			}
				
		} else {
			if ($this->generated) {
				if ($scopeDepth == 1) {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_intro.js') . '"></script>
			 		';
				} else if ($scopeDepth == 2) {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . $this->singularize(strtolower($objectName)) . '.js') . '"></script>
			 		';
				}
			}
			
			if ($this->siteMapDefinition) {
			    $onPage = false;
				
				$sitemap = json_decode($this->siteMapDefinition);
				
				foreach($sitemap->Pages[0]->Pages as $page_item) {
				    if (strpos($this->website_url, strtolower(str_replace(' ', '', $page_item->name))) !== false  ) {
//						$html .= '
//		<script src="/js/main_' . $this->singularize(strtolower($page_item->name)) . '.js"></script>
//			 ';
				    	if (file_exists(str_ireplace($this->config['frontend']['appBase'], "", $this->config['frontend']['path']) . "/" . $this->getScriptSource($scope, 'js/main_' . $this->singularize(strtolower(str_replace(' ', '', $page_item->name))) . '.js'))) {
				            $html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . $this->singularize(strtolower(str_replace(' ', '', $page_item->name))) . '.js') . '"></script>
			 ';
				        } else {
				        	$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . strtolower(str_replace(' ', '', $page_item->name)) . '.js') . '"></script>
			 ';
				        }
				            
				        
						$onPage = true;
					}
					if(isset($page_item->Pages)) {
						foreach($page_item->Pages as $subpage_item) {
							if (strpos($this->website_url, strtolower($subpage_item->name)) !== false  ) {
								$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_' . $this->singularize(strtolower($subpage_item->name)) . '.js') . '"></script>
			 ';
								$onPage = true;
							}
							
							
						}
					}
					
				}
				
				
				$scopeObjectName = $this->getScopeObjectName();
				
				if ($scopeObjectName === "admin") {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_admin.js') . '"></script>
					';
					
					$onPage = true;
				}
				
				if (!$onPage) {
					$html .= '
		<script src="js/main_intro.js"></script>
			 ';
				}
				
				
			} else {
				//TODO can be improved by depending fully on siteMap definition.
				$scopeObjectName = $this->getScopeObjectName();
				
				if ($scopeObjectName === "editor") {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_editor.js') . '"></script>
			 		';
				} else if ($scopeObjectName === "documents") {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_document.js') . '"></script>
			 		';
				} else if ($scopeObjectName === "monitoring") {
					
				} else if ($scopeObjectName === "admin") {
					$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_admin.js') . '"></script>
					';
				} else {
					if (!$this->activescope_usermanagement) {
						$html .= '
		<script src="' . $this->getScriptSource($scope, 'js/main_intro.js') . '"></script>
			 		';
					}
				}
			}
			
		}
		
		return $html;
	}
	function renderHTMLScripts_ModelsAndViews($scope) {
		$html = "";
		
		$html .= $this->renderHTMLScriptByDirectory(
				$scope,
				"js",
				"app.min.js",
				null,
				array("app.min.js", "main", "config", "ontologydriven.nlp.min.js", "ontologydriven.wiki.min.js", "ontologydriven.admin.min.js", "ontologydriven.codegeneration.min.js", "ontologydriven.km.min.js", "ontologydriven.edi.min.js")
		);
		
		return $html;
	}
	function renderHTMLScripts_Models() {
		$km = new KM();
		
		$html = "";
		
		if (isset($this->activescope_OntologyClass)) {
		    foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
				if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js')) {
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js"></script>
    							';
				}
				if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js')) {
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js"></script>
    							';
				}
				if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js')) {
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
    							';
				}
			}
			
			foreach($this->usedOntologyClasses as $ocName) {
				if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js')) {
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js"></script>
    							';
				}
			}
					
		} else {
			if ($this->siteMapDefinition) {
			    $sitemap = json_decode($this->siteMapDefinition);
			
				foreach($sitemap->Pages[0]->Pages as $page_item) {
					if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . $this->singularize(strtolower($page_item->name)) . '.js')) {
						$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . $this->singularize(strtolower($page_item->name)) . '.js"></script>
    							';
					}
				}
				foreach($sitemap->ontologies as $ontology_item) {
					$ontology = $km->getOntologyByName($ontology_item->name);
					
					$ontologyClasses = $ontology->getOntologyClasses();
					foreach($ontologyClasses as $ontologyClass_item) {
						if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($ontologyClass_item->name) . '.js')) {
							$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($ontologyClass_item->name) . '.js"></script>
    							';
						}
					}
				}
			}
				
		}
		
		return $html;
	}
	function archive() {
		if ($auth->isLogged()) {
			if (isset($this->activescope_Ontology)) {
				if(class_exists("Administration_" . $this->activescope_Ontology->name) && $this->activescope_admin) {
					$admin_class = "Administration_" . $this->activescope_Ontology->name;
					$admin = new $admin_class;
		
					foreach($admin->sections as $section_item) {
						$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($section_item) . '.js"></script>
    						';
					}
		
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_admin.js"></script>
    						';
		
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/main_admin.js"></script>
    						';
				}
					
				if (isset($this->activescope_OntologyClass)) {
					if (property_exists($this->activescope_OntologyClass->name, "id")) {
						foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
    							';
							}
						}
		
						foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
							if (file_exists($this->desc['ontology']['js'] . 'js/views/' . strtolower($oclass->name) . 'list.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/' . strtolower($oclass->name) . 'list.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . '.js"></script>
    							';
							}
							if (file_exists( $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . 'entity.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . 'entity.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/views/detail_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
   								';
							}
						}
							
						//if (file_exists($this->desc['app']['js'] . 'js/views/detail_' . strtolower($this->activescope_OntologyClass->name) . '.js')) {
						//	$html .= '<script src="' . $this->desc['app']['js'] . 'js/views/detail_' . strtolower($this->activescope_OntologyClass->name) . '.js"></script>
						//	';
						//}
						$html .= '<script src="' . $this->desc['app']['js'] . 'js/main_' . strtolower($this->activescope_OntologyClass->name) . '.js"></script>
    					';
					} else {
						foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js"></script>
	   							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
    							';
							}
						}
							
						if (file_exists($this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($this->activescope_OntologyClass->name) . '.js')) {
							$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($this->activescope_OntologyClass->name) . '.js"></script>
    						';
						}
		
						/*$html .= '<script src="' . $this->desc['app']['js'] . 'js/main_' . strtolower($this->activescope_OntologyClass->name) . '.js"></script>
						 ';*/
					}
		
				} else {
		
					foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
							
						if (property_exists($oclass->name, "id")) {
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . '.js"></script>
    						';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower($oclass->name) . 'entity.js"></script>
    							';
							}
							if (file_exists($this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js')) {
								$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/models/model_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
    							';
							}
						}
					}
		
					foreach ($this->activescope_Ontology->OntologyClasses as $oclass) {
						if (property_exists($oclass->name, "id")) {
							//if (file_exists('js/views/detail_' . strtolower($oclass->name) . '.js')) {
							//}
							/*$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . '.js"></script>
							 ';
		
							 $html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower($oclass->name) . 'entity.js"></script>
							 ';
							 $html .= '<script src="' . $this->desc['ontology']['js'] . 'js/views/detail_' . strtolower(str_replace("Relation", "RelationEntity", $oclass->name)) . '.js"></script>
							 ';*/
						}
					}
		
					/*$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/main_intro.js"></script>
					 ';*/
				}
		
		
		
			} else {
				if (!$this->activescope_usermanagement) {
					$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/main_intro.js"></script>
    				';
				}
					
			}
		} else {
			if (!$this->activescope_usermanagement) {
				$html .= '<script src="' . $this->desc['ontology']['js'] . 'js/main_intro.js"></script>
    				';
			}
		
		}
	}
}
?>