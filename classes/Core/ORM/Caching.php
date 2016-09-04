<?php
trait Caching {
	function __construct() {
	}
	function store($class_name) {
		if (function_exists ("apc_exists")) {
			if (!apc_exists("saved_" . $class_name . "_" . $this->id)) {
				apc_store("saved_" . $class_name . "_" . $this->id, true);
			}
		}
	}
}
?>