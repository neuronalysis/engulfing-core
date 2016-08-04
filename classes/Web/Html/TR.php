<?php
class TR {
	var $onmouseover;
	var $onmouseout;
	var $nclick;
	var $ondblclick;
	var $tds = array();
	
	function TR() {
	}
	function addTD($td) {
	}
	function setTDs($tds) {
		for ($i=0; $i<count($tds); $i++) {
			$this->tds[$i] = new TD();
			$this->tds[$i]->content = $tds[$i];
		}
	}
	function render() {
		$str = "";
		$str .= "<tr class='" . $this->classname . "' id='" . $this->id . "' onmouseover='" . $this->onmouseover . "' onmouseout='" . $this->onmouseout . "' onclick='" . $this->onclick . "' ondblclick='" . $this->ondblclick . "'>";
		for ($i=0; $i<count($this->tds); $i++) {
			$str .= $this->tds[$i]->render();
		}
		$str .= "</tr>";
		
		return $str;
	}
}
?>