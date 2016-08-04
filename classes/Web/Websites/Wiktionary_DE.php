<?php
class Wiktionary_DE extends Wiktionary {
	function __construct() {
		$this->language = "de";
	}
	function getContent($regexp) {
		$iex = new Extraction();
	
		//echo "wiki-getword: " . $regexp . "/" . utf8_decode($regexp) . "\n";
			
		//if ($this->get_http_response_code('http://de.wiktionary.org/wiki/' .  utf8_decode(str_replace(" ", "_", $regexp))) != '404') {
			$structure = $iex->structureHTML(file_get_contents('http://de.wiktionary.org/wiki/' .  utf8_decode(str_replace(" ", "_", $regexp))));
		//}
			
		if (!isset($structure)) {
			return null;
		}
			
		$structure = $iex->optimizeArray($structure);
	
		return $structure;
	}
	function IsFlected($structure, $word_type) {
		//echo $word_type . "\n";
		 
		$iex = new Extraction();
		//$iex->showArray($structure);
	
		if ($word_type == "subjunction") return false;
		if ($word_type == "preposition") return false;
	
		$flected_word = $iex->search($structure, "Konjugierte");
		if (count($flected_word) == 0) $flected_word = $iex->search($structure, "Deklinierte");
		if (count($flected_word) == 0) $flected_word = $iex->search($structure, "flektierte");
	
		//echo "count.flected: " . count($flected_word) . "\n";
		if (count($flected_word) > 0) {
			return true;
		}
		 
		return false;
	}
	function getLexeme($structure, $word_type, $isFlected, $fallback) {
		$lexeme = new Lexeme();
		$iex = new Extraction();
	
		if ($isFlected == true) {
			$iex = new Extraction();
	
			$flections_temp = $iex->search($structure, "Grammatische Merkmale:", true);
			//$iex->showArray($flections_temp[0][1]);
			//print_r($flections_temp[0][1][0][1]);
			//echo "fuck:\n";
			//$flections_temp = $iex->search($structure, "Grammatische Merkmale:");
	
			if (is_array($flections_temp[0][1][0][1])) {
				if (is_array($flections_temp[0][1][0][1][1])) {
					if (isset($flections_temp[0][1][0][1][1][1])) $lexeme->name = utf8_decode($flections_temp[0][1][0][1][1][1]);
				} else {
					if (isset($flections_temp[0][1][0][1][1])) $lexeme->name = utf8_decode($flections_temp[0][1][0][1][1]);
				}
			
			} else {
				//print_r($flections_temp[0][1][0][2]);
				//echo "\n";
				if (is_array($flections_temp[0][1][0][2])) {
					$lexeme->name = utf8_decode($flections_temp[0][1][0][2][1]);
				} else {
					$lexeme->name = utf8_decode($flections_temp[0][1][0][1]);
				}
				
			}
				
		} else {
			//echo "$word_type\n";
			if ($word_type == "article" || $word_type == "pronoun-rel") {
				$flections_temp = $iex->search($structure, "wikitable float-right hintergrundfarbe2");
					
				$flection_lexeme = $iex->getLastElementFromString(($flections_temp[0][1][1][1]));
					
	
				 
				$lexeme->name = utf8_decode($flection_lexeme);
	
			} else {
				$lexeme->name = utf8_decode($fallback);
			}
	
		}
		 
		//print_r($lexeme);
		
		if (isset($lexeme->name)) {
			$response_lexeme = $lexeme->save();
			 
			$lexeme = json_decode($response_lexeme);
	
			return $lexeme;
		}
	}
	function getFlexions_From_Flected($structure, $word_type) {
		$iex = new Extraction();
		//$iex->showArray($structure);
	
		$structure = $iex->search($structure, "Grammatische Merkmale:", true, 0);
	
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
	
			//$iex->showArray($structure);
				
			$flections = $iex->search($structure, "Person");
			if (count($flections) == 0) {
				$flections = $iex->search($structure, "Verbs");
			}
			
			
	
			$iex->showArray($flections);
			//echo "count. flextions: " . count($flections) . "\n";
				
			for ($i=0; $i<count($flections); $i++) {
				if (count($flections[$i]) == 3) {
					$flections[$i][0] = $flections[$i][1];
					$flections[$i][1] = $flections[$i][2];
					unset($flections[$i][2]);
				}
				
				if (is_numeric(stripos($flections[$i][0], "1"))) {
					$grammar_attributes[$i]->person = 1;
				}
				if (is_numeric(stripos($flections[$i][0], "2"))) {
					$grammar_attributes[$i]->person = 2;
				}
				if (is_numeric(stripos($flections[$i][0], "3"))) {
					$grammar_attributes[$i]->person = 3;
				}
				if (is_numeric(stripos($flections[$i][0], "Singular"))) {
					$grammar_attributes[$i]->numerus = "singular";
				}
				if (is_numeric(stripos($flections[$i][0], "Plural"))) {
					$grammar_attributes[$i]->numerus = "plural";
				}
				if (is_numeric(stripos($flections[$i][0], "Partizip Perfekt"))) {
					$grammar_attributes[$i]->tempus = "participe perfect";
				}
				if (is_numeric(stripos($flections[$i][0], "Präsens"))) {
					$grammar_attributes[$i]->tempus = "presence";
				}
				if (is_numeric(stripos($flections[$i][0], "erweiterter Infinitiv"))) {
					$grammar_attributes[$i]->tempus = "extended infinitive";
				}
				//echo $flections[$i][0] . "\n";
			}
		} else if ($word_type == "article") {
			$flections = $iex->search($structure, "Artikel");
				
				
			for ($i=0; $i<count($flections); $i++) {
				if (is_numeric(stripos($flections[$i][0], "Maskulinum")) || is_numeric(stripos($flections[$i][0], "maskulinen"))) {
					$grammar_attributes[$i]->genus = "mas";
				}
				if (is_numeric(stripos($flections[$i][0], "Femininum")) || is_numeric(stripos($flections[$i][0], "femininen"))) {
					$grammar_attributes[$i]->genus = "fem";
				}
				if (is_numeric(stripos($flections[$i][0], "Neutrum")) || is_numeric(stripos($flections[$i][0], "neutralen"))) {
					$grammar_attributes[$i]->genus = "neu";
				}
				if (is_numeric(stripos($flections[$i][0], "Genitiv"))) {
					$grammar_attributes[$i]->kasus = "genitive";
				}
				if (is_numeric(stripos($flections[$i][0], "Dativ"))) {
					$grammar_attributes[$i]->kasus = "dative";
				}
				if (is_numeric(stripos($flections[$i][0], "Akkusativ"))) {
					$grammar_attributes[$i]->kasus = "accusative";
				}
				if (is_numeric(stripos($flections[$i][0], "Nominativ"))) {
					$grammar_attributes[$i]->kasus = "nominative";
				}
			}
		} else if ($word_type == "substantive") {
			$flections = $iex->search($structure, "Substantiv");
				
				
			for ($i=0; $i<count($flections); $i++) {
				if (is_numeric(stripos($flections[$i][0], "Genitiv"))) {
					$grammar_attributes[$i]->kasus = "genitive";
				}
				if (is_numeric(stripos($flections[$i][0], "Akkusativ"))) {
					$grammar_attributes[$i]->kasus = "accusative";
				}
				if (is_numeric(stripos($flections[$i][0], "Nominativ"))) {
					$grammar_attributes[$i]->kasus = "nominative";
				}
				if (is_numeric(stripos($flections[$i][0], "Dativ"))) {
					$grammar_attributes[$i]->kasus = "dative";
				}
				if (is_numeric(stripos($flections[$i][0], "Singular"))) {
					$grammar_attributes[$i]->numerus = "singular";
				}
				if (is_numeric(stripos($flections[$i][0], "Plural"))) {
					$grammar_attributes[$i]->numerus = "plural";
				}
			}
	
		}
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
				if (($grammar_attributes[$i]->tempus . "-" . $grammar_attributes[$i]->name . "-" . $grammar_attributes[$i]->genus . "-" . $grammar_attributes[$i]->numerus . "-" . $grammar_attributes[$i]->kasus == $grammar_attributes_unique[$j]->tempus . "-" . $grammar_attributes_unique[$j]->name . "-" . $grammar_attributes_unique[$j]->genus . "-" . $grammar_attributes_unique[$j]->numerus . "-" . $grammar_attributes_unique[$j]->kasus)) {
					$matched = true;
				}
			}
			if ($matched == false) {
				$grammar_attributes_unique[$u] = $grammar_attributes[$i];
				$u++;
	
			}
		}
			
		if (count($grammar_attributes_unique) > 0) {
			return $grammar_attributes_unique;
		}
	}
	function getFlexions_From_NonFlected($structure, $word_type) {
		$iex = new Extraction();
	
		//echo "type: " . $word_type . "\n";
		$grammar_attributes = array();
		$grammar_attributes[0] = new Grammar_Flexion();
			
		$flections = array();
	
		$j = 0;
		if ($word_type == "verb") {
			//$iex = new Extraction();
	
			//$iex->showArray($structure);
			//echo "lex: " . $name . "\n";
			$flections_temp = $iex->search($structure, "flexbox");
				
			//print_r($flections_temp);
				
			$grammar_attributes[0]->person = 1;
			$grammar_attributes[0]->numerus = "singular";
			if (is_array($flections_temp[0][1][1][2])) {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][1][2][0]));
			} else {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][1][2]));
			}
			$grammar_attributes[0]->name = $strings[0];
	
			$grammar_attributes[1]->person = 2;
			$grammar_attributes[1]->numerus = "singular";
			if (is_array($flections_temp[0][1][1][2])) {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][2][1][0]));
			} else {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][2][1]));
			}
			$grammar_attributes[1]->name = $strings[0];
	
			$grammar_attributes[2]->person = 3;
			$grammar_attributes[2]->numerus = "singular";
			if (is_array($flections_temp[0][1][1][2])) {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][3][1][0]));
			} else {
				$strings = explode(" ", str_replace("[", "",$flections_temp[0][1][3][1]));
			}
			$grammar_attributes[2]->name = $strings[0];
				
			$grammar_attributes[3]->person = 1;
			$grammar_attributes[3]->numerus = "plural";
				
			$grammar_attributes[4]->person = 3;
			$grammar_attributes[4]->numerus = "plural";
	
		} else if ($word_type == "preposition") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "adverb") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "particle") {
			$grammar_attributes[0]->name = null;
		} else if ($word_type == "pronoun-flex") {
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
			$flections_temp = $iex->search($structure, "inflection-table");
				
			//print_r($flections_temp);
				
			$temp_genus = $this->getGenus($structure, $word_type);
				
			$grammar_attributes[0]->genus = $temp_genus;
			$grammar_attributes[0]->numerus = "singular";
			$grammar_attributes[0]->kasus = "nominative";
			$grammar_attributes[0]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][1]));
	
			$grammar_attributes[1]->genus = $temp_genus;
			$grammar_attributes[1]->numerus = "singular";
			$grammar_attributes[1]->kasus = "genitive";
			if (is_array($flections_temp[0][1][2][1])) {
				$grammar_attributes[1]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][1][0]));
			} else {
				$grammar_attributes[1]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][1]));
			}
	
			$grammar_attributes[2]->genus = $temp_genus;
			$grammar_attributes[2]->numerus = "singular";
			$grammar_attributes[2]->kasus = "dative";
			$grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][1]));
	
				
			$grammar_attributes[3]->genus = $temp_genus;
			$grammar_attributes[3]->numerus = "singular";
			$grammar_attributes[3]->kasus = "accusative";
			$grammar_attributes[3]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][1]));
				
			if ($flections_temp[0][1][1][2][0] != "?") {
				$grammar_attributes[4]->genus = $temp_genus;
				$grammar_attributes[4]->numerus = "plural";
				$grammar_attributes[4]->kasus = "nominative";
				$grammar_attributes[4]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][2]));
	
				$grammar_attributes[5]->genus = $temp_genus;
				$grammar_attributes[5]->numerus = "plural";
				$grammar_attributes[5]->kasus = "genitive";
				$grammar_attributes[5]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][2]));
					
				$grammar_attributes[6]->genus = $temp_genus;
				$grammar_attributes[6]->numerus = "plural";
				$grammar_attributes[6]->kasus = "dative";
				$grammar_attributes[6]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][2]));
					
				$grammar_attributes[7]->genus = $temp_genus;
				$grammar_attributes[7]->numerus = "plural";
				$grammar_attributes[7]->kasus = "accusative";
				$grammar_attributes[7]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][2]));
			}
				
		} else if ($word_type == "article") {
				
			//$iex->showArray($structure);
				
			$flections_temp = $iex->search($structure, "wikitable float-right hintergrundfarbe2");
				
			//print_r($flections_temp[0][1][1]);
			$grammar_attributes[0]->genus = "mas";
			$grammar_attributes[0]->numerus = "singular";
			$grammar_attributes[0]->kasus = "nominative";
			$grammar_attributes[0]->name = $iex->getLastElementFromString($flections_temp[0][1][1][1]);
				
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
				
			//echo "plural-article: " . utf8_encode($flections_temp[0][1][1][4]) . "\n";
			if ($flections_temp[0][1][1][4] != "?" && utf8_decode($flections_temp[0][1][1][4]) != "?") {
				$grammar_attributes[12]->numerus = "plural";
				$grammar_attributes[12]->kasus = "nominative";
				$grammar_attributes[12]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][4]));
	
				$grammar_attributes[13]->numerus = "plural";
				$grammar_attributes[13]->kasus = "genitive";
				$grammar_attributes[13]->name = $iex->getLastElementFromString(($flections_temp[0][1][2][4]));
	
				$grammar_attributes[14]->numerus = "plural";
				$grammar_attributes[14]->kasus = "dative";
				$grammar_attributes[14]->name = $iex->getLastElementFromString(($flections_temp[0][1][3][4]));
	
				$grammar_attributes[15]->numerus = "plural";
				$grammar_attributes[15]->kasus = "accusative";
				$grammar_attributes[15]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][4]));
			}
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
		} else if ($word_type == "pronoun" || $word_type == "pronoun-inter" || $word_type == "pronoun-rel" || $word_type == "pronoun-indef") {
			$iex = new Extraction();
				
				
			$flections_temp = $iex->search($structure, "wikitable float-right hintergrundfarbe2");
				
			$iex->showArray($structure);
				
			//print_r($flections_temp[0][1][1]);
			if ($flections_temp) {
				$grammar_attributes[0]->genus = "mas";
				$grammar_attributes[0]->numerus = "singular";
				$grammar_attributes[0]->kasus = "nominative";
				$grammar_attributes[0]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][1]));
					
				$grammar_attributes[1]->genus = "fem";
				$grammar_attributes[1]->numerus = "singular";
				$grammar_attributes[1]->kasus = "nominative";
				$grammar_attributes[1]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][2]));
	
				//print_r($flections_temp[0][1][1][3]);
					
				$grammar_attributes[2]->genus = "neu";
				$grammar_attributes[2]->numerus = "singular";
				$grammar_attributes[2]->kasus = "nominative";
				$grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][3]));
				if (!$grammar_attributes[2]->name) $grammar_attributes[2]->name = $iex->getLastElementFromString(($flections_temp[0][1][1][3][0]));
	
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
				$grammar_attributes[9]->numerus = "accusative";
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
				if (!$grammar_attributes[11]->name) $grammar_attributes[11]->name = $iex->getLastElementFromString(($flections_temp[0][1][4][3][0]));
	
	
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
			return $grammar_attributes_unique;
		}
	}
	function getGenus($structure, $type) {
		$genus = "";
	
		$iex = new Extraction();
	
		if ($type == "substantive") {
			$genus_sub = $iex->search($structure, utf8_decode("Genus: Maskulinum"));
				
			if (count($genus_sub) == 1) {
				$genus = "mas";
			}
				
			$genus_sub = $iex->search($structure, utf8_decode("Genus: Femininum"));
				
			if (count($genus_sub) == 1) {
				$genus = "fem";
			}
		}
	
		return $genus;
	}
	function gettype_Structures($structure_unfiltered) {
		//print_r($structure_unfiltered);
	
		$iex = new Extraction();
	
		$h2s = $iex->search($structure_unfiltered, "h2", true, 0);
	
		if (count($h2s) <= 2) {
			$h3s = $iex->search($h2s[0], "h3", true, 0);
		} else {
			$h3s = $iex->search($h2s[1], "h3", true, 0);
		}
	
	
	
		return $h3s;
	}
	function gettype_Flections($structure) {
		$iex = new Extraction();
	
		$structure = $iex->search($structure, "Grammatische Merkmale:", true, 0);
		//$iex->showArray($structure);
		
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
	}
	function gettype($structure) {
		$iex = new Extraction();
		//$iex->showArray($structure);
		
		$type_by_flections = $this->gettype_Flections($structure);
		//echo "gettype\n\n\n";
	
		//print_r($structure[2][0]);
		
		if (!is_array($structure[2][0][1])) {
			$type_string = $structure[2][0][1];
			if ($structure[2][0][1] == ",") {
				$type_string = $structure[2][0][0][1];
			}
		} else {
			$type_string = $structure[2][0][0][1];
		}
		
		
		//echo "type: " . utf8_decode($type_string) . "\n";
		if (!isset($type_string)) return null;
		
	
		switch (utf8_decode($type_string)) {
			case 'Deklinierte Form':
				$type = $type_by_flections;
				break;
			case 'Artikel':
				$type = "article";
				break;
			case 'Numerale':
				$type = "numerale";
				break;
			case 'Gradpartikel':
				$type = "particle";
				break;
			case utf8_decode('Präposition'):
				$type = "preposition";
				break;
			case 'Subjunktion':
				$type = "subjunction";
				break;
			case 'Kontraktion':
				$type = "contraction";
				break;
			case 'Konjunktion':
				$type = "conjunction";
				break;
			case 'Adverb':
				$type = "adverb";
				break;
			case 'Indefinitpronomen':
				$type = "pronoun-indef";
				break;
			case 'Demonstrativpronomens':
				$type = "pronoun-demo";
				break;
			case 'Personalpronomen':
				$type = "pronoun-pers";
				break;
			case 'Interrogativpronomen':
				$type = "pronoun-inter";
				break;
			case 'Reflexivpronomen':
				$type = "pronoun-flex";
				break;
			case 'Relativpronomen':
				$type = "pronoun-rel";
				break;
			case 'Pronomen':
				$type = "pronoun";
				break;
			case 'Subjunktion':
				$type = "subjunction";
				break;
			case 'Adjektiv':
				$type = "adjective";
				break;
			case 'Konjugierte Form':
			case 'Partizip':
			case 'Partizip II':
			case 'Erweiterter Infinitiv':
			case 'Verb':
				$type = "verb";
				break;
			case 'Substantiv':
				$type = "substantive";
				break;
			default:
				$type = null;
				break;
					
		}
		//$iex->showArray($structure);
	
		return $type;
	}
	function getTranslation($word, $target_lan = "en") {
		$structure = $this->getContent($word);
	
		$trans = $iex->search($structure, utf8_decode("Übersetzungen"), true, 0);
		$trans_en = $iex->search($trans, utf8_decode("Englisch"), true, 0);
	
		$iex = new Extraction();
		//$iex->showArray($trans_en);
	
		$english = $trans_en[0][2];
	
		return $english;
	}

}
?>
