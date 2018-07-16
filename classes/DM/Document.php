<?php
class Document {
	use Helper;
	
	var $elements = array();
	var $header;
	var $body;
	var $text;
	
	var $matched_words = array();
	var $isEncrypted;
	var $size;
	var $document;
	var $processing;
	var $keywords = array();
	var $language;
	var $Ontologies = array();
	var $information;
	var $resource_path;
	
	var $type;
	
	var $sections = array();
	
	var $grid;
	
	function __construct() {
		
    }
    function getDOMComplexity() {
    	$amount_elements = 0;
    
   		foreach ($this->elements as $element_item) {
   			$amount_elements += $element_item->countElements();
   			$amount_elements++;
   		}
    
    	return $amount_elements;
    }
    function getComplexity() {
    	$amount_elements = 0;
    
    	foreach ($this->body->elements as $element_item) {
    		$amount_elements += $element_item->countElements();
    		$amount_elements++;
    	}
    	
    	return $amount_elements;
    }
    function getTextContent() {
    	$textContent = "";
    	if (isset($this->body->elements)) {
    		foreach ($this->body->elements as $element) {
    			if (isset($element)) $textContent .= str_replace(":", "", $element->getTextContent());
    		}
    	}
    	
    	
    	$textContent .= " ";
    	
    	return $textContent;
    }
    function getLinks() {
    	$links = array();
    	
    	for ($i=0; $i<count($this->body->elements); $i++) {
    		if (get_class($this->body->elements[$i]) == "Document_URL") {
    			array_push($links, $this->body->elements[$i]);
    		}
    		
    		$links = array_merge($links, $this->body->elements[$i]->getLinks());
    	}
    	 
    	return $links;
    }
    function getTokens() {
    	$tokens = array();
    	
    	$text = $this->getText();
    	
    	preg_match_all("~[#@]?\w+|\pP+|\S~u", str_replace("_", " ", $text), $text_tokens);
    	
    	foreach($text_tokens as $token_items) {
    		foreach($token_items as $token_item) {
    			array_push($tokens, $token_item);
    		}
    	}
   
    	return $tokens;
    }
    function getText() {
    	$text = "";
    	
    	for ($i=0; $i<count($this->body->elements); $i++) {
    		if (isset($this->body->elements[$i]->text)) {
    			if ($this->body->elements[$i]->text != "") {
    				$text .= $this->body->elements[$i]->text . " ";
    			}
    		}
    	
    		$text .= $this->body->elements[$i]->getText();
    	}
    	
    	return $text;
    }
    function getOntologiesByKeywords($keywords) {
    	$onto = new KM();
    	
    	$hasSpecials = false;
    	
    	$Ontology_wordcounts = array();

    	$names = array();
    	$Ontologies = array();
    	$special_Ontologies = array();
    	 
    	foreach ($keywords as $word_item) {
    		if (!isset($word_item->Lexeme->OntologyClass->Ontology)) {
    			//echo $word_item->name . "\n";
    		}
    		if (isset($Ontology_wordcounts[$word_item->Lexeme->OntologyClass->Ontology->name])) {
    			$Ontology_wordcounts[$word_item->Lexeme->OntologyClass->Ontology->name]++;
    		} else {
    			$Ontology_wordcounts[$word_item->Lexeme->OntologyClass->Ontology->name] = 1;
    		}
    		if (isset($word_item->Lexeme->Lexeme_OntologyProperty)) {
    			if ($word_item->Lexeme->Lexeme_OntologyProperty->isSpecial == true) {
    				$special_Ontologies[$word_item->Lexeme->OntologyClass->Ontology->name] = true;
    				$hasSpecials = true;
    			}
    		}
    		
    		if ($Ontology_wordcounts[$word_item->Lexeme->OntologyClass->Ontology->name] > 1 && !in_array($word_item->Lexeme->OntologyClass->Ontology->name, $names)) {
    			array_push($names, $word_item->Lexeme->OntologyClass->Ontology->name);
    			 
    			$Ontology = $onto->getOntologyByName($word_item->Lexeme->OntologyClass->Ontology->name);
    			 
    			 
    			array_push($Ontologies, $Ontology);
    		}
    		
    	}
    	if ($hasSpecials == true) {
    		for ($i=0; $i<count($Ontologies); $i++) {
    			//echo $Ontologies[$i] . "\n";
    			if (!isset($special_Ontologies[$Ontologies[$i]->name])) {
    				array_splice($Ontologies, $i, 1);
    			}
    		}
    	}
    	
    	
    	return $Ontologies;
    }
    function renderGridHTML() {
    	$html = "";
    	
    	if (count($this->grid->rows) > 0) {
    		$html .= "<html>";
    		
    		$html .= "<body>";
    		
    		foreach ($this->grid->rows as $row_item) {
    			if (count($row_item->columns) > 0) {
    				$html .= '<table border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">';
    				 
    				$html .= $row_item->renderGridHTML();
    				 
    				$html .= "</table>";
    			}
    			
     		}
    		
    		$html .= "</body>";
    		
    		$html .= "</html>";
    	}
    	
    	return $html;
    }
    function processURL() {
    	$path = str_replace("+", " ", $this->resource_path);
    	
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
    function explodeTableCells() {
    	if (isset($this->body)) {
    		if (isset($this->body->elements)) {
    			for ($i=0; $i<count($this->body->elements); $i++) {
    				if (isset($this->body->elements[$i])) {
    					$this->body->elements[$i]->explodeTableCells();
    				}
    				 
    			}
    		}
    	}
    	
    }
    function countValuesByKey($key) {
    	$amout_values = array();
    	$f=0;
    	
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    	
    			$value = $element_item->searchValueByKey($key);
    	
    			if ($value) {
    				if (is_array($value)) {
    					$amout_values[$f] = count($value);
    					$f++;
    				} else {
    					$amout_values[$f] = 1;
    				}
    			}
    		}
    	}
    	
    	if (count($amout_values) == 0) {
    		$amout_values[0] = 0;
    	}
    	 
    	return $amout_values;
    }
    function countValuesByDate() {
    	$amout_values = array();
    	$f=0;
    	
    	$values = array();
    	 
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    			
    			$values = $element_item->searchValueByDate($values);
    			
    			if ($values) {
    				if (is_array($values)) {
    					$amout_values[$f] = count($values);
    					$f++;
    				} else {
    					$amout_values[$f] = 1;
    				}
    			}
    		}
    	}
    	 
    	if (count($amout_values) == 0) {
    		$amout_values[0] = 0;
    	}
    
    	return $amout_values;
    }
    function searchValueByDateAndIndex($index) {
    	$amout_values = array();
     	 
    	$values = array();
    
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    			 
    			$values = $element_item->searchValueByDate($values);
    			 
    			if ($values) {
    				if (is_array($values)) {
    					return $values[$index];
    				}
    			}
    		}
    	}
    
    	return null;
    }
	function searchKey($key) {
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    		
    			$value = $element_item->searchKey($key);
    		
    			if ($value) return $value;
    		}
    	}
    	
    	
    	return null;
    }
    function searchValueByKey($key) {
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    
    			$value = $element_item->searchValueByKey($key);
    
    			if ($value) return $value;
    		}
    	}
    	 
    	 
    	return null;
    }
    function searchValueByPropertyEntity($propertyentity) {
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    
    			$value = $element_item->searchValueByPropertyEntity($propertyentity);
    
    			if ($value) return $value;
    		}
    	}
    	 
    	 
    	return null;
    }
    function searchValueByKeyAndFragmentAndIndex($key, $fragment, $index) {
    	$active_fragment=0;
    	
    	if (isset($this->body->elements)) {
    		foreach($this->body->elements as $element_item) {
    			$element_item->container = $this;
    			
    			$check_value = $element_item->searchValueByKey($key);
    			if ($check_value) {
    				if ($active_fragment == $fragment) {
     					$value = $element_item->searchValueByKeyAndFragmentAndIndex($key, $fragment, $index);
    					
    					if ($value) {
    						return $value;
    					}
    				} else {
    					$active_fragment++;
    				}
    			}
    
    			
    		}
    	}
    	 
    	 
    	return null;
    }
    function identifyOntology() {
    	
    }
    function extractInformationByOntologies($Ontologies) {
    	$information = array();
    	
    	for ($i=0; $i<count($Ontologies); $i++) {
    		$info = $this->extractInformationByOntology($Ontologies[$i]);
    		//$onto str_replace("", "", get_class($info[0][0])) . "\n";
    		$onto_name = strtolower($Ontologies[$i]->name);
    		
    		if (isset($info)) {
    			array_push($information, $info);
    		}
    	}
    	
    	return $information;
    }
    function extractInformationByNamedEntity($Ontology, $named_entity, $value = null) {
    	for ($oc=0; $oc<count($Ontology->OntologyClasses); $oc++) {
    		if ($Ontology->OntologyClasses[$oc]->name == $named_entity->OntologyProperty->OntologyClass->name) {
    			for ($oce=0; $oce<count($Ontology->OntologyClasses[$oc]->entities); $oce++) {
    				for ($ope=0; $ope<count($Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities); $ope++) {
    					for ($opew=0; $opew<count($Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities[$ope]->Lexeme->Words); $opew++) {
    						if ($Ontology->OntologyClasses[$oc]->entities[$oce]->OntologyClass_propertyentities[$ope]->Lexeme->Words[$opew]->name == $named_entity->getValue()) {
     							return $Ontology->OntologyClasses[$oc]->entities[$oce];
    						}
    					}
    				}
    			}
    		}
    	}
    	return null;
    }
    
    function mapPropertyNameByObject($object) {
    	$propertyname = $this->pluralize(strtolower(str_replace("", "", get_class($object))));
    	
    	return $propertyname;
    }
}
class Document_Header extends Element {
}
class Document_Body extends Element {
	var $object_class_names = array('Document_Section');
	
	var $mergers = array();
	
}
class Document_Merger {
	var $posX;
	var $posY;
	var $element;
}
class Document_List extends Element {
	
}
class Document_List_Item extends Element {
	
}
class Document_Table extends Element {
	var $html_tag = "table";
	var $html_attributes = array('style="border: 1px solid black;"');
	
}
class Document_Table_Group extends Element {
}
class Document_Table_Head extends Element {
}
class Document_Table_Foot extends Element {
}
class Document_Table_Body extends Element {
}
class Document_Table_Row extends Element {
	var $html_tag = "tr";
	var $html_attributes = array('style="border: 1px solid black;"');
	
}
class Document_Table_HeadCell extends Element {
	var $html_tag = "th";
}
class Document_Table_Cell extends Element {
	var $html_tag = "td";
	var $html_attributes = array('style="border: 1px solid black;"');
	
}
class Document_URL extends Element {
	var $html_tag = "a";
	var $text;
	var $type = "Document_URL";
	
}
class Document_Section extends Element {
	var $processed_rows;
	var $page;
	var $object_class_names = array('Document_Section', 'Document_Textblock');
	var $html_tag = "div";
	var $xpath_self = '//div[@id="page-container"]';
	
	function __construct($page = 1) {
		$this->page = $page;
	}
}
class Document_Style extends Element {
	var $text;
	var $type = "Document_Style";
}
class Document_Textblock extends Element {
    function paragraph($content = null) {
    	$converter = new Converter();
    	
		$doc_p = new Document_Paragraph();
		$doc_p->text = $converter->tidy($content);
		array_push($this->elements, $doc_p);
	}
	function getWidthByText() {
		$text = $this->getTextContent();
		
		return strlen($text) * 7;
	}
}
class Document_Paragraph extends Element {
	var $html_tag = "";
	var $text;
	var $type = "Document_Paragraph";
	var $object_class_names = array('Document_URL');
	
	function __construct() {
		
	}
	function getWidthByText() {
		return strlen($this->text) * 7;
	}
	function explodeContent($content) {
		$transformer = new Transformer();
		
		if (stripos($content, " / ") !== false) {
			$frags = explode("/", $content);
			
			$frags = $this->fixExplodables($frags);
			
			if (stripos($content, ":") !== false) {
				for ($i=0; $i<count($frags); $i++) {
					$frags[$i] = explode(": ", $frags[$i]);
				}
			}
			
			
			//print_r($frags);
			
			$exploded = $transformer->transformArrayToTable($frags);
			
			return $exploded;
		} else if (stripos($content, " | ") !== false) {
			$frags = explode(" | ", $content);
			
			$frags = $this->fixExplodables($frags);
			
			if (stripos($content, ":") !== false) {
				for ($i=0; $i<count($frags); $i++) {
					$frags[$i] = explode(": ", $frags[$i]);
				}
			}
			
			
			//print_r($frags);
			
			$exploded = $transformer->transformArrayToTable($frags);
			
			return $exploded;
		} else if (stripos($content, "; ") !== false) {
			$frags = explode("; ", $content);
			$frags = $this->fixExplodables($frags);
			
			for ($i=0; $i<count($frags); $i++) {
				if (!is_array($frags[$i])) {
					$frags[$i] = explode(": ", $frags[$i]);
				}
				
			}
			
			$exploded = $transformer->transformArrayToTable($frags);
			
			return $exploded;
		} else if (stripos($content, ", ") !== false && stripos($content, ": ") !== false) {
			$frags = explode(", ", $content);
			$frags = $this->fixExplodables($frags);
			
			for ($i=0; $i<count($frags); $i++) {
				if (!is_array($frags[$i])) {
					$frags[$i] = explode(": ", $frags[$i]);
				}
				
			}
			
			$exploded = $transformer->transformArrayToTable($frags);
			
			return $exploded;
		} else if (stripos($content, "  ") !== false && stripos($content, ": ") !== false) {
			$frags = explode("  ", $content);
			$frags = $this->fixExplodables($frags);
			
			for ($i=0; $i<count($frags); $i++) {
				if (!is_array($frags[$i])) {
					$frags[$i] = explode(": ", $frags[$i]);
				}
				
			}
			
			$exploded = $transformer->transformArrayToTable($frags);
			
			return $exploded;
		} else if (stripos($content, "/") !== false) {
				$frags = explode("/", $content);
					
				$frags = $this->fixExplodables($frags);
					
				if (stripos($content, ":") !== false) {
					for ($i=0; $i<count($frags); $i++) {
						$frags[$i] = explode(": ", $frags[$i]);
					}
				}
					
					
				//print_r($frags);
					
				$exploded = $transformer->transformArrayToTable($frags);
					
				return $exploded;
		} else if (stripos($content, ": ") !== false && stripos(str_ireplace(": ", "", $content), " ") == false) {
			$frags = explode(": ", $content);
			
			$exploded = $transformer->transformArrayToTable($frags);
				
			return $exploded;
		} else {
			return $this;
		}
		 
		return null;
	}
	function fixExplodables($explodables) {
		$fixed = array();
		
		
		$count_explodables_with_doublepoints = 0;
		for ($i=0; $i<count($explodables); $i++) {
			if (stripos($explodables[$i], ":") !== false) {
				$count_explodables_with_doublepoints++;
			}
		}
		//echo "count_explodables_with_doublepoints: " . $count_explodables_with_doublepoints . "\n";
		
		if ($count_explodables_with_doublepoints == 0) {
			for ($i=0; $i<count($explodables); $i++) {
				$fixed[0][$i] = $explodables[$i];
			}
			
			return $fixed;
		}
		
		if (count($explodables) == $count_explodables_with_doublepoints) {
			for ($i=0; $i<count($explodables); $i++) {
				$fixed[$i] =  str_replace(":", ": ", str_replace(": ", ":", $explodables[$i]));
			}
		} else {
			for ($i=0; $i<count($explodables); $i++) {
				$exploded_by_space = explode(" ", $explodables[$i]);
				$count_exploded_by_space = count($exploded_by_space);
				if (stripos($explodables[$i], ":") == false && $count_explodables_with_doublepoints > 0) {
					$exploded_by_space[0] .= ":";
				}
					
				$fixed[$i] = $exploded_by_space[0] . " " . $exploded_by_space [1];
			}
		}
		
		
		return $fixed;
	}
}
class Document_Section_Title extends Element {
	var $text;
	var $type = "Document_Section_Title";
}

class Document_Grid {
	var $rows = array();
	
	function processDOMElements($domelements) {
		foreach($domelements as $item_element) {
			/*for ($r=0; $r<count($this->rows); $r++) {
				if ($this->rows[$r]->position == $this->active_row_position) {
					$match_row = true;
					$match_column = false;
					
					for ($c=0; $c<count($this->rows[$r]->columns); $c++) {
						if ($this->rows[$r]->columns[$c]->position == $this->active_column_position) {
							$match_column = true;
							
							if (isset($this->rows[$r]->columns[$c]->content)) {
								if (isset($item_element->value)) $this->rows[$r]->columns[$c]->content->body .= $item_element->value;
							} else {
								if (isset($item_element->value)) {
									$content = new Grid_Content();
									$content->font_size = $this->active_font_size;
									$content->font_color = $this->active_font_color;
									$content->body = $item_element->value;
									$ttf_box = imagettfbbox($content->font_size, 0, "../../engulfing/data/fonts/times.ttf", $content->body);
									$content->string_width = $ttf_box[2] - $ttf_box[0];
									$this->rows[$r]->columns[$c]->content = $content;
								}
							}
						}
					}
					
					if ($match_column == false) {
						$column = new Grid_Column();
						$column->position = $this->active_column_position;
						$column->width = $this->active_column_width;
							
						if (isset($item_element->value)) {
							$content = new Grid_Content();
							$content->font_size = $this->active_font_size;
							$content->font_color = $this->active_font_color;
							$content->body = $item_element->value;
							$ttf_box = imagettfbbox($content->font_size, 0, "../../engulfing/data/fonts/times.ttf", $content->body);
							$content->string_width = $ttf_box[2] - $ttf_box[0];
							$column->content = $content;
						} else {
							$content = new Grid_Content();
							
							$content->font_size = $this->active_font_size;
							$content->font_color = $this->active_font_color;
							$column->content = $content;
						}
					
						array_push($this->rows[$r]->columns, $column);
					}
				}
			}*/
						
			$row = new Grid_Row();
			array_push($this->rows, $row);
			
			
			if (isset($item_element->elements)) {
				$this->processDOMElements($item_element->elements);
			}
		}
	}
	function getRowByPosition($position) {
		foreach($this->rows as $row_item) {
			if ($row_item->position == $position) return $row_item;
		}
		
		$row = new Grid_Row();
		$row->position = $position;
		array_push($this->rows, $row);
		
		return $row;
	}
}


?>
