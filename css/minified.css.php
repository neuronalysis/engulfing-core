<?php
header('Content-type: text/css');
ob_start("compress");

function compress( $minify )
{
	/* remove comments */
	$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );

	/* remove tabs, spaces, newlines, etc. */
	$minify = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $minify );

	return $minify;
}
function getScopeDepth() {
	$url_parsed = parse_url ( $_SERVER ['REQUEST_URI'] );
	$levels = explode ( "/", $url_parsed ['path'] );

	if (strpos($url_parsed ['path'], "localhost") !== false) {
		$depth = count($levels) - 2;
	} else if (strpos($url_parsed ['path'], "/api/") !== false) {
		$depth = null;
		//$scopename = $rest->singularize($levels[4]);
	} else {
		$depth = count($levels) - 2;
		//$scopename = $levels[1];
	}

	return $depth;
}
function getDescendings($folder = "engulfing", $desc = "") {
	$scopeDepth = getScopeDepth();
	$descendings = array();
	 
	$root = "http://localhost.engulfing/";
	$km = "http://localhost.ontologydriven/km/";
	$edi = "http://localhost.ontologydriven/edi/";
	 
	$descendings['core']['km'] = $km;
	$descendings['core']['edi'] = $edi;

		if ($scopeDepth == 0) {
			$descendings['core']['js'] = $root;
			$descendings['core']['bootstrap'] = $root;
			$descendings['core']['css'] = $root;
			 
			//$descendings['core']['js'] = "../";
			//$descendings['core']['bootstrap'] = "../";
			 
			$descendings['ontology']['js'] = "";
			 
		} else if ($scopeDepth == 1) {
			$descendings['core']['js'] = $root;
			$descendings['core']['bootstrap'] = $root;
			$descendings['core']['css'] = $root;
			 
			/*$descendings['core']['js'] = "../../";
			 $descendings['core']['bootstrap'] = "../../";
			  
			 $descendings['ontology']['js'] = "";*/
			$descendings['ontology']['js'] = "";

			$descendings['app']['nav'] = "../";
			$descendings['app']['js'] = "";
		} else if ($scopeDepth == 2) {
			$descendings['core']['js'] = $root;
			$descendings['core']['bootstrap'] = $root;
			$descendings['core']['css'] = $root;
			 
			/*$descendings['core']['js'] = "../../";
			 $descendings['core']['bootstrap'] = "../../";
			  
			 $descendings['ontology']['js'] = "../";*/
			$descendings['ontology']['js'] = "../";

			$descendings['app']['nav'] = "../../";
			$descendings['app']['js'] = "../";
		}
	 
	 
	return $descendings;
}
/* css files for combining */
$desc = getDescendings();

include($desc['core']['bootstrap'] . 'bootstrap/css/bootstrap.min.css');
include($desc['core']['bootstrap'] . 'bootstrap/arrows/css/bootstrap-arrows.css');
include($desc['core']['bootstrap'] . 'bootstrap/css/bootstrap-datepicker.css');
include($desc['core']['bootstrap'] . 'bootstrap/fileinput/css/fileinput.min.css');
include($desc['core']['js'] . 'js/core/select2/select2.css');
include($desc['core']['js'] . 'js/core/backgrid.min.css');
include($desc['core']['js'] . 'js/core/backgrid-paginator.min.css');
include($desc['core']['js'] . 'js/core/backgrid-select-all.min.css');
include($desc['core']['js'] . 'js/core/backgrid-filter.min.css');
include($desc['core']['css'] . 'css/styles.css');

ob_end_flush();