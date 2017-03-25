<?php
trait WebsiteNavigation {
	function renderHTMLNavigation_Search() {
		$html = '
		<div class="col-sm-3 col-md-3 pull-right" style="padding-top: 10px; padding-right: 0px; text-align: right;">
			<div id="quicksearch"></div>
		</div>';
		
		return $html;
	}
	function renderHTMLNavigation() {
		$topdomain = $this->getTopDomain();
		 
		$html = '
		<nav class="navbar navbar-default navbar-fixed-top" style="padding-bottom: 5px;">
			<div class="container">';

		$html .= $this->renderHTMLNavigation_Header();
		$html .= $this->renderHTMLNavigation_Bar();
		$html .= $this->renderHTMLNavigation_Sub();
		
		$html .= '
			</div>
		</nav>';
		
		return $html;
	}
	function renderHTMLNavigation_Header() {
		$html = "";
	
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		
		$home_url = $this->getHomeUrl();
		
		$html .= '
				<div class="navbar-header">
					';
		 
		$html .= '
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigationbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					';
		
		
		
		$html .= '
					<a class="navbar-brand" href="' . $home_url . '">' . $this->getHomeTitle() . '</a>';
	
	
		$html .= '
				</div>';
	
		return $html;
	}
	function renderHTMLNavigation_Bar() {
		$home_url = $this->getHomeUrl();
		
		$html = "";
		
		$html .= '
				<div id="navigationbar" class="collapse navbar-collapse" style="border-bottom: 1px solid; border-color: #e7e7e7;">';
		
		$html .= '
					<ul class="nav navbar-nav">';
		
		$html .= $this->renderHTMLNavigation_Menu();

		$html .= '
					</ul>';

		$html .= '
					<ul class="nav navbar-nav navbar-right">';
		
		if($this->auth->isLogged() && ($this->generated == true || $_COOKIE['UserRoleID'] == 1)) {
			$html .= $this->renderHTMLNavigation_Administration();
		}

		$html .= $this->renderHTMLNavigation_Authentication();

		if ($this->isShop) {
			$html .= '
					<li><a href="' . $home_url . 'ecommerce/basket' . '">Basket</a></li>';
		}
		
		
		$html .= '
					</ul>';

		
		
		$html .= '
				</div>';
		
		return $html;
	}
	function renderHTMLNavigation_Sub() {
		$topdomain = $this->getTopDomain();
	
		$html = "";
		
		
		$html .= '
				<div id="navbar_sub" class="navbar-collapse collapse" style="">';
	
		$html .= '
					<ul class="nav navbar-nav">';
		
		$html .= $this->renderHTMLNavigation_Menu_Classes();
		
		$html .= '
					</ul>';
		
		
		$html .= $this->renderHTMLNavigation_Search();
		
		
		$html .= '
				</div>';
	
		return $html;
	}
	function renderHTMLNavigation_Menu_Classes() {
		$html = "";
		
		$scope = $this->getScopeName();
		
		if ($this->siteMapDefinition) {
		} else {
			if (isset($this->activescope_Ontology)) {
				//if($this->auth->isLogged()) {
					foreach($this->activescope_Ontology->OntologyClasses as $OntologyClass_item) {
						if (property_exists($OntologyClass_item->name, "id")) {
							if (file_exists(strtolower("../" . $this->activescope_Ontology->name) . '/' . $this->pluralize(strtolower($OntologyClass_item->name)))) {
									$html .= '
						<li><a href="' . $this->getScriptSource($scope, $this->pluralize(strtolower($OntologyClass_item->name)) . '') . '">' . $this->pluralize($OntologyClass_item->name) . '</a></li>';
							} else {
								if (file_exists(strtolower("../../" . $this->activescope_Ontology->name) . '/' . $this->pluralize(strtolower($OntologyClass_item->name)))) {
									$html .= '
						<li><a href="' . $this->getScriptSource($scope, $this->pluralize(strtolower($OntologyClass_item->name)) . '') . '">' . $this->pluralize($OntologyClass_item->name) . '</a></li>';
								} else {
									//echo "asedrf\n";
								}
							}
						} else {
							if (file_exists(strtolower("../" . $this->activescope_Ontology->name) . '/' . strtolower($OntologyClass_item->name))) {
								$html .= '
						<li><a href="' . $this->getScriptSource($scope, $this->pluralize(strtolower($OntologyClass_item->name)) . '') . '">' . $this->pluralize($OntologyClass_item->name) . '</a></li>';
							} else {
								if (file_exists(strtolower("../../" . $this->activescope_Ontology->name) . '/' . strtolower($OntologyClass_item->name))) {
									$html .= '
						<li><a href="' . $this->getScriptSource($scope, $this->pluralize(strtolower($OntologyClass_item->name)) . '') . '">' . $this->pluralize($OntologyClass_item->name) . '</a></li>';
								}
							}
						}
					}
					
					foreach($this->usedOntologyClasses as $ocName) {
						$html .= '
						<li><a href="' . $this->getScriptSource($scope, $this->pluralize(strtolower($ocName)) . '') . '">' . $ocName . '</a></li>';
						
					} 
				
			}
		}
		
		
		return $html;
	}
	function renderHTMLNavigation_Menu() {
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		 
		$home_url = $this->getHomeUrl();
			
		$html = "";
	
		if ($this->siteMapDefinition) {
			$sitemap = json_decode($this->siteMapDefinition);
			
			foreach($sitemap->Pages[0]->Pages as $page_item) {
				
				if (isset($page_item->Pages)) {
					$html .= '
						<li class="dropdown active">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' . $page_item->name . '<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">';
					
					foreach($page_item->Pages as $subpage_item) {
						if (isset($subpage_item->urlPartName)) {
							$html .= '<li><a href="' . $home_url . strtolower($subpage_item->urlPartName) . '">' . $subpage_item->name . '</a></li>
						';
						} else {
							$html .= '<li><a href="' . $home_url . strtolower($subpage_item->name) . '">' . $subpage_item->name . '</a></li>
						';
						}
					}
					
						
					$html .= '
							</ul>
						</li>';
				} else {
					$html .= '<li><a href="' . $home_url . strtolower($page_item->name) . '">' . $page_item->name . '</a></li>
						';
				}
				
			}
		} else {
			if (isset($this->activescope_Ontology)) {
				if($this->auth->isLogged()) {
					$html .= '
						<li class="dropdown active">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' . $this->activescope_Ontology->name . '<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">';
					
					if(isset($this->Ontologies)) {
						foreach($this->Ontologies as $Ontology_item) {
							if ($this->isAllowed($Ontology_item)) {
								if (isset($Ontology_item) && $Ontology_item->isFinal) {
									if ($Ontology_item->name !== $this->activescope_Ontology->name) {
										$html .= '
									<li><a href="' . $home_url . strtolower($Ontology_item->name) . '/">' . $Ontology_item->name . '</a></li>';
									}
								}
							}
						}
					}
					
					$html .= '
							</ul>
						</li>';
				} else {
					$html .= '
						<li class="dropdown active">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' . $this->activescope_Ontology->name . '<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">';
					
					foreach($this->Ontologies as $Ontology_item) {
						if ($this->isAllowed($Ontology_item)) {
							if (isset($Ontology_item) && $Ontology_item->isFinal) {
								if ($Ontology_item->name !== $this->activescope_Ontology->name) {
									$html .= '
									<li><a href="' . $home_url . strtolower($Ontology_item->name) . '/">' . $Ontology_item->name . '</a></li>';
								}
							}
						}
					}
					
					$html .= '
							</ul>
						</li>';
				}
			} else {
				if($this->auth->isLogged()) {
					if (isset($this->Ontologies)) {
						foreach($this->Ontologies as $Ontology_item) {
							if ($this->isAllowed($Ontology_item)) {
								if (isset($Ontology_item) && $Ontology_item->isFinal) {
									$html .= '<li><a href="' . $home_url . strtolower($Ontology_item->name) . '/">' . $Ontology_item->name . '</a></li>';
								}
							}
						}
					}
				} else {
					if(isset($this->Ontologies)) {
						foreach($this->Ontologies as $Ontology_item) {
							if ($this->isAllowed($Ontology_item)) {
								if (isset($Ontology_item) && $Ontology_item->isFinal) {
									$html .= '<li><a href="' . $home_url . strtolower($Ontology_item->name) . '/">' . $Ontology_item->name . '</a></li>';
								}
							}
						}
					}
				}
			}
		}
		
    	return $html;
	}
	function renderHTMLNavigation_Administration() {
		$topdomain = $this->getTopDomain();
		
		$html = "";
		
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
		
		if ($this->isLocalRequest()) {
			$home_url = "";
			if ($this->generated) {
				$home_url = "http://localhost." . $topdomain . "/";
			} else {
				$home_url = "http://localhost." . $topdomain . "/";
			}
		} else {
			$home_url = "";
			if ($this->generated) {
				$home_url = "http://www." . $topdomain . ".com/";
			} else {
				$home_url = "http://www." . $topdomain . ".com/";
			}
		}
		
		if($this->generated == true) {
			$html .= '
				<li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Administration<span class="caret"></span></a>
              <ul class="dropdown-menu" role="menu">';
			if (isset($this->activescope_Ontology->name)) {
				foreach($this->Ontologies as $Ontology_item) {
					if (strtolower($this->activescope_Ontology->name) == strtolower($Ontology_item->name)) {
						foreach($Ontology_item->OntologyClasses as $OntologyClass_item) {
							$html .= '<li><a href="' . $home_url . $this->pluralize(strtolower($OntologyClass_item->name)) . '">' . $OntologyClass_item->name . '</a></li>';
						}
					}
				}
			} else {
				foreach($this->Ontologies as $Ontology_item) {
					foreach($Ontology_item->OntologyClasses as $OntologyClass_item) {
						$html .= '<li><a href="' . $home_url . $this->pluralize(strtolower($OntologyClass_item->name)) . '">' . $OntologyClass_item->name . '</a></li>';
					}
				}
			}
			
				
				
			$html .= '
		      </ul>
            </li>';
		} else {
			$scopeName = $this->getScopeName();
			
			
			if($this->auth->isLogged() && ($_COOKIE['UserRoleID'] == 1)) {
				$html .= '
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Administration<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">';
				
				if (isset($scopeName) && in_array($scopeName, array("extraction"))) {
					$html .= '
								<li><a href="' . $home_url . strtolower($scopeName) . '/monitoring' . '">' . 'Monitoring' . '</a></li>
								<li role="separator" class="divider"></li>';
				}

				$html .= '
								<li><a href="' . $home_url . 'admin/housekeeping' . '">' . 'Housekeeping' . '</a></li>
								<li><a href="' . $home_url . 'admin/development' . '">' . 'Development' . '</a></li>
								<li role="separator" class="divider"></li>
								';
				
				$html .= '
								<li class="menu-item dropdown dropdown-submenu">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">User Management</a>
									<ul class="dropdown-menu">
						';
				if (isset($this->activescope_usermanagement)) {
								$html .= '
										<li><a href="' . $home_url . 'usermanagement/' . 'users' . '">' . 'Users' . '</a></li>
										<li><a href="' . $home_url . 'usermanagement/' . 'roles' . '">' . 'Roles' . '</a></li>';
				}
					
				$html .= '
									</ul>
								</li>';
				
				$html .= '
							</ul>
						</li>';
			}
		}

		
		return $html;
	}
	function renderHTMLNavigation_Authentication($auth_url = null) {
		$topdomain = $this->getTopDomain();
		
		$html = "";
	
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
		
		if (!$auth_url) {
			if ($this->isLocalRequest()) {
				$auth_url = "";
				if ($this->generated) {
					$auth_url = "http://localhost.generated/api/authentication";
				} else {
					$auth_url = "http://localhost.ontologydriven/api/authentication";
				}
			} else {
				$auth_url = "";
				if ($this->generated) {
					$auth_url = "http://www.ontologydriven.com/api/authentication";
				} else {
					$auth_url = "http://www.ontologydriven.com/api/authentication";
				}
			}
		}
		
		
		if ($this->isLocalRequest()) {
			$home_url = "";
			if ($this->generated) {
				$home_url = "http://localhost." . $topdomain . "/";
			} else {
				$home_url = "http://localhost." . $topdomain . "/";
			}
		} else {
			$home_url = "";
			if ($this->generated) {
				$home_url = "http://www." . $topdomain . ".com/";
			} else {
				$home_url = "http://www." . $topdomain . ".com/";
			}
		}
		
		if(!$this->auth->isLogged()) {
			$html .= '
						<li><a href="' . $home_url . 'usermanagement/register">Sign Up</a></li>';
			$html .= '
						<li class="divider-vertical"></li>
						<li class="dropdown">
							<a class="dropdown-toggle" href="#" data-toggle="dropdown">Sign In <strong class="caret"></strong></a>
							<div class="dropdown-menu" style="padding: 15px; padding-bottom: 10px;">
								<form method="post" action="' . $auth_url . '/login" accept-charset="UTF-8">
									<input style="margin-bottom: 15px;" type="text" placeholder="UserName" id="loginName" name="LoginUserName" autocapitalize="none" /><br>
									<input style="margin-bottom: 15px;" type="password" placeholder="Password" id="loginPassword" name="LoginUserPassword" /><br>
									<input type="hidden" id="referer" name="refererURL" />
									<input style="float: left; margin-right: 10px;" type="checkbox" name="user_remember_me" id="user_remember_me" value="1" />
									<label class="string optional" for="user_remember_me">Remember me</label><br>
									<input class="btn btn-primary btn-block" type="submit" id="sign-in" value="Sign In" />
								</form>
							</div>
						</li>';
		} else {
			$html .= '
						<li class="divider-vertical"></li>
						<li class="dropdown">
							<a class="dropdown-toggle" href="#" data-toggle="dropdown">My Account<strong class="caret"></strong></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="' . $home_url . 'usermanagement/users/#' . $_COOKIE['UserID'] . '">Profile</a></li>
							</ul>
						</li>
						<li><a href="' . $auth_url . '/logout" id="signout">Sign Out</a></li>';
		}
	
		return $html;
	}
}
?>