<?php
class User extends Thing {
	var $name;
	
	protected $password;
	
	protected $recoveryToken;
	//static $encryptions = array("password");
	//static $validationRules = array("name" => "/^[A-Z0-9]{5,10}$/", "password" => "/^[A-Z0-9]{5,10}$/");
	
	var $eMail;
	var $birthDate;
	var $Language;
	var $Role;
	
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
	function getWatchlists() {
		$rest = REST::getInstance();
		
		$ormr = new \ORM_Request("Watchlist", array("ownerID" => $this->id));
		
		$watchlists = $rest->orm->getByNamedFieldValues($ormr);
		
		if (isset($watchlists[0])) {
			$ormr_wli = new \ORM_Request("WatchlistItem", array("watchlistID" => $watchlists[0]->id));
			
			$watchlists[0]->WatchlistItems = $rest->orm->getByNamedFieldValues($ormr_wli);
			
			echo json_encode ( $watchlists[0], JSON_PRETTY_PRINT );
		} else {
			$watchlist = new Watchlist();
			$watchlist->Owner = $rest->orm->getById("Owner", $this->id);
			
			$watchlist->id = $rest->orm->save($watchlist);
			
			echo json_encode ( $watchlist, JSON_PRETTY_PRINT );
		}
		
		
	}
}
class Owner {
	
}
?>