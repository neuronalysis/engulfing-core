<?php
class Comparator {
	use ObjectHelper;
	
	var $results = array();
	
	//TODO improvements necessary. bad approach to save results in class-variable
	//TODO most probably not robust for general purpose (which is intended); contains app-specific code to be cleaned up.
	function compareTwoObjects($a,$b, $ignoreID = true, $convertNumericStrings = true) {
		if(is_object($a) && is_object($b)) {
			if(get_class($a)!=get_class($b))
				return false;
				foreach($a as $key => $val) {
					if(!$this->compareTwoObjects($val,$b->$key) && (!in_array($key, array("id")) || !$ignoreID)) {
						if (!is_array($val) && !is_array($b->$key)) {
							if (!is_string($key)) $key= print_r($key, true);
							if (!is_string($val)) $key= print_r($val, true);
							
							$classNameWithoutNS = $this->getNameWithoutNamespace($key);
							
							if (isset($b->$classNameWithoutNS)) {
								$bVal = $b->$classNameWithoutNS;
							}
							
							if (isset($bVal)) {
								if (!is_string($bVal)) $bVal= print_r($bVal, true);
								
								$delta = new Difference();
								$delta->key = $key;
								
								$delta->before = $val;
								$delta->after = $bVal;

								array_push($this->results, $delta);
								
								//echo "delta key " . $key . ": " . $val . "; " . $bVal. "\n";
							}
							
						} else {
							//echo "delta key " . $key . "\n";
						}
						//return false;
					}
				}
				return true;
		}
		else if(is_array($a) && is_array($b)) {
			while(!is_null(key($a)) && !is_null(key($b))) {
				if (key($a)!==key($b) || !$this->compareTwoObjects(current($a),current($b)))
					return false;
					next($a); next($b);
			}
			return is_null(key($a)) && is_null(key($b));
		}
		else
			if (!is_string($a)) $a = print_r($a, true);
			if (!is_string($b)) $b = print_r($b, true);
			
			return "" . $a === "" . $b;
	}
	
}

class Difference {
	var $key;
	var $before;
	var $after;
}
?>