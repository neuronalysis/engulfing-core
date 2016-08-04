<?php
class TD {
	
	function TD() {
	}
	function render() {
		$str = "";
		$sys = new GSystem();
		
		if ($this->colspan) 	$colspan = "colspan='" . $this->colspan . "'";
		if ($this->align) 		$align = "align='" . $this->align . "'";
		if ($this->width	) 	$style = "width: " . $this->width . ";";
		if ($this->classname	) 	$class = "class='" . $this->classname . "'";
		if ($this->columnkey	) 	$columnkey = "columnkey='" . $this->columnkey . "'";
		if ($this->padding_right) 	$style .= "padding-right: " . $this->padding_right . ";";
		
		$str .= "<td " . "style='" . $style . "'" . " " . $class . " " . $colspan . " " . $align . " " . $columnkey . ">";
		if ($this->buttonType	) 	$str .= "<div class='ntHeader_Button'/>";
		
		if ($this->sorters) {
			$str .= '<a href="#" onclick="' . "sortTableBy(this, '" . $this->columnkey . "');" . '">' . $this->content . "</a>&nbsp;<img src='../engulfing/images/arrow_up.gif'/>"; 
		} else {
			$str .= $sys->wellformXML($this->content);
		}
		
		$str .= "</td>";
		
		return $str;
	}
}
?>