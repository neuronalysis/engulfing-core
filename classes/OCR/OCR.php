<?php
namespace OCR;

class OCR  {
	function __construct() {
	}
	
	function getDocuments() {
	    $rest = \REST::getInstance();
	    
	    $documents = $rest->orm->getAllByName(new \ORM_Request("\\OCR\\Document", null, array("informationJSON")));
	    
	    return $documents;
	}
}
class Document extends \Thing {
	var $name;
	var $arrayJSON;
	var $informationJSON;
	var $ontologyName;
	
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