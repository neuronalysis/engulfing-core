<?php
class HCache {
	
	var $db;					//DB Connection
	var $dbtable			= "caches";
	function HCache($cacheid = null) {
		$this->init();
		if ($cacheid) {
			$this->id = $cacheid;
			
			$cache = $this->getThis();
			
			$this->uri 									= $cache['uri'];
			$this->content 							= $cache['content'];
		}
	}
	function init() {
 		global $engulfing;
		if ($engulfing) $this->db = $engulfing->database;
	}
	function save() {
		if ($this->db) return $this->db->save($this);
	}
	function getThis() {
		if ($this->db) return $this->db->get($this);
	}
}
?>