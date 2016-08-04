<?php
class Website_Wiktionary extends Website {
	var $baseurl;
	var $ressource;
	var $website_directory;
	var $website_sections;
	
	
	function __construct() {
	}
	function parse($page = 1) {
		if (!$this->dom) return;
		
		$this->xpath = new DOMXPath($this->dom);
		
		$this->getDirectory();
		
		$this->getSections("English");
		
		if (count($this->website_directory->website_chapters) == 0) {
			for ($i=0; $i<count($this->website_sections); $i++) {
				$this->website_directory->website_chapters[$i] = new Website_Chapter();
				$this->website_directory->website_chapters[$i]->title = $this->website_sections[$i]->title;
				
				for ($j=0; $j<count($this->website_sections[$i]->website_sections); $j++) {
					$this->website_directory->website_chapters[$i]->website_chapters[$j] = new Website_Chapter();
					$this->website_directory->website_chapters[$i]->website_chapters[$j]->title = $this->website_sections[$i]->website_sections[$j]->title;
				}
			}
		}
	}
	function getSections($language = null) {
		$sections = array();
		
		if (count($this->website_directory->website_chapters) > 0) {
			$i=0;
			foreach ($this->website_directory->website_chapters as $chapter) {
				if ($language != null) {
					if ($chapter->href == $language) {
						
						$xpath = '//h2[span/@id="' . $chapter->href . '"]';
							
						$xml_section = $this->xpath->query($xpath);
							
						$xpath_title = 'span[@class = "mw-headline"]';
						$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
							
							
						$sections[$i] = new Website_Section();
						$sections[$i]->title = $xml_title->item(0)->textContent;
						
						$sections[$i]->website_sections = $this->getSubSections($chapter);
						$sections[$i]->website_content = $this->getContent($chapter, $xml_section->item(0));
							
						$i++;
					}
			
				} else {
					$xpath = '//h2[span/@id="' . $chapter->href . '"]';
			
					$xml_section = $this->xpath->query($xpath);
			
					$xpath_title = 'span[@class = "mw-headline"]';
					$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
			
			
					$sections[$i] = new Website_Section();
					$sections[$i]->title = $xml_title->item(0)->textContent;
					
					$sections[$i]->website_sections = $this->getSubSections($chapter);
					$sections[$i]->website_content = $this->getContent($chapter, $xml_section->item(0));
			
					$i++;
				}
					
			}
		} else {
			$xpath = '//h2';
				
			$xml_section = $this->xpath->query($xpath);
				
			$xpath_title = 'span[@class = "mw-headline"]';
			$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
				
				
			$sections[0] = new Website_Section();
			if ($xml_title->item(0)) $sections[0]->title = $xml_title->item(0)->textContent;
			
			$sections[0]->website_sections = $this->getSubSections();
			$sections[0]->website_content = $this->getContent(null, $xml_section->item(0));
				
		}
		
		
		
		$this->website_sections = $sections;
	}
	function getContent($chapter, $context) {
		$xpath_content = 	'following-sibling::*';
		
		$xml_content = $this->xpath->query($xpath_content, $context);
		
		$content = new Website_Wiktionary_Content();
		
		foreach ($xml_content as $item) {
			if (substr($item->nodeName, 0, 1) == "h") {
				break;
			} else if (substr($item->nodeName, 0, 1) == "p") {
				$xml_content_name = $this->xpath->query('strong', $item);
				
				if ($xml_content_name->item(0)) {
					$content->name .= $xml_content_name->item(0)->nodeValue;
					
					$xml_content_word_form = $this->xpath->query('i', $item);
					
					if ($xml_content_word_form->item(0)) {
						
						$content->word_form .= $xml_content_word_form->item(0)->nodeValue;
					}
						
				}
				$xml_content_p = $this->xpath->query('strong', $item);
			} else if (substr($item->nodeName, 0, 2) == "ol") {
				$xml_content_word_forms = $this->xpath->query('li', $item);
				foreach ($xml_content_word_forms as $wordformitem) {
					$content->word_form .= $wordformitem->nodeValue;
					
					$xml_content_word_form = $this->xpath->query('span', $wordformitem);
					
					if ($xml_content_word_form->item(0)) {
						$content->word_form .= $xml_content_word_form->item(0)->nodeValue;
						
						$xml_content_word_form_of = $this->xpath->query('i', $xml_content_word_form->item(0));
						if ($xml_content_word_form_of->item(0)) {
							$content->word_form_of = $xml_content_word_form_of->item(0)->nodeValue;
						} else {
							$xml_content_word_form_of = $this->xpath->query('span/i', $xml_content_word_form->item(0));
							if ($xml_content_word_form_of->item(0)) {
								$content->word_form_of = $xml_content_word_form_of->item(0)->nodeValue;
							} else {
								$xml_content_word_form_of = $this->xpath->query('span/a', $xml_content_word_form->item(0));
								if ($xml_content_word_form_of->item(0)) {
									$content->word_form_of = $xml_content_word_form_of->item(0)->nodeValue;
								}
							}
						}
						
					}
					
					
					
				}
				//echo "arsch";
			} else {
				//$content->text .= $item->nodeValue;
			}
		}
		
		return $content;
	}
	function getSubSections($chapter = null) {
		$sections = array();
		
		if ($chapter) {
			$i=0;
			foreach ($chapter->website_chapters as $subchapter) {
				$xpath = '//h3[span/@id="' . $subchapter->href . '"]';
					
				$xml_section = $this->xpath->query($xpath);
			
				//print_r($xml_section);
				$xpath_title = 'span[@class = "mw-headline"]';
				$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
					
					
				$sections[$i] = new Website_Section();
				$sections[$i]->title = $xml_title->item(0)->textContent;
				
				$sections[$i]->website_sections = $this->getSubSubSections($subchapter);
				$sections[$i]->website_content = $this->getContent($subchapter, $xml_section->item(0));
			
				$i++;
			}
		} else {
			$xpath = '//h3[not(not(span[@class = "mw-headline"]))]';
				
			$xml_section = $this->xpath->query($xpath);
			
			$i=0;
			foreach ($xml_section as $section_item) {
				$xpath_title = 'span[@class = "mw-headline"]';
				$xml_title = $this->xpath->query($xpath_title, $section_item);
				
				
				$sections[$i] = new Website_Section();
				if ($xml_title->item(0)) $sections[$i]->title = $xml_title->item(0)->textContent;
				$sections[$i]->website_content = $this->getContent(null, $section_item);
				
				$i++;
			}
			
			//$sections[0] = new Website_Section();
			//print_r($xml_section);
			
		}
		
		
		return $sections;
	}
	function getSubSubSections($chapter = null) {
		$sections = array();
		
		if ($chapter) {
			$i=0;
			foreach ($chapter->website_chapters as $subchapter) {
				$xpath = '//h4[span/@id="' . $subchapter->href . '"]';
				
				$xml_section = $this->xpath->query($xpath);
				
				if ($xml_section->item(0)) {
					//print_r($xml_section);
					$xpath_title = 'span[@class = "mw-headline"]';
					$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
						
						
					$sections[$i] = new Website_Section($xml_title->item(0)->textContent);
					$sections[$i]->website_sections = $this->getSubSubSubSections($subchapter);
					$sections[$i]->website_content = $this->getContent($subchapter, $xml_section->item(0));
					
					$i++;
				}
				
			}
		} else {
			$xpath = '//h4';
				
			$xml_section = $this->xpath->query($xpath);
				
			//print_r($xml_section);
			$xpath_title = 'span[@class = "mw-headline"]';
			$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
				
			if ($xml_title->item(0)) {
				$sections[0] = new Website_Section();
				$sections[0]->title = $xml_title->item(0)->textContent;
			}
			
			
				
		}
		
		return $sections;
	}
	function getSubSubSubSections($chapter = null) {
		$sections = array();
	
		if ($chapter) {
			$i=0;
			foreach ($chapter->website_chapters as $subchapter) {
				$xpath = '//h5[span/@id="' . $subchapter->href . '"]';
			
				$xml_section = $this->xpath->query($xpath);
			
				//print_r($xml_section);
				$xpath_title = 'span[@class = "mw-headline"]';
				$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
			
			
				$sections[$i] = new Website_Section();
				if ($xml_title->item(0)) {
					$sections[$i]->title = $xml_title->item(0)->textContent;
				}
				$sections[$i]->website_content = $this->getContent($subchapter, $xml_section->item(0));
			
				$i++;
			}
		} else {
			$xpath = '//h5';
			
			$xml_section = $this->xpath->query($xpath);
			
			//print_r($xml_section);
			$xpath_title = 'span[@class = "mw-headline"]';
			$xml_title = $this->xpath->query($xpath_title, $xml_section->item(0));
			
			
			$sections[0] = new Website_Section();
			$sections[0]->title = $xml_title->item(0)->textContent;
		}
		
	
		return $sections;
	}
	function getDirectory() {
		$xpath = '//div[@id="toc"]';
    	
    	// We starts from the root element
    	$xml_directory = $this->xpath->query($xpath);
    	
    	$this->website_directory = new Website_Directory();
    	$this->website_directory->website_chapters = $this->getChapters($xml_directory->item(0));
    	
    	/*foreach ($bodies as $body) {
     		$arrays = $this->getArrays($xpath, $body);
     	
 	  	}*/
	}
	function getChapters($context) {
		$chapters = array();
		
		$xpath = 'ul/li[contains(@class, "toclevel-1")]';
		
		$xml_content = $this->xpath->query($xpath, $context);
		$i=0;
		foreach ($xml_content as $item) { 
			$xpath_title = 'a/span[@class = "toctext"]';
			$xml_title = $this->xpath->query($xpath_title, $item);
			
			$xpath_href = 'a/@href';
			$xml_href = $this->xpath->query($xpath_href, $item);
			
			$chapters[$i] = new Website_Chapter();
			$chapters[$i]->title = $xml_title->item(0)->textContent;
			$chapters[$i]->href = substr($xml_href->item(0)->nodeValue, 1);
			
			$chapters[$i]->website_chapters = $this->getSubChapters($item);
			
			$i++;
		}
		
		return $chapters;
	}
	function getSubChapters($context) {
		$chapters = array();
	
		$xpath = 'ul/li[contains(@class, "toclevel-2")]';
	
		$xml_content = $this->xpath->query($xpath, $context);
		$i=0;
		foreach ($xml_content as $item) {
			$xpath_title = 'a/span[@class = "toctext"]';
			$xml_title = $this->xpath->query($xpath_title, $item);
			
			$xpath_href = 'a/@href';
			$xml_href = $this->xpath->query($xpath_href, $item);
			
			$chapters[$i] = new Website_Chapter();
			$chapters[$i]->title = $xml_title->item(0)->textContent;
			$chapters[$i]->href = substr($xml_href->item(0)->nodeValue, 1);
				
			$chapters[$i]->website_chapters = $this->getSubSubChapters($item);
			
			$i++;
		}
	
		return $chapters;
	}
	function getSubSubChapters($context) {
		$chapters = array();
	
		$xpath = 'ul/li[contains(@class, "toclevel-3")]';
	
		$xml_content = $this->xpath->query($xpath, $context);
		$i=0;
		foreach ($xml_content as $item) {
			$xpath_title = 'a/span[@class = "toctext"]';
			$xml_title = $this->xpath->query($xpath_title, $item);
				
			$xpath_href = 'a/@href';
			$xml_href = $this->xpath->query($xpath_href, $item);
				
			$chapters[$i] = new Website_Chapter();
			$chapters[$i]->title = $xml_title->item(0)->textContent;
			$chapters[$i]->href = substr($xml_href->item(0)->nodeValue, 1);
				
			$chapters[$i]->website_chapters = $this->getSubSubSubChapters($item);
				
			$i++;
		}
	
		return $chapters;
	}
	function getSubSubSubChapters($context) {
		$chapters = array();
	
		$xpath = 'ul/li[contains(@class, "toclevel-4")]';
	
		$xml_content = $this->xpath->query($xpath, $context);
		$i=0;
		foreach ($xml_content as $item) {
			$xpath_title = 'a/span[@class = "toctext"]';
			$xml_title = $this->xpath->query($xpath_title, $item);
	
			$xpath_href = 'a/@href';
			$xml_href = $this->xpath->query($xpath_href, $item);
	
			$chapters[$i] = new Website_Chapter($xml_title->item(0)->textContent, substr($xml_href->item(0)->nodeValue, 1));
	
			$i++;
		}
	
		return $chapters;
	}
	function getEtymologies() {
		if (!isset($this->website_directory->website_chapters)) return;
		
		$etymologies = array();
		 
		
		
    	for ($i=0; $i<count($this->website_directory->website_chapters); $i++) {
    		if (isset($this->website_directory->website_chapters[$i]->title)) {
    			
     			if ($this->website_directory->website_chapters[$i]->title == "English") {
    				//print_r($this->website_directory->website_chapters);
    				for ($j=0; $j<count($this->website_directory->website_chapters[$i]->website_chapters); $j++) {
    					if (isset($this->website_directory->website_chapters[$i]->website_chapters[$j]->title)) {
    						if (substr($this->website_directory->website_chapters[$i]->website_chapters[$j]->title, 0, 9) == "Etymology") {
    							array_push($etymologies, "Etymology " . $i);
    						}	
    					}
    					
    				}
    			}
    		}
    		
    	}
    	
		//echo "count.chapters:" . count($etymologies) . "\n";
    	
		return $etymologies;
    	 
	}
    function getWords() {
		//TODO simplyfication
    	$etymologies = $this->getEtymologies();
    	
    	$words = array();
    	
    	if (!isset($this->website_directory->website_chapters)) {
    		$word = new Word();
    		$word->name = $this->ressource;
    		$word->Type = "proper noun";
    		
    		$word->Lexeme = new Lexeme();
    		$word->Lexeme->name = $this->ressource;
    			
    		array_push($words, $word);
    		
    		return $words;
    	}
		
    	
    	for ($i=0; $i<count($this->website_directory->website_chapters); $i++) {
    		if (isset($this->website_directory->website_chapters[$i]->title)) {
    			if ($this->website_directory->website_chapters[$i]->title == "English") {
    				if (count($etymologies) <= 1) {
    					for ($j=0; $j<count($this->website_directory->website_chapters[$i]->website_chapters); $j++) {
    						if (isset($this->website_directory->website_chapters[$i]->website_chapters[$j]->title)) {
    							if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Verb") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "verb";
    								$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of);
    									
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Proper noun") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "proper noun";
    									
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    							
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Noun") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "noun";
    								$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of);
    							
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    							
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Determiner") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "determiner";
    								$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of);
    							
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    							
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Adverb") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "adverb";
    								$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of);
    							
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    							
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Pronoun") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "pronoun";
    								$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of);
    							
    								if (isset($this->website_sections[$i]->website_sections[$j]->website_content)) {
    									$word->Lexeme = new Lexeme();
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_content->word_form_of)) {
    										$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_content->word_form_of;
    									} else {
    										$word->Lexeme->name = $this->ressource;
    									}
    							
    								}
    									
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Article") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "article";
    								array_push($words, $word);
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Adjective") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "adjective";
    								array_push($words, $word);
    									
    							} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->title == "Preposition") {
    								$word = new Word();
    								$word->name = $this->ressource;
    								$word->Type = "preposition";
    								array_push($words, $word);
    							}
    						}
    						
    					}
    			
    					return $words;
    				} else {
    					for ($j=0; $j<count($this->website_directory->website_chapters[$i]->website_chapters); $j++) {
    						if (substr($this->website_directory->website_chapters[$i]->website_chapters[$j]->title, 0, 9) == "Etymology") {
    							//echo "chapters:" . count($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters);
    							for ($k=0; $k<count($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters); $k++) {
    								if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Verb") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "verb";
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k])) {
    										$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of);
    										
    										if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content)) {
    											$word->Lexeme = new Lexeme();
    											if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of)) {
    												$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of;
    											} else {
    												$word->Lexeme->name = $this->ressource;
    											}
    										}
    											
    										array_push($words, $word);
    									}
    									
    									
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Noun") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "noun";
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k])) {
    										$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of);
    										
    										if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content)) {
    											$word->Lexeme = new Lexeme();
    											if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of)) {
    												$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of;
    											} else {
    												$word->Lexeme->name = $this->ressource;
    											}
    										}
    											
    										array_push($words, $word);
    									}
    									
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Determiner") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "determiner";
    									$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of);
    										
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content)) {
    										$word->Lexeme = new Lexeme();
    										if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of)) {
		    									$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of;
		    								} else {
		    									$word->Lexeme->name = $this->ressource;
		    								}
    									}
    									
    									array_push($words, $word);
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Adverb") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "adverb";
    									
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k])) {
    										$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of);
    										
    										if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content)) {
    											$word->Lexeme = new Lexeme();
    											if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of)) {
    												$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of;
    											} else {
    												$word->Lexeme->name = $this->ressource;
    											}
    										}
    											
    										array_push($words, $word);
    										array_push($words, $word);
    									}
    									
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Pronoun") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "pronoun";
    									
    									if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k])) {
    										$word->extractWordForm($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form, $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of);
    										
    										if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content)) {
    											$word->Lexeme = new Lexeme();
    											if (isset($this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of)) {
    												$word->Lexeme->name = $this->website_sections[$i]->website_sections[$j]->website_sections[$k]->website_content->word_form_of;
    											} else {
    												$word->Lexeme->name = $this->ressource;
    											}
    										}
    											
    										array_push($words, $word);
    										array_push($words, $word);
    									}
    									
    									
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Conjunction") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "conjunction";
    									array_push($words, $word);
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Article") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "article";
    									array_push($words, $word);
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Preposition") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "preposition";
    									array_push($words, $word);
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Numeral") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "numeral";
    									
    									array_push($words, $word);
    								} else if ($this->website_directory->website_chapters[$i]->website_chapters[$j]->website_chapters[$k]->title == "Adjective") {
    									$word = new Word();
    									$word->name = $this->ressource;
    									$word->Type = "adjective";
    									
    									array_push($words, $word);    									
    								}
    							}
    						}
    					}
    					 
    				}
    			
    			}
    		}
    		
    	}
		
    	return $words;
     }
}

class Website_Wiktionary_Content extends Website_Content {
	var $text;
	var $name;
	var $word_form;
	var $word_form_of;
	
	function Website_Wiktionary_Content() {
		
	}
}
?>
