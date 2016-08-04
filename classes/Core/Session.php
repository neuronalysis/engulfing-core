<?php
class Session {
	var $dbtable			= "sessions";
	
	function Session() {
	}
	function start() {
		if (session_id() == "") session_start();
		
		$this->idString = session_id();
		
		return $this->db->save($this);
	}
	function end() {
	}
}
?>