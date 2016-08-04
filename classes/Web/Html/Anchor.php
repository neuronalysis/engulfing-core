<?php 
class Anchor {
	var $target = "_self";
	
	function Anchor($href = null, $caption = null, $id = null) {
		if ($href)			$this->href = $this->wellform($href);
		if ($caption)		{
			$this->caption = $caption;
		} else {
			$this->caption = $this->href;
		}
		if ($id) $this->href .= $id;
	}
	function render() {
		$str = "";
		
		$str .= "<a href='" . $this->href . "' target='" . $this->target . "'>";
		
		$str .= $this->caption;
		
		$str .= "</a>";
		
		return $str;
	}
	function wellform($bad) {
		if (ereg("http://", $bad)) {
			return $bad;
		} else {
			return $bad;
		}
	}
}
?>