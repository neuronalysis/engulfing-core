<?php
class Download {
	var $dbtable			= "downloads";
	function Download($id = null) {
		$this->init();
		if ($id) {
			$select = new SelectQuery();
			$select->from 				= $this->dbtable;
			$select->where->Name	= $id;
			$item = $this->getThis($select);
			
			$this->id 						= $item['id'];
			$this->Name 					= $item['Name'];
			$this->Data 					= $item['Data'];
		}
	}
	function get() {
		$file = fopen ($this->url, "r");
		if (!$file) {
		    echo "<p>Datei konnte nicht ge�ffnet werden.\n";
		    exit;
		}
		$content = "";
		while (!feof ($file)) {
		    $line = fgets ($file, 1024);
		    $content .= $line;
		}
		fclose($file);
		
		$this->Data = $content;
	}
	function init() {
 		global $engulfing;
		$this->db = $engulfing->database;
	}
	function getThis($select = null) {
		if ($select) {
			$result = $this->db->select($select);
			return $result->fetchRow(DB_FETCHMODE_ASSOC);
		} else {
			return $this->db->get($this);
		}
	}
	function save() {
		return $this->db->save($this);
	}
	function delete() {
		return $this->db->delete($this);
	}
}
?>