<?php
include_once (__DIR__ . "/../../../engulfing-core/classes/Things/Things.php");
include_once (__DIR__ . "/../../../engulfing-core/classes/Core/Helper.php");

include_once ('User.php');
include_once ('Role.php');
include_once ('Language.php');

class Authentication {
	use Helper;
	
	var $showRegister 	= true;
	var $showNewUser 	= true;
	var $userclass		= "User";
	
	function __construct() {
		$this->orm = new ORM(array("convert" => true));
	}
	function isLogged() {
		if (isset($_COOKIE['logged'])) {
			if (($_COOKIE['logged'] == 1 || gettype($_COOKIE['logged']) == "array")) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function recover($email) {
		$objects = $this->getByNamedFieldValues("User", array("email"), array($email));
		
		$objects[0] = $this->getById("User", $objects[0]->id);
		
		if (isset($objects[0])) {
			return $objects[0];
		}
	}
	function resetPassword($recoveryToken) {
		$objects = $this->getByNamedFieldValues("User", array("recoveryToken"), array($recoveryToken));
	
		$objects[0] = $this->getById("User", $objects[0]->id);
		
		if (isset($objects[0])) {
			return $objects[0];
		}
	}
	function signupUser($request) {
		$restTransformer = new REST_Transformer ();
		$newUser = $restTransformer->deserialize_JSON ( $request->getBody (), "User" );
		
		$password = $newUser->getPassword();
		
		if (isset($newUser->name) && isset($password) && isset($newUser->eMail)) {
			$newUser = $newUser->save(null, array("password"));
			
			$app = \Slim\Slim::getInstance();
			if (isset($newUser)) {
				session_start();
					
				setcookie("logged", "1", time()+3600, "/", null);
				setcookie("UserName", "" . $newUser->name, time() + (3600 * 1), "/", null);
				setcookie("UserID", "" . $newUser->id, time()+3600, "/", null);
				setcookie("UserRoleID", "" . $newUser->Role->id, time()+3600, "/", null);
				setcookie("UserLanguageID", "" . 0, time()+3600, "/", null);
				setcookie("UserEMail", "" . $newUser->eMail, time()+3600, "/", null);
					
						
				$app->redirect($_SERVER['HTTP_REFERER']);
			} else {
				$app->redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	function login($username, $password) {
	    $rest = \REST::getInstance();
	    
	    $orm_req = new ORM_Request("User", array("name" => $username), array("roleID", "languageID"));
	    $objects = $rest->orm->getByNamedFieldValues($orm_req);
		
		if (isset($objects[0])) {
			$objects[0]->setPassword($this->crypto($username, $password));
			$this->orm->save($objects[0]);
			
			if ( hash_equals($objects[0]->getPassword(), crypt($password, $objects[0]->getPassword())) ) {
				$objects[0] = $this->orm->getById("User", $objects[0]->id);
				return $objects[0];
			}
		}
	}
	function logout() {
		$rest = \REST::getInstance();
		
		$response = $rest->request("api/authentication/users?UserName=" . $username, "GET");
		
		$restTransformer = new REST_Transformer();
		$result = $restTransformer->deserialize_JSON($response, "User");
		
		return $result;
	}
}
?>