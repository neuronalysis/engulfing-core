<?php
class Upload {
	var $type 			= "upload";
	//var $classname 		= "dropdown";
	//var $size			= 1;
	
	//var $entries;
	
	//var $editable = true;
	//var $defaultValue = false;
	
	function Upload() {
	}
	function render() {
		if ($this->editable == false) $disabled = " disabled ";
		if ($this->id) $id = " id='" . $this->id . "' ";
		
		$str = "";
		$str .= "<input " . $id . " class='" . $this->classname . "' type='file' style='" . $this->style . "' name='" . $this->name . "'/>";
		$entryCaption = $this->entryCaption;
		$entryValue = $this->entryValue;
		$entryAttributes = $this->entryAttributes;
		$entryTypes = $this->entryTypes;
		$entryInterfaces = $this->entryInterfaces;
		
		
		return $str;
	}
}
?>