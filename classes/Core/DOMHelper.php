<?php
trait DOMHelper {
	
	function DOMHelper() {
	}
	function getFragments() {
    	$fragments = array();
    	
    	$this->body->setContainers();
    	
    	for ($i=0; $i<count($this->body->elements); $i++) {
     		if (isset($this->body->elements[$i]->text)) {
     			if ($this->body->elements[$i]->text != "") {
     				
     				array_push($fragments, new Fragment($this->body->elements[$i]));
     			}
    		}
    		
    		$fragments = array_merge($fragments, $this->body->elements[$i]->getFragments());
    	}
    	
    	return $fragments;
    }
    function processHTMLElements($htmlelements) {
    	$this->body = new Document_Body();
    		
    	foreach($htmlelements as $root_item) {
    		if (get_class($root_item) == "Head_Website") {
    			$this->header = new Document_Header();
    
    			if (isset($this->header)) {
    				$elements = $root_item->processHTMLElements();
    				foreach($elements as $element_item) {
    					array_push($this->header->elements, $element_item);
    				}
    			}
    		}
    	}
    
    	foreach($htmlelements as $root_item) {
    		//echo get_class($root_item) . "\n";
    		if (get_class($root_item) == "Body_Website" || get_parent_class($root_item) == "Body_Website") {
    			$this->body = new Document_Body();
    
    			if (isset($this->body)) {
    				$elements = $root_item->processHTMLElements();
    				foreach($elements as $element_item) {
    					array_push($this->body->elements, $element_item);
    				}
    			}
    		}
    	}
    
    
    }
    function processURL() {
    	$path = str_replace("+", " ", $this->ressource_path);
    	 
    	$a = explode("?", $path);
    	if (isset($a[1])) {
    		$b = explode(" ", $a[1]);
    	} else {
    		$a = explode("/", $path);
    
    		$b[0] = "Name=" . $a[count($a)-1];
    	}
    	 
    	 
    	 
    	$table = new Document_Table();
    	 
    	$url_parameters = array();
    	foreach($b as $pair) {
    		$c = explode("=", $pair);
    
    		$row = new Document_Table_Row();
    
    		$cell = new Document_Table_Cell();
    		$text = new Document_Textblock();
    		$p = new Document_Paragraph();
    		$p->text = $this->mapURLParameter($c[0]);
    
    		array_push($text->elements, $p);
    		array_push($cell->elements, $text);
    		array_push($row->elements, $cell);
    
    		if (isset($c[1])) {
    			$cell = new Document_Table_Cell();
    			$text = new Document_Textblock();
    			$p = new Document_Paragraph();
    			$p->text = $c[1];
    			 
    			array_push($text->elements, $p);
    			array_push($cell->elements, $text);
    			array_push($row->elements, $cell);
    			 
    			array_push($table->elements, $row);
    		}
    
    
    	}
    	 
    	array_push($this->body->elements, $table);
    }
    function mapURLParameter($parameter) {
    	$mapped = $parameter;
    	switch ($parameter) {
    		case "s":
    			$mapped = "Symbol";
    
    			break;
    		default:
    
    			break;
    	}
    
    	return $mapped;
    }
}
?>
