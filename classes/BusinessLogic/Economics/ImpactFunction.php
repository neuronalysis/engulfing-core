<?php
class ImpactFunction extends ImpactFunction_Generated {
	
	function getValuation() {
		$eco = new Economics();
		
		$concrete_formula = $this->formula;
		
		foreach($this->RelationIndicatorImpactFunctions as $rel_item) {
			$lastobservations = $eco->getLastIndicatorObservationsByIndicator($rel_item->Indicator);
			
			$concrete_formula = str_ireplace("Indicator(" . $rel_item->Indicator->id . ")", $lastobservations[0]->value, $concrete_formula);
		}
		
		$valuation = eval('return '.$concrete_formula.';');
		
		return $valuation;
	}
	
	
}
?>