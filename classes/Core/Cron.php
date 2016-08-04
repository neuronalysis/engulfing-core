<?php
class Cron {
	
	var $db;
	
	function Cron() {
		$this->db = new DataBase_Cron();
	}
	function getInstruments() {
		$query = "
			SELECT *
			FROM instruments
			";
				
		$result = $this->db->execute($query);
		
		$i=0;
		while ($row = mysql_fetch_assoc($result)) {
	    	$inst[$i]->id			= $row['id'];
	    	$inst[$i]->name			= $row['inst_name'];
	    	$inst[$i]->url			= $row['inst_url'];
	    	$inst[$i]->symbol		= $row['inst_symbol'];
	    	$inst[$i]->country		= $row['inst_country'];
	    	$inst[$i]->xpath		= $row['inst_xpath'];
	    	$inst[$i]->xpath_time	= $row['inst_xpath_time'];
	    	$i++;
		}
		
		//$item = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		//$events = $this->database->getRecords("Event_TR");
		//$event = new Event_TR();
		//$event->db = $this->database;
		//$event->get($item['id']);
		
		/*$i=0;
		while ($item = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
			$events[$i]->id = $item['id'];
			$events[$i]->event_date = $item['event_date'];
			$events[$i]->event_for = $item['event_for'];
			$events[$i]->event_period = $item['event_period'];
			$events[$i]->event_expectation = $item['event_expectation'];
			$events[$i]->event_indicatorid = $item['event_indicatorid'];
			
			$i++;
		}*/
		
		return $inst;
	}
	function getLastQuote($instrumentid) {
		$query = "SELECT *
			FROM history_instruments_intraday
			WHERE instrument_id = " . $instrumentid . "
			ORDER BY hinst_intra_datetime DESC
			LIMIT 0, 1
			";
		
		
		$result = $this->db->execute($query);
		
		
		$i=0;
		while ($row = mysql_fetch_assoc($result)) {
	    	$inst[$i]->last		= $row['hinst_intra_last'];
	    	$i++;
		}
		
		return $inst[0]->last;
	}
	function saveQuotes($quotedata) {
		if ($quotedata->close == 0) return;
		
		if ($quotedata->date != null) {
			$date = $quotedata->date;
		} else {
			$date = date("Y-m-d");
		}
		if ($quotedata->time != null) {
			$time = $quotedata->time;
		} else {
			$time = date("H:i:s");
		}
		
		$query = "INSERT INTO history_instruments_intraday (
		instrument_id,
		hinst_intra_datetime,
		hinst_intra_last,
		hinst_intra_change_pct
		)
		VALUES (
			'$quotedata->id',
			'$date $time',
			'$quotedata->close',
			'$quotedata->change_pct'
		)
		";
		//ON DUPLICATE KEY 
		//UPDATE
		//	hinst_intra_last = '$quotedata->close',
		//	hinst_intra_change_pct = '$quotedata->change_pct'
		
		//echo $query;
		
		$result = $this->db->execute($query);
	}
	function cleanUpIntradayHistory() {
		
	}
	function translate(Release $release, $cron) {
		$latestevent = $release->getLatestEvent();
		
		
		
		$cron_elements = split(" ", $cron);
		
		//echo "cron-string: " . $cron . "<br><br>";
		
		
		$time_minutes = $cron_elements[1];
		$time_hour = $cron_elements[2];
		
		$day_elements = split("#", $cron_elements[5]);
		
		$weekday = $day_elements[0];
		$position = $day_elements[1];
		//echo $cron . "+ " . $weekday . ";;;" . $position . "---";
		
		switch ($weekday) {
			case 'B':
				$weekday_string = "BD";
				break;
			case 0:
				$weekday_string = "Sat";
				break;
			case 1:
				$weekday_string = "Sun";
				break;
			case 2:
				$weekday_string = "Mon";
				break;
			case 3:
				$weekday_string = "Tue";
				break;
			case 4:
				$weekday_string = "Wed";
				break;
			case 5:
				$weekday_string = "Thu";
				break;
			case 6:
				$weekday_string = "Fri";
				break;
			default:
				break;
		}
		
		//echo "weekday-string: " . $weekday_string . "<br>";
		
		switch ($position) {
			case 1:
				$position_string = "0 week";
				$position_string_for = "-" . $release->getDelay() . " month";
				break;
			case 2:
				$position_string = "1 week";
				$position_string_for = "-" . $release->getDelay() . " month";
				break;
			case "":
				break;
			default:
				$position_string_for = "-" . $release->getDelay() . " month";
				if ($weekday_string == 'BD') {
					$position_string = "+" . ($position - 1) . " weekdays";
				} else {
					$position_string = "-1 week";
				}
				break;
				
		}
		
		//echo "position: " . $position . "; " . "position_string: " . $position_string . "; position_string_for: " . $position_string_for .  "<br>";
		
		//echo "latestevent: " . $latestevent['event_date'] . "<br>";
		
		if ($position > 0) {
			$i = 0;
			$y = 2011;
			
			while ($i < 24) {
				if (($i) % 12 == 0) {
					$y = $y + 1;
				}
					
				$date = date_create();
				date_date_set($date, $y, ($i % 12) + 1, 1);
					
				//date("Y-m-d", strtotime("+0 weekdays", date_timestamp_get($date)));
				$month = strftime("%b", date_timestamp_get($date));
					
				//echo strtotime($latestevent['event_date']) . "---" . date_timestamp_get($date) . "<br>";
			
				if (strtotime($latestevent['event_date']) < date_timestamp_get($date)) {
			
					if ($weekday_string == "BD") {
						$wd = date("w", date_timestamp_get($date));
						$daystoadd = $this->getCorrectDayToAdd($position, $wd, NULL);
						
						$position_string = "+" . $daystoadd . " weekdays";
													
						$time = date("Y-m-d", strtotime($position_string, date_timestamp_get($date)));
			
					} else {
						$wd = date("w", date_timestamp_get($date));
						
						$daystoadd = $this->getCorrectDayToAdd($position, $wd, $weekday);
							
						$position_string = "+" . $daystoadd . " days";
						
						
						//echo date("Y-m-d", strtotime("+1 week" , strtotime($position_string, date_timestamp_get($date))));
						//echo "date : " . $date . "::";
						if ($position == 2) {
							$time = date("Y-m-d", strtotime("+1 week" , strtotime($position_string, date_timestamp_get($date))));
						} else {
							$time = date("Y-m-d", strtotime($position_string, date_timestamp_get($date)));
						}
						//echo $time . "-";
						//echo $position_string . "; " . 
						
						//$time = date("Y-m-d", strtotime($position_string . " " . $weekday_string . " " . $month . " " . $y));
					}
			
					//echo $position . " -- wd: " . $wd . "; position_string: " . $position_string . "; date: " . date("Y-m-d",date_timestamp_get( $date)) . "<br>";
					
					$time_for = date("Y-m-d", strtotime($position_string_for, date_timestamp_get($date)));
			
					//echo "date: " . $time . "; time_hour: " . $time_hour . ":" . $time_minutes . "<br>";
					
					$event = new Event_Release();
					$event->db = $release->db;
			
					$event->event_date = $time . " " . $time_hour . ":" . $time_minutes;
					$event->event_for = $time_for;
			
					$event->event_date_status = $release->release_cron_job_status;
					
					//echo "event_date_status: " . $event->event_date_status . "<br>";
					
					$event->event_release_id = $release->getid();
			
					//$event->save();
				}
					
				$i++;
			}
			
		} else {
			$i = 0;
			$y = 2011;
			
			$date = date_create();
			date_date_set($date, $y, 1, 1);
			
			$wd = date("w", date_timestamp_get($date));
			
			$daystoadd = $this->getCorrectDayToAdd($position, $wd, $weekday);
			//echo "wd:: " . $wd;
			
			$position_string = "+" . $daystoadd . " days";
				
			$date_new = date("Y-m-d", strtotime($position_string, date_timestamp_get($date)));
				
			
			while ($i < 600) {
				
				//echo "date: " . date("Y-m-d", strtotime("+5 weekdays", date_timestamp_get($date)));
				$month = strftime("%b", date_timestamp_get($date));
					
				//echo strtotime($latestevent['event_date']) . " - " . strtotime($date_new) . "<br>";
				
				if (strtotime($latestevent['event_date']) < strtotime($date_new)) {
					$date_new = date("Y-m-d", strtotime("+1 week", strtotime($date_new)));
					date("Y-m-d", strtotime($date_new));
					
					$time = $date_new;
					
					//echo $position . " -- wd: " . $wd . "; position_string: " . $position_string . "; date: " . date("Y-m-d",date_timestamp_get( $date)) . "<br>";
					
					$time_for = date("Y-m-d", strtotime("-1 week", strtotime($date_new)));
					//echo "date: " . $time . "; time_hour: " . $time_hour . ":" . $time_minutes . "<br>";
						
					$event = new Event_Release();
					$event->db = $release->db;
			
					$event->event_date = $time . " " . $time_hour . ":" . $time_minutes;
					$event->event_for = $time_for;
					
					$event->event_date_status = $release->release_cron_job_status;
					
					//echo "event_date_status: " . $event->event_date_status . "<br>";
					
			
					$event->event_release_id = $release->getid();
			
					//$event->save();
				} else {
					$date_new = date("Y-m-d", strtotime("+1 week", strtotime($date_new)));
						
				}
				
				$i++;
			}
		}
						
		return "latestevent: " . $latestevent['event_date'] . " crontime: " . $time_hour . ":" . $time_minutes . ", " . $position . " - " . $weekday . "; " . $time;
		
		
	}
	function getCorrectDayToAdd($position, $weekday_now, $weekday_tobe) {
		if ($weekday_now == 0) {
			$weekday_now = 7;
		} else if ($weekday_now == 6) {
			$weekday_now = 6;
		}
		
		
		if ($weekday_tobe == NULL) {
			if ($weekday_now <= 5) {
				$weekend_addition = 0;
			} else {
				$weekend_addition = 8 - $weekday_now;
			}
			
			$daystoadd = $position + $weekend_addition - 1;
		} else {
			switch ($position) {
				
				case ($position <= 5):
					$daystoadd = $weekday_tobe - $weekday_now;
					break;
				case ($position > 5):
					$daystoadd = $weekday_tobe - $weekday_now - 1 + 7;
					break;
				default:
					break;
			
			}
		}
		
		return $daystoadd;
	}
}
?>
