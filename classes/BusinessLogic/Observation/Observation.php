<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/observation/Observation_Generated.php");


include_once ('Watchlist.php');
include_once ('WatchlistItem.php');



class Observation extends Observation_Generated {
	var $classes = array("Watchlist", "WatchlistItem");
	
	var $entities = '{}';
	
	function Observation() {
	}
	
}
?>