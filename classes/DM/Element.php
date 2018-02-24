<?php 
class Element {
	var $type;
	
	var $xpath;
	var $node;
	var $css;
	var $index;
	
	var $xpath_self;
	var $xpath_self_attributes_contain = array();
	var $xpath_self_attributes_equal = array();
	//var $xpath_self_position;
	var $object_class_names = array();
	var $object_attribute_names = array();
	var $elements = array();

	var $name;
	var $value;
	var $style;
	var $container;
	
	var $suppress = false;
	
	var $html_tag;
	var $html_attributes;
	
	function __construct() {
	}
	function setType($recursive = false) {
		$this->Type = get_class($this);
		
		if ($recursive == true) {
			if (isset($this->elements)) {
				for ($i=0; $i<count($this->elements); $i++) {
					if (isset($this->elements[$i])) {
						$this->elements[$i]->setType(true);
					}
				}
			}
			
		}
	}
	function countElements() {
		$amount_elements = 0;
		
		if (isset($this->elements)) {
			foreach ($this->elements as $element_item) {
				$amount_elements += $element_item->countElements();
				$amount_elements++;
			}
		}
		
		
		return $amount_elements;
	}
	function setContainers() {
		if (isset($this->elements)) {
			for ($i=0; $i<count($this->elements); $i++) {
				$this->elements[$i]->index = $i;
				
				$this->elements[$i]->container = $this;
					
				$this->elements[$i]->setContainers();
			}
			
		}
		
	}
	function unsetContainers() {
		if (isset($this->elements)) {
			for ($i=0; $i<count($this->elements); $i++) {
				unset($this->elements[$i]->container);
				
				$this->elements[$i]->unsetContainers();
			}
		}
	}
    function renderHTML() {
    	$html = "";
    	
    	if ($this->html_tag != "") {
   			$html .= "<" . $this->html_tag;
   			
   			if (isset($this->html_attributes) > 0) {
   				foreach ($this->html_attributes as $html_attribute_item) {
   					$html .= " " . $html_attribute_item;
   				}
   			}
   				
   			
   			$html .= ">";
   		}
   		
   		if (isset($this->elements)) {
   			if (count($this->elements) == 0) {
   				unset($this->elements);
   			}
   		}
   		
    	if (isset($this->elements)) {
    		foreach ($this->elements as $element_item) {
    			if (isset($element_item))	{
    				$html .= $element_item->renderHTML();
    			}
    		}
    	} else {
    		if (isset($this->text)) {
    			$html .= $this->text;
    		}
    	}
    		
   		if ($this->html_tag != "") $html .= "</" . $this->html_tag . ">";
    	    		 
    	
    	return $html;
    }
    function mergeSections() {
    	$merged = array();
    	 
    	for ($i=0; $i<count($this->elements); $i++) {
   			if (get_class($this->elements[$i]) == "Document_Section") {
   				if (count($this->elements[$i]->elements) == 2) {
   					if (get_class($this->elements[$i]->elements[0]) == "Document_Textblock" && get_class($this->elements[$i]->elements[1]) == "Document_Section") {
   						//echo "text.box.width: " . $this->elements[$i]->elements[1]->posX . "; text.width: " . $this->elements[$i]->elements[0]->getWidthByText() . " [" . $this->elements[$i]->elements[0]->getTextContent() . "]" . "\n";
   						if ($this->elements[$i]->elements[1]->posX < $this->elements[$i]->elements[0]->getWidthByText() && $this->elements[$i]->elements[1]->posX < 50) {
   							if ($this->elements[$i]->elements[1]->hasOnlyText()) {
   								$textblock = $this->elements[$i]->elements[1]->elements[0];
   								$this->elements[$i]->elements[1] = $textblock;
   							}
   						}
   						
   					}
   				}
   			} else {
   				$this->elements[$i]->mergeSections();
   			}
   	
    		array_push($merged, $this->elements[$i]);
    	}
    	 
    	$this->elements = $merged;
    }
    function explodeTableCells() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (get_class($this->elements[$i]) == "Document_Table_Cell") {
    				if (isset($this->elements[$i]->elements[0]->elements[0]->text)) {
    					if ($this->isExplodeable($this->elements[$i]->elements[0]->elements[0]->text)) {
    						$this->elements[$i]->elements[0] = $this->explodeContent($this->elements[$i]->elements[0]->elements[0]->text);
    					}
    				}
    				
    			} else {
    				if (isset($this->elements[$i])) {
    					$this->elements[$i]->explodeTableCells();
    				}
    			}
    		}
    	}
    }
    function explode() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (get_class($this->elements[$i]) == "Document_Paragraph") {
    				$this->elements[$i] = $this->elements[$i]->explodeContent($this->elements[$i]->text);
    			} else {
    				if (isset($this->elements[$i])) {
    					$this->elements[$i]->explode();
    				}
    			}
    		}
    	}
    }
    function searchKey($key) {
    	$values = array();
    	
    	if (get_class($this) == "Document_Paragraph") {
    		//echo "text: " . $this->text . "\n";
    		if (stripos($this->text, $key) !== false) {
    			if (trim(str_replace(":", "", $this->text)) == $key) {
    				$value = $key;
    			} else {
    				$value = null;
    			}
    	
    	
    	
    			return $value;
    		}
    	} else {
    		if (isset($this->elements)) {
    			foreach($this->elements as $element_item) {
    				if (isset($element_item)) {
    					$element_item->container = $this;
    	
    					$value = $element_item->searchKey($key);
    	
    					if ($value) return $value;
    				}
    					
    			}
    		}
    			
    	}
    	
    	return null;
    	 
    }
	function searchValueByDate($values) {
		if (get_class($this) == "Document_Paragraph") {
			$dt_explode = explode(" ", trim(html_entity_decode($this->text) . " 2014"));
			
			if (count($dt_explode) > 2) {
				if (strtotime(trim($this->text . " 2014")) > 1) {
					$value = date("Y-m-d H:i:s e O P T", strtotime(trim(html_entity_decode($this->text) . " 2014")));
					
					if ($value) {
						array_push($values, $value);
					}
					
				}
			}
			
		} else {
			if (isset($this->elements)) {
				foreach($this->elements as $element_item) {
					if (isset($element_item)) {
						$element_item->container = $this;
			
						$values = $element_item->searchValueByDate($values);
			
					}
						
				}
			}
		}
		
		return $values;
	}
	function getContaining($container_name, $content = null, $enforce_multiple = false) {
		if ($content == null) {
			if (isset($this->container)) {
				$container = $this->container;
			} else {
				return null;
			}
			
		} else {
			if (isset($content->container)) {
				$container = $content->container;
			} else {
				return null;
			}
		}
		
		
		if (($enforce_multiple == false && get_class($container) == $container_name) || ($enforce_multiple == true && get_class($container) == $container_name && count($container->elements) > 1)) {
			$containing = $container;
		} else {
			$containing = $this->getContaining($container_name, $container, $enforce_multiple);
		}
		
		return $containing;
	}
	function getElementsByName($name) {
		$elements = array();
		
		if (isset($this->elements)) {
			foreach($this->elements as $element_item) {
				if (get_class($element_item) == $name) {
					array_push($elements, $element_item);
				}
			}
		}
			
		
		
		
		return $elements;
	}
	function getFirstElementByName($name) {
		for ($i=0; $i<count($this->elements); $i++) {
			if (get_class($this->elements[$i]) == $name) {
				return $this->elements[$i];
			} else {
				return $this->elements[$i]->getFirstElementByName($name);
			}
		}
		
		return null;
	}
	function getFollowing($element) {
		if (isset($element->container->elements[$element->index + 1])) {
			return $element->container->elements[$element->index + 1];
		} else {
			return null;
		}
	}
	function getValueFromTable($table) {
		$group = $table->elements[0];
		
		$body = $group->elements[0];
		
		$rows = $body->elements;
	}
	function searchValueByPropertyEntity($propertyentity) {
		$value = null;
		
		if (get_class($this) == "Document_Paragraph") {
		    for ($opew=0; $opew<count($propertyentity->Lexeme->Words); $opew++) {
		    	//echo $propertyentity->Lexeme->Words[$opew]->name . "\n";
		    	if (stripos($this->text, $propertyentity->Lexeme->Words[$opew]->name) !== false) {
		    		//echo "text: " . $this->text . "\n";
		    		if (strtolower(trim(str_replace(":", "", $this->text))) == strtolower($propertyentity->Lexeme->Words[$opew]->name)) {
		    			
		    			$value = $this->text;
		    			
		    			//echo "value: " . $value . "\n";
		    		}
    			}
    		}
    	} else {
    		if (isset($this->elements)) {
    			foreach($this->elements as $element_item) {
    				if (isset($element_item)) {
    					$element_item->container = $this;
    	
    					$value = $element_item->searchValueByPropertyEntity($propertyentity);
    	
    					if ($value) return $value;
    				}
    	
    			}
    		}
    	
    	}
    	return $value;
	}
	function searchValueByKey($key) {
		$values = array();
		$value = null;
		
		if (get_class($this) == "Document_Paragraph") {
			if (stripos($this->text, $key) !== false) {
				if (strtolower(trim(str_replace(":", "", $this->text))) == strtolower($key)) {
					if (get_class($this->container->container) == "Document_Table_Cell") {
						$key_container_index = $this->container->container->index;
						
						$containing_row = $this->getContaining("Document_Table_Row");
						
						if (!isset($containing_row->elements[1]->elements)) {
							return null;
						}
						
						if (get_class($containing_row->elements[1]->elements[0]) != "Document_Table") {
							if (count($containing_row->elements) == 2) {
								if (isset($containing_row->elements[1]->elements[0]->text)) {
									$value = $containing_row->elements[1]->elements[0]->text;
								} else {
									$value = $containing_row->elements[1]->elements[0]->elements[0]->text;
								}
							} else if (count($containing_row->elements) == 3) {
								$following_row = $this->getFollowing($containing_row);
								if (isset($following_row)) {
									$containing_body = $this->getContaining("Document_Table_Body");
									if (isset($containing_body->elements[1]->elements[$key_container_index]->elements[0]->text)) {
										$value = $containing_body->elements[1]->elements[$key_container_index]->elements[0]->text;
									} else {
										$value = $containing_body->elements[1]->elements[$key_container_index]->elements[0]->elements[0]->text;
									}
								} else {
									$table = $containing_row->container->container->container;
									$textblock = $table->container;
									$following_textblock = $this->getFollowing($textblock);
									
									if (isset($following_textblock)) {
										$following_textblock->unsetContainers();
										
										$row = $following_textblock->elements[0]->elements[0]->elements[0]->elements[0];
										if (isset($row->elements[$key_container_index]->elements[0]->text)) {
											$value = $row->elements[$key_container_index]->elements[0]->text;
										} else {
											$value = $row->elements[$key_container_index]->elements[0]->elements[0]->text;
										}
									} else {
										$section = $textblock->container;
										$following_section = $this->getFollowing($section);
										
										if (isset($following_section)) {
											$following_section->unsetContainers();
											
											$table = $following_section->getFirstElementByName("Document_Table");
											$row = $table->getFirstElementByName("Document_Table_Row");
											if (isset($row->elements[$key_container_index]->elements[0]->text)) {
												$value = $row->elements[$key_container_index]->elements[0]->text;
											} else {
												$value = $row->elements[$key_container_index]->elements[0]->elements[0]->text;
											}
										}
											
									}
								}
								
							} else {
								$cells = $containing_row->getElementsByName("Document_Table_Cell");
								
								if (count($cells) == 2) {
									$value = $cells[1]->getTextContent();
								} else {
									$following_row = $this->getFollowing($containing_row);
									
									if (isset($following_row)) {
										if (isset($following_row->elements[$key_container_index])) {
											$value = $following_row->elements[$key_container_index]->getTextContent();
										}
									}
								}
							}
						}
					} else if (get_class($this->container->container) == "Document_Section") {
						if (count($this->container->container->container->elements) == 2) {
							$value = trim($this->container->container->container->elements[1]->getTextContent());
						} else if (count($this->container->container->elements) == 2) {
							$value = trim($this->container->container->elements[1]->getTextContent());
						} else {
							$this->container->container->unsetContainers();
						}
					}
				}
			}
		} else {
			if (isset($this->elements)) {
				foreach($this->elements as $element_item) {
					if (isset($element_item)) {
						$element_item->container = $this;
		
						$value = $element_item->searchValueByKey($key);
		
						if ($value) return $value;
					}
						
				}
			}
				
		}
		return $value;
	}
	function searchValueByKeyAndFragmentAndIndex($key, $fragment, $index) {
		$values = array();
		if (get_class($this) == "Document_Paragraph") {
			
			if (stripos($this->text, $key) !== false) {
				if (get_class($this->container->container->container) == "Document_Table_Row") {
					if (count($this->container->container->container->elements) > 6) {
						if (count($this->container->container->container->container->elements) > 10) {
							$value = $this->container->container->container->container->elements[$index]->elements[$this->container->container->index]->elements[0]->elements[0]->text;
						}
					} else {
						$value = null;
					}
				} else {
					$value = null;
				}
	
				return $value;
			}
		} else {
			if (isset($this->elements)) {
				foreach($this->elements as $element_item) {
					$element_item->container = $this;
					$value = $element_item->searchValueByKeyAndFragmentAndIndex($key, $fragment, $index);
				
					if ($value) return $value;
				}
			}
			
		}
	
		return null;
	}
	function queryXPaths($xobjects) {
		$combined_xpath = "";
		
		$copy = $xobjects;
		foreach($xobjects as $xobject) {
			$xpath_string = $xobject->xpath_self;
			if ($xobject->xpath_self_attributes_contain) {
				$xpath_string .= "[contains(@";
				foreach($xobject->xpath_self_attributes_contain as $key => $value) {
					$xpath_string .= $key . ', "' . $value . '"';
				}
				$xpath_string .= ")]";
			}
			if (isset($xobject->xpath_self_position)) {
				if ($xobject->xpath_self_position) {
					$xpath_string .= "[position() = " . $xobject->xpath_self_position . "]";
					//echo $xpath_string . "\n";
				}
			}
			
			
			$combined_xpath .= str_replace('#comment', 'comment()', str_replace('#text', 'text()', $xpath_string));
				
			if (next($copy)) $combined_xpath .= " | ";
		}
		
		$result = $this->xpath->query($combined_xpath, $this->node);
		
		return $result;
	}
	function deriveElementFromNode($node, $page, $scope = null) {
		$result_entities = array();
		
		foreach($this->object_class_names as $item_object_class_name) {
			$template_class_object = new $item_object_class_name($page);
			
			if ($template_class_object->xpath_self == $node->nodeName) {
				$class_object_entity = $template_class_object;
				array_push($result_entities, $class_object_entity);
			}
		}
		
		if (count($result_entities) > 1) {
			//echo count($result_entities) . "\n";
			foreach($result_entities as $result_entity_item) {
				foreach($result_entity_item->xpath_self_attributes_contain as $key => $value) {
					$attribute = $node->getAttribute($key);
					if (isset($attribute)) {
						if (strpos(substr($attribute, 0, 3), $value) !== false) {
							//echo $attribute . "; " . "[" . $value . "]" . "\n";
							//echo $node->nodeName . ": " . $node->nodeValue . "\n";
								
							$class_object_entity = $result_entity_item;
						}
					}
				}
				
				
			}
		}
		
		if (isset($class_object_entity)) {
			$class_object_entity->css = $this->css;
			$class_object_entity->xpath = $this->xpath;
			$class_object_entity->node = $node;
			$class_object_entity->name = $node->nodeName;
					
			if (get_class($class_object_entity) == "A_Website") {
				$class_object_entity->href = $node->getAttribute("href");
			}
			if ($node->nodeType == 1) {
				$style = $this->getStyleByClassAttributes($node->getAttribute("class"));
				
				//echo $node->nodeName . "; " . $node->nodeValue . ";" . $node->getAttribute("class") . "\n";
				if (isset($style->posX)) $class_object_entity->posX = $style->posX;
				if (isset($style->posY)) $class_object_entity->posY = $style->posY;
				if (isset($style->width)) $class_object_entity->width = $style->width;
						
			}
			
			$class_object_entity->value = $node->nodeValue;
			
		} else {
			return null;
		}
		
		
		return $class_object_entity;
	}
	function processDOMElement($page = null) {
		$combined_objects = array();
			
		foreach($this->object_class_names as $item_object_class_name) {
			//echo "class.element.name. " . $item_object_class_name . "; page: " . $page . "\n";
			$template_class_object = new $item_object_class_name($page);
			if (isset($template_class_object->xpath_self_position) != null) {
				$template_class_object->xpath_self_position = $page;
			}
			
			array_push($combined_objects, $template_class_object);
		}
		
		
		$class_object_entity_nodes = $this->queryXPaths($combined_objects);
		
		if (!$class_object_entity_nodes) {
			return null;
		}
		
		foreach($class_object_entity_nodes as $item_class_object_entity_node) {
			$class_object_entity = $this->deriveElementFromNode($item_class_object_entity_node, $page);
			
			
			if ($class_object_entity) {
				$class_object_entity->processDOMElement($page);
				
				
				if ($class_object_entity->suppress != true) {
					$class_object_entity = $this->unsetAttributes($class_object_entity);
					array_push($this->elements, $class_object_entity);
				}
			}
		}
    }
    function hasOnlyOneParagraphOrEmpty() {
    	if (!isset($this->elements)) return true;
    	
    	if (count($this->elements) == 1) {
    		if (get_class($this->elements[0]) == "Document_Paragraph") {
    			return true;
    		} else if (get_class($this->elements[0]) == "Document_Textblock") {
    			if (count($this->elements[0]->elements) == 1) {
    				if (get_class($this->elements[0]->elements[0]) == "Document_Paragraph") {
    					return true;
    				}
    			}
    		} else if (get_class($this->elements[0]) == "Document_Section") {
    			if (isset($this->elements[0]->elements)) {
    				if (count($this->elements[0]->elements) == 1) {
    					if (get_class($this->elements[0]->elements[0]) == "Document_Textblock") {
    						if (get_class($this->elements[0]->elements[0]->elements[0]) == "Document_Paragraph") {
    							return true;
    						}
    					}
    				}
    			} else {
    				return true;
    			}
    			
    		}
    	}
    	return false;
    }
    function returnOnlyOneParagraphOrEmptyText() {
    	if (!isset($this->elements)) return "";
    	 
    	if (count($this->elements) == 1 && get_class($this->elements[0]) == "Document_Paragraph") return $this->elements[0]->text;
    	 
    	return "";
    }
    function transformTextsToSectionsWhereNecessary() {
    	$transformed = array();
    	
    	for ($i=0; $i<count($this->elements); $i++) {
    		if ($this->elements[$i]->hasOnlyText() && $this->elements[$i]->getTextContent() == " " && $this->elements[$i]->width > 20) {
				$section_before = new Document_Section();
				for ($j=0; $j<$i; $j++) {
					array_push($section_before->elements, $this->elements[$j]);
				}
				
				$section_after = new Document_Section();
				$section_after->posX = $this->elements[$i]->width;
				
				for ($j=$i+1; $j<count($this->elements); $j++) {
					array_push($section_after->elements, $this->elements[$j]);
				}
				
				$this->elements = array();
				array_push($this->elements, $section_before);
    			array_push($this->elements, $section_after);
    			 
    		} else {
    			$this->elements[$i]->transformTextsToSectionsWhereNecessary();
    		}
    	
    		//array_push($merged, $this->elements[$i]);
    	}
    	 
    	//$this->elements = $merged;
    }
    function transformTextsToSectionsWhereNecessary_BasedOnPosX() {
    	$transformed = array();
		
    	for ($i=0; $i<count($this->elements); $i++) {
    		if (isset($this->elements[$i])) {
    			if ($this->elements[$i]->hasOnlyText() && ($this->elements[$i]->posX * 1) < -50) {
    				$section_before = new Document_Section();
    				for ($j=0; $j<$i; $j++) {
    					array_push($section_before->elements, $this->elements[$j]);
    				}
    			
    				$section_after = new Document_Section();
    				$section_after->posX = $this->elements[$i]->posX;
    			
    				for ($j=$i+1; $j<count($this->elements); $j++) {
    					array_push($section_after->elements, $this->elements[$j]);
    				}
    			
    				$this->elements = array();
    				array_push($this->elements, $section_before);
    				array_push($this->elements, $section_after);
    			
    			} else {
    				$this->elements[$i]->transformTextsToSectionsWhereNecessary_BasedOnPosX();
    			}
    		}
    		
    		 
    		//array_push($merged, $this->elements[$i]);
    	}
    
    	//$this->elements = $merged;
    }
    function mergeTextBlocks() {
    	$merged = array();
    	
    	for ($i=0; $i<count($this->elements); $i++) {
    		if ($this->elements[$i]->hasOnlyText()) {
    			$merged_text = $this->elements[$i]->getTextContent();
    			
    			$merged_textblock = new Document_Textblock();
    			$merged_textblock->elements[0] = new Document_Paragraph();
    			$merged_textblock->elements[0]->text = $merged_text;
    			
    			unset($this->elements[$i]->elements);
    			
    			$this->elements[$i]->elements[0] = $merged_textblock;
    		} else {
    			$this->elements[$i]->mergeTextBlocks();
    		}
    		
    		array_push($merged, $this->elements[$i]);
    	}
    	
    	$this->elements = $merged;
    }
    function areAllSameLevel_Y() {
    	$level = -999;
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->posY)) {
    				if ($level == -999) {
    					$level = $this->elements[$i]->posY;
    				} else if ($level != $this->elements[$i]->posY) {
    					return false;
    				}
    			}
    		}
    	}
    	
    	return true;
    }
    function harmonizePos_Y($level) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			$this->elements[$i]->harmonizePos_Y($level + 1);
    			
   				$allsameY = $this->areAllSameLevel_Y();
    			
    			if (isset($this->elements[$i]->posY)) {
    				if (isset($this->elements[$i]->container->posY)) {
    					if (isset($this->elements[$i]->container->container->posY)) {
    						echo $level . ":" . $this->elements[$i]->posY . " -- " . $this->elements[$i]->container->posY . " -- " . $this->elements[$i]->container->container->posY . "\n";
    					}
    				} else {
    					if (isset($this->elements[$i]->container->container->posY)) {
    						echo $level . ":" . $this->elements[$i]->posY . " -- " . $this->elements[$i]->container->posY . " -- " . $this->elements[$i]->container->container->posY . "\n";
    					}
    				}
    			}
    		}
    	}
    }
    function matchPosition($positions, $current) {
    	foreach($positions as $pos) {
    		if (abs($pos - $current) < 4) {
    			//echo "pos: " . $pos . "\n";
    			return $pos;
    		}
    	}
    	
    	return false;
    }
    function countNodes() {
    	$count = 0;
    	
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			$count++;
    			
    			$count += $this->elements[$i]->countNodes();
    		}
    	}
    	
    	return $count;
    }
    function isLowestPositioned() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->posX) && isset($this->elements[$i]->posY)) {
    				return false;
    			} else {
    				if (!$this->elements[$i]->hasOnlyOneParagraphOrEmpty()) {
    					return $this->elements[$i]->isLowestPositioned();
    				}
    				
    			}
    		}
    	}
    	
    	return true;
    }
    function getAbsolutePosition_X() {
    	if (isset($this->posX)) {
    		$absX = $this->posX;
    	} else {
    		$absX = 0;
    	}
    	
    	if (isset($this->container)) {
   			$absX = $absX + $this->container->getAbsolutePosition_X();
    	}
    	
    	return $absX;
    }
    function getAbsolutePosition_Y() {
    	if (isset($this->posY)) {
    		$absY = $this->posY;
    	} else {
    		$absY = 0;
    	}
    	
    	if (isset($this->container)) {
   			$absY = $absY + $this->container->getAbsolutePosition_Y();
    	}
    	
    	return $absY;
    }
    function getRootElement() {
   		if (isset($this->container)) {
   			$root = $this->container->getRootElement();
   		} else {
   			$root = clone $this;
   		}
    	
   		return $root;
    }
    function getDescendingPositioned() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->posX) && isset($this->elements[$i]->posY)) {
    				return $this->elements[$i];
    			} else {
    				return $this->elements[$i]->getDescendingPositioned();
    			}
    		}
    	}
    }
    function getElementByPosition($x, $y) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			$absX = $this->elements[$i]->getAbsolutePosition_X();
    			$absY = $this->elements[$i]->getAbsolutePosition_Y();
    			 
    			if ($y == $absY && $x == $absX) {
    				$this->elements[$i]->unsetContainers();
    				$result = $this->elements[$i];
    				break;
    			} else {
    				$result = false;
    			}
    			 
    			if ($result == false) {
    				$result = $this->elements[$i]->getElementByPosition($x, $y);
    			}
    			 
    		}
    	}
    	 
    	if (!isset($result)) return false;
    
    	return $result;
    }
    function simplifyCoords() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->posX) && isset($this->elements[$i]->posY)) {
    				$descending = $this->elements[$i]->getDescendingPositioned();
    				
    				echo $this->elements[$i]->posX . "/" . $this->elements[$i]->posY . "\n";

    				echo $descending->posX . "/" . $descending->posY . "\n";
    				
    				/*$this->elements[$i]->posX = $this->elements[$i]->posX + $descending->posX;
    				$this->elements[$i]->posY = $this->elements[$i]->posY + $descending->posY;
    				
    				$descending->posX = null;
    				$descending->posY = null;*/
    			} else {
    				$this->elements[$i]->simplifyCoords();
    			}
    		}
    	}
    }
    function insert_XY($x, $y, $insert) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
   				$absX = $this->elements[$i]->getAbsolutePosition_X();
   				$absY = $this->elements[$i]->getAbsolutePosition_Y();
    				
   				//echo $x . "/" . $y . " --- " . $absX . "/" . $absY . "\n";
   				if ($x == $absX && $y == $absY) {
   					array_push($this->elements, $insert);
   				} else {
   					$this->elements[$i]->insert_XY($x, $y, $insert);
   				}
    		}
    	}
    }
    function findMatchToInsert_Y($x, $y) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			$absX = $this->elements[$i]->getAbsolutePosition_X();
    			$absY = $this->elements[$i]->getAbsolutePosition_Y();
    			
    			if (abs($x - $absX) < 2 && abs($y - $absY) > 0 && abs($y - $absY) < 20) {
    				$match = $this->elements[$i];
    						
    				$result = $match;
    				break;
    				
    			} else {
    				$result = false;
    			}
    			
    			if ($result == false) {
    				$result = $this->elements[$i]->findMatchToInsert_Y($x, $y);
    			}
    			
    		}
     	}
    	
     	if (!isset($result)) return false;
     	
    	return $result;
    }
    function findMatchToInsert_X($x, $y) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			$absX = $this->elements[$i]->getAbsolutePosition_X();
    			$absY = $this->elements[$i]->getAbsolutePosition_Y();
    			 
    			if (abs($y - $absY) < 5 && abs($x - $absX) > 0 && abs($x - $absX) < 20) {
    				$match = $this->elements[$i];
    				
    				$result = $match;
    				break;
    				
    				//$result = false;
    				
    			} else {
    				$result = false;
    			}
    			 
    			if ($result == false) {
    				$result = $this->elements[$i]->findMatchToInsert_X($x, $y);
    			}
    			 
    		}
    	}
    	 
    	if (!isset($result)) return false;
    
    	return $result;
    }
    function appendAtPosition($pos, $insert) {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->posX) && isset($this->elements[$i]->posY)) {
    				$absX = $this->elements[$i]->getAbsolutePosition_X();
    				$absY = $this->elements[$i]->getAbsolutePosition_Y();
    				
    				//echo $absX . "=" . $pos->posX . "; " . $absY . "=" . $pos->posY . "\n";
    				if ($pos->posX == $absX && $pos->posY == $absY) {
    					//echo "inserted \n";
    					array_push($this->elements[$i]->elements, $insert);
    				} else {
    					$this->elements[$i]->appendAtPosition($pos, $insert);
    				}
    			} else {
    				$this->elements[$i]->appendAtPosition($pos, $insert);
    				
    			}
    				 
    		}
    	}
    	    	
    }
    function isMergerCandidate() {
    	$subnodes = $this->countNodes();
    	
    	if ((isset($this->posX) || isset($this->posY))) {
    		if (get_class($this) == "Document_Section") {
    			if ($subnodes <= 3) {
    				return true;
    			}
    		}
    	}
    	
    	return false;
    }
    function rearrangeByPosition($searchbody) {
    	$root = $this->getRootElement();
    	
    	//print_r($root);
    	
    	if (isset($this->elements)) {
    	
    		for ($i=0; $i<count($this->elements); $i++) {
				$absPosX = $this->elements[$i]->getAbsolutePosition_X();
				$absPosY = $this->elements[$i]->getAbsolutePosition_Y();
    				
				if ($this->elements[$i]->isMergerCandidate()) {
					$foundX = $searchbody->findMatchToInsert_X($absPosX, $absPosY);
					//print_r($found);
					if ($foundX !== false) {
						$merger = new Document_Merger();
						$merger->posX = $foundX->getAbsolutePosition_X();
						$merger->posY = $foundX->getAbsolutePosition_Y();
						$merger->element = $this->elements[$i];
							
						$merger->element->posX = $merger->posX - $absPosX;
						$merger->element->posY = $merger->posY - $absPosY;
						//unset($merger->element->elements);
						$merger->element->unsetContainers();
						array_push($searchbody->mergers, $merger);
							
						array_splice($this->elements, $i, 1);
					}
					
					$foundY = $searchbody->findMatchToInsert_Y($absPosX, $absPosY);
					//print_r($found);
					if ($foundY !== false) {
						$merger = new Document_Merger();
						$merger->posX = $foundY->getAbsolutePosition_X();
						$merger->posY = $foundY->getAbsolutePosition_Y();
						$merger->element = $this->elements[$i];
					
						$merger->element->posX = $merger->posX - $absPosX;
						$merger->element->posY = $merger->posY - $absPosY;
						//unset($merger->element->elements);
						$merger->element->unsetContainers();
						array_push($searchbody->mergers, $merger);
					
						array_splice($this->elements, $i, 1);
					}
				}
 					
    			
 				if (isset($this->elements[$i])) $this->elements[$i]->rearrangeByPosition($searchbody);
    		}
    	}

    	if (isset($this->mergers)) {
    		$this->mergers = $searchbody->mergers;
    		
    		for ($i=0; $i<count($this->mergers); $i++) {
    			$this->insert_XY($this->mergers[$i]->posX, $this->mergers[$i]->posY, $this->mergers[$i]->element);
    		}
    	}
    }
    function groupByPos_Y() {
    	if (isset($this->elements)) {
    		$withnopos = array();
    		
    		$tmp_sort = array();
    		$sorted = array();
    		$positions = array();
    		
    		for ($i=0; $i<count($this->elements); $i++) {
    			$this->elements[$i]->groupByPos_Y();
    			
    			
    			if (isset($this->elements[$i]->posY)) {
    				$posY = $this->elements[$i]->posY;
    				
    				$matchPos = $this->matchPosition($positions, $posY);
    				
    				array_push($positions, $posY);
    				//echo "pos: " . $this->elements[$i]->posY . "\n";
    				if ($matchPos !== false) {
    					//echo "matched: " . $matchPos . "; " . $posY . "\n";
    					
    					if ($this->elements[$i]->posX - $tmp_sort[$matchPos]->posX > 0) {
    						$this->elements[$i]->posY = null;
    						$this->elements[$i]->posX = $this->elements[$i]->posX - $tmp_sort[$matchPos]->posX;
    						
    						if (is_array($tmp_sort[$matchPos]->elements)) {
    							array_push($tmp_sort[$matchPos]->elements, $this->elements[$i]);
    						}
    					} else {
    						$tmp_sort_copy = clone $tmp_sort[$matchPos];
    						$this_element = clone $this->elements[$i];
    						
    						$tmp_sort[$matchPos] = $this_element;
    						$this->elements[$i] = $tmp_sort_copy;
    						
    						$this->elements[$i]->posY = null;
    						$this->elements[$i]->posX = $this->elements[$i]->posX - $tmp_sort[$matchPos]->posX;
    						
    						if (is_array($tmp_sort[$matchPos]->elements)) {
    							array_push($tmp_sort[$matchPos]->elements, $this->elements[$i]);
    						}
    					}
    					
    				} else {
    					//echo "not matched: " . $posY . "\n";
    					$tmp_sort[$posY] = $this->elements[$i];
    				}
    				
    			} else {
    				array_push($withnopos, $this->elements[$i]);
    			}
    		}
    		
    		krsort($tmp_sort, SORT_NUMERIC);
    		
    		$this->elements = array();
    		
    		foreach($withnopos as $position_item) {
    			array_push($this->elements, $position_item);
    		}
    		foreach($tmp_sort as $position_item) {
    			array_push($this->elements, $position_item);
    		}
    		
    	}
    }
    function sortByPos_Y() {
    	$tmp_sort_y = array();
    	$sorted_rows = array();
    
    
    	for ($i=0; $i<count($this->rows); $i++) {
    		$this->rows[$i]->sortColumnsByPositions();
    
    		$tmp_sort_y[$this->rows[$i]->position * 10] = $this->rows[$i];
    	}
    
    	krsort($tmp_sort_y, SORT_NUMERIC);
    
    	$i_y=0;
    	foreach($tmp_sort_y as $position => $position_item) {
    		$sorted_rows[$i_y] = $position_item;
    		$sorted_rows[$i_y]->position = $sorted_rows[$i_y]->position;
    
    		$i_y++;
    	}
    
    	$this->rows = $sorted_rows;
    }
    function removeEmptyElements() {
    	$cleaned = array();
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i])) $this->elements[$i]->removeEmptyElements();
    			
    			if (count($this->elements[$i]->elements) > 0) {
    				if (count($this->elements[$i]->elements) == 1) {
    					if (get_class($this->elements[$i]->elements[0]) == "Document_Paragraph") {
    						if ($this->elements[$i]->elements[0]->text != "") {
    							array_push($cleaned, $this->elements[$i]);
    						}
    					} else {
    						array_push($cleaned, $this->elements[$i]);
    					}
    				} else {
    					array_push($cleaned, $this->elements[$i]);
    				}
    			} else if (get_class($this->elements[$i]) == "Document_Paragraph") {
    				if ($this->elements[$i]->text != "") {
    					array_push($cleaned, $this->elements[$i]);
    				}
    			}
    		}
    	}
    	
    	if (count($cleaned) == 0) {
    		unset($this->elements);
    	} else {
    		$this->elements = $cleaned;
    	}
    }
    function updateWhiteSpaceElements() {
    	$cleaned = array();
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i])) {
    				$this->elements[$i]->updateWhiteSpaceElements();
    			}
    
    			 
    			if (get_class($this->elements[$i]) == "Document_Textblock" && !isset($this->elements[$i]->elements)) {
    				if (!isset($this->elements[$i]->text) && $this->elements[$i]->width > 1 && $i == 0) {
    					$para = new Document_Paragraph();
    					$para->text = " ";
    					$this->elements[$i]->elements[0] = $para;
    					$this->elements[$i]->clean();
    					
    					array_push($cleaned, $this->elements[$i]);
    				} else {
    					array_push($cleaned, $this->elements[$i]);
    				}
    			} else {
    				array_push($cleaned, $this->elements[$i]);
    			}
    		}
    	}
    
    	if (count($cleaned) == 0) {
    		unset($this->elements);
    	} else {
    		$this->elements = $cleaned;
    	}
    }
    function trimTextBlocks() {
    	 
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->text)) {
    				$this->elements[$i]->text = trim($this->elements[$i]->text, " \t\n\r\0\x0B\:");
    			}
    			 
    			$this->elements[$i]->trimTextBlocks();
    
    		}
    	}
    }
    function removeDoubleSpaces() {
    
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->text)) {
    				$this->elements[$i]->text = str_replace("  ", " ", $this->elements[$i]->text);
    			}
    
    			$this->elements[$i]->removeDoubleSpaces();
    
    		}
    	}
    }
    function removeLineBreaks() {
    	
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->text)) {
    				$this->elements[$i]->text = preg_replace( "/\r|\n/", "", $this->elements[$i]->text );
    			}
    	
    			 $this->elements[$i]->removeLineBreaks();
    			 
    		}
    	}
    }
    function removeDoublePoints() {
    	 
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->text)) {
    				$this->elements[$i]->text = rtrim($this->elements[$i]->text, ":");
    			}
    			 
    			$this->elements[$i]->removeDoublePoints();
    
    		}
    	}
    }
    function removeWhiteSpaceElements() {
    	$cleaned = array();
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			if ($this->elements[$i]->getTextContent() != " ") {
    				array_push($cleaned, $this->elements[$i]);
    			}
    			
    			if (isset($this->elements[$i])) {
    				$this->elements[$i]->removeWhiteSpaceElements();
    			}
    			 
    			
    			
    		}
    	}
    	 
    	if (count($cleaned) == 0) {
    		unset($this->elements);
    	} else {
    		$this->elements = $cleaned;
    	}
    }
    function simplify() {
    	if (isset($this->elements)) {
    		for ($i=0; $i<count($this->elements); $i++) {
    			 if (isset($this->elements[$i]->elements)) {
    				if (count($this->elements[$i]->elements) == 1) {
    					if (get_class($this->elements[$i]) == get_class($this->elements[$i]->elements[0])) {
    						//echo "yes\n";
    						if ($this->elements[$i]->elements[0]->posX == 0 && $this->elements[$i]->elements[0]->posY == 0) {
								$this->elements[$i]->elements[0]->posX = $this->elements[$i]->posX;
    							$this->elements[$i]->elements[0]->posY = $this->elements[$i]->posY;
    							if (isset($this->elements[$i]->width)) $this->elements[$i]->elements[0]->width = $this->elements[$i]->width;
    							
    							$this->elements[$i] = $this->elements[$i]->elements[0];
    						}
    						
    					}
    				} else if ($this->elements[$i]->hasOnlyOfSame()) {
    					//echo "i: " . $i . "\n";
    					if (isset($this->elements[$i]->posY)) {
    						if ($this->elements[$i]->posY == 0) {
    							array_splice($this->elements, $i, 1, $this->elements[$i]->elements);
    						}
    					} else {
    						array_splice($this->elements, $i, 1, $this->elements[$i]->elements);
    					}
    					
    				} else {
    					$this->elements[$i]->simplify();
    				}
    			}
    		}
    	}
    }
    function unsetAttributes($entity) {
    	unset($entity->object_class_names);
    	unset($entity->object_attribute_names);
    	//unset($entity->node);
    	//unset($entity->css);
    	unset($entity->xpath);
    	unset($entity->xpath_self);
    	unset($entity->xpath_self_attributes_contain);
    	unset($entity->xpath_self_attributes_equal);
    	unset($entity->xpath_self_position);
    	//unset($entity->html_tag);
    	unset($entity->html_attributes);
    	unset($entity->container);
    	unset($entity->position);
    	//unset($entity->style);
    	unset($entity->document);
    	unset($entity->Type);
    	unset($entity->name);
    	unset($entity->page);
    	unset($entity->suppress);
    	 
    	if (get_class($entity) != "Text_Website_Content") {
    		unset($entity->value);
    	}
    	if (count($entity->elements) == 0) {
    		unset($entity->elements);
    	}
    	
    	if (isset($entity->elements)) {
    		$unsuppressed = array();
    		foreach($entity->elements as $element) {
    			if (count(get_object_vars($element)) > 0) {
    				array_push($unsuppressed, $element);
    			}
    		}
			if (count($unsuppressed) > 0) $entity->elements = $unsuppressed;
    	}
    	
    	
    	return $entity;
    }
    function processList($element_website) {
    	$textblock = new Document_Textblock();
    	
    		$list = new Document_List();
    		$list->elements = $element_website->processHTMLElements();
    	
    	$textblock->elements[0] = $list;
    	
    	return $textblock;
    }
    function processListItem($element_website) {
    	$listitem = new Document_List_Item();
    	 
     	$listitem->elements = $element_website->processHTMLElements();
    	 
    	return $listitem;
    }
    function processTextBlock($element_website) {
    	//echo get_class($element_website) . "\n";
    	$textblock = new Document_Textblock();
    	 
    	$textblock->elements = $element_website->processHTMLElements();
    	
    	return $textblock;
    }
    function hasOnlyText() {
    	if (isset($this->elements)) {
	    	foreach($this->elements as $element_item) {
	    		if (!$element_item->hasOnlyText()) return false;
	    		if (get_class($element_item) != "Document_Textblock" && get_class($element_item) != "Document_Paragraph") {
	    			return false;
	    		}
	    	}
    	}
    	
    	return true;
    }
    function hasOnlyOfSame() {
    	if (isset($this->elements)) {
    		foreach($this->elements as $element_item) {
    			if (get_class($element_item) != get_class($this)) {
    				return false;
    			}
    		}
    	} else {
    		return false;
    	}
    	
    	
    	return true;
    }
    function hasOnlyTextBlocks() {
    	if (isset($this->elements)) {
    		foreach($this->elements as $element_item) {
    			if (get_class($element_item) != "Document_Textblock") {
    				return false;
    			}
    		}
    	} else {
    		return false;
    	}
    	
    	
    	return true;
    }
    function hasOnlyParagraphs() {
    	if (isset($this->elements)) {
	    	foreach($this->elements as $element_item) {
	    		if (get_class($element_item) != "Document_Paragraph") {
	    			return false;
	    		}
	    	}
    	} else {
    		return false;
    	}
    	
    	return true;
    }
    function getSuperOrdinatedFragmentContainer($pre_count = -99) {
    	if (isset($this->container)) {
    		$fragments = $this->container->getFragments();
    		
    		if (count($fragments) <= $pre_count || count($fragments) < 2) {
    			$cont = $this->container->getSuperOrdinatedFragmentContainer($pre_count);
    		} else {
    			$cont = $this->container;
    		}
     		return $cont;
    		
    	}
    }
    function removeSelfFromFragments($self, $fragments) {
    	$cleaned = array();
    	
    	for ($i=0; $i<count($fragments); $i++) {
    		if (spl_object_hash($fragments[$i]->element) != spl_object_hash($self)) {
    			array_push($cleaned, $fragments[$i]);
    		}
    	}
    	
    	return $cleaned;
    }
    function getContainingFragmentIndex($fragments) {
    	for ($i=0; $i<count($fragments); $i++) {
    		if ($this->text == $fragments[$i]->text) {
    			return $i;
    		}
    	}
    	
    	return null;
    }
    function getFollowingFragmentContainer($super_fragment_container) {
    	$super_fragments = $super_fragment_container->getFragments();
    	
    	$following_super_fragment_container = $this->getFollowing($super_fragment_container);
    	if (isset($following_super_fragment_container)) {
    		$following_super_fragments = $following_super_fragment_container->getFragments();
    		
    		if (count($super_fragment_container->elements) == count($following_super_fragment_container->elements)) {
    			return $following_super_fragment_container;
    		} else {
    			$following_super_fragment_container = $this->getFollowing($following_super_fragment_container);
    			
    			if (isset($following_super_fragment_container)) {
    				$following_super_fragments = $following_super_fragment_container->getFragments();
    				
    				if (count($super_fragment_container->elements) == count($following_super_fragment_container->elements)) {
    					return $following_super_fragment_container;
    				}
    			}
    			
    		}
    	}
    	
    	
    	return null;
    }
    function getSiblingFromTable($keyCellElement, $tableElement) {
    	$sibling = null;
    	
    	if ($keyCellElement) {
    		$containing_cell = 	$keyCellElement;
    		$containing_row =	$keyCellElement->getContaining("Document_Table_Row");
    		
    		$containing_row_cells = $containing_row->getElementsByName("Document_Table_Cell");
    		 
       	}
    	
    	if (isset($tableElement)) {
    		$contained_rows = $tableElement->getElementsByName("Document_Table_Row");
    	}
    		
    	$keyRowIndex = $containing_row->index;
    	$keyColumnIndex = $containing_cell->index;
    	
    	
    	//echo "contained-rows: " . count($contained_rows) . "; " . " containing-row-cells: " . count($containing_row_cells) . "\n";
    	switch (count($containing_row_cells)) {
    		case 2:
    			//print_r($containing_row_cells[1]);
    			$sibling = new Fragment();
    			$sibling->text  = trim(preg_replace("/ {2,}/", " ", preg_replace( "/\r|\n/", "", preg_replace('/\s+\t+/', '', $containing_row_cells[1]->getTextContent()))), " \:\n\r");
    			 
    			
    			break;
    		case 3:
    			switch (count($contained_rows)) {
    				case 1:
    					$parent_section = $tableElement->getContaining("Document_Section");
    					$parent_parent_section = $parent_section->getContaining("Document_Section");
    						
    					if (isset($parent_parent_section)) {
    						$containing_sections = $parent_parent_section->getElementsByName("Document_Section");
    						//echo "count-sections: " . count($containing_sections) . "\n";
    						
    						//print_r($containing_sections[1]);
    						
    						$containing_parent_row_cells = $containing_sections[1]->getFirstElementByName("Document_Table_Body");
    						//print_r($containing_parent_row_cells);
    						if ($containing_parent_row_cells) {
    							$content_table_rows = $containing_parent_row_cells->getElementsByName("Document_Table_Row");
    						
    							switch (count($content_table_rows)) {
    								case 1:
    									$containing_content_cells = $content_table_rows[0]->getElementsByName("Document_Table_Cell");
    									 
    									if (count($containing_content_cells) == count($containing_row_cells)) {
    										for ($i=0; $i<count($containing_content_cells); $i++) {
    											if ($keyColumnIndex == $containing_content_cells[$i]->index) {
    												$content_cell = $containing_content_cells[$i];
    											}
    										}
    										 
    										$sibling = new Fragment();
    										$sibling->text  = trim(preg_replace("/ {2,}/", " ", preg_replace( "/\r|\n/", "", preg_replace('/\s+\t+/', '', $content_cell->getTextContent()))), " \:\n\r");
    									}
    							
    									break;
    								default:
    									break;
    							}
    						}
	    					
    						
    					}
    					
    					
    					
    					break;
    				default:
    					break;
    			}
    			
    			break;
    		default:
    			switch (count($contained_rows)) {
    				case 1:
    					break;
    				case 2:
    					break;
    				case 3:
    					switch ($keyRowIndex) {
    						case 0:
    					   
    							break;
    						case 1:
    							$contained_cells = $contained_rows[2]->getElementsByName("Document_Table_Cell");
    					   
    							for ($i=0; $i<count($contained_cells); $i++) {
    								if ($keyColumnIndex == $contained_cells[$i]->index) $content_cell = $contained_cells[$i];
    							}
    					   
    							$sibling = new Fragment();
    							$sibling->text  = trim(preg_replace("/ {2,}/", " ", preg_replace( "/\r|\n/", "", preg_replace('/\s+\t+/', '', $content_cell->getTextContent()))), " \:\n\r");
    							
    							//echo $sibling->text . "\n";
    							break;
    						case 2:
    							break;
    						default:
    							break;
    					}
    					 
    					break;
    				default:
    					break;
    			}
    			
    			break;
    	}
    		 
    	//print_r($sibling);
    	
    	return $sibling;
    }
    function getSiblingFragments() {
    	$super_fragment_container = $this->getSuperOrdinatedFragmentContainer();
    	if (isset($super_fragment_container)) {
    		$super_fragments = $super_fragment_container->getFragments();
    		$super_super_fragment_container = $super_fragment_container->getSuperOrdinatedFragmentContainer(count($super_fragments));
    		if (isset($super_super_fragment_container)) {
    			$super_super_fragments = $super_super_fragment_container->getFragments();
    		}
    	}
    	   	
    	$siblings_withtext = array();
    	
    	//echo "class: " . get_class($super_fragment_container) . "\n";
    	if (get_class($super_fragment_container) == "Document_Table_Row") {
    		$containing_table_body = $super_fragment_container->getContaining("Document_Table_Body");
    		if (!isset($containing_table_body)) {
    			$containing_table_body = $super_fragment_container->getContaining("Document_Table_Group");
    		}
    		
    		$containing_cell = $this->getContaining("Document_Table_Cell");
    		$containing_row = $this->getContaining("Document_Table_Row");
    		
    		$sibling = $this->getSiblingFromTable($containing_cell, $containing_table_body);
    		
    		if ($sibling) {
    			array_push($siblings_withtext, $sibling);
    		}
    	} else if (get_class($super_fragment_container) == "Document_Table_Cell") {
    		$containing_table_body = $super_fragment_container->getContaining("Document_Table_Body");
    		if (!isset($containing_table_body)) {
    			$containing_table_body = $super_fragment_container->getContaining("Document_Table_Group");
    		}
    		
    		$containing_cell = $this->getContaining("Document_Table_Cell");
    		$containing_row = $this->getContaining("Document_Table_Row");
    		
    		$sibling = $this->getSiblingFromTable($containing_cell, $containing_table_body);
    		
    		if ($sibling) {
    			array_push($siblings_withtext, $sibling);
    		}
    	} else if (get_class($super_fragment_container) == "Document_Section") {
    		if (count($super_fragments) > 1) {
    			$super_fragments_without_self = $this->removeSelfFromFragments($this, $super_fragments);
    			
    			if (count($super_fragments_without_self) == 1) {
    				array_push($siblings_withtext, $super_fragments_without_self[0]);
    			} else if (count($super_fragments_without_self) == 2) {
    				array_push($siblings_withtext, $super_fragments_without_self[1]);
    			} else {
    				if (count($super_fragments) == 6) {
    					$containing_fragment_index = $this->getContainingFragmentIndex($super_fragments);

    					array_push($siblings_withtext, $super_fragments[3 + $containing_fragment_index]);
    				} else if (count($super_fragments) > 10) {
    					$containing_fragment_index = $this->getContainingFragmentIndex($super_fragments);
    					
    					$followup_container = $this->getFollowingFragmentContainer($super_fragment_container);
    					
    					if (isset($followup_container)) {
    						$following_super_fragments = $followup_container->getFragments();
    						
    						if (isset($following_super_fragments[$containing_fragment_index])) {
    							array_push($siblings_withtext, $following_super_fragments[$containing_fragment_index]);
    						}
    						
    					}
    				} else {
    					foreach($super_fragments_without_self as $fragment_item) {
    						array_push($siblings_withtext, $fragment_item);
    					}
    				}
    				
    				
    				
    			}
    		}
    	} else if (get_class($super_fragment_container) == "Document_Textblock") {
    		$containing_row = $this->getContaining("Document_Table_Row");
    		$containing_cell = $this->getContaining("Document_Table_Cell");
    		$containing_section = $this->getContaining("Document_Section");
    		
    		//print_r($containing_section);
    		
    		if (isset($containing_cell)) {
    			$containing_fragment_index = $this->getContainingFragmentIndex($containing_cell->getFragments());
    		}
    		if (isset($containing_row)) {
    			$containing_table_body = $containing_row->getContaining("Document_Table_Body");
    		}
    		if (isset($containing_table_body)) {
    			$contained_rows = $containing_table_body->getElementsByName("Document_Table_Row");
    		}
    		
    		//echo "cont-frag-index: " . $containing_fragment_index . "\n";
    		if ($containing_row->index == count($contained_rows) - 2) {
     			$value_row = $contained_rows[$containing_row->index + 1];
     			
     			$cont_fragments = $value_row->getFragments();
     			 
    			$contained_cells = $value_row->getElementsByName("Document_Table_Cell");
    			
    			$content_cell = $contained_cells[$containing_cell->index-1];
    			
    			$cont_fragments = $content_cell->getFragments();
    			 
    			
    			if (trim($cont_fragments[0]->text) == "") {
    				//print_r($cont_fragments[1]);
    				unset($cont_fragments[1]->element);
    				array_push($siblings_withtext, $cont_fragments[1]);
    			} else {
    				//print_r($cont_fragments[0]);
    				unset($cont_fragments[0]->element);
    				array_push($siblings_withtext, $cont_fragments[0]);
    			}
    		}
    	}
    	
    	return $siblings_withtext;
    }
    function getLinks() {
    	$links = array();
    
    	for ($i=0; $i<count($this->elements); $i++) {
    		if (get_class($this->elements[$i]) == "Document_URL") {
    			array_push($links, $this->elements[$i]);
    		}
    		
    		$links = array_merge($links, $this->elements[$i]->getLinks());
    	}
    
    	return $links;
    }
    function getFragments($excluding_self = false) {
    	$fragments = array();
    	
    	if (isset($this->elements)) {
     		for ($i=0; $i<count($this->elements); $i++) {
    			if (isset($this->elements[$i]->text)) {
    				if ($this->elements[$i]->text != "") {
    					array_push($fragments, new Fragment($this->elements[$i]));
    				}
    			}
    			
    			if (isset($this->elements[$i])) {
    				$sub_fragments = $this->elements[$i]->getFragments();
    				if (isset($sub_fragments[0])) $fragments = array_merge($fragments, $sub_fragments);
    			}
    			
    		}
    	}
    	
    	if ($excluding_self == true) {
    		$fragments_ex_self = array();
    		
    		for ($i=0; $i<count($fragments); $i++) {
    			if (spl_object_hash($fragments[$i]->element) != spl_object_hash($this)) {
    				array_push($fragments_ex_self, $fragments[$i]);
    			}
    			
    		}
    		
    		$fragments = $fragments_ex_self;
    	}
    	
    	return $fragments;
    }
	function processSpan($element_website) {
    	$textblock = $this->processTextBlock($element_website);
    	
    	$textblock->width = $element_website->width;
    	
    	if (isset($element_website->posX)) {
    		$section = new Document_Section();
    		$section->posX = $element_website->posX;
    		
    		$section->elements = $element_website->processHTMLElements();
    		
    		return $section;
    	}
    	 
    	return $textblock;
    }
    function processSection($element_website) {
    	//echo "proc. posX: " . $element_website->posX . "\n";
    	$section = new Document_Section();
    	$section->posX = $element_website->posX;
    	$section->posY = $element_website->posY;
    	 
    	$section->elements = $element_website->processHTMLElements();
    	
    	return $section;
    }
    function processTable($element_website) {
    	$table = new Document_Table();

    		$tablegroup = new Document_Table_Group();
    	 
    		$tablegroup->elements = $element_website->processHTMLElements();
    		
    	$table->elements[0] = $tablegroup;
    		 
    	return $table;
    }
    function processTableHead($element_website) {
    	$tablehead = new Document_Table_Head();
    
    	$tablehead->elements = $element_website->processHTMLElements();
    
    	return $tablehead;
    }
	function processTableFoot($element_website) {
    	$tablefoot = new Document_Table_Foot();
    
    	$tablefoot->elements = $element_website->processHTMLElements();
    
    	return $tablefoot;
    }
    function processTableBody($element_website) {
    	$tablebody = new Document_Table_Body();
    
    	$tablebody->elements = $element_website->processHTMLElements();
    
    	return $tablebody;
    }
    function processTableRow($element_website) {
    	$row = new Document_Table_Row();
    
    	$row->elements = $element_website->processHTMLElements();
    
    	return $row;
    }
    function processBody($element_website) {
    	$cell = new Document_Body();
    
    	$cell->elements = $element_website->processHTMLElements();
    
    	return $cell;
    }
    function processTableCell($element_website) {
    	$cell = new Document_Table_Cell();
    
    	$cell->elements = $element_website->processHTMLElements();
    
    	return $cell;
    }
    function processTableHeadCell($element_website) {
    	$cell = new Document_Table_HeadCell();
    
    	$cell->elements = $element_website->processHTMLElements();
    
    	return $cell;
    }
    function processStyle($element_website) {
    	$style = new Document_Style();
    	$style->text = $element_website->node->nodeValue;
    	
    	return $style;
    }
    function processLink($element_website) {
    	$textblock = new Document_Textblock();
    	 
    	$paragraph = new Document_Paragraph();
    	$paragraph->text = trim(preg_replace('/\t+/', '', $element_website->node->textContent), " \:\n\r");
    	
    	$textblock->elements[0] = $paragraph;
    	
    	return $textblock;
    }
    function processText($element_website) {
    	$textblock = new Document_Textblock();
    	
    		$paragraph = new Document_Paragraph();
    		//echo $element_website->value . "\n";
    		$paragraph->text = $element_website->value;
    	
    	$textblock->elements[0] = $paragraph;

    	return $textblock;
    }
    function processSectionTitle($element_website) {
    	$sectiontitle = new Document_Section_Title();
    	
    	$sectiontitle->elements = $element_website->processHTMLElements();
    	 
        if ($sectiontitle->hasOnlyTextBlocks() == true) {
        	if (count($sectiontitle->elements) == 1) {
    			$sectiontitle_textblock = $sectiontitle->elements[0];
    			
    			if (isset($sectiontitle_textblock->elements[0]->text)) {
    				$sectiontitle->text = $sectiontitle_textblock->elements[0]->text;
    				unset($sectiontitle->elements);
    			}
    			
    		}
    		
    	}

    	return $sectiontitle;
    }
    function processHTMLElements() {
		$processed_elements = array();
		
		if (isset($this->elements)) {
			for ($i=0; $i<count($this->elements); $i++) {
				if (get_class($this->elements[$i]) == "Cite_Website" || get_class($this->elements[$i]) == "Font_Website" || get_class($this->elements[$i]) == "Abbr_Website" || get_class($this->elements[$i]) == "Small_Website" || get_class($this->elements[$i]) == "Caption_Website" || get_class($this->elements[$i]) == "BlockQuote_Website" || get_class($this->elements[$i]) == "Center_Website" || get_class($this->elements[$i]) == "Sall_Website" || get_class($this->elements[$i]) == "I_Website" || get_class($this->elements[$i]) == "Em_Website" || get_class($this->elements[$i]) == "Label_Website" || get_class($this->elements[$i]) == "U_Website" || get_class($this->elements[$i]) == "Sup_Website" || get_class($this->elements[$i]) == "Pre_Website" || get_class($this->elements[$i]) == "P_Website" || get_class($this->elements[$i]) == "Strong_Website" || get_class($this->elements[$i]) == "B_Website" || get_class($this->elements[$i]) == "Nav_Website") {
					$website_element = $this->processTextBlock($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "Div_Website" || get_parent_class($this->elements[$i]) == "Div_Website"|| get_class($this->elements[$i]) == "Section_Website" || get_class($this->elements[$i]) == "Header_Website" || get_class($this->elements[$i]) == "Footer_Website" || get_class($this->elements[$i]) == "IFrame_Website") {
					$website_element = $this->processSection($this->elements[$i]);
					array_push($processed_elements, $website_element);
						
				/*} else if (get_class($this->elements[$i]) == "A_Website") {
					$website_element = $this->processURL($this->elements[$i]);
					array_push($processed_elements, $website_element);
						*/
				} else if (get_class($this->elements[$i]) == "A_Website") {
					$website_element = $this->processLink($this->elements[$i]);
					array_push($processed_elements, $website_element);
						
				} else if (get_class($this->elements[$i]) == "Span_ConvertedPdf_Content" || get_class($this->elements[$i]) == "Span_Website") {
					$website_element = $this->processSpan($this->elements[$i]);
					array_push($processed_elements, $website_element);
						
				} else if (get_class($this->elements[$i]) == "Text_Website_Content") {
					$website_element = $this->processText($this->elements[$i]);
					array_push($processed_elements, $website_element);
						
				} else if (get_class($this->elements[$i]) == "H1_Website" || get_class($this->elements[$i]) == "H2_Website" || get_class($this->elements[$i]) == "H3_Website" || get_class($this->elements[$i]) == "H4_Website" || get_class($this->elements[$i]) == "H5_Website" || get_class($this->elements[$i]) == "H6_Website") {
					$website_element = $this->processSectionTitle($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "Ul_Website" || get_class($this->elements[$i]) == "Ol_Website" || get_class($this->elements[$i]) == "Dl_Website") {
					$website_element = $this->processList($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "Li_Website" || get_class($this->elements[$i]) == "Dd_Website") {
					$website_element = $this->processListItem($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "Table_Website") {
					$website_element = $this->processTable($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "THead_Website") {
					$website_element = $this->processTableHead($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "TFoot_Website") {
					$website_element = $this->processTableFoot($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "TBody_Website") {
					$website_element = $this->processTableBody($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "TR_Website") {
					$website_element = $this->processTableRow($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "TH_Website") {
					$website_element = $this->processTableHeadCell($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "TD_Website") {
					$website_element = $this->processTableCell($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "ColdFusion_Website") {
					$website_element = $this->processBody($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else if (get_class($this->elements[$i]) == "Style_Website") {
					$website_element = $this->processStyle($this->elements[$i]);
					array_push($processed_elements, $website_element);
				} else {
					//echo get_class($this->elements[$i]) . "\n";
				}
				
			}
		}
		
		return $processed_elements;
    }
    function getStyleByClassAttributes($class_attributes) {
		$class_attributes = str_replace("_ ", "", $class_attributes);

		//echo $class_attributes . "\n";
		
		$style = new Style();
		$style->class = $class_attributes;
		
		$class_explode = explode(" ", $class_attributes);
		if (count($class_explode) <= 1) {
			$css_values = $this->getCSSValueByKey($class_attributes);
			if (isset($css_values)) {
				$keys = array_keys($css_values);
				$values = array_values($css_values);
				foreach($keys as $key=>$value) {
					if ($value == ".width") {
						$style->width = $values[$key];
					} else {
						$style->$value= $values[$key];
					}
				}
				
				
				$marginleft = "margin-left";
				
				if (isset($style->width)) $this->width = $style->width;
				
				if (isset($style->$marginleft)) $style->posX = $style->$marginleft;
			}
		} else {
			for ($j=1; $j<count($class_explode); $j++) {
				$type = substr($class_explode[$j], 0, 1);
				$type2 = substr($class_explode[$j], 0, 2);
					
				if (($type == "x" || $type == "y" || $type == "w" || $type == "h" || $type2 == "fs" || $type2 == "fc") && ($type2 != "ws")) {
					$keys_array = $this->getCSSValueByKey($class_explode[$j]);
			
					if ($keys_array != null) {
						$key = array_keys($this->getCSSValueByKey($class_explode[$j]));
						$values = array_values($this->getCSSValueByKey($class_explode[$j]));
					}
				}
					
				if (isset($key)) {
					if ($type == "x") {
						$style->posX = $values[0];
						$style->$key[0] = $values[0];
						//echo "x: " . $style->posX . " of " . get_class($this) . "\n";
					} else if ($type == "w" && $type2 != "ws") {
						$style->$key[0] = $values[0];
						//$this->width = $values[0];
							
						//echo "width: " . $this->width . " of " . get_class($this) . "\n";
					} else if ($type == "y") {
						$style->posY = $values[0];
						//$this->posY = $values[0];
						//echo "posY: " . $this->posY . "\n";
						$style->$key[0] = $values[0];
					} else if ($type2 == "fs") {
						$style->$key[0] = $values[0];
					} else if ($type2 == "fc") {
						$style->$key[0] = $values[0];
					}
				}
			}
		}
		
		
		
		
		//print_r($this->website_css->website_css_attributes);
		
		return $style;
	}
    function getCSSValueByKey($key) {
		if (isset($this->css->website_css_attributes[$key])) {
			return str_replace("px", "", str_replace("pt", "", $this->css->website_css_attributes[$key]));
		} else {
			return null;
		}
	}
    function processNode($node) {
		foreach ($node->childNodes as $element) {
			if ($element->nodeType == 1) {
				if (in_array($element->nodeName, $this->allowedChildren)) {
					$obj = new $element->nodeName;
					array_push($this->children, $obj);
					
					//echo $element->nodeType . "; " . $element->nodeName . "\n";
				} else {
					//echo "missed node name: " . $element->nodeName . "\n";
				}
			}
			
		}
	}
	function setChildren() {
		
	}
	function getText() {
		$text = "";
		 
		for ($i=0; $i<count($this->elements); $i++) {
			if (isset($this->elements[$i]->text)) {
				if ($this->elements[$i]->text != "") {
					$text .= $this->elements[$i]->text . " ";
				}
			}
			
			$text .= $this->elements[$i]->getText();
		}
		 
		return $text;
	}
	function getTextContent() {
		$textContent = "";
		
		if (isset($this->elements)) {
			foreach ($this->elements as $element) {
				if (isset($element->text)) {
					$textContent .= $element->text;
				} else {
					if (isset($element)) $textContent .= $element->getTextContent();
				}
			}
		}
		 
		return $textContent;
	}
	function clean() {
		unset($this->xpath);
		unset($this->xpath_self);
		unset($this->node);
		unset($this->css);
		unset($this->object_class_names);
		unset($this->object_attribute_names);
		unset($this->xpath_self_position);
		unset($this->xpath_self_attributes_contain);
		unset($this->xpath_self_attributes_equal);
		unset($this->suppress);
		unset($this->processed_rows);
		unset($this->name);
		unset($this->value);
		unset($this->position);
		unset($this->style);
		//unset($this->html_tag);
		//unset($this->html_attributes);
		unset($this->page);
		unset($this->container);
		
		if (isset($this->elements)) {
			for ($i=0; $i<count($this->elements); $i++) {
				if (isset($this->elements[$i])) $this->elements[$i]->clean();
			}
			
			if (count($this->elements) == 0) unset($this->elements);
		}
		
		
		
	}
	function cleanDeluxe() {
		unset($this->mergers);
		if (isset($this->posX)) unset($this->posX);
		if (isset($this->posY)) unset($this->posY);
	
		unset($this->html_tag);
		unset($this->html_attributes);
		
		if (isset($this->elements)) {
			for ($i=0; $i<count($this->elements); $i++) {
				if (isset($this->elements[$i])) $this->elements[$i]->cleanDeluxe();
			}
				
			if (count($this->elements) == 0) unset($this->elements);
		}
		
	}
	
}
class Style {
	var $class;
	var $posX;
	var $posY;
}
?>