<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-core/classes/Core/things/Thing_Generated.php");

class Things_Generated {
    function __construct() {
    }
}
?>