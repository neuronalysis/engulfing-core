<?php 
include_once ('Body.php');
include_once ('Head.php');
include_once ('Table.php');
include_once ('TR.php');
include_once ('TD.php');
include_once ('Form.php');
include_once ('Select.php');
include_once ('MultiSelect.php');
include_once ('Form.php');
include_once ('Anchor.php');
include_once ('Upload.php');
class Html extends Element {
	var $children = array("body");
	
	function Html() {
	}
	function render() {
		$str = "";
		
		$str .= "<html>";
		
		$head = new Head();
		
		$str .= $head->render();
		$str .= "</html>";
		
		return $str;
	}
}
?>