<?php
class Instrument extends Instrument_Generated {
	
	protected $inputFactors = array(1 => array("Indicators" => array("Corporate Earnings Expectations", "Interest Rates")));
	
	function __construct() {
	}
	function getConsensusEstimateForPeriod($period) {
			
	}
	function getPrognosisForPeriod($period = null) {
		$cyb = new Cybernetics();
	
		$prognosis = $cyb->getPrognosis();
		
		return $prognosis;
	}
}

class InstrumentObservation extends InstrumentObservation_Generated {
	function __construct() {
	}
	
}
?>