<?php
class Website_ConvertedPDF extends Website {
	
	var $object_class_names = array('Head_Website', 'Body_ConvertedPdf');
	
	function __construct() {
	}
	
	function getPageCount() {
		$this->xpath = new DOMXPath($this->dom);
	
		$xpath_pages = '//div[contains(@id, "pf")]';
	
		$page_nodes = $this->xpath->query($xpath_pages);
	
	
		return $page_nodes->length;
	}
}
class Body_ConvertedPdf extends Body_Website {
	var $object_class_names = array('Div_ConvertedPdf_Document');
	
}
class Div_ConvertedPdf_Container extends Div_Website {
	var $xpath_self_attributes_contain = array("class" => "c ");
	
	var $object_class_names = array('Div_ConvertedPdf_Text');
}
class Div_ConvertedPdf_Document extends Div_Website {
	var $xpath_self_attributes_contain = array("id" => "page-container");
	
	var $object_class_names = array('Div_ConvertedPdf_Page', 'Text_Website_Content');
}
class Div_ConvertedPdf_Page extends Div_Website {
	var $xpath_self_attributes_contain = array("id" => "pf");
	
	var $object_class_names = array('Div_Website');
	
	function __construct($page) {
		$this->xpath_self_position = $page;
	}
}
?>
