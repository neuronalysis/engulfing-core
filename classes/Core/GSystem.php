<?php
class GSystem {
	var $hostmode;
	function __construct() {
	}
	function groupByWeek($datefield, $ungrouped, $valuefield) {
		$today = $this->getToday();
		
		for ($i=0; $i<count($ungrouped); $i++) {
			$dif = $this->compareDate($this->getToday(), $ungrouped[$i]->$datefield);
			$backweek =  intval($dif / 7);
			
			if ($backweek < 50 && $i < 1000) {
				$grouped[$backweek] = $grouped[$backweek] + $ungrouped[$i]->$valuefield;
			}
		}
		
		return $grouped;
	}
	function logTimeStamps($timestamps) {
		echo "start time: " . $timestamps[0] . "\n";
		
		for ($i=0; $i<count($timestamps); $i++) {
			if ($i>0) {
				echo "time used step $i: " . ($timestamps[$i] - $timestamps[$i-1]) . "\n";
			}
				
		}
		
		echo "end time: " . $timestamps[count($timestamps)-1] . "\n";
		echo "total time used: " . ($timestamps[count($timestamps)-1] - $timestamps[0]) . "\n";
	}
	function logHTTP() {
		global $username, $password, $PHPSESSid;
        global $REMOTE_ADDR, $REQUEST_URI;
		
		$today = $this->getToday();		$now = $this->getNow();
		
		$openfile = getcwd() . "/logs/http/" . "log-" . $today . ".txt";
		$file_source = fopen ($openfile, "a+");
		
        if (!$file_source) {
            echo "<p>Datei konnte nicht ge&uuml;ffnet werden.\n";
            exit;
        }
		fwrite($file_source, $today . ";" . $now . ";" . $REMOTE_ADDR . ";" . $REQUEST_URI . ";" . $PHPSESSid . ";" . $username . ";;" . "\n");
    fclose($file_source);
	}
	function logDB($querystring) {
		if (ereg("SELECT", $querystring)) return;
	
		global $username, $password, $PHPSESSid;
        global $REMOTE_ADDR, $REQUEST_URI;
		
		$today = $this->getToday();		$now = $this->getNow();
		$openfile = getcwd() . "/logs/db/" . "log-" . $today . ".txt";
		$file_source = fopen ($openfile, "a+");
		
    if (!$file_source) {
        echo "<p>Datei konnte nicht ge&uuml;ffnet werden.\n";
        exit;
    }
		fwrite($file_source, $today . ";" . $now . ";" . $username . ";" . $querystring . ";;" . "\n");
    fclose($file_source);
	}
	function getOak($oaking) {
		$heute = $this->getToday(); 
		if ($oaking == "year") {
			return $heute['year'] . "-" . "01" . "-" . "01";
		}
		if ($oaking == "quarter") {
			return $this->getQuarterStart($heute);
		}
	}
	function getQuarterEnd($date) {
		$exp = explode("-", $date);
		$year = intval($exp[0]);
		$month = intval($exp[1]);
		$day = intval($exp[2]);
		
		if ($month >= 0 && $month <= 3) {
			return $year . "-" . "03" . "-" . "31";
		} else if ($month >= 4 && $month <= 6) {
			return $year . "-" . "06" . "-" . "30";
		} else if ($month >= 7 && $month <= 9) {
			return $year . "-" . "09" . "-" . "30";
		} else if ($month >= 10 && $month <= 12) {
			return $year . "-" . "12" . "-" . "31";
		}
	}
	function getQuarterStart($date) {
		$exp = explode("-", $date);
		$year = intval($exp[0]);
		$month = intval($exp[1]);
		$day = intval($exp[2]);
		
		if ($month >= 0 && $month <= 3) {
			return $year . "-" . "01" . "-" . "01";
		} else if ($month >= 4 && $month <= 6) {
			return $year . "-" . "04" . "-" . "01";
		} else if ($month >= 7 && $month <= 9) {
			return $year . "-" . "07" . "-" . "01";
		} else if ($month >= 10 && $month <= 12) {
			return $year . "-" . "10" . "-" . "01";
		}
	}
	function getToday() {
		$heute = getdate(); 
		if (strlen($heute['mon']) == 1) $heute['mon'] = "0" . $heute['mon'];
		return $heute['year'] . "-" . $heute['mon'] . "-" . $heute['mday'];
	}
	function getNow() {
		$jetzt = getdate(); 
		return $jetzt['hours'] . ":" . $jetzt['minutes'] . ":" . $jetzt['seconds'];
	}
	function datediff($date1, $date2) {
		$dStart = new DateTime(date($date1));
		$dEnd  = new DateTime(date($date2));
		$dDiff = $dStart->diff($dEnd, false);
	
		$dDiff->format('%R'); // use for point out relation: smaller/greater
		
		if ($dDiff->invert) {
			return $dDiff->days * -1;
		} else {
			return $dDiff->days;
		}
	}
	function compareDate($tocompare, $target = null) {
		if ($target == null) $target = $this->getToday();
		
		if (!ereg("-", $target)) $target = $this->convertDateCHtoUS($target);
		if (!ereg("-", $tocompare)) $tocompare = $this->convertDateCHtoUS($tocompare);
		
		$exp1 = explode("-", $tocompare);
		$year1 = intval($exp1[0]);
		$month1 = intval($exp1[1]);
		$day1 = intval($exp1[2]);
		
		$exp2 = explode("-", $target);
		$year2 = intval($exp2[0]);
		$month2 = intval($exp2[1]);
		$day2 = intval($exp2[2]);
		
		if (($year1 + $month1 + $day1) != 0 && ($year2 + $month2 + $day2) != 0) {
			if ($year1 > 1970 && $year2 > 1970) {
				$t_indic1 = strtotime(date ("d F Y", mktime(0,0,0,$month1,$day1,$year1)));
				$t_indic2 = strtotime(date ("d F Y", mktime(0,0,0,$month2,$day2,$year2)));
			} else {
				$t_indic1 = ($year1 * 365.25 + $month1 * 30 + $day1);
				$t_indic2 = ($year2 * 365.25 + $month2 * 30 + $day2);
			}
			if (($t_indic1 - $t_indic2) == 0) {
				$timediff = 0;
			} else {
				$timediff = (int) (($t_indic1 - $t_indic2) / 3600 / 24);
			}
		} else {
			return false;
		}
		
		return $timediff;
	}
	function getDateByStamp($timestamp) {
		$year = substr($timestamp, 0, 4);
		$month = substr($timestamp, 4, 2);
		$day = substr($timestamp, 6, 2);
		
		return $day . "." . $month . "." . $year;
	}
	function getStampByDate($timestamp) {
		$year = substr($timestamp, 6, 4);
		$month = substr($timestamp, 3, 2);
		$day = substr($timestamp, 0, 2);
		
		return $year . $month . $day;
	}
	function renderArray($array, $key) {
		$str = "";
		
		for ($i=0; $i<count($array); $i++) {
			$str .= $array[$i]->$key;
			if ($i < count($array)-1) $str .= ", ";
		}
		
		return $str;
	}
	function convertDateUStoCH($toconvert, $format = null) {
		$toconvert = str_replace(" ", "", $toconvert);
		$ch = explode(".", $toconvert);
		$us = explode("-", $toconvert);
		if (count($us) == 3) {
			if ($format == null) {
				$converted = substr("00", 0, 2-strlen($us[2])) . $us[2] . "." . substr("00", 0, 2-strlen($us[1])) . $us[1] . "." . $us[0];
			} else if ($format == "dd.mm.yy") {
				$converted = substr("00", 0, 2-strlen($us[2])) . $us[2] . "." . substr("00", 0, 2-strlen($us[1])) . $us[1] . "." . substr($us[0], 2, 2);
			}
		} else if (count($ch) > 0) {
			$converted = $this->makeCHnice($toconvert);
		} else {
			$converted = "";
		}
		return $converted;
	}
	function convertDateCHtoUS($toconvert, $makenice = true) {
		$toconvert = str_replace(" ", "", $toconvert);
		$ch = explode(".", $toconvert);
		$us = explode("-", $toconvert);
		if (count($ch) == 3) {
			if (strlen($ch[2]) == 2) $ch[2] = "20" . $ch[2];
			if (strlen($ch[2]) == 1) $ch[2] = "200" . $ch[2];
			$converted = $ch[2] . "-" . substr("00", 0, 2-strlen($ch[1])) . $ch[1] . "-" .  substr("00", 0, 2-strlen($ch[0])) . $ch[0] ;
		} else if (count($us) > 0) {
			if ($makenice) {
				$converted = $this->makeUSnice($toconvert);
			} else {
				$converted = $toconvert;
			}
		} else {
			$converted = "";
		}
		
		return $converted;
	}
	function makeCHnice($ugly) {
		$ch = explode(".", $ugly);
		if (count($ch) != 3) {
			$nice = "";
		} else {
			if (strlen($ch[2]) == 2) $ch[2] = "20" . $ch[2];
			$nice = substr("00", 0, 2-strlen($ch[0])) . $ch[0] . "." .  substr("00", 0, 2-strlen($ch[1])) . $ch[1] . "." . substr("0000", 0, 4-strlen($ch[2])) . $ch[2];
		}
		
		return $nice;
	}
	function makeUSnice($ugly) {
		$us = explode("-", $ugly);
		if (count($us) != 3) {
			$nice = "";
		} else {
			$nice = substr("0000", 0, 4-strlen($us[0])) . $us[0] . "-" .  substr("00", 0, 2-strlen($us[1])) . $us[1] . "-" . substr("00", 0, 2-strlen($us[2])) . $us[2];
		}
		
		return $nice;
	}
  function wellformSubst ($subst, $type = null) {
    $wellformed = ucwords(strtolower ($subst));
    return $wellformed;
  }
	function resetJavaScript($content) {
		$content = str_replace("class=line_selected", "class=line_swap", $content);
	
		return $content;
	}
	function convertToDecimal ($source, $decimals = 2, $afterzero = 2) {
		if ($decimals > 0) {
			$converted = substr($source, 0, strlen($source)-$decimals) . "." . substr($source, strlen($source)-$decimals, $decimals);
		} else {
			$converted = substr($source, 0, strlen($source));
		}
		$converted = number_format ($converted, 2, ".", "");
	
		return $converted;
	}
  function _get_contents ($file_name) {
  	return implode("", file($file_name));
  }
	function writeStream($stream, $targetfile) {
		$file = fopen($targetfile, "w");
    	
		fwrite($file, $stream); 
		
		fclose($file);
	}
	function utf8_encode_file($file_name, $wellform = false, $noencoding = null) {
		$content = $this->_get_contents($file_name);
		//$utfconform = utf8_encode ($this->_get_contents($file_name));
		/*
		if ($wellform == true) $utfconform = $this->wellformXML($utfconform);
		*/
		if ($wellform == true) $content = $this->wellformUTF8($content);
		if ($noencoding != null) $content = $this->removeEncoding($content, $noencoding);
		$this->writeStream($content, $file_name);
	}
	function removeEncoding($withencoding, $encoding) {
		return str_replace($encoding, "", $withencoding);
	}
	function addDays($urdate, $days) {
		$exp1 = explode("-", $urdate);
		$year1 = intval($exp1[0]);
		$month1 = intval($exp1[1]);
		$day1 = intval($exp1[2]);
		
		$t_indic1 = strtotime(date ("d F Y", mktime(0,0,0,$month1,$day1,$year1))) / 3600 / 24;
		
		$newdatestamp = ($t_indic1 + $days) * 3600 * 24;
		
		$newdate = $this->Convert($newdatestamp, true);
		
		return $newdate;
	}
	function Convert($source, $date = true) {
    	$monat = $source['mon']; 
    	$tag = $source['mday']; 
    	$jahr = $source['year']; 
    	$stunde = $source['hours']; 
    	$minute = $source['minutes']; 
    	$sekunde = $source['seconds'];
		
		if ($date) {
			$date = date("Y-m-d", $source);
			return $date;
		} else {
			return "$stunde:$minute:$sekunde";
		}
	}
	function formatTime($time) {
		return date_format(date_create($time), "H:i");		
	}
	function formatDate($date) {
		return date_format(date_create($date), "M j");		
	}
	function convertDateToPeriod($date, $frequency) {
		if (checkdate(date("m", strtotime($date)), date("y", strtotime($date)),date("d", strtotime($date))) == false) {
			return $date;
		}
		
		if ($frequency == 30) {
			$period = date_format(date_create($date), "M");
		} elseif($frequency == 90) {
			$period = "Q" . ceil(date("m", strtotime($date))/3);
		} else {
			$period = date_format(date_create($date), "M");
		}
			
		
		return $period;
	}
	function convertCurrency($valuta, $from, $to) {
		$fxrates['USD']['USD'] = 1;
		$fxrates['USD']['CHF'] = 1.2295;
		$fxrates['USD']['GBP'] = 0.5447;
		$fxrates['USD']['HUF'] = 210.0500;
		
		if ($fxrates['USD'][$from]) $fxrate = $fxrates['USD'][$to] * (1 / $fxrates['USD'][$from]);
		
		$converted = $valuta * $fxrate;
		
		$converted = 		$converted = number_format ($converted, 2, ".", "");
		
		return $converted; 
	}
	function bubbleSort($sort_array,$column = 0,$reverse = false) {
	  $lunghezza=count($sort_array);
	  
		for ($i = 0; $i < $lunghezza ; $i++){
	    for ($j = $i + 1; $j < $lunghezza ; $j++){
	      if($reverse){
	        if ($sort_array[$i][$column] < $sort_array[$j][$column]){
	          $tmp = $sort_array[$i];
	          $sort_array[$i] = $sort_array[$j];
	          $sort_array[$j] = $tmp;
	        }
	      }else{
	        if ($sort_array[$i][$column] > $sort_array[$j][$column]){
	          $tmp = $sort_array[$i];
	          $sort_array[$i] = $sort_array[$j];
	          $sort_array[$j] = $tmp;
	        }
	      }
	    }
	  }
		
	  return $sort_array;          
	} 
	function bubbleSortObject($sort_array,$sort_key,$reverse = false) {
	  $lunghezza=count($sort_array);
	  
		for ($i = 0; $i < $lunghezza ; $i++){
	    for ($j = $i + 1; $j < $lunghezza ; $j++){
	      if($reverse){
	        if ($sort_array[$i]->$sort_key < $sort_array[$j]->$sort_key){
	          $tmp = $sort_array[$i];
	          $sort_array[$i] = $sort_array[$j];
	          $sort_array[$j] = $tmp;
	        }
	      }else{
	        if ($sort_array[$i]->$sort_key > $sort_array[$j]->$sort_key){
	          $tmp = $sort_array[$i];
	          $sort_array[$i] = $sort_array[$j];
	          $sort_array[$j] = $tmp;
	        }
	      }
	    }
	  }
		
	  return $sort_array;          
	} 
	/*
	* Methoden, noch nicht entg�ltig einer Klasse zugewiesen sind
	*/
	function eliminateXMLVersionOutOfString($source) {
    	$result = str_replace("<?xml version=" . chr(34) . "1.0" . chr(34) . "?>", "", $source);
		
		return $result;
	}
	function eliminateXMLVersion($pnrs) {
		for ($i=0; $i<count($pnrs); $i++) {
      		if (file_exists(getcwd() . "/xml/xmlSelectResponses/" . $pnrs[$i])) {
    			$d = dir(getcwd() . "/xml/xmlSelectResponses/" . $pnrs[$i]);
    		} else {
    			return false;
    		}
    		
    		while($entry=$d->read()) {
       			if (ereg(".xml", $entry)) {
    				$stream = "";
    		
    				$source = getcwd() . "/xml/xmlSelectResponses/" . $pnrs[$i] . "/" . $entry;
    				if (file_exists($source)) {
    					$fp = fopen ($source, "r");
    		
    					$stream = fread($fp, filesize($source));
    			
    					fclose ($fp);
    					$stream = $this->eliminateXMLVersionOutOfString($stream);
    					$fp = fopen ($source, "w");
    		
    					fwrite($fp, $stream);
    		
    					fclose($fp);
    				}
    			}
    		}
    		
    		$d->close();
		}
	}
	function trimString($string, $length = 15, $after = "...") {
		if (strlen($string) < $length) {
			return $string;
		} else {
			return substr($string, 0, $length) . $after;
		}
	}
	function roundit($value, $dec, $sm) {
		$mul = pow(10, $dec);
	
		$result = number_format(round($value/$sm) * ($sm), 2);
		
		return $result;
	}
	function removeHTML($badformed) {
		$badformed = str_replace("&auml;", "�", $badformed);
		$badformed = str_replace("&uuml;", "�", $badformed);
		$badformed = str_replace("&ouml;", "�", $badformed);
		
		$wellformed = $badformed;
		
		return $wellformed;
	}
	function wellformXML($badformed) {
		$badformed = str_replace("\r", "", str_replace("", "", $badformed));
		$badformed = str_replace("�", "&uuml;", $badformed);
		$badformed = str_replace("�", "&Uuml;", $badformed);
		$badformed = str_replace("�", "&auml;", $badformed);
		$badformed = str_replace("�", "&Auml;", $badformed);	
		$badformed = str_replace("�", "&ouml;", $badformed);
		$badformed = str_replace("�", "&Ouml;", $badformed);
		$badformed = str_replace("ü", "&uuml;", $badformed);
		$badformed = str_replace("Ü", "&Uuml;", $badformed);
		$badformed = str_replace("ä", "&auml;", $badformed);
		$badformed = str_replace("Ä", "&Auml;", $badformed);	
		$badformed = str_replace("ö", "&ouml;", $badformed);
		$badformed = str_replace("Ö", "&Ouml;", $badformed);
		$badformed = str_replace("�", " ", $badformed);
		$badformed = str_replace("\\", "", $badformed);
		
		$wellformed = $badformed;
		
		return $wellformed;
	}
	function setHTML($badformed) {
		$badformed = str_replace("�", "&auml;", $badformed);
		$badformed = str_replace("�", "&uuml;", $badformed);
		$badformed = str_replace("�", "&ouml;", $badformed);
		
		$wellformed = $badformed;
		
		return $wellformed;
	}
}
?>