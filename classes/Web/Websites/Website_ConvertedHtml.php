<?php
class Website_ConvertedHtml extends Website {
	var $baseurl;
	var $resource;
	var $website_document;
	var $website_directory;
	var $website_sections;
	
	var $object_class_names = array('HTML_ConvertedHtml_Document');
	
	function __construct() {
	}
	
	function parse() {
		$this->processDOM();
		
		unset($this->dom);
		unset($this->xpath);
	}
	
	function processDOM($page = null) {
		if (!$this->dom) return;
		$this->xpath = new DOMXPath($this->dom);

		foreach($this->object_class_names as $item_object_class_name) {
			$template_class_object = new $item_object_class_name;
	
			$class_object_entity_nodes = $this->xpath->query($template_class_object->xpath_self);
	
	
			foreach($class_object_entity_nodes as $item_class_object_entity_node) {
				$class_object_entity = new $item_object_class_name;
				 
				$class_object_entity->xpath = $this->xpath;
				$class_object_entity->node = $item_class_object_entity_node;
				 
				$class_object_entity->processDOMElement();
				 
				unset($class_object_entity->xpath);
				unset($class_object_entity->node);
	
				array_push($this->elements, $class_object_entity);
			}
		}
	
	}
	
	function convertFromHTML() {
		$processed_html = $this->processHTML($this->elements);
	
		
		//$this->explodeTableCells();
		 
		$this->body->clean();
		
		//echo $this->renderHTML();
		
	}
	function processHTML($elements) {
		$section_level = 0;
	
		$active_section = null;
		$active_textblock = null;
		$active_table = null;
		 
		$page = 0;
		 
		$this->body = new Document_Body();
		
		for ($i=0; $i<count($elements); $i++) {
			if (get_class($elements[$i]) == "HTML_ConvertedHtml_Document") {
				for ($j=0; $j<count($elements[$i]->elements); $j++) {
					if (get_class($elements[$i]->elements[$j]) == "Table_ConvertedHtml_Table") {
						$exploded_table = new Document_Table();
						
						array_push($exploded_table->elements, new Document_Table_Group());
						array_push($exploded_table->elements[0]->elements, new Document_Table_Body());
						
						foreach($elements[$i]->elements[$j]->elements as $tr_item) {
							$tr = new Document_Table_Row();
							
							$c=0;
							foreach($tr_item->elements as $td_item) {
								$td = new Document_Table_Cell();
								$td->index = $c;
								
								$c++;
								foreach($td_item->elements as $td_sub_item) {
									if (get_class($td_sub_item) == "P_ConvertedHtml_Content") {
										if (isset($td_sub_item->elements)) {
											foreach($td_sub_item->elements as $text_item) {
											
												$text = new Document_Textblock();
											
												$p = new Document_Paragraph();
											
												$p->text = $text_item->value;
											
												array_push($text->elements, $p);
											}
											
											array_push($td->elements, $text);
										}
										
											
										
									} else if (get_class($td_sub_item) == "Table_ConvertedHtml_Table") {
										$exploded_table_sub = new Document_Table();
										
										array_push($exploded_table_sub->elements, new Document_Table_Group());
										array_push($exploded_table_sub->elements[0]->elements, new Document_Table_Body());
										
										foreach($td_sub_item->elements as $tr_item_sub) {
											$tr_sub = new Document_Table_Row();
												
											$c_sub=0;
											foreach($tr_item_sub->elements as $td_item_sub) {
												$td_sub = new Document_Table_Cell();
												$td_sub->index = $c_sub;
										
												$c_sub++;
												foreach($td_item_sub->elements as $td_sub_item_sub) {
													if (get_class($td_sub_item_sub) == "P_ConvertedHtml_Content") {
														if (isset($td_sub_item_sub->elements)) {
															foreach($td_sub_item_sub->elements as $text_item_sub) {
															
																$text_sub = new Document_Textblock();
															
																$p_sub = new Document_Paragraph();
															
																$p_sub->text = $text_item_sub->value;
															
																array_push($text_sub->elements, $p_sub);
															}
															
															array_push($td_sub->elements, $text_sub);
														}
														
															
														
													}
														
												}
										
												array_push($tr_sub->elements, $td_sub);
											}
												
											array_push($exploded_table_sub->elements[0]->elements[0]->elements, $tr_sub);
										}
										
										
										array_push($td->elements, $exploded_table_sub);
									}
									
								}
								
								array_push($tr->elements, $td);
							}
							
							array_push($exploded_table->elements[0]->elements[0]->elements, $tr);
						}
						
						
						$active_section = new Document_Section();
						$active_textblock = new Document_Textblock();
						
						
						array_push($active_textblock->elements, $exploded_table);
						array_push($active_section->elements, $active_textblock);
						
						array_push($this->body->elements, $active_section);
					} else if (get_class($elements[$i]->elements[$j]) == "P_ConvertedHtml_Content") {
						$active_section = new Document_Section();
						
						if (isset($elements[$i]->elements[$j]->elements)) {
							$text = new Document_Textblock();
							foreach($elements[$i]->elements[$j]->elements as $text_item) {
								$p = new Document_Paragraph();
								$p->text = $text_item->value;
								array_push($text->elements, $p);
							}
							array_push($active_section->elements, $text);
						}
						array_push($this->body->elements, $active_section);
					}
				}
			}
		}
	}
}
class HTML_ConvertedHtml_Document extends Element {
	var $xpath_self = '//html/body';
	var $object_class_names = array('Table_ConvertedHtml_Table', 'P_ConvertedHtml_Content');
}
class Table_ConvertedHtml_Table extends Element {
	var $xpath_self = 'table';
	var $object_class_names = array('TR_ConvertedHtml_Table');
	//var $xpath_attributes = '@class';
	//var $object_class_names = array('Div_ConvertedPdf_Page_Container');
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
class TR_ConvertedHtml_Table extends Element {
	var $xpath_self = 'tr';
	var $object_class_names = array('TD_ConvertedHtml_Table');
	//var $xpath_attributes = '@class';
	//var $object_class_names = array('Div_ConvertedPdf_Page_Container');
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
class TD_ConvertedHtml_Table extends Element {
	var $xpath_self = 'td';
	var $object_class_names = array('Table_ConvertedHtml_Table', 'P_ConvertedHtml_Content');
	//var $xpath_attributes = '@class';
	//var $object_class_names = array('Div_ConvertedPdf_Page_Container');
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
class P_ConvertedHtml_Content extends Element {
	var $xpath_self = 'p';
	var $object_class_names = array('Text_ConvertedPdf_Content');
	//var $xpath_attributes = '@class';
	//var $object_class_names = array('Div_ConvertedPdf_Page_Container');
	//var $object_class_names = array('Div_ConvertedPdf_Page');
}
?>
