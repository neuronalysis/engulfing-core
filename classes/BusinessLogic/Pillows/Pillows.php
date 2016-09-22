<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/pillows/Pillows_Generated.php");


include_once ('PillowCase.php');
include_once ('PillowFilling.php');


class Pillows extends Pillows_Generated {
	
}
?>