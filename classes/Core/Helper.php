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
				case 'ds':
					$plural = $singular;
					break;
				case 'es':
					$plural = $singular;
					break;
				case 'nt':
					$plural = $singular . 's';
					break;
				case 'rs':
					$plural = $singular;
					break;
				case 'ts':
					$plural = $singular;
					break;
				case 'ns':
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
							if ($value === null) $value = 0;
							$value = (bool)$value;
						}
						if (in_array($key, array("dbtable", "eager", "loading_eager", "loading_list", "loading_one", "dbfieldnames", "constraints_unique", "dbfieldnames_modification", "databaseConnections"))) {
							unset($objects->$key);
						}
					}
				}
			}
		}
	}
	function crypto($username, $password) {
		$cost = 10;
		
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		
		$hash = crypt($password, $salt);
		
		return $hash;
	}
	function starts_with_upper($str, $offset = 0) {
		$chr = mb_substr ($str, $offset, 1, "UTF-8");
		return mb_strtolower($chr, "UTF-8") != $chr;
	}
	function getScopeName() {
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
		
		if (strpos($url_parsed ['path'], "localhost") !== false) {
			$scopename = $levels[1];
		} else if (strpos($url_parsed ['path'], "/api/") !== false) {
			$apiPos = array_search("api", $levels);
			$scopename = $this->singularize($levels[$apiPos+1]);
		} else {
			$scopename = $levels[1];
		}
	
		return $scopename;
	}
	function getScopeObjectName() {
		$scopename = "";
		 
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
	
		if (strpos($url_parsed ['path'], "localhost") !== false) {
			$scopename = $levels[2];
		} else if (strpos($url_parsed ['path'], "/api/") !== false) {
			$scopename = $this->singularize($levels[4]);
		} else {
			if (isset($levels[2])) {
				$scopename = $levels[2];
			}
		}
	
		return $scopename;
	}
	function getScopeDepth() {
		$rest = new REST();
	
		$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
		$levels = explode ( "/", $url_parsed ['path'] );
	
		if (strpos($url_parsed ['path'], "localhost") !== false) {
			$depth = count($levels) - 2;
		} else if (strpos($url_parsed ['path'], "/api/") !== false) {
			$depth = null;
			//$scopename = $rest->singularize($levels[4]);
		} else {
			$depth = count($levels) - 2;
			//$scopename = $levels[1];
		}
	
		return $depth;
	}
	function getTopDomain() {
		if (isset($this->activescope_Ontology)) {
			if ($this->generated) {
				$topdomain = "generated/" . strtolower($this->title);
			} else {
				$ontology = new $this->activescope_Ontology->name;
				if (isset($ontology->topdomain)) {
					$topdomain = $ontology->topdomain;
				} else {
					$topdomain = "ontologydriven";
				}
			}
		} else {
			if ($this->generated) {
				if ($this->isLocalRequest()) {
					$topdomain = "generated/" . strtolower($this->title);
				} else {
					$topdomain = strtolower($this->title);
				}
			} else {
				$topdomain = "ontologydriven";
			}
		}
		 
		return $topdomain;
	}
}
?>