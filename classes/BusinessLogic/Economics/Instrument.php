<?php
class Instrument extends Instrument_Generated {
	
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