<?php
class MultiSelect {
	var $type 			= "multiselect";
	var $classname 		= "dropdown";
	var $size			= 1;
	
	var $entries;
	
	var $editable = true;
	var $defaultValue = false;
	
	function MultiSelect() {
	}
	function render() {
		if ($this->editable == false) $disabled = " disabled ";
		if ($this->id) $id = " id='" . $this->id . "' ";
		
		$str = "";
		$str .= "<select " . $id . " class='" . $this->classname . "' style='" . $this->style . "' name='" . $this->name . "' size='" . $this->size . "' onchange='" . $this->onchange . "' onclick='" . $this->onclick . "' " . $disabled . ">";
		
		$entryCaption = $this->entryCaption;
		$entryValue = $this->entryValue;
		$entryAttributes = $this->entryAttributes;
		$entryTypes = $this->entryTypes;
		$entryInterfaces = $this->entryInterfaces;
		
		if ($this->defaultValue == true) {
			$default = new DefaultValue();
			
			$default->$entryValue = -1;
			$default->$entryCaption = "nicht ausgew�hlt";
			array_unshift($this->entries, $default);
		}
		for ($i=0; $i<count($this->entries); $i++) {
			if ($this->value == $this->entries[$i]->$entryValue) {
				$checked = " selected";
			} else {
				$checked = "";
			}
			if (isset($entryAttributes)) {
				if (isset($this->entries[$i]->$entryAttributes)) {
					$attributes = $this->entries[$i]->$entryAttributes;
				} else {
					$attributes = "";
				}
			}
			if (isset($entryTypes)) {
				if (isset($this->entries[$i]->$entryTypes)) {
					$types = $this->entries[$i]->$entryTypes;
				} else {
					$types = "";
				}
			}
			if (isset($entryInterfaces)) {
				if (isset($this->entries[$i]->$entryInterfaces)) {
					$interfaces = $this->entries[$i]->$entryInterfaces;
				} else {
					$interfaces = "";
				}
			}
			$str .= "<option value='" . $this->entries[$i]->$entryValue . "' attributes='" . $attributes . "' " .  "' type='" . $types . "' " . "' interfaces='" . $interfaces . "' " . $checked . ">" . $this->entries[$i]->$entryCaption . "</option>";
		}
		$str .= "</select>";
		
		
		$str .= "<button type='button' name='save' onclick='addListItem();'>" . "Add Item" . "</button>";
		
		$str .= "<ul id='list'>";
		
		$str .= "<li>" . "arsch" . "</li>";
		
		$str .= "</ul>";
		
		return $str;
	}
}
?>