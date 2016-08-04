<?php
class Datum {
	
	var $mode;
	
	function Datum($mode = null) {
		$this->mode = $mode;
	}
	
	function Convert($source, $date = true) {
    	$monat = $source['mon']; 
    	$tag = $source['mday']; 
    	$jahr = $source['year']; 
    	$stunde = $source['hours']; 
    	$minute = $source['minutes']; 
    	$sekunde = $source['seconds'];
		
		switch ($this->mode) {
			case 'string':
				if ($date) {
					return "$jahr-$monat-$tag";
				} else {
					return "$stunde:$minute:$sekunde";
				}
			case 'stamp':
				if ($date) {
					$date = date("Y-m-d", $source);
					return $date;
				} else {
					return "$stunde:$minute:$sekunde";
				}
			default:
				break;
		}
	}
	
	function getNowDate() {
    $heute = getdate();
		return $this->Convert($heute, true);
	}
	
	function getNowTime() {
    	$heute = getdate();
		return $this->Convert($heute, false);
	}
	function addDays($urdate, $days) {
		$exp1 = explode("-", $urdate);
		$year1 = intval($exp1[0]);
		$month1 = intval($exp1[1]);
		$day1 = intval($exp1[2]);
		
		$t_indic1 = strtotime(date ("d F Y", mktime(0,0,0,$month1,$day1,$year1))) / 3600 / 24;
		
		$newdatestamp = ($t_indic1 + $days) * 3600 * 24;
		
		$this->mode = 'stamp';
		$newdate = $this->Convert($newdatestamp, true);
		$this->mode = 'string';
		
		return $newdate;
	}
	function compareDate($tocompare, $target) {
		$exp1 = explode("-", $tocompare);
		$year1 = intval($exp1[0]);
		$month1 = intval($exp1[1]);
		$day1 = intval($exp1[2]);
		
		$exp2 = explode("-", $target);
		$year2 = intval($exp2[0]);
		$month2 = intval($exp2[1]);
		$day2 = intval($exp2[2]);
		
//		echo "tocompare: " . $tocompare . "; target: " . $target . "<br/>";
		
		if (($year1 + $month1 + $day1) != 0 && ($year2 + $month2 + $day2) != 0 && ($year2 + $month2 + $day2) < 2090) {
			$t_indic1 = strtotime(date ("d F Y", mktime(0,0,0,$month1,$day1,$year1)));
			$t_indic2 = strtotime(date ("d F Y", mktime(0,0,0,$month2,$day2,$year2)));
			$timediff = ($t_indic1 - $t_indic2) / 3600 / 24;
		} else {
			return false;
		}
		
		return $timediff;
	}
	function datediff($date1, $date2) {
		$dStart = new DateTime(date($date1));
		$dEnd  = new DateTime(date($date2));
		$dDiff = $dStart->diff($dEnd);
		
		$dDiff->format('%R'); // use for point out relation: smaller/greater
		return $dDiff->days;		
	}
	function getPeriods($span, $amount, $oaking = null) {
		$sys = new GSystem();
		
		if ($oaking) {
			$today = $sys->getOak($oaking);
		} else {
			$today = $sys->getToday();
		}
		switch ($span) {
			case "quarter":
				$start = $sys->getQuarterStart($sys->addDays($today, -640));
				break;
		}
		
		$j = 0;
		for ($i=(-1*$amount); $i<$amount; $i++) {
			$pds[$j] = new Period();
			$pds[$j]->id = $j;
			$pds[$j]->setPeriod($span, $start);
			$pds[$j]->decl = $pds[$j]->start . " - " . $pds[$j]->end;
			$pds[$j]->value = $pds[$j]->start . "," . $pds[$j]->end;
			$pds[$j]->obj_Filter_Period = $pds[$j]->decl;
			$start = $sys->addDays($pds[$j]->end, 1);
			
			$j++;
		}
		
		return $pds;
	}
	function getDeadlines($span, $amount, $oaking = null) {
		$sys = new GSystem();
		
		if ($oaking) {
			$today = $sys->getOak($oaking);
		} else {
			$today = $sys->getToday();
		}
		switch ($span) {
			case "quarter":
				$start = $sys->getQuarterStart($sys->addDays($today, -640));
				break;
		}
		
		$j = 0;
		for ($i=(-1*$amount); $i<$amount; $i++) {
			$dds[$j] = new Deadline();
			$dds[$j]->id = $j;
			$dds[$j]->setDeadline($span, $start);
			$dds[$j]->decl = " - " . $dds[$j]->end;
			$dds[$j]->value = $dds[$j]->end;
			$dds[$j]->obj_Filter_Deadline = $dds[$j]->decl;
			$start = $sys->addDays($dds[$j]->end, 1);
			
			$j++;
		}
		
		return $dds;
	}
}
?>