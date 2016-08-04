<?php
class Website_Grid extends Document {
	
	function __construct() {
	}
	function processDOM($dom) {
		$this->body = new Document_Body();
		
		if (isset($dom->elements)) {
			if (count($dom->elements) == 1) {
				$gsection = new Website_Grid_Section();
				$gsection->processDOM($dom->elements[0]);
				
				array_push($this->body->elements, $gsection);
			} else {
				for ($i=0; $i<count($dom->elements); $i++) {
					if ($dom->elements[$i]->posY) {
						//echo get_class($dom->elements[$i]) . "\n";
					} else {
						$grow = new Website_Grid_Row();
						$grow->processDOM($dom->elements[$i]);
				
						array_push($this->body->elements, $grow);
					}
						
				}
			}
		}
		
		
		
		
		
	}
}

class Website_Grid_Section extends Document_Section {
	function __construct() {
		
	}
	function processDOM($dom, $gcontainer = null) {
		if (isset($dom->elements)) {
			if (count($dom->elements) == 1) {
				//echo get_class($dom->elements[0]) . "\n";
			} else {
				for ($i=0; $i<count($dom->elements); $i++) {
					if (get_class($dom->elements[$i]) == "Document_Textblock") {
						$gelement = new Website_Grid_Textblock();
					} else if (get_class($dom->elements[$i]) == "Document_Section") {
						if ($dom->elements[$i]->posY) {
							echo get_class($dom->elements[$i]) . "; y: " . $dom->elements[$i]->posY . "<br>";
							
							if ($gcontainer) {
							} else {
								$gcontainer = new Website_Grid_Table();
							}
							$gelement = new Website_Grid_Row();
							$gelement->processDOM($dom->elements[$i], $gcontainer);
							
							array_push($gcontainer->elements, $gelement);
							
							array_push($this->elements, $gcontainer);
							
							
						} else {
							$gelement = new Website_Grid_Section();
					
							$gelement->processDOM($dom->elements[$i]);
					
							array_push($this->elements, $gelement);
						}
					}
					
					$gelement->processDOM($dom->elements[$i]);
					
					array_push($this->elements, $gelement);
					//echo get_class($dom->elements[$i]) . "\n";
				}
			}
		}
	}
}
class Website_Grid_Textblock extends Document_Textblock {
	function __construct() {
		
	}
	function processDOM($dom, $gcontainer = null) {
		if (isset($dom->elements)) {
			for ($i=0; $i<count($dom->elements); $i++) {
				//echo get_class($dom->elements[$i]) . "\n";
				if (get_class($dom->elements[$i]) == "Document_Paragraph") {
					$gelement = new Website_Grid_Paragraph();
				} else if (get_class($dom->elements[$i]) == "Document_Textblock") {
					$gelement = new Website_Grid_Textblock();
				} else if (get_class($dom->elements[$i]) == "Document_Section") {
					if ($dom->elements[$i]->posY) {
						echo get_class($dom->elements[$i]) . "; y: " . $dom->elements[$i]->posY . "<br>";
							
						if ($gcontainer) {
						} else {
							$gcontainer = new Website_Grid_Table();
						}
						$gelement = new Website_Grid_Row();
						$gelement->processDOM($dom->elements[$i], $gcontainer);
						
						array_push($gcontainer->elements, $gelement);
						
						array_push($this->elements, $gcontainer);
						
						//echo get_class($dom->elements[$i]) . "; y: " . $dom->elements[$i]->posY . "\n";
					} else {
						/*$grow = new Website_Grid_Row();
						$grow->processDOM($dom->elements[$i]);
					
						array_push($this->body->elements, $grow);*/
					}
				}
				
				$gelement->processDOM($dom->elements[$i]);
				
				array_push($this->elements, $gelement);
				//echo get_class($dom->elements[$i]) . "\n";
			}
		}
	}
}
class Website_Grid_Paragraph extends Document_Paragraph {
	function __construct() {

	}
	function processDOM($dom) {
		if ($dom->text) {
			$this->text = $dom->text;
		}
	}
}

class Website_Grid_Table extends Document_Table {
	
}
class Website_Grid_Row extends Document_Section {
	function __construct() {
		
	}
	function processDOM($dom) {
		for ($i=0; $i<count($dom->elements); $i++) {
			//echo get_class($dom->elements[$i]) . "\n";
			if (get_class($dom->elements[$i]) == "Document_Textblock") {
				$gelement = new Website_Grid_Textblock();
			} else if (get_class($dom->elements[$i]) == "Document_Section") {
				if ($dom->elements[$i]->posY) {
					echo get_class($dom->elements[$i]) . "; y: " . $dom->elements[$i]->posY . "<br>";
							
				} else {
					$gelement = new Website_Grid_Section();
					
					$gelement->processDOM($dom->elements[$i]);
					
					array_push($this->elements, $gelement);
					/*$grow = new Website_Grid_Row();
					$grow->processDOM($dom->elements[$i]);
				
					array_push($this->body->elements, $grow);*/
				}
			}
			
			$gelement->processDOM($dom->elements[$i]);
			
			array_push($this->elements, $gelement);
				
		}
	}
}

class Website_Grid_Cell {
	
}


?>
