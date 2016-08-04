<?php
class Wiktionary {
	function __construct() {
		$this->language;
	}
	function getWords($regexp) {
		$words = array();
	
		unset($temp);
		unset($flection);
			
		unset($temp_wordname);
		unset($temp_type);
	
		$iex = new Extraction();
	
		$encoding = "";
		$structure = $this->getContent($regexp);
		//$iex->showArray($structure);
		
		//return null;
		if (!isset($structure)) {
			return null;
		}
	
		$type_structures = $this->gettype_Structures($structure);
		//$iex->showArray($type_structures[1]);
		
		if (count($type_structures) == 0) return null;
			
			
		//echo "count: " . count($type_structures) . "\n";
			
		$j=0;
			
		for ($w=0; $w<count($type_structures); $w++) {
			$temp[$w] = new Word();
	
			$temp[$w]->Type = $this->gettype($type_structures[$w]);
	
			//echo "type:  " .  $temp[$w]->Type . "\n";
			//$iex->showArray($type_structures[$w]);
			
			if (!isset($temp[$w]->Type)) break;
			
			//echo "type: " .  $temp[$w]->Type . "\n";
			
			
			$isFlected[$w] = $this->IsFlected($type_structures[$w], $temp[$w]->Type);
			if ($isFlected[$w] == true) {
				echo $temp[$w]->Type . " flected \n";
				
				$flections[$w] = $this->getFlexions_From_Flected($type_structures[$w], $temp[$w]->Type);
			} else {
				echo $temp[$w]->Type . " not flected \n";
				
				$flections[$w] = $this->getFlexions_From_NonFlected($type_structures[$w], $temp[$w]->Type);
			}
			
			//print_r($flections[$w]);
			
			$Lexemes[$w] = $this->getLexeme($type_structures[$w], $temp[$w]->Type, $isFlected[$w], $regexp);
	
			//print_r($Lexemes[$w]);
			
			//print_r($flections[$w]);
			
			for ($i=0; $i<count($flections[$w]); $i++) {
				$words[$j] = new Word();
				 
				$words[$j]->Type = $temp[$w]->Type;
				 
				 
				if (isset($flections[$w][$i]->name)) {
					$words[$j]->name = utf8_decode($flections[$w][$i]->name);
				} else {
					$words[$j]->name = utf8_decode($regexp);
				}
	
				$words[$j]->numerus = $flections[$w][$i]->numerus;
				$words[$j]->kasus = $flections[$w][$i]->kasus;
				$words[$j]->genus = $flections[$w][$i]->genus;
				$words[$j]->person = $flections[$w][$i]->person;
				$words[$j]->tempus = $flections[$w][$i]->tempus;
				 
				$words[$j]->language = $this->language;
				
				if (isset($Lexemes[$w]->id)) {
					$words[$j]->Lexeme_id = $Lexemes[$w]->id;
				}
				 
				
				$response_words[$j] = $words[$j]->save();
				 
				//print_r($words[$j]);
				$j++;
			}
		}
		
		//print_r($words);
			
		return $words;
	}
	function getWordsByNameAndtypeAndLanguage($name, $word_type, $language) {
		$preselect = $this->getWords($name);
	
		$words = array();
	
		$j=0;
		for ($i=0; $i<count($preselect); $i++) {
			if ($preselect[$i]->Type == $word_type && $preselect[$i]->language == $language) {
				$words[$j] = $preselect[$i];
				$j++;
			}
		}
	
		return $words;
	}
	
}
?>
