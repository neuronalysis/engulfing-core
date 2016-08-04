<?php
trait Helper {
	
	function Helper() {
	}
	function isLocalRequest() {
		$whitelist = array(
				'127.0.0.1',
				'::1'
		);
			
		if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
			return true;
		}
			
		return false;
	}
	function pluralize($singular) {
		if ($singular === "corpus") return "corpora";
		if (in_array(strtolower($singular), array("information", "knowledge", "development"))) return $singular;
		
		
		if (strlen($singular) > 0) {
			$last_two_letters = strtolower(substr($singular,-2,2));
			$last_letter = strtolower($singular[strlen($singular)-1]);
			
			switch($last_two_letters) {
				case 'er':
					$plural = substr($singular,0,-2).'ers';
					break;
				case 'ws':
					$plural = $singular;
					break;
				case 'ls':
					$plural = $singular;
					break;
				case 'es':
					$plural = $singular;
					break;
				default:
					switch($last_letter) {
						case 'y':
							$plural = substr($singular,0,-1).'ies';
							break;
						case 's':
							$plural = $singular.'es';
							break;
						case 'd':
						case 'a':
						case 'e':
						case 'g':
							$plural = $singular.'s';
							break;
						case 'm':
							$plural = $singular.'s';
							break;
						case 'n':
						case 'r':
						case 't':
							$plural = $singular.'s';
							break;
						default:
							$plural = $singular;
							break;
					}
			
					break;
			}
		} else {
			$plural = $singular;
		}
		 
	    return $plural;
	}
	function singularize($plural) {
		if ($plural === "corpora") return "corpus";
		if ($plural === "financials") return "financials";
		if ($plural === "quotes") return "quotes";
		
		//echo $plural;
		$last_letter = strtolower(substr($plural,-1,1));
		$last_two_letters = strtolower(substr($plural,-2,2));
		$last_three_letters = strtolower( substr($plural,-3,3));
		$last_four_letters = strtolower( substr($plural,-4,4));
		
		switch($last_four_letters) {
			case 'sses':
				$singular = substr($plural,0,-4).'ss';
				break;
			default:
				switch($last_three_letters) {
					case 'ies':
						$singular = substr($plural,0,-3).'y';
						break;
					default:
						switch($last_two_letters) {
							case 'es':
								$singular = substr($plural,0,-2).'e';
								break;
							case 'ss':
								$singular = $plural;
								break;
							case 'us':
								$singular = $plural;
								break;
							default:
								switch($last_letter) {
									case 's':
										$singular = substr($plural,0,-1);
										break;
									default:
										$singular = $plural;
										break;
								}
				
								break;
						}
						break;
				}
				break;
		}
		
		
		
		 
		return $singular;
	}
	function sort_on_field(&$objects, $on, $order = 'ASC', $type = "str") {
		if ($type === "num") {
			$comparer = ($order === 'DESC')
			? "return -(\$a->{$on} - \$b->{$on});"
			: "return (\$a->{$on} - \$b->{$on})";
			usort($objects, create_function('$a,$b', $comparer));
		} else {
			$comparer = ($order === 'DESC')
			? "return -strcmp(\$a->{$on},\$b->{$on});"
			: "return strcmp(\$a->{$on},\$b->{$on});";
			usort($objects, create_function('$a,$b', $comparer));
		}
	}
	function deabbrevate($string) {
		return $string;
	}
	function abbrevate($string) {
		$abbrevated = preg_replace('/[^A-Z ]/', '', $string);;
	
		return $abbrevated;
	}
	function cleanObjects($objects) {
		if(is_array($objects)) {
			foreach($objects as $object) {
				if (is_array($object)) {
					foreach($object as $key => $value) {
						if (is_array($value) || is_object($value)) {
							$this->cleanObjects($value);
						} else {
							if ($value === null) {
								//unset($key);
							} else {
								if (gettype($value) == "boolean") {
									if ($value === null) $value = 0;
									//echo "key: " . $key . "\n";
									$value = (bool)$value;
								}
								
							}
						}
					}
				} else {
					$this->cleanObjects($object);
				}
			}
		} else if (is_object($objects)) {
			if (isset($objects->RelationOntologyClassOntologyClassEntities)) {
				foreach($objects->RelationOntologyClassOntologyClassEntities as $rel_ococ_e) {
					if (isset($rel_ococ_e->OutgoingOntologyClassEntity)) {
						if (isset($rel_ococ_e->OutgoingOntologyClassEntity->RelationOntologyClassOntologyClassEntities)) {
							foreach($rel_ococ_e->OutgoingOntologyClassEntity->RelationOntologyClassOntologyClassEntities as $rel_sub__ococ_e) {
								if (isset($rel_sub__ococ_e->OutgoingOntologyClassEntity)) {
									unset($rel_sub__ococ_e->OutgoingOntologyClassEntity);
								}
							}
						}
					}
				}
			}
			
			foreach($objects as $key => $value) {
				if (is_array($value)) {
					if (count($value) == 0) {
						unset($objects->$key);
					} else {
						if (in_array($key, array("dbtable", "eager", "loading_eager", "loading_list", "loading_one", "dbfieldnames", "constraints_unique", "dbfieldnames_modification", "databaseConnections"))) {
							unset($objects->$key);
						} else {
							$this->cleanObjects($value);
						}
					}
				} else if (is_object($value)) {
					//$this->cleanObjects($value);
				} else {
					if ($value === null) {
						//unset($objects->$key);
					} else {
						if (gettype($value) == "boolean") {
							//echo "key: " . $key . "\n";
							if ($value === null) $value = 0;
							$value = (bool)$value;
						}
						if (in_array($key, array("dbtable", "eager", "loading_eager", "loading_list", "loading_one", "dbfieldnames", "constraints_unique", "dbfieldnames_modification", "databaseConnections"))) {
							unset($objects->$key);
						}
						//echo "key: " . $key . "\n";
					}
				}
			}
		} else {
			//echo "key: " . $key . "\n";
		}
	}
	function crypto($username, $password) {
		$cost = 10;
		
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		
		$hash = crypt($password, $salt);
		
		return $hash;
	}
}
?>