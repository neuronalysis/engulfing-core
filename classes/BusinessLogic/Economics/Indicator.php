<?php
class Indicator extends Indicator_Generated {
	
	protected $defaultOrder = "popularity DESC";
	
	function getLastIndicatorObservation() {
		$rest = new REST();
	
		$sql = "select id, indicatorID, MAX(date) AS date FROM indicatorobservations WHERE indicatorID = " . $this->id . " GROUP BY indicatorID";
	
		$indicatorobservations = $rest->orm->getAllByQuery($sql, "IndicatorObservation", array("indicatorID"));
		 
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