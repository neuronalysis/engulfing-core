<?php
trait AccessControl {
	
	function __construct() {
	}
	function isPermitted($class_name) {
		$auth = Authentication::getInstance();
		
		if (stripos($class_name, "user") !== false && isset($this->recoveryToken)) {
		} else if (stripos($class_name, "request") !== false) {
		} else {
			if (!$UserID = $auth->isLogged()) {
				if (stripos($class_name, "user") !== false && $this->isNew()) {
				} else {
					if (!$_SERVER['PHP_AUTH_USER']) {
						return false;
					}
				}
		
			}
		}
		
		return true;
	}
}
?>