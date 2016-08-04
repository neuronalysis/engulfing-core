<?php
trait Printer {
	
	var $debugMode;
	
	function __construct() {
		
	}
	function xprint_pick($object, $name, $pick) {
		if (is_object($object)) {
			if (get_class($object) === $name) {
				$key = array_keys($pick)[0];
				
				if (isset($object->$key)) {
					if ($object->$key === $pick[$key]) {
						echo json_encode ( $object, JSON_PRETTY_PRINT ) . "\n\n";
						//$this->xprint($object);
					}
				}
				
			}
			//$this->xprint_pick($object, $name, $pick);
			
		} else if (is_array($object)) {
			$printingArray = array();
			foreach($object as $key => $objectItem) {
				$this->xprint_pick($objectItem, $name, $pick);
				
			}
			
			
		
		
		}
	}
	
	function xprint($object, $whitelist, $conditions) {
		if ($this->debugMode) {
			if (is_object($object)) {
				$printing = $this->whitelistFields($object, $whitelist, true);
			} else if (is_array($object)) {
				$printingArray = array();
				foreach($object as $key => $objectItem) {
					array_push($printingArray, clone $objectItem);
				}
				
				$printing = $this->whitelistFields($printingArray, $whitelist, true);
				
				
			}
			
			if (is_array($object)) {
				for ($i=0; $i<count($object); $i++) {
					if (is_object($object[$i])) {
						$objectVars = get_object_vars($object[$i]);
						foreach($objectVars as $key => $value) {
							if (is_array($object[$i]->$key)) {
								if (count($object[$i]->$key) > 0) {
									//print_r($object[$i]->$key);
									$object_key_values = $object[$i]->$key;
									
									$cleaned = array();
									
									for ($j=0; $j<count($object_key_values); $j++) {
										if (get_class($object_key_values[$j]) === "Word") {
											if (isset($object_key_values[$j]->Lexeme)) {
												array_push($cleaned, $object_key_values[$j]);
												//echo $object[$i]->$key[$j]->Lexeme->name . "\n";
											}
										}
										
									}
									
									
								}
								
								$object[$i]->$key = $cleaned;
					
							}
							
							
						}
					
					}
				}
				
				$cleaned = array();
				
				for ($i=0; $i<count($object); $i++) {
					if (is_object($object[$i])) {
						$objectVars = get_object_vars($object[$i]);
						foreach($objectVars as $key => $value) {
							
							if ($key === "Words") {
								if (count($object[$i]->$key) > 0) {
									array_push($cleaned, $object[$i]);
								}
							}
						}
							
					}
				}
				
				
				$printing = $cleaned;
			}
			
			
			
			
			echo json_encode ( $printing, JSON_PRETTY_PRINT ) . "\n\n";
		}
		
		
	}
	function chechConditionals($object, $conditionals) {
		
	}
	function whitelistFields($object, $whitelist, $noNulls) {
		if (is_object($object)) {
			//echo get_class($object) . "\n";
			$objectVars = get_object_vars($object);
			foreach($objectVars as $key => $value) {
				if (!in_array($key, $whitelist) || is_null($value)) {
					unset($object->$key);
				} else {
					if (is_object($value)) {
						$value = $this->whitelistFields($value, $whitelist, $noNulls);
					} else if (is_array($value)) {
						$value = $this->whitelistFields($value, $whitelist, $noNulls);
						//print_r($value);
					} else {
						if (!in_array($key, $whitelist)) {
							unset($object->$key);
						}
					}
				}
				
				
				
			}
		} else if (is_array($object)) {
			foreach($object as $key => $objectItem) {
				$objectItem = $this->whitelistFields($objectItem, $whitelist, $noNulls);
			}
			
			//print_r($toRemove);
			
			//$object = $cleaned;
		}
		
		return $object;
	}
	function xprint_object($object, $level) {
		$out .= get_class($object) . " Object\n(\n";
		
		$level .= "    ";
		
		
		$objectVars = get_object_vars($object);
		foreach($objectVars as $key => $value) {
			if (is_object($value)) {
				$out .= print_r($value, true);
			}
		}
		
		return $out;
	}
}
?>
