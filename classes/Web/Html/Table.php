<?php
class Table {
	var $trs = array();
	
	function Table() {
	}
	function addTR($tr) {
	}
	function render() {
		$str = "";
		$str .= "<input type='hidden' name='tableName' value='" . $this->name . "'/>";
		
		if ($this->scrolling) {
			$str .= '<div style="position: absolute; overflow: auto; width: ' . $this->width . 'px; height: ' . $this->height . 'px; background-color: #D4D0C8; border: 1px solid #000000; font-family: Verdana, Arial; color: #777777; font-size: 12px;">';
		}
		
		$str .= "<table name='" . $this->name . "' class='" . $this->classname . "' style='" . $this->style . "' rowtype='" . $this->rowtype . "' objtype='" . $this->objtype . "'>";
		
		if ($this->title) $str .= $this->title->render();
		if ($this->header) $str .= $this->header->render();
		
		for ($i=0; $i<count($this->trs); $i++) {
			$str .= $this->trs[$i]->render();
		}
		if ($this->footer) $str .= $this->footer->render();
		$str .= "</table>";
		
		if ($this->scrolling) {
			$str .= '</div>';
		}
		
		return $str;
	}
	function renderByData($data) {
		$str = "";
		
		$str .= '<table>';
		
		$array_keys = get_object_vars($data[0]);
		
		for ($i=0; $i<count($data); $i++) {
			$str .= '<tr>';
			for ($j=0; $j<count($array_keys); $j++) {
				$str .= '<td>';
				$str .= $data[$i]->$array_keys[$j];
				$str .= '</td>';
			}
			$str .= '</tr>';
		}
		
		$str .= '</table>';
		
		return $str;
	}
}
?>