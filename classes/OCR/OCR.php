<?php
namespace OCR;

class OCR  {
	function __construct() {
	}
	
}
class Document extends \Thing {
	var $name;
	
	var $Pages;
	
	function getPageByNumber($number) {
		$rest = new \REST();
		
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
		$rest = new \REST();
		
		$pages = $rest->orm->getByNamedFieldValues(new \ORM_Request("\\OCR\\Page", array("documentID" => $this->id, "number" => $number, "version" => $version)));
		
		if ($pages[0]) return $pages[0];
		
		return null;
	}
	function getPages($currentPage = null) {
		$rest = new \REST();
		
		$pagesFiltered = array();
		
		$pagesTotal = $rest->orm->getByNamedFieldValues("\\OCR\\Page", array("documentID"), array($this->id), false, null, false, true);
		
		$pages = $rest->orm->getByNamedFieldValues("\\OCR\\Page", array("documentID"), array($this->id), false, null, false, true, null, "number DESC");
		
		foreach($pages as $relObjectItem) {
			unset($relObjectItem->altoXML);
			//if (intval($relObjectItem->number) !== intval($currentPage)) {
				array_push($pagesFiltered, $relObjectItem);
			//}
		}
		
		//$result = new \stdClass();
		//$result->items = $pagesFiltered;
		//$result->total_count = count($pagesTotal);
		
		if ($pages[0]) return $pagesFiltered;
		
		return null;
	}
}
class Page extends \Thing {
	var $number;
	
	var $documentID;
	
	var $altoXML;
	
	var $version;
	var $differenceDefinition;
	
	function getVersions() {
		$rest = new \REST();
		
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