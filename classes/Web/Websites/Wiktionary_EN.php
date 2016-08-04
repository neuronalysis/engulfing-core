<?php
class Wiktionary_EN extends Wiktionary {
	function __construct() {
		$this->language = "en";
	}
	function getContent($regexp) {
		$iex = new Extraction();
	
		//echo "wiki-getword: " . $regexp . "/" . utf8_decode($regexp) . "\n";
			
		//if ($this->get_http_response_code('http://de.wiktionary.org/wiki/' .  utf8_decode(str_replace(" ", "_", $regexp))) != '404') {
		$structure = $iex->structureHTML(file_get_contents('http://en.wiktionary.org/wiki/' .  utf8_decode(str_replace(" ", "_", $regexp))));
		//}
		//print_r($structure);
		
		
		if (!isset($structure)) {
			return null;
		}
			
		$structure = $iex->optimizeArray($structure);
		//$iex->showArray($structure);
		
		return $structure;
	}
	
	function gettype($structure) {
		$iex = new Extraction();
	
		
		//echo ($structure[0][1][2][1][0]) . "\n";
		if (!isset($structure[2][0])) return null;
	
		//$iex->showArray($structure[1][0][2][1]);
		
		//echo $structure[2][0] . "\n";
		
		
		switch ($structure[2][0]) {
			case 'Deklinierte Form':
				//$type = $type_by_flections;
				break;
			case 'Particle':
				$type = "particle";
				break;
			case 'Article':
				$type = "article";
				break;
			case 'Numerale':
				//$type = "numerale";
				break;
			case 'Gradpartikel':
				//$type = "particle";
				break;
			case 'Preposition':
				$type = "preposition";
				break;
			case 'Subjunktion':
				//$type = "subjunction";
				break;
			case 'Kontraktion':
				//$type = "contraction";
				break;
			case 'Conjunction':
				$type = "conjunction";
				break;
			case 'Adverb':
				$type = "adverb";
				break;
			case 'Indefinitpronomen':
				//$type = "pronoun-indef";
				break;
			case 'Demonstrativpronomens':
				//$type = "pronoun-demo";
				break;
			case 'Personalpronomen':
				//$type = "pronoun-pers";
				break;
			case 'Reflexivpronomen':
				//$type = "pronoun-flex";
				break;
			case 'Relativpronomen':
				//$type = "pronoun-rel";
				break;
			case 'Determiner':
				$type = "determiner";
				break;
			case 'Pronoun':
				if ($structure[1][0][2][1] == "possessive pronoun") {
					$type = "pronoun-poss";
				} else if ($structure[1][0][2][1] == "personal") {
					$type = "pronoun-pers";
				} else {
					$type = "pronoun";
				}
				
				break;
			case 'Adjective':
				$type = "adjective";
				break;
			case 'Konjugierte Form':
			case 'Partizip':
			case 'Partizip II':
			case 'Erweiterter Infinitiv':
			case 'Verb':
				$type = "verb";
				break;
			case 'Noun':
			case 'Proper noun':
				$type = "substantive";
				break;
			default:
				$type = null;
				break;
					
		}
		//$iex->showArray($structure);
	
		return $type;
	}
	function IsFlected($structure, $word_type) {
		$iex = new Extraction();
		
		if (is_array($structure[1][0][2])) {
			if ($structure[1][0][2][1] == "(") {
				return false;
			} else {
				return true;
			}
		} else {
			//$iex->showArray($structure[1][1][2]);
			
			if (is_array($structure[1][2][2])) {
				if ($structure[1][2][2][1] == "(") {
					return false;
				} else {
					return true;
				}
			} else {
				if (is_array($structure[1][1][2])) {
					//$iex->showArray($structure[1][1][2]);
					//echo $structure[1][1][2][1] . "\n";
					if ($structure[1][1][2][1] == "(") {
						return false;
					} else {
						return true;
					}
				} else {
					return true;
				}
			}
		}
	}
	function getLexeme($structure, $word_type, $isFlected, $fallback) {
		$lexeme = new Lexeme();
		$iex = new Extraction();
	
		if ($isFlected == true) {
			$iex = new Extraction();
			//$iex->showArray($structure);
	
			if ($word_type == "verb") {
				$flections_temp = $iex->search($structure, "form of");
				if (count($flections_temp) == 0) {
					$flections_temp = $iex->search($structure, "of");
				}
				if (count($flections_temp) == 0) {
					$flections_temp = $iex->search($structure, "Verbs");
				}
					
				if (count($flections_temp) > 0) {
					//$iex->showArray($flections_temp);
					//echo "count: " . count($flections_temp[0][1][0]) . "\n";
					
					if (is_array($flections_temp[0][1][0])) {
						if (count($flections_temp[0][1][0]) < 5) {
							$infinitive_string = $flections_temp[0][1][0][1];
						} else {
							$infinitive_string = $fallback;
						}
						
					} else {
						$infinitive_string = $flections_temp[0][1][1];
						
					}
						
				}
				
				$lexeme->name = utf8_decode($infinitive_string);
			} else {
				$flections_temp = $iex->search($structure, "Form of");
				//print_r($flections_temp);
				//echo "fuck:\n";
				//$flections_temp = $iex->search($structure, "Grammatische Merkmale:");
				
				if (is_array($flections_temp[0][1])) {
					if (isset($flections_temp[0][1][1])) $lexeme->name = utf8_decode($flections_temp[0][1][1]);
				} else {
					if (isset($flections_temp[0][1][0][1])) {
						$lexeme->name = utf8_decode($flections_temp[0][1][0][1]);
					} else {
						$lexeme->name = utf8_decode($fallback);
					}
					
				}
			}
			
			
				
			//echo $lexeme->name . "\n";
		} else {
			//echo "$word_type\n";
			if ($word_type == "pronoun-rel") {
				$flections_temp = $iex->search($structure, "wikitable float-right hintergrundfarbe2");
					
				$flection_lexeme = $iex->getLastElementFromString(($flections_temp[0][1][1][1]));
					
	
				 
				$lexeme->name = utf8_decode($flection_lexeme);
	
			} else {
				$lexeme->name = utf8_decode($fallback);
			}
	
		}
		 
		if (isset($lexeme->name)) {
			$response_lexeme = $lexeme->save();
			 
			$lexeme = json_decode($response_lexeme);
	
			return $lexeme;
		}
	}
	function getFlexions_From_Flected($structure, $word_type) {
		$iex = new Extraction();
		//$iex->showArray($structure);
	
		$grammar_attributes = array();
		$grammar_attributes[0] = new Grammar_Flexion();
	
		//$iex->showArray($structure);
	
		$flections = array();
	
		//echo "type: " . $word_type . "\n";
	
		$j = 0;
		if ($word_type == "pronoun" || $word_type == "pronoun-demo" || $word_type == "pronoun-indef") {
			$flections_temp = $iex->search($structure, "Flexion");
			for ($i=0; $i<count($flections_temp); $i++) {
				if (is_numeric(stripos($flections_temp[$i][0], "Indefinitpronomen")) || is_numeric(stripos($flections_temp[$i][0], "Demonstrativpronomen"))) {
					$flections[$j] = $flections_temp[$i];
					$j++;
				}
			}
				
			if (count($flections_temp) == 0) {
				$flections_temp = $iex->search($structure, "Demonstrativpronomen");
				for ($i=0; $i<count($flections_temp); $i++) {
					if (is_numeric(stripos($flections_temp[$i][0], "Demonstrativpronomen"))) {
						$flections[$j] = $flections_temp[$i];
						$j++;
					}
				}
			}
				
			if (count($flections_temp) == 0) {
				$flections_temp = $iex->search($structure, "Indefinitpronomen");
				for ($i=0; $i<count($flections_temp); $i++) {
					if (is_numeric(stripos($flections_temp[$i][0], "Indefinitpronomen"))) {
						$flections[$j] = $flections_temp[$i];
						$j++;
					}
				}
			}
	
	
			for ($i=0; $i<count($flections); $i++) {
				if (is_numeric(stripos($flections[$i][0], "Maskulinum"))) {
					$grammar_attributes[$i]->genus = "mas";
				}
				if (is_numeric(stripos($flections[$i][0], "Femininum"))) {
					$grammar_attributes[$i]->genus = "fem";
				}
				if (is_numeric(stripos($flections[$i][0], "Neutrum"))) {
					$grammar_attributes[$i]->genus = "neu";
				}
				if (is_numeric(stripos($flections[$i][0], "Singular"))) {
					$grammar_attributes[$i]->numerus = "singular";
				}
				if (is_numeric(stripos($flections[$i][0], "Nominativ"))) {
					$grammar_attributes[$i]->kasus = "nominative";
				}
				if (is_numeric(stripos($flections[$i][0], "Akkusativ"))) {
					$grammar_attributes[$i]->kasus = "accusative";
				}
				if (is_numeric(stripos($flections[$i][0], "Dativ"))) {
					$grammar_attributes[$i]->kasus = "dative";
				}
				if (is_numeric(stripos($flections[$i][0], "Genitiv"))) {
					$grammar_attributes[$i]->kasus = "genitive";
				}
				//echo $flections[$i][0] . "\n";
			}
		} else if ($word_type == "adjective") {
			$flections = $iex->search($structure, "Adjektivs");
			if (count($flections) == 0) {
				$flections = $iex->search($structure, "von");
			}
	
			$j=0;
			for ($i=0; $i<count($flections); $i++) {
				if (!is_numeric(stripos($flections[$i][0], "Superlativ"))) {
					if (is_numeric(stripos($flections[$i][0], "Maskulinum"))) {
						$grammar_attributes[$j]->genus = "mas";
					}
					if (is_numeric(stripos($flections[$i][0], "Femininum"))) {
						$grammar_attributes[$j]->genus = "fem";
					}
					if (is_numeric(stripos($flections[$i][0], "Neutrum"))) {
						$grammar_attributes[$j]->genus = "neu";
					}
					if (is_numeric(stripos($flections[$i][0], "Singular"))) {
						$grammar_attributes[$j]->numerus = "singular";
					}
					if (is_numeric(stripos($flections[$i][0], "Plural"))) {
						$grammar_attributes[$j]->numerus = "plural";
					}
					if (is_numeric(stripos($flections[$i][0], "Nominativ"))) {
						$grammar_attributes[$j]->kasus = "nominative";
					}
					if (is_numeric(stripos($flections[$i][0], "Akkusativ"))) {
						$grammar_attributes[$j]->kasus = "accusative";
					}
					if (is_numeric(stripos($flections[$i][0], "Dativ"))) {
						$grammar_attributes[$j]->kasus = "dative";
					}
					if (is_numeric(stripos($flections[$i][0], "Genitiv"))) {
						$grammar_attributes[$j]->kasus = "genitive";
					}
					$j++;
				}
	
				//echo $flections[$i][0] . "\n";
			}
		} else if ($word_type == "verb") {
			$iex = new Extraction();
	
				
			//echo $structure[1][0][1][0][0][0];
			
			$flections_temp = $iex->search($structure, "form of");
			if (count($flections_temp) == 0) {
				$flections_temp = $iex->search($structure, "of");
			}
			if (count($flections_temp) == 0) {
				$flections_temp = $iex->search($structure, "Verbs");
			}
			
			
		
			$j=0;
			for ($i=0; $i<1; $i++) {
				if (is_numeric(stripos($flections_temp[$i], " and "))) {
					$flections_and[$i] = explode(" and ", $flections_temp[$i]);
					$flections[$j] = $flections_and[$i][0];
					$j++;
					$flections[$j] = $flections_and[$i][1];
				} else {
					$flections[$j] = $flections_temp[$i];
					$j++;
				}
				
			}
			
			
			//$iex->showArray($flections_temp[0][0]);
			
			if (count($flections_temp) > 0) {
				//$iex->showArray($flections_temp);
				//echo "count: " . count($flections_temp[0][1][0]) . "\n";
				
				if (is_array($flections_temp[0][1][0])) {
					if (count($flections_temp[0][1][0]) < 5) {
						$infinitive_string = $flections_temp[0][1][0][1];
					} else {
						$infinitive_string = $fallback;
					}
					
				} else {
					$infinitive_string = $flections_temp[0][1][1];
					
				}
					
			}
			
			
			//echo $infinitive_string . "\n";
			
			//echo "inf-string: " . $infinitive_string . "\n";
			//echo "flections.count: " . count($flections) . "\n";
			
			$grammar_attributes[count($flections)] = new Grammar_Flexion();
			$grammar_attributes[count($flections)]->name = $infinitive_string;
			$grammar_attributes[count($flections)]->tempus = "infinitive";
			
			//print_r($flections);
			
				
			for ($i=0; $i<count($flections); $i++) {
				if (is_numeric(stripos($flections[$i][0], "1"))) {
					$grammar_attributes[$i]->person = 1;
				}
				if (is_numeric(stripos($flections[$i][0], "2"))) {
					$grammar_attributes[$i]->person = 2;
				}
				if (is_numeric(stripos($flections[$i][0], "third-person"))) {
					$grammar_attributes[$i]->person = 3;
				}
				if (is_numeric(stripos($flections[$i][0], "Partizip Perfekt"))) {
					$grammar_attributes[$i]->tempus = "participe perfect";
				}
				if (is_numeric(stripos(strtolower($flections[$i][0]), "past participle"))) {
					$grammar_attributes[$i]->tempus = "past participle";
				}
				if (is_numeric(stripos(strtolower($flections[$i][0]), "present participle"))) {
					$grammar_attributes[$i]->tempus = "present participle";
				}
				if (is_numeric(stripos($flections[$i][0], "simple present"))) {
					$grammar_attributes[$i]->tempus = "presence";
				}
				if (is_numeric(stripos(strtolower($flections[$i][0]), "simple past"))) {
					$grammar_attributes[$i]->tempus = "simple past";
				}
				if (is_numeric(stripos($flections[$i][0], "erweiterter Infinitiv"))) {
					$grammar_attributes[$i]->tempus = "extended infinitive";
				}
				
				//print_r($grammar_attributes);
				//echo $flections[$i][0] . "\n";
			}
		} else if ($word_type == "article") {
		} else if ($word_type == "preposition") {
		} else if ($word_type == "substantive") {
					
			$flections_temp = $iex->search($structure, "form of");
			if (count($flections_temp) == 0) {
				$flections_temp = $iex->search($structure, "of");
			}
			
			if (count($flections_temp) > 0) {
				//$iex->showArray($flections_temp);
				if (is_array($flections_temp[0][1])) {
					$singular_string = $flections_temp[0][1][1];
					
					$grammar_attributes[0]->numerus = "plural";
						
					$grammar_attributes[count($flections_temp)] = new Grammar_Flexion();
					$grammar_attributes[count($flections_temp)]->name = $singular_string;
					$grammar_attributes[count($flections_temp)]->numerus = "singular";
				} else {
					$grammar_attributes[0]->numerus = "singular";
				}
				
				
				//echo "singlular: " . $singular_string . "\n";
			}
			
			
			
				
		}
		
		//echo "before unifiqation \n";
		
		//print_r($grammar_attributes);
	
		$grammar_attributes_unique = array();
	
		$u = 0;
		for ($i=0; $i<count($grammar_attributes); $i++) {
			$matched = false;
			if (count($grammar_attributes_unique) == 0) {
				$grammar_attributes_unique[$u] = $grammar_attributes[$i];
				$u++;
	
				$matched = true;
			}
			for ($j=0; $j<count($grammar_attributes_unique); $j++) {
				//echo $grammar_attributes[$i]->tempus . "-" . $grammar_attributes[$i]->name . "-" . $grammar_attributes[$i]->genus . "-" . $grammar_attributes[$i]->numerus . "-" . $grammar_attributes[$i]->kasus . "   VS    " . $grammar_attributes_unique[$j]->tempus . "-" . $grammar_attributes_unique[$j]->name . "-" . $grammar_attributes_unique[$j]->genus . "-" . $grammar_attributes_unique[$j]->numerus . "-" . $grammar_attributes_unique[$j]->kasus . "\n";
				if (($grammar_attributes[$i]->tempus . "-" . $grammar_attributes[$i]->name . "-" . $grammar_attributes[$i]->genus . "-" . $grammar_attributes[$i]->numerus . "-" . $grammar_attributes[$i]->kasus == $grammar_attributes_unique[$j]->tempus . "-" . $grammar_attributes_unique[$j]->name . "-" . $grammar_attributes_unique[$j]->genus . "-" . $grammar_attributes_unique[$j]->numerus . "-" . $grammar_attributes_unique[$j]->kasus)) {
					$matched = true;
				}
			}
			if ($matched == false) {
				//echo $grammar_attributes[$i]->tempus . "-" . $grammar_attributes[$i]->name . "-" . $grammar_attributes[$i]->genus . "-" . $grammar_attributes[$i]->numerus . "-" . $grammar_attributes[$i]->kasus . "\n"; 
				$grammar_attributes_unique[$u] = $grammar_attributes[$i];
				$u++;
	
			}
		}
		
		//echo "after unifiqation \n";
		
		//print_r($grammar_attributes_unique);
			
		return $grammar_attributes_unique;
	}
	function getFlexions_From_NonFlected($structure, $word_type) {
		$iex = new Extraction();
	
		//echo "non-flected type: " . $word_type . "\n";
		$grammar_attributes = array();
		$grammar_attributes[0] = new Grammar_Flexion();
	
		$flections = array();
	
		$j = 0;
		if ($word_type == "verb") {
			$grammar_attributes[0]->tempus = "infinitive";
		} else if ($word_type == "preposition") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "article") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "adverb") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "particle") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "pronoun") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "pronoun-flex") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "pronoun-indef") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "adjective") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "subjunction") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "conjunction") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "contraction") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "subjunction") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "substantive") {
			
			//$iex->showArray($structure[1][0][2]);
			
			if ($structure[1][0][2][2] == "plural") {
				$plural_str = $structure[1][0][2][3][1];
				
				$grammar_attributes[1]->numerus = "plural";
				$grammar_attributes[1]->name = $plural_str;
			} else {
				if ($structure[1][1][2][2] == "plural") {
					$plural_str = $structure[1][1][2][3][1];
					
					$grammar_attributes[1]->numerus = "plural";
					$grammar_attributes[1]->name = $plural_str;
				} else {
					if ($structure[1][0][2][4] == "plural") {
						$plural_str = $structure[1][0][2][5][1];
						
						$grammar_attributes[1]->numerus = "plural";
						$grammar_attributes[1]->name = $plural_str;
					} else {
						//$iex->showArray($structure[1][1][2]);
					}	
				}	
			}
			
			$grammar_attributes[0]->numerus = "singular";
			$grammar_attributes[0]->name = null;
	
			
				
		} else if ($word_type == "numerale") {
			$flections_temp = $iex->search($structure, "wikitable float-right hintergrundfarbe2");
				
			//$iex->showArray($structure);
				
			//print_r($flections_temp[0][1][1]);
				
			$grammar_attributes[0]->genus = "mas";
			$grammar_attributes[0]->numerus = "singular";
			$grammar_attributes[0]->kasus = "nominative";
			$grammar_attributes[0]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][1]));
				
			$grammar_attributes[1]->genus = "fem";
			$grammar_attributes[1]->numerus = "singular";
			$grammar_attributes[1]->kasus = "nominative";
			$grammar_attributes[1]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][2]));
	
			$grammar_attributes[2]->genus = "neu";
			$grammar_attributes[2]->numerus = "singular";
			$grammar_attributes[2]->kasus = "nominative";
			$grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][3]));
	
			//print_r($flections_temp[1][1][1]);
				
			$grammar_attributes[3]->genus = "mas";
			$grammar_attributes[3]->numerus = "singular";
			$grammar_attributes[3]->kasus = "genitive";
			$grammar_attributes[3]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][1]));
				
			$grammar_attributes[4]->genus = "fem";
			$grammar_attributes[4]->numerus = "singular";
			$grammar_attributes[4]->kasus = "genitive";
			$grammar_attributes[4]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][2]));
	
			$grammar_attributes[5]->genus = "neu";
			$grammar_attributes[5]->numerus = "singular";
			$grammar_attributes[5]->kasus = "genitive";
			$grammar_attributes[5]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][3]));
	
			//print_r($flections_temp[2][1][1]);
				
			$grammar_attributes[6]->genus = "mas";
			$grammar_attributes[6]->numerus = "singular";
			$grammar_attributes[6]->kasus = "dative";
			$grammar_attributes[6]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][1]));
				
			$grammar_attributes[7]->genus = "fem";
			$grammar_attributes[7]->numerus = "singular";
			$grammar_attributes[7]->kasus = "dative";
			$grammar_attributes[7]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][2]));
	
			$grammar_attributes[8]->genus = "neu";
			$grammar_attributes[8]->numerus = "singular";
			$grammar_attributes[8]->kasus = "dative";
			$grammar_attributes[8]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][3]));
				
				
			$grammar_attributes[9]->genus = "mas";
			$grammar_attributes[9]->numerus = "singular";
			$grammar_attributes[9]->kasus = "accusative";
			$grammar_attributes[9]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][1]));
				
			$grammar_attributes[10]->genus = "fem";
			$grammar_attributes[10]->numerus = "singular";
			$grammar_attributes[10]->kasus = "accusative";
			$grammar_attributes[10]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][2]));
	
			$grammar_attributes[11]->genus = "neu";
			$grammar_attributes[11]->numerus = "singular";
			$grammar_attributes[11]->kasus = "accusative";
			$grammar_attributes[11]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][3]));
	
	
			$grammar_attributes[12]->genus = "mas";
			$grammar_attributes[12]->numerus = "singular";
			$grammar_attributes[12]->kasus = "nominative";
			$grammar_attributes[12]->name = $flections_temp[1][1][1][1];
				
			$grammar_attributes[13]->genus = "fem";
			$grammar_attributes[13]->numerus = "singular";
			$grammar_attributes[13]->kasus = "nominative";
			$grammar_attributes[13]->name = $flections_temp[1][1][1][2];
	
			$grammar_attributes[14]->genus = "neu";
			$grammar_attributes[14]->numerus = "singular";
			$grammar_attributes[14]->kasus = "nominative";
			$grammar_attributes[14]->name = $flections_temp[1][1][1][3];
	
			//print_r($flections_temp[1][1][1]);
				
			$grammar_attributes[15]->genus = "mas";
			$grammar_attributes[15]->numerus = "singular";
			$grammar_attributes[15]->kasus = "genitive";
			$grammar_attributes[15]->name = $flections_temp[1][1][2][1];
				
			$grammar_attributes[16]->genus = "fem";
			$grammar_attributes[16]->numerus = "singular";
			$grammar_attributes[16]->kasus = "genitive";
			$grammar_attributes[16]->name = $flections_temp[1][1][2][2];
	
			$grammar_attributes[17]->genus = "neu";
			$grammar_attributes[17]->numerus = "singular";
			$grammar_attributes[17]->kasus = "genitive";
			$grammar_attributes[17]->name = $flections_temp[1][1][2][3];
	
			//print_r($flections_temp[2][1][1]);
				
			$grammar_attributes[18]->genus = "mas";
			$grammar_attributes[18]->numerus = "singular";
			$grammar_attributes[18]->kasus = "dative";
			$grammar_attributes[18]->name = $flections_temp[1][1][3][1];
				
			$grammar_attributes[19]->genus = "fem";
			$grammar_attributes[19]->numerus = "singular";
			$grammar_attributes[19]->kasus = "dative";
			$grammar_attributes[19]->name = $flections_temp[1][1][3][2];
	
			$grammar_attributes[20]->genus = "neu";
			$grammar_attributes[20]->numerus = "singular";
			$grammar_attributes[20]->kasus = "dative";
			$grammar_attributes[20]->name = $flections_temp[1][1][3][3];
				
				
			$grammar_attributes[21]->genus = "mas";
			$grammar_attributes[21]->numerus = "singular";
			$grammar_attributes[21]->kasus = "accusative";
			$grammar_attributes[21]->name = $flections_temp[1][1][4][1];
				
			$grammar_attributes[22]->genus = "fem";
			$grammar_attributes[22]->numerus = "singular";
			$grammar_attributes[22]->kasus = "accusative";
			$grammar_attributes[22]->name = $flections_temp[1][1][4][2];
	
			$grammar_attributes[23]->genus = "neu";
			$grammar_attributes[23]->numerus = "singular";
			$grammar_attributes[23]->kasus = "accusative";
			$grammar_attributes[23]->name = $flections_temp[1][1][4][3];
				
			$grammar_attributes[24]->numerus = "plural";
			$grammar_attributes[24]->kasus = "nominative";
			$grammar_attributes[24]->name = $flections_temp[1][1][1][4];
				
			$grammar_attributes[25]->numerus = "plural";
			$grammar_attributes[25]->kasus = "genitive";
			$grammar_attributes[25]->name = $flections_temp[1][1][2][4];
				
			$grammar_attributes[26]->numerus = "plural";
			$grammar_attributes[26]->kasus = "dative";
			$grammar_attributes[26]->name = $flections_temp[1][1][3][4];
				
			$grammar_attributes[27]->numerus = "plural";
			$grammar_attributes[27]->kasus = "accusative";
			$grammar_attributes[27]->name = $flections_temp[1][1][4][4];
		} else if ($word_type == "pronoun-pers") {
			$iex = new Extraction();
				
				
			$flections_temp = $iex->search($structure, "wikitable");
				
			//$iex->showArray($flections_temp);
				
			//print_r($flections_temp);
			if ($flections_temp) {
				$grammar_attributes[0]->genus = "mas";
				$grammar_attributes[0]->numerus = "singular";
				$grammar_attributes[0]->kasus = "nominative";
				$grammar_attributes[0]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][1]));
					
				$grammar_attributes[1]->genus = "fem";
				$grammar_attributes[1]->numerus = "singular";
				$grammar_attributes[1]->kasus = "nominative";
				$grammar_attributes[1]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][2]));
	
				//print_r($flections_temp[0][1][1][3]);
					
				$grammar_attributes[2]->genus = "neu";
				$grammar_attributes[2]->numerus = "singular";
				$grammar_attributes[2]->kasus = "nominative";
				$grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][3]));
				if (!$grammar_attributes[2]->name) $grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][3][0]));
	
				//print_r($flections_temp[1][1][1]);
					
				$grammar_attributes[3]->genus = "mas";
				$grammar_attributes[3]->numerus = "singular";
				$grammar_attributes[3]->kasus = "genitive";
				$grammar_attributes[3]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][1]));
					
				$grammar_attributes[4]->genus = "fem";
				$grammar_attributes[4]->numerus = "singular";
				$grammar_attributes[4]->kasus = "genitive";
				$grammar_attributes[4]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][2]));
	
				$grammar_attributes[5]->genus = "neu";
				$grammar_attributes[5]->numerus = "singular";
				$grammar_attributes[5]->kasus = "genitive";
				$grammar_attributes[5]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][3]));
	
				//print_r($flections_temp[2][1][1]);
					
				$grammar_attributes[6]->genus = "mas";
				$grammar_attributes[6]->numerus = "singular";
				$grammar_attributes[6]->kasus = "dative";
				$grammar_attributes[6]->name = $iex->getLastElementFromString(($flections_temp[0][1][5][1]));
					
				$grammar_attributes[7]->genus = "fem";
				$grammar_attributes[7]->numerus = "singular";
				$grammar_attributes[7]->kasus = "dative";
				$grammar_attributes[7]->name = $iex->getLastElementFromString(($flections_temp[0][1][5][2]));
	
				$grammar_attributes[8]->genus = "neu";
				$grammar_attributes[8]->numerus = "singular";
				$grammar_attributes[8]->kasus = "dative";
				$grammar_attributes[8]->name = $iex->getLastElementFromString(($flections_temp[0][1][5][3]));
					
					
				$grammar_attributes[9]->genus = "mas";
				$grammar_attributes[9]->numerus = "accusative";
				$grammar_attributes[9]->kasus = "accusative";
				$grammar_attributes[9]->name = $iex->getLastElementFromString(($flections_temp[0][1][6][1]));
					
				$grammar_attributes[10]->genus = "fem";
				$grammar_attributes[10]->numerus = "singular";
				$grammar_attributes[10]->kasus = "accusative";
				$grammar_attributes[10]->name = $iex->getLastElementFromString(($flections_temp[0][1][6][2]));
	
				$grammar_attributes[11]->genus = "neu";
				$grammar_attributes[11]->numerus = "singular";
				$grammar_attributes[11]->kasus = "accusative";
				$grammar_attributes[11]->name = $iex->getLastElementFromString(($flections_temp[0][1][6][3]));
				if (!$grammar_attributes[11]->name) $grammar_attributes[11]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][3][0]));
	
	
				$grammar_attributes[12]->numerus = "plural";
				$grammar_attributes[12]->kasus = "nominative";
				$grammar_attributes[12]->name = $flections_temp[1][1][3][4];
	
				$grammar_attributes[13]->numerus = "plural";
				$grammar_attributes[13]->kasus = "genitive";
				$grammar_attributes[13]->name = $flections_temp[1][1][4][4];
	
				$grammar_attributes[14]->numerus = "plural";
				$grammar_attributes[14]->kasus = "dative";
				$grammar_attributes[14]->name = $flections_temp[1][1][5][4];
	
				$grammar_attributes[15]->numerus = "plural";
				$grammar_attributes[15]->kasus = "accusative";
				$grammar_attributes[15]->name = $flections_temp[1][1][6][4];
			} else {
				$grammar_attributes[0]->name = null;
			}
		}
	
	
		$grammar_attributes_unique = array();
	
		$u = 0;
		for ($i=0; $i<count($grammar_attributes); $i++) {
			$matched = false;
			if (count($grammar_attributes_unique) == 0) {
				$grammar_attributes_unique[$u] = $grammar_attributes[$i];
				$u++;
	
				$matched = true;
			}
			for ($j=0; $j<count($grammar_attributes_unique); $j++) {
				if (($grammar_attributes[$i]->name . "-" . $grammar_attributes[$i]->genus . "-" . $grammar_attributes[$i]->numerus . "-" . $grammar_attributes[$i]->kasus . "-" . $grammar_attributes[$i]->person == $grammar_attributes_unique[$j]->name . "-" . $grammar_attributes_unique[$j]->genus . "-" . $grammar_attributes_unique[$j]->numerus . "-" . $grammar_attributes_unique[$j]->kasus . "-" . $grammar_attributes_unique[$j]->person)) {
					$matched = true;
				}
			}
			if ($matched == false) {
				$grammar_attributes_unique[$u] = $grammar_attributes[$i];
				$u++;
	
			}
		}
			
		if (count($grammar_attributes_unique) > 0) {
			//print_r($grammar_attributes_unique);
			return $grammar_attributes_unique;
		}
	}
	function getEtymologies($structure_unfiltered) {
		$etymologies = array();
		
		$iex = new Extraction();
		
		
		
		//$iex->showArray($structure_unfiltered);
		
		
		$h2s = $iex->search($structure_unfiltered, "h2", true, 0);
		
		if ($h2s[1][2][0] == "Translingual") {
			//echo "arsch\n";
			$h3s = $iex->search($h2s[2], "h3");
				
		} else {
			if ($h2s[1][2][0] != "English") {
				if ($h2s[0][2][0] == "English") {
					$h3s = $iex->search($h2s[0], "h3");
					
				} else {
					return null;
				}
				
			} else {
				$h3s = $iex->search($h2s[1], "h3");
			}
			
				
		}
		
		//$iex->showArray($h2s[2]);
		//echo 
		//$iex->showArray($h3s);
		
		$j=0;
		for ($i=0; $i<count($h3s); $i++) {
			if (is_numeric(strpos(strtolower($h3s[$i][2][0]), strtolower("etymology")))) {
				$etymologies[$j] = $h3s[$i];
				$j++;
			}
			//echo $h3s[$i][2] . "\n";
		}
		
		if (count($etymologies) <= 1) {
			if ($h2s[1][2][0] != "English") {
				$etymologies[0] = $h2s[0];
			} else {
				$etymologies[0] = $h2s[1];
			}
				
			//return $h2s[1];
		}
		
		//$iex->showArray($etymologies);
		
		
		return $etymologies;
	}
	function gettype_Structures($structure_unfiltered) {
		//print_r($structure_unfiltered);
	
		$iex = new Extraction();
	
		$ws = array();
		
		$etymologies = $this->getEtymologies($structure_unfiltered);
		//$iex->showArray($etymologies);
		
		//echo "count: etym: " . count($etymologies) . "\n";
		
		if (count($etymologies) == 1) {
			$h3s = $iex->search($etymologies[0], "h3", true, 0);
			
			$j=0;
			for ($i=0; $i<count($h3s); $i++) {
				//$iex->showArray($h3s[$i]);
				//echo $h3s[$i][2][0] . "\n";
				if ($h3s[$i][2][0] != "Alternative forms" && $h3s[$i][2][0] != "Number" && $h3s[$i][2][0] != "Statistics" && $h3s[$i][2][0] != "Etymology" && $h3s[$i][2][0] != "Pronunciation" && $h3s[$i][2][0] != "See also" && $h3s[$i][2][0] != "Anagrams" && $h3s[$i][2][0] != "References") {
					$ws[$j] = $h3s[$i];
					$j++;
				}
				
			}
				
		} else {
			$j=0;
			for ($e=0; $e<count($etymologies); $e++) {
				$h4s[$e] = $iex->search($etymologies[$e], "h4", true, 0);
				
				//print_r($h4s[$e]);
				
				for ($i=0; $i<count($h4s[$e]); $i++) {
					if ($h4s[$e][$i][2][0] != "Alternative forms" && $h4s[$e][$i][2][0] != "Statistics" && $h4s[$e][$i][2][0] != "Etymology" && $h4s[$e][$i][2][0] != "Pronunciation" && $h4s[$e][$i][2][0] != "See also" && $h4s[$e][$i][2][0] != "Anagrams" && $h4s[$e][$i][2][0] != "Cardinal number" && $h4s[$e][$i][2][0] != "Letter" && $h4s[$e][$i][2][0] != "Number" && $h4s[$e][$i][2][0] != "References") {
						$ws[$j] = $h4s[$e][$i];
						$j++;
					}
				}
			}
		}
		
 		
		
		
		return $ws;
	}
	function gettype_Flections($structure) {
		$iex = new Extraction();
	
		$structure = $iex->search($structure, "Grammatische Merkmale:", true, 0);
	
		$flex = $iex->search($structure, "Superlativ");
		if (count($flex) > 0) return "adjective";
	
		$flex = $iex->search($structure, "Demonstrativpronomen");
		if (count($flex) > 0) return "pronoun-demo";
	
		$flex = $iex->search($structure, "Indefinitpronomen");
		if (count($flex) > 0) return "pronoun-indef";
	
		$flex = $iex->search($structure, "Artikel");
		if (count($flex) > 0) return "article";
	
		$flex = $iex->search($structure, "Substantiv");
		if (count($flex) > 0) return "substantive";
	
		$flex = $iex->search($structure, "Adjektiv");
		if (count($flex) > 0) return "adjective";
	
		return null;
	}}
?>
