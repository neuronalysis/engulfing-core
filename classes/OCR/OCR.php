<?php
namespace OCR;

class OCR  {
	function __construct() {
	}
	
	function getDocuments() {
	    $rest = \REST::getInstance();
	    
	    //$documents = $rest->orm->getAllByName(new \ORM_Request("\\OCR\\Document", null, array("informationJSON")));
	    $documents = $rest->orm->getAllByName(new \ORM_Request("\\OCR\\Document", null));
	    
	    foreach ($documents as $document_item) {
	    	$orm_req = new \ORM_Request("Page", array("documentID" => $document_item->id));
	    	$orm_req->order = "number ASC";
	    	
	    	$relobjects = $rest->orm->getByNamedFieldValues($orm_req);
	    	
	    	$latestPages= array();
	    	$Pages = array();
	    	foreach($relobjects as $relObjectItem) {
	    		unset($relObjectItem->altoXML);
	    		
	    		if (isset($latestPages[$relObjectItem->number]->version)) {
	    			if ($relObjectItem->version > $latestPages[$relObjectItem->number]->version) {
	    				$latestPages[$relObjectItem->number] = $relObjectItem;
	    			}
	    		} else {
	    			$latestPages[$relObjectItem->number] = $relObjectItem;
	    		}
	    	}
	    	
	    	foreach($latestPages as $key => $value) {
	    		array_push($Pages, $value);
	    	}
	    	$document_item->Pages = $Pages;
	    }
	    
	    
	    if (isset($_GET['page'])) {
	    	$result = new \stdClass();
	    	$result->items = $documents;
	    	$result->total_count = $rest->orm->getTotalAmount("\\OCR\\Document");
	    } else {
	    	$result = $documents;
	    }
	    
	    return $result;
	}
	function getDocumentById($id) {
		$rest = \REST::getInstance();
		
		$result = $rest->orm->getById("\\OCR\\Document", $id, false);
		
		if ($result) {
			$orm_req = new \ORM_Request("Page", array("documentID" => $id));
			$orm_req->order = "number ASC";
			
			$relobjects = $rest->orm->getByNamedFieldValues($orm_req);
			
			$latestPages= array();
			$Pages = array();
			foreach($relobjects as $relObjectItem) {
				unset($relObjectItem->altoXML);
				
				if (isset($latestPages[$relObjectItem->number]->version)) {
					if ($relObjectItem->version > $latestPages[$relObjectItem->number]->version) {
						$latestPages[$relObjectItem->number] = $relObjectItem;
					}
				} else {
					$latestPages[$relObjectItem->number] = $relObjectItem;
				}
			}
			
			foreach($latestPages as $key => $value) {
				array_push($Pages, $value);
			}
			$result->Pages = $Pages;
		} else {
			
		}
		
		return $result;
	}
	//TODO quick and dirty hack; clean it up.
	function getDifferences($pageNumber = null, $versionNumber = null) {
		$rest = \REST::getInstance();
		$restTransformer = new \REST_Transformer_KOKOS();
		
		if (isset($_GET['pageNumber'])) $pageNumber = $_GET['pageNumber'];
		if (isset($_GET['versionNumber'])) $versionNumber= $_GET['versionNumber'];
		
		
		if ($pageNumber) {
			if ($versionNumber) {
				$sql = "SELECT * FROM pages WHERE number = $pageNumber AND version <= $versionNumber ORDER BY updatedAt DESC";
			} else {
				$sql = "SELECT * FROM pages WHERE number = $pageNumber AND version > 1 ORDER BY updatedAt DESC";
			}
		} else {
			$sql = "SELECT * FROM pages WHERE version > 1 ORDER BY updatedAt DESC";
		}
		
		$pages = $rest->orm->executeQuery($sql, "Page");
		
		$sqlUsers = "SELECT * FROM users";
		
		$users = $rest->orm->executeQuery($sqlUsers, "User", null, 'ocr');
		$differences = array();
		
		foreach($pages as $pageItem) {
			if ($pageItem->differenceDefinition) {
				$diffs = $restTransformer->deserialize_JSON ( $pageItem->differenceDefinition, "Difference", false, "OCR", true);
				
				if (is_array($diffs) && count($diffs) > 0) {
					foreach($diffs as $differenceItem) {
						unset($pageItem->altoXML);
						unset($pageItem->differenceDefinition);
						
						$differenceItem->Page = $pageItem;
						
						$users_filtered= array_map(function($e) {
							return is_object($e) ? $e->id : $e['id'];
						}, $users);
							
							
							$updatedByUser = $users[array_search($differenceItem->Page->updatedBy, $users_filtered)];
							$userObj = $rest->orm->convertStdClassToObject($updatedByUser, "User");
							
							$differenceItem->Page->updatedByUser = $userObj;
							
							array_push($differences, $differenceItem);
					}
				}
				
			}
		}
		
		$response = new \stdClass();
		$response->items = $differences;
		
		return $response;
	}
	function getPageByDocumentIdAndPageNumber($documentID, $pageNumber) {
		$rest = \REST_KOKOS::getInstance();
		$auth = \Authentication::getInstance();
		
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $auth->isLogged()) return null;
		}
		
		$document = $rest->get($documentID);
		
		$page = $document->getPageByNumber($pageNumber);
		
		$dom = new \DOMDocument();
		$dom->loadXML($page->altoXML);
		$xmlconv = new \XMLConverter("ALTO");
		$alto = $xmlconv->convertToObjectTree($dom);
		$page->alto = $alto;
		
		$page->versions = $page->getVersions();
		
		unset($page->altoXML);
		
		return $page;
	}
	function getPageByDocumentIdAndPageNumberAndVersion($documentID, $pageNumber, $versionNumber) {
		$rest = \REST_KOKOS::getInstance();
		$auth = \Authentication::getInstance();
		
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $auth->isLogged()) return null;
		}
		
		
		$document = $rest->get($documentID);
		
		$page = $document->getPageByNumberAndVersion($pageNumber, $versionNumber);
		
		$dom = new \DOMDocument();
		$dom->loadXML($page->altoXML);
		$xmlconv = new \XMLConverter("ALTO");
		$alto = $xmlconv->convertToObjectTree($dom);
		$page->alto = $alto;
		
		$page->versions = $page->getVersions();
		
		unset($page->altoXML);
		
		return $page;
	}
	function getImageByDocumentIdAndPageNumber($documentID, $pageNumber) {
		$rest = \REST_KOKOS::getInstance();
		$auth = \Authentication::getInstance();
		$config= $rest->getConfig();
		
		$document = $rest->get($documentID);
		
		$scanImageFile = $config['frontend']['work'] . 'ocr/images/' . $document->name . "_" . sprintf('%04d', $pageNumber) . ".jpg";
		
		$img = new \stdClass();
		
		if (file_exists($scanImageFile)) {
			$size = getimagesize($scanImageFile);
			
			$img->filePath = basename($scanImageFile);
			$img->width = $size[0] . 'px';
			$img->height = $size[1] . 'px';
		} else {
			$img->error = $scanImageFile . " does not exist";
		}
		
		return $img; 
	}
	//TODO to much bloat and conversion round-trips. lot of potential for performance tuning.
	function updatePageByDocumentIdAndPageNumber($id, $pageNumber) {
		$rest = \REST_KOKOS::getInstance();
		$auth = \Authentication::getInstance();
		
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $auth->isLogged()) return null;
		}
		
		$sys = new \GSystem();
		$objconv = new \ObjectConverter();
		$comparator = new \Comparator_KOKOS();
		
		$document = $rest->get($id);
		
		$page = $document->getPageByNumber($pageNumber);
		
		$dom = new \DOMDocument();
		$dom->loadXML($page->altoXML);
		$xmlconv = new \XMLConverter("ALTO");
		$previousALTO= $xmlconv->convertToObjectTree($dom);
		
		$request = \Slim\Slim::getInstance ()->request ();
		
		$restTransformer = new \REST_Transformer_KOKOS ();
		
		$alto = $restTransformer->deserialize_JSON ( $request->getBody(), "alto", false, "ALTO" );
		
		
		$page->alto = $alto->alto;
		
		$altoXML = $objconv->convertToDOMDocument($page->alto);
		
		
		$currentALTO= $xmlconv->convertToObjectTree($altoXML);
		
		$comparator->compareTwoObjects($previousALTO, $currentALTO, true, true, array("WIDTH", "HEIGHT", "HPOS", "VPOS", "SUBS_TYPE", "SUBS_CONTENT"));
		
		$sys->isValidAgainstSchema($altoXML, __DIR__ . "/../../../data/schema/alto-v2.0.xsd");
		
		$page->altoXML = $altoXML->saveXML();
		$page->differenceDefinition = json_encode($comparator->results, JSON_PRETTY_PRINT);
		
		$insertID = $rest->orm->save($page);
		$page->id = $insertID;
		if ($page) {
			$rest->cleanObjects($page);
			
			$page->versions = $page->getVersions();
			
			unset($page->altoXML);
			
			return $page;
		}
	}
	function updatePageByDocumentIdAndPageNumberAndVersion($id, $pageNumber, $versionNumber) {
		$rest = \REST_KOKOS::getInstance();
		$auth = \Authentication::getInstance();
		
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $auth->isLogged()) return null;
		}
		
		$document = $rest->get($id);
		$objconv = new \ObjectConverter();
		
		$page = $document->getPageByNumberAndVersion($pageNumber, $versionNumber);
		
		$request = \Slim\Slim::getInstance ()->request ();
		
		$restTransformer = new \REST_Transformer_KOKOS ();
		$alto = $restTransformer->deserialize_JSON ( $request->getBody(), "alto", false, "ALTO" );
		
		$page->alto = $alto->alto;
		
		$altoXML = $objconv->convertToDOMDocument($page->alto);
		
		$page->altoXML = $altoXML->saveXML();
		
		$rest->orm->restore($page, $versionNumber);
		
		if ($page) {
			$rest->cleanObjects($page);
			
			$page->versions = $page->getVersions();
			
			unset($page->altoXML);
			
			return $page;
		}
	}
	function getUserRegistrations() {
		$rest = \REST::getInstance();
		
		$sqlUsers = "SELECT * FROM users";
		
		$users = $rest->orm->executeQuery($sqlUsers, "User", null, 'ocr');
		$registrations = array();
		
		foreach($users as $userItem) {
			unset($userItem->id);
			unset($userItem->password);
			unset($userItem->roleID);
			unset($userItem->languageID);
			unset($userItem->birthDate);
			unset($userItem->eMail);
			unset($userItem->recoveryToken);
			
			array_push($registrations, $userItem);
		}
		
		$registrations;
		
		$response = new \stdClass();
		$response->items = $registrations;
		
		return $response;
	}
	function getPagesByDocumentId($documentID) {
		$rest = \REST::getInstance();
		$auth = \Authentication::getInstance();
		
		$className = $rest->singularize($rest->orm->getOntologyClassName());
		
		if (stripos($className, "user") === false) {
			if (!$UserID = $auth->isLogged()) return null;
		}
		
		$document = $rest->get($documentID);
		
		$pages = $document->getPages();
		
		foreach($pages as $pageItem) {
			unset($pageItem->altoXML);
			unset($pageItem->documentID);
		}
		
		return $pages;
	}
	function getUserRankings() {
		$rest = \REST::getInstance();
		
		$differences = $this->getDifferences();
		
		$changes = array();
		$rankings = array();
		
		$sqlUsers = "SELECT * FROM users";
		
		$users = $rest->orm->executeQuery($sqlUsers, "User", null, 'ocr');
		
		$users_filtered= array_map(function($e) {
			return is_object($e) ? $e->name : $e['name'];
		}, $users);
			
		foreach($differences->items as $differenceItem) {
			if (isset($changes[$differenceItem->Page->updatedByUser->name])) {
				$changes[$differenceItem->Page->updatedByUser->name]++;
			} else {
				$changes[$differenceItem->Page->updatedByUser->name] = 1;
			}
		}
		
		arsort($changes);
		
		$i=1;
		foreach($changes as $key => $value) {
			$rankingItem = new \stdClass();
			$rankingItem->position = $i;
			$rankingItem->changes = $value;
			
			$rankingUser = $users[array_search($key, $users_filtered)];
			$userObj = $rest->orm->convertStdClassToObject($rankingUser, "User");
			
			$rankingItem->user = $userObj;
			array_push($rankings, $rankingItem);
			
			$i++;
		}
		
		$response = new \stdClass();
		$response->items = $rankings;
		
		return $response;
	}
}
class Document extends \Thing {
	var $name;
	//var $arrayJSON;
	//var $informationJSON;
	//var $ontologyName;
	
	var $Pages;
	
	function getPageByNumber($number) {
		$rest = \REST::getInstance();
		
		$pages = $rest->orm->getByNamedFieldValues(new \ORM_Request("\\OCR\\Page", array("documentID" => $this->id, "number" => $number)));
		
		$latestPage = null;
		foreach($pages as $pageItem) {
			if (isset($latestPage->version)) {
				if ($pageItem->version > $latestPage->version) {
					$latestPage = $pageItem;
				}
			} else {
				$latestPage = $pageItem;
			}
		}
		
		if ($latestPage) return $latestPage;
		
		return null;
	}
	function export() {
		try {
			$rest = \REST_KOKOS::getInstance();
			
			$className = $rest->singularize($rest->orm->getOntologyClassName());
			
			if (stripos($className, "user") === false) {
				if (!$UserID = isLogged()) return null;
			}
			
			$document = $rest->get($this->id);
			
			$altoXML = $document->getAltoXML();
			
			header('Content-type: text/xml');
			header('Content-Disposition: attachment; filename="alto_document_' . $document->name . '.xml"');
			
			echo $altoXML->saveXML();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}';
		}
	}
	function getPageByNumberAndVersion($number, $version) {
		$rest = \REST::getInstance();
		
		$pages = $rest->orm->getByNamedFieldValues(new \ORM_Request("\\OCR\\Page", array("documentID" => $this->id, "number" => $number, "version" => $version)));
		
		if ($pages[0]) return $pages[0];
		
		return null;
	}
	function getDataArray() {
	    
	}
	function getPages($currentPage = null) {
		$rest = \REST::getInstance();
		
		$pagesFiltered = array();
		
		$ocr_request = new \ORM_Request("\\OCR\\Page", array("documentID" => $this->id));
		
		$pagesTotal = $rest->orm->getByNamedFieldValues($ocr_request);
		
		$ocr_request->order = "number DESC";
		$pages = $rest->orm->getByNamedFieldValues($ocr_request);
		
		foreach($pages as $relObjectItem) {
			unset($relObjectItem->altoXML);
			//if (intval($relObjectItem->number) !== intval($currentPage)) {
				array_push($pagesFiltered, $relObjectItem);
			//}
		}
		
		if ($pages[0]) return $pagesFiltered;
		
		return null;
	}
	function getAltoXML() {
		$rest = \REST::getInstance();
		$dom = new \DOMDocument();
		$xmlconv = new \XMLConverter("ALTO");
		$objconv = new \ObjectConverter();
		
		$maxVersions = array();
		
		$pagesFiltered = array();
		
		$ocr_request = new \ORM_Request("\\OCR\\Page", array("documentID" => $this->id));
		
		$pagesTotal = $rest->orm->getByNamedFieldValues($ocr_request);
		
		$ocr_request->order = "number DESC";
		$pages = $rest->orm->getByNamedFieldValues($ocr_request);
		
		foreach($pages as $page_item) {
			if (isset($page_item->version)) {
				if (isset($maxVersions[$page_item->number])) {
					if ($maxVersions[$page_item->number] < $page_item->version) {
						$maxVersions[$page_item->number] = $page_item->version;
					}
				} else {
					$maxVersions[$page_item->number] = $page_item->version;
				}
			}
		}
		
		foreach($pages as $page_item) {
			if ($page_item->version === $maxVersions[$page_item->number]) {
				array_push($pagesFiltered, $page_item);
			}
		}
			
		//print_r($maxVersions);
		
		$dom->loadXML($pagesFiltered[0]->altoXML);
		$docAlto = $xmlconv->convertToObjectTree($dom);
		
		for($i=1; $i<count($pagesFiltered); $i++) {
			$dom->loadXML($pagesFiltered[$i]->altoXML);
			$alto = $xmlconv->convertToObjectTree($dom);
			
			
			array_push($docAlto->Layout->Pages, $alto->Layout->Pages[0]);
		}
		
		$altoXML = $objconv->convertToDOMDocument($docAlto);
		//echo $altoXML->saveXML();
		
		return $altoXML;
	}
}
class Page extends \Thing {
	var $number;
	
	var $documentID;
	
	var $altoXML;
	
	var $version;
	var $differenceDefinition;
	
	function getVersions() {
		$rest = \REST::getInstance();
		
		$orm_req = new \ORM_Request("\\OCR\\Page", array("documentID" => $this->documentID, "number" => $this->number));
		$orm_req->order = "version ASC";
		$pages = $rest->orm->getByNamedFieldValues($orm_req);
		
		$versions = array();
		
		foreach($pages as $pageItem) {
			$ver = new \stdClass();
			$ver->id = $pageItem->id;
			$ver->version = $pageItem->version;
			
			array_push($versions, $ver);
		}
		
		return $versions;	
	}
}
class User extends \Thing {
	var $name;
	
	protected $password;
	
	protected $recoveryToken;
	//static $encryptions = array("password");
	//static $validationRules = array("name" => "/^[A-Z0-9]{5,10}$/", "password" => "/^[A-Z0-9]{5,10}$/");
	
	var $eMail;
	var $birthDate;
	var $Role;
	var $Language;
	
	function __construct() {
	}
	function getEncryptions() {
		return $this->encryptions;
	}
	function getValidationRules() {
		return $this->validationRules;
	}
	function getPassword() {
		return $this->password;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getRecoveryToken() {
		return $this->recoveryToken;
	}
	function setRecoveryToken($recoveryToken) {
		$this->recoveryToken = $recoveryToken;
	}
}
?>