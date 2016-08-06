<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}

include_once ($desc . "../engulfing/engulfing-core/classes/BusinessLogic/GEO/GEO.php");
include_once ($desc . "../engulfing/engulfing-core/classes/BusinessLogic/Cybernetics/Cybernetics.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/GSystem.php");

include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/economics/Economics_Generated.php");


include_once ('Release.php');
include_once ('ReleasePublication.php');
include_once ('Instrument.php');
include_once ('Indicator.php');

class Economics extends Economics_Generated {
	var $classes = array("Release", "ReleasePublication", "Indicator", "Instrument");
	
	var $entities = '{}';
	
	function __construct() {
	}
	
	function getNextReleasePublications() {
		$rest = new REST();
		
		$sql = "select id, releaseID, MIN(date) AS date FROM releasepublications WHERE date >= CURDATE() GROUP BY releaseID ORDER BY date ASC";
		
		$releasepublications = $rest->getAllByQuery($sql, "ReleasePublication", array("releaseID"));
		
		//print_r($releasepublications);
		
		foreach($releasepublications as $relpub_item) {
			$relpub_item->Release = $rest->getById("Release", $relpub_item->releaseID, false);
			
			if (isset($relpub_item->Release)) {
				$relpub_item->name = $relpub_item->Release->name;
			}
				
			unset($relpub_item->releaseID);
		}
		
		return $releasepublications;
	}
	function getPublicationsByRelease($release) {
		$rest = new REST();
		
		$publications = $rest->getByNamedFieldValues("ReleasePublication", array("releaseID"), array($release->id));
		
		return $publications;
	}
	function getIndicatorsByRelease($release) {
		$rest = new REST();
	
		$indicators = $rest->getByNamedFieldValues("Indicator", array("releaseID"), array($release->id), false, null, false, true);
	
		return $indicators;
	}
	function filterReleasesWithPendingReleasePublications($releases) {
		$filtered = array();
		
		$sys = new GSystem();
		
		foreach($releases as $rel_item) {
			$lastImportReleasePublicationsDate = $rel_item->getLastImportReleasePublicationsDate();
			
			if ($lastImportReleasePublicationsDate) {
				if ($sys->datediff($lastImportReleasePublicationsDate, date("Y-m-d")) < -10) {
					//echo $rel_item->lastImportReleasePublicationsDate . "; " . $rel_item->lastImportReleasePublicationsStatus . "; " . $sys->datediff($rel_item->lastImportReleasePublicationsDate, date("Y-m-d")) . "\n";
					$releasepublications = $this->getFutureLastReleasePublicationsByRelease($rel_item);
						
					if (isset($releasepublications[0])) {
						if ($sys->datediff($releasepublications[0]->date, date("Y-m-d")) >= 0) {
							//echo ">= 0: " . $rel_item->name . ": " . $releasepublications[0]->date . "; " . $sys->datediff($releasepublications[0]->date, date("Y-m-d")) . "\n";
							array_push($filtered, $rel_item);
						} else {
							//echo "<  0: " . $rel_item->name . ": " . $releasepublications[0]->date . "; " . $sys->datediff($releasepublications[0]->date, date("Y-m-d")) . "\n";
						}
					} else {
						array_push($filtered, $rel_item);
					}
				}
			} else {
				//echo $rel_item->lastImportReleasePublicationsDate . "; " . $rel_item->lastImportReleasePublicationsStatus . "; " . $sys->datediff($rel_item->lastImportReleasePublicationsDate, date("Y-m-d")) . "\n";
				$releasepublications = $this->getFutureLastReleasePublicationsByRelease($rel_item);
				
				if (isset($releasepublications[0])) {
					if ($sys->datediff($releasepublications[0]->date, date("Y-m-d")) >= 0) {
						//echo ">= 0: " . $rel_item->name . ": " . $releasepublications[0]->date . "; " . $sys->datediff($releasepublications[0]->date, date("Y-m-d")) . "\n";
						array_push($filtered, $rel_item);
					} else {
						//echo "<  0: " . $rel_item->name . ": " . $releasepublications[0]->date . "; " . $sys->datediff($releasepublications[0]->date, date("Y-m-d")) . "\n";
					}
				} else {
					array_push($filtered, $rel_item);
				}
			}
			
		}
		
		return $filtered;
	}
	function filterReleasesWithPendingIndicators($releases, $importprocess) {
		$filtered = array();
	
		$sys = new GSystem();
		
		foreach($releases as $rel_item) {
			$rel_item->setDataBaseConnections($importprocess->getDatabaseConnections());
			
			$lastImportReleaseIndicatorsDate = $rel_item->getLastImportReleaseIndicatorsDate();
			
			//echo "lastImportReleaseIndicatorsDate: " . $lastImportReleaseIndicatorsDate . "\n";
			
			if ($lastImportReleaseIndicatorsDate) {
				if ($sys->datediff($lastImportReleaseIndicatorsDate, date("Y-m-d")) < -10) {
					$availableIndicators = $rel_item->getAvailableIndicators();
						
					if ($availableIndicators) {
						if ($availableIndicators > 0) {
							$indicators = $rest->countByObjectAndId("Indicator", array("releaseID" => $rel_item->id));
							
							if (intval($indicators) !== intval($availableIndicators)) {
								//echo "   availableIndicators: " . $availableIndicators . "; effective.indicators: " . count($indicators) . "\n";
								array_push($filtered, $rel_item);
							}
						} else {
							$this->countAvailableIndicatorsByRelease($rel_item, $importprocess);
								
							array_push($filtered, $rel_item);
						}
					} else {
						$this->countAvailableIndicatorsByRelease($rel_item, $importprocess);
					
						array_push($filtered, $rel_item);
					}
				}
			} else {
				$availableIndicators = $rel_item->getAvailableIndicators();
					
				if ($availableIndicators) {
					if ($availableIndicators > 0) {
						$indicators = $importprocess->countByObjectAndId("Indicator", array("releaseID" => $rel_item->id));
							
						if (intval($indicators) !== intval($availableIndicators)) {
							//echo "   availableIndicators: " . $availableIndicators . "; effective.indicators: " . $indicators . "\n";
							array_push($filtered, $rel_item);
						}
					} else {
						$this->countAvailableIndicatorsByRelease($rel_item, $importprocess);
							
						//array_push($filtered, $rel_item);
					}
				} else {
					//$this->countAvailableIndicatorsByRelease($rel_item, $importprocess);
				
					array_push($filtered, $rel_item);
				}
			}
			
			
		}
	
		return $filtered;
	}
	function filterIndicatorsWithPendingIndicatorObservations($indicators) {
		$filtered = array();
	
		$sys = new GSystem();
	
		foreach($indicators as $ind_item) {
			$lastImportIndicatorObservationsDate = $ind_item->getLastImportIndicatorObservationsDate();
			
			if ($lastImportIndicatorObservationsDate) {
				if ($sys->datediff($lastImportIndicatorObservationsDate, date("Y-m-d")) < -10) {
					if ($ind_item->isHeadlineNumber || $ind_item->popularity > 30) {
						$indicatorobservations = $this->getLastIndicatorObservationsByIndicator($ind_item);
					
						if (isset($indicatorobservations[0])) {
							if ($sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) >= 0) {
								//echo $ind_item->name . ": " . $indicatorobservations[0]->date . "; " . $sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) . "\n";
								array_push($filtered, $ind_item);
							} else {
					
							}
						} else {
							array_push($filtered, $ind_item);
						}
					}
				}
			} else {
				if ($ind_item->isHeadlineNumber || $ind_item->popularity > 30) {
					$indicatorobservations = $this->getLastIndicatorObservationsByIndicator($ind_item);
						
					if (isset($indicatorobservations[0])) {
						if ($sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) >= 0) {
							//echo $ind_item->name . ": " . $indicatorobservations[0]->date . "; " . $sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) . "\n";
							array_push($filtered, $ind_item);
						} else {
								
						}
					} else {
						array_push($filtered, $ind_item);
					}
				}
			}
		}
	
		return $filtered;
	}
	function filterInstrumentsWithPendingInstrumentObservations($instruments) {
		$filtered = array();
	
		$sys = new GSystem();
	
		foreach($instruments as $inst_item) {
			$lastImportInstrumentObservationsDate = $inst_item->getLastImportInstrumentObservationsDate();
				
			echo $lastImportInstrumentObservationsDate . "\n";
			if ($lastImportInstrumentObservationsDate) {
				if ($sys->datediff($lastImportInstrumentObservationsDate, date("Y-m-d")) < 1) {
					$instrumentobservations = $this->getLastInstrumentObservationsByInstrument($inst_item);
						
					if (isset($instrumentobservations[0])) {
						if ($sys->datediff($instrumentobservations[0]->date, date("Y-m-d")) >= 0) {
							//echo $ind_item->name . ": " . $indicatorobservations[0]->date . "; " . $sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) . "\n";
							array_push($filtered, $inst_item);
						} else {
								
						}
					} else {
						array_push($filtered, $inst_item);
					}
				}
			} else {
				$instrumentobservations = $this->getLastInstrumentObservationsByInstrument($inst_item);

				if (isset($instrumentobservations[0])) {
					if ($sys->datediff($instrumentobservations[0]->date, date("Y-m-d")) >= 0) {
						//echo $ind_item->name . ": " . $indicatorobservations[0]->date . "; " . $sys->datediff($indicatorobservations[0]->date, date("Y-m-d")) . "\n";
						array_push($filtered, $inst_item);
					} else {

					}
				} else {
					array_push($filtered, $inst_item);
				}
			}
		}
	
		return $filtered;
	}
	function getPendings($type = null) {
		if ($type) {
			if ($type === "indicatorobservations") {
				$this->getPendings_IndicatorObservations();
			} else if ($type === "instrumentobservations") {
				$this->getPendings_InstrumentObservations();
			}
		} else {
			$this->getPendings_Releases();
			$this->getPendings_ReleaseIndicators();
			$this->getPendings_ReleasePublications();
		}
	}
	function getPendings_Releases() {
		echo "importpendings.releases\n";
		
		$edi = new EDI();
		
		$ip = $edi->getImportProcessById(2);
		$ip->loadDomainClassesBySchema();
		
		$this->importReleases($ip);
	}
	function getPendings_ReleasePublications() {
		echo "importpendings.releasepublications\n";
		
		$rest = new REST();
	
		$edi = new EDI();
		$iex = new Extraction();
		$km = new KM();
		
		$releases = $rest->getAllByName("Release", true);
		$releases_publications = $this->filterReleasesWithPendingReleasePublications($releases);
		
		echo "count.releases.with.pending.publications: " . count($releases_publications) . "\n";
		
		$ip = $edi->getImportProcessById(3);
		$ip->loadDomainClassesBySchema();
			
		
		foreach(array_slice($releases_publications, 0, 3) as $rel_item) {
			$this->importReleasePublicationsByRelease($rel_item, $ip);
		}
		
		
	}
	function getPendings_ReleaseIndicators() {
		echo "\nimportpendings.releaseindicators\n";
		
		$edi = new EDI();
		$iex = new Extraction();
		$km = new KM();
	
		//print_r(array_slice($releases, 0, 1));
		
		$ip = $edi->getImportProcessById(4);
		$ip->loadDomainClassesBySchema();
	
		$releases = $ip->getAllByName("Release", true, null, null, null, true);
		
		$releases_indicators = $this->filterReleasesWithPendingIndicators($releases, $ip);
	
		echo "count.releases.with.pending.indicators: " . count($releases_indicators) . "\n";
		
		foreach(array_slice($releases_indicators, 0, 3) as $rel_item) {
			$this->importReleaseIndicatorsByRelease($rel_item, $ip);
		}
	}
	function getPendings_IndicatorObservations() {
		echo "\nimportpendings.indicatorobservations\n";
		
		$rest = new REST();
	
		$edi = new EDI();
		$iex = new Extraction();
		$km = new KM();
	
		//$sql = "SELECT id, name, popularity FROM indicators WHERE indicators.popularity > 0 ";
		//$indicators = $rest->getAllByQuery($sql, "Indicator");
		$indicators = $rest->getAllByName("Indicator", true, null, null, null, true);
	
		$indicators = $this->filterIndicatorsWithPendingIndicatorObservations($indicators);
		
		//print_r(array_slice($indicators, 0, 5));
		
		echo "count.indicators.with.pending.indicatorobservations: " . count($indicators) . "\n";
		
		$ip = $edi->getImportProcessById(5);
		$ip->loadDomainClassesBySchema();
	
		foreach(array_slice($indicators, 0, 5) as $ind_item) {
			$this->importIndicatorObservationsByIndicator($ind_item, $ip);
		}
	}
	function getPendings_InstrumentObservations() {
		echo "\nimportpendings.instrumentobservations\n";
	
		$rest = new REST();
	
		$edi = new EDI();
		$iex = new Extraction();
		$km = new KM();
	
		//$sql = "SELECT id, name, popularity FROM indicators WHERE indicators.popularity > 0 ";
		//$indicators = $rest->getAllByQuery($sql, "Indicator");
		$instruments = $rest->getAllByName("Instrument", true, null, null, null, true);
	
		$instruments = $this->filterInstrumentsWithPendingInstrumentObservations($instruments);
	
		//print_r(array_slice($indicators, 0, 5));
	
		echo "count.instruments.with.pending.instrumentobservations: " . count($instruments) . "\n";
	
		$ip = $edi->getImportProcessById(7);
		$ip->loadDomainClassesBySchema();
	
		foreach(array_slice($instruments, 0, 5) as $inst_item) {
			$this->importInstrumentObservationsByInstrument($inst_item, $ip);
		}
	}
	function getLastIndicatorObservations($onlyHeadlineNumbers = false) {
		$rest = new REST();
	
		$sql_onlyHeadlineNumbers = "";
		if ($onlyHeadlineNumbers) {
			$sql_onlyHeadlineNumbers = " AND (indicators.isHeadlineNumber = 1 OR indicators.popularity > 0) AND indicators.isSeasonallyAdjusted = 1 ";
		}

		$sql = "SELECT indicators.id, indicatorobservations.indicatorID, MAX(date) AS date 
				FROM indicators, indicatorobservations 
				WHERE indicators.id = indicatorobservations.indicatorID AND date <= NOW() " . $sql_onlyHeadlineNumbers . " GROUP BY indicatorID
				ORDER BY date DESC";
	
		$indicatorobservations = $rest->getAllByQuery($sql, "IndicatorObservation", array("indicatorID"));
	
		//print_r($indicatorobservations);
		foreach($indicatorobservations as $obs_item) {
			$obs_item->Indicator = $rest->getById("Indicator", $obs_item->indicatorID, false);
				
			unset($obs_item->Indicator->IndicatorObservations);
			unset($obs_item->indicatorID);
		}
	
		return $indicatorobservations;
	}
	function getLastInstrumentObservations() {
		$rest = new REST();
	
		$sql = "SELECT instruments.id, instrumentobservations.instrumentID, MAX(date) AS date
				FROM instruments, instrumentobservations
				WHERE instruments.id = instrumentobservations.instrumentID AND date <= NOW() GROUP BY instrumentID
				ORDER BY date DESC";
	
		$instrumentobservations = $rest->getAllByQuery($sql, "InstrumentObservation", array("instrumentID"));
	
		//print_r($indicatorobservations);
		if ($instrumentobservations) {
			foreach($instrumentobservations as $obs_item) {
				$obs_item->Instrument = $rest->getById("Instrument", $obs_item->instrumentID, false);
			
				unset($obs_item->Instrument->InstrumentObservations);
				unset($obs_item->indicatorID);
			}
		}
	
		return $instrumentobservations;
	}
	function getLastReleasePublications() {
		$rest = new REST();
		
		$sql = "select id, releaseID, MAX(date) AS date FROM releasepublications WHERE date < NOW() GROUP BY releaseID ORDER BY date DESC";
		
		$releasepublications = $rest->getAllByQuery($sql, "ReleasePublication", array("releaseID"));
		
		foreach($releasepublications as $relpub_item) {
			$relpub_item->Release = $rest->getById("Release", $relpub_item->releaseID, false);
			
			unset($relpub_item->releaseID);
		}
		
		return $releasepublications;
	}
	function getFutureLastReleasePublicationsByRelease($release) {
		$rest = new REST();
		
		$sql = "select id, releaseID, MAX(date) AS date FROM releasepublications WHERE releaseID = " . $release->id . " GROUP BY releaseID ORDER BY date DESC";
		
		$releasepublications = $rest->getAllByQuery($sql, "ReleasePublication", array("releaseID"));
		
		foreach($releasepublications as $relpub_item) {
			$relpub_item->Release = $rest->getById("Release", $relpub_item->releaseID, false);
			
			unset($relpub_item->releaseID);
		}
		
		return $releasepublications;
	}
	function countAvailableIndicatorsByRelease($release, $importprocess) {
		$edi = new EDI();
		$iex = new Extraction();
	
		$url = $importprocess->getUrl($release->id);
		
		$ressource = $edi->getRessource ($url);
		
		if ($ressource->isJson($ressource->content)) {
			
			$jsonObject = json_decode($ressource->content);
			
			echo "   availableIndicators to be saved: " . $jsonObject->count . "\n";
			
			$release->setAvailableIndicators($jsonObject->count);
			$release->save(null, array("availableIndicators"), array(), true);
			
		}
	}
	function importReleaseIndicatorsByRelease($release, $importprocess, $offsetStep = 500, $limit = 500) {
		$edi = new EDI();
		$iex = new Extraction();
	
		$availableIndicators = $release->getAvailableIndicators();
		
		echo "available-indicators: " . $availableIndicators . "\n";
		
		
		if ($availableIndicators > $limit) {
			for ($i=0; $i<$availableIndicators / $limit; $i++) {
				$url = $importprocess->getUrl($release->id, array("offset" => $i * $offsetStep, "limit" => $limit, "order_by" => "popularity", "sort_order" => "desc"));
				
				echo $url . "\n";
				
				
				$ressource = $edi->getRessource ($url);
					
				if ($ressource->isJson($ressource->content)) {
					$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
					
					if (count($objects) > 0) {
						$importprocess->importObjects($objects);
					}
					$release->setLastImportReleaseIndicatorsStatus(1);
					$release->setLastImportReleaseIndicatorsDate(date('Y-m-d H:i:s', time()));
					$release->save(null, array("lastImportReleaseIndicatorsDate", "lastImportReleaseIndicatorsStatus"), array(), true);
				
				}
			}
		} else {
			$url = $importprocess->getUrl($release->id);
			
			$ressource = $edi->getRessource ($url);
			
			echo $url . "\n";
			
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
					
				echo "objects.count: " . count($objects) . "\n";
				
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$release->setLastImportReleaseIndicatorsStatus(1);
				$release->setLastImportReleaseIndicatorsDate(date('Y-m-d H:i:s', time()));
					
				$release->save(null, array("lastImportReleaseIndicatorsDate", "lastImportReleaseIndicatorsStatus"), array(), true);
			}
		}
		
		
	}
	function getLastReleasePublicationByRelease($release) {
		$rest = new REST();
		
		$sql = "select id, releaseID, MAX(date) AS date FROM releasepublications WHERE releaseID = " . $release->id . " AND date < NOW() GROUP BY releaseID ORDER BY date DESC";
		
		$releasepublications = $rest->getAllByQuery($sql, "ReleasePublication", array("releaseID"));
		
		foreach($releasepublications as $relpub_item) {
			$relpub_item->Release = $rest->getById("Release", $relpub_item->releaseID, false);
			
			unset($relpub_item->releaseID);
		}
		
		return $releasepublications;
	}
	function getLastIndicatorObservationsByIndicator($indicator) {
		$rest = new REST();
	
		$sql = "select id, indicatorID, MAX(date) AS date FROM indicatorobservations WHERE indicatorID = " . $indicator->id . " AND date < NOW() GROUP BY indicatorID ORDER BY date DESC";
	
		$indicatorobservations = $rest->getAllByQuery($sql, "IndicatorObservation", array("indicatorID"));
	
		foreach($indicatorobservations as $indobs_item) {
			$indobs_item->Indicator = $rest->getById("Indicator", $indobs_item->indicatorID, false);
				
			unset($indobs_item->indicatorID);
		}
	
		return $indicatorobservations;
	}
	function getLastInstrumentObservationsByInstrument($instrument) {
		$rest = new REST();
	
		$sql = "select id, instrumentID, MAX(date) AS date FROM instrumentobservations WHERE instrumentID = " . $instrument->id . " AND date < NOW() GROUP BY instrumentID ORDER BY date DESC";
	
		$instrumentobservations = $rest->getAllByQuery($sql, "InstrumentObservation", array("instrumentID"));
	
		foreach($instrumentobservations as $instobs_item) {
			$instobs_item->Instrument = $rest->getById("Instrument", $instobs_item->instrumentID, false);
	
			unset($instobs_item->instrumentID);
		}
	
		return $instrumentobservations;
	}
	function importReleases($importprocess) {
		$edi = new EDI();
		$edi->setDataBaseConnections($importprocess->getDatabaseConnections());
		
		$iex = new Extraction();
		$iex->setDataBaseConnections($importprocess->getDatabaseConnections());
		
		$releases = $iex->getAllByName("Release", true);
		
		$url = $importprocess->getUrl();
		
		$ressource = $edi->getRessource ($url);
		
		$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
		
		echo count($objects);
		
		if (count($releases) !== count($objects)) {
			if (count($objects) > 0) {
				$importprocess->importObjects($objects);
			}
		}
	}
	function importReleasePublicationsByRelease($release, $importprocess) {
		$releasepublications = $this->getLastReleasePublicationByRelease($release);
		
		$edi = new EDI();
		$iex = new Extraction();
		
		
		if (isset($releasepublications) && count($releasepublications) > 0) {
			//echo "last-date: " . $releasepublications[0]->date . "\n";
			
			$url = $importprocess->getUrl($release->id, array("include_release_dates_with_no_data" => "true", "realtime_start" => date("Y-m-d", strtotime($releasepublications[0]->date . ' +1 day')) ));
			
			echo "release-publications-url: " . $url . "\n";
			
			$ressource = $edi->getRessource ($url);
				
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
				
				//echo "asdf";
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
					
					
				}
				$release->setLastImportReleasePublicationsStatus(1);
				$release->setLastImportReleasePublicationsDate(date('Y-m-d H:i:s', time()));
				
				$release->save(null, array("lastImportReleasePublicationsDate", "lastImportReleasePublicationsStatus"), array(), true);
			}
		} else {
			$url = $importprocess->getUrl($release->id, array("include_release_dates_with_no_data" => "true"));
				
			echo "release-publications-url: " . $url . "\n";
				
			$ressource = $edi->getRessource ($url);
			
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
			
				//print_r($objects[0]);
			
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$release->setLastImportReleasePublicationsStatus(1);
				$release->setLastImportReleasePublicationsDate(date('Y-m-d H:i:s', time()));
				
				$release->save(null, array("lastImportReleasePublicationsDate", "lastImportReleasePublicationsStatus"), array(), true);
			}
		}
	}
	function importIndicatorObservationsByIndicator($indicator, $importprocess) {
		$indicatorobservations = $this->getLastIndicatorObservationsByIndicator($indicator);
	
		$edi = new EDI();
		$iex = new Extraction();
	
	
		if (isset($indicatorobservations) && count($indicatorobservations) > 0) {
			//echo "last-date: " . $indicatorobservations[0]->date . "\n";
	
			$url = $importprocess->getUrl($indicator->id, array("include_release_dates_with_no_data" => "true", "observation_start" => date("Y-m-d", strtotime($indicatorobservations[0]->date . ' +1 day')) ));
	
			//echo "indicator-observations-url: " . $url . "\n";
	
			$ressource = $edi->getRessource ($url);
	
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
	
				//echo "asdf";
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
	
				$indicator->setLastImportIndicatorObservationsStatus(1);
				$indicator->setLastImportIndicatorObservationsDate(date('Y-m-d H:i:s', time()));
	
				$indicator->save(null, array("lastImportIndicatorObservationsDate", "lastImportIndicatorObservationsStatus"), array(), true, true);
			}
		} else {
			$url = $importprocess->getUrl($indicator->id, array("include_release_dates_with_no_data" => "true"));
	
			echo "indicator-observations-url: " . $url . "\n";
	
			$ressource = $edi->getRessource ($url);
	
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
					
				//print_r($objects[0]);
					
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
	
				$indicator->setLastImportIndicatorObservationsStatus(1);
				$indicator->setLastImportIndicatorObservationsDate(date('Y-m-d H:i:s', time()));
	
				$indicator->save(null, array("lastImportIndicatorObservationsDate", "lastImportIndicatorObservationsStatus"), array(), true, true);
			}
		}
	}
	function importInstrumentObservationsByInstrument($instrument, $importprocess) {
		$instrumentobservations = $this->getLastInstrumentObservationsByInstrument($instrument);
	
		$edi = new EDI();
		$iex = new Extraction();
	
	
		if (isset($instrumentobservations) && count($instrumentobservations) > 0) {
			echo "last-date: " . $instrumentobservations[0]->date . "\n";
				
			$url = $importprocess->getUrl($instrument->id);
				
			echo "instrument-observations-url: " . $url . "\n";
				
			$ressource = $edi->getRessource ($url);
	
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
	
				//echo "asdf";
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$instrument->setLastImportInstrumentObservationsStatus(1);
				$instrument->setLastImportInstrumentObservationsDate(date('Y-m-d H:i:s', time()));
				
				$instrument->save(null, array("lastImportInstrumentObservationsDate", "lastImportInstrumentObservationsStatus"), array(), true, true);
			} else {
				$objects = $iex->getEntitiesFromCsv($ressource->content, ",", $importprocess->DataService->schemaDefinition, $instrument->id);
				
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$instrument->setLastImportInstrumentObservationsStatus(1);
				$instrument->setLastImportInstrumentObservationsDate(date('Y-m-d H:i:s', time()));
				
				$instrument->save(null, array("lastImportInstrumentObservationsDate", "lastImportInstrumentObservationsStatus"), array(), true, true);
			
				
			}
		} else {
			$url = $importprocess->getUrl($instrument->id);
	
			echo "instrument-observations-url: " . $url . "\n";
	
			$ressource = $edi->getRessource ($url);
				
			if ($ressource->isJson($ressource->content)) {
				$objects = $iex->extractInformationFromJSONRessourceWithSchema($ressource, $importprocess->DataService->schemaDefinition);
					
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$instrument->setLastImportInstrumentObservationsStatus(1);
				$instrument->setLastImportInstrumentObservationsDate(date('Y-m-d H:i:s', time()));
				
				$instrument->save(null, array("lastImportInstrumentObservationsDate", "lastImportInstrumentObservationsStatus"), array(), true, true);
			} else {
				$objects = $iex->getEntitiesFromCsv($ressource->content, ",", $importprocess->DataService->schemaDefinition, $instrument->id);
				
				if (count($objects) > 0) {
					$importprocess->importObjects($objects);
				}
				
				$instrument->setLastImportInstrumentObservationsStatus(1);
				$instrument->setLastImportInstrumentObservationsDate(date('Y-m-d H:i:s', time()));
				
				$instrument->save(null, array("lastImportInstrumentObservationsDate", "lastImportInstrumentObservationsStatus"), array(), true, true);
			
				
			}
		}
	}
}

class Frequency extends Frequency_Generated {

	function __construct() {
	}
}
?>