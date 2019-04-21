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
	function add() {
		$this->save();
	}
	function update($id) {
		$this->save($id);
	}
	function save($id = null) {
		$rest = REST::getInstance();
		
		if (!$UserID = $rest->isLogged()) return null;
		
		$request = $rest->app->request ();
		
		$restTransformer = new \REST_Transformer ();
		$object = $restTransformer->deserialize_JSON ( $request->getBody (), 'User' );
		
		$object->setPassword($this->crypto($object->name, $object->password));
		
		try {
			$saveResp = $rest->orm->save($object);
			
			if (intval($saveResp)) {
				$object->id = $saveResp;
			}
		} catch ( Exception $e ) {
			$extract = new stdClass();
			$extract->error = new stdClass();
			$extract->error->message = "Database Transaction Failure";
			
			$extract->error->details = $e->getMessage();
			$extract->error->file = $e->getFile();
			$extract->error->code = $e->getCode();
			$extract->error->line = $e->getLine();
			
			echo json_encode ( $extract, JSON_PRETTY_PRINT );
			exit ();
		}
		
		if ($object) {
			$rest->cleanObjects($object);
			
			echo json_encode ( $object, JSON_PRETTY_PRINT );
		}
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