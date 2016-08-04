<?php
class Indicator extends Indicator_Generated {
	
	function __construct() {
	}
    
	function getLastIndicatorObservation() {
		$rest = new REST();
	
		$sql = "select id, indicatorID, MAX(date) AS date FROM indicatorobservations WHERE indicatorID = " . $this->id . " GROUP BY indicatorID";
	
		$indicatorobservations = $rest->getAllByQuery($sql, "IndicatorObservation", array("indicatorID"));
		 
		if (isset($indicatorobservations[0])) {
			return $indicatorobservations[0];
		}
		
	}
	
}

class IndicatorObservation extends IndicatorObservation_Generated {
	function __construct() {
	}
}
?>