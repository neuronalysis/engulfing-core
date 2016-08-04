<?php 
class Head {
	function Head() {
	}
	function render() {
		$str = "";
		
		$str .= "<head>";
		$str .= "<title>" . $this->title . "</title>";
		
		$str .= "<link href='style/default.css' rel='stylesheet' type='text/css'>\n";
		$str .= "<script language='JavaScript' src='../engulfing/js/form.js'></script>";
		$str .= "<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
		$str .= "</head>";
		
		return $str;
	}
}
?>