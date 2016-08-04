<?php
class Cookie {
	var $dbtable			= "cookies";
	
	function Cookie() {
	}
	function setCookie($name, $value) {
		$this->name = $name;
		$this->value = $value;
		
		return $this->db->save($this);
		
	}
	function getCookie() {
	}
}
?>