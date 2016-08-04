<?php
$desc = "";
if (!file_exists("../engulfing/")) {
	$desc = "../";
	if (!file_exists($desc . "../engulfing/")) {
		$desc .= "../";
	}
}
include_once ($desc . "../engulfing/engulfing-generated/classes/things/Things_Generated.php");
include_once ($desc . "../engulfing/engulfing-generated/classes/authentication/Authentication_Generated.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/Helper.php");
include_once ($desc . "../engulfing/engulfing-core/classes/Core/ORM/ORM.php");

include_once ('User.php');
include_once ('Role.php');
include_once ('Language.php');

class Authentication {
	use Helper;
	use ORM;
	
	var $showRegister 	= true;
	var $showNewUser 	= true;
	var $userclass		= "User";
	
	function __construct() {
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
		$objects = $this->getByNamedFieldValues("User", array("name"), array($username), false, null, true);
		
		if (isset($objects[0])) {
			$objects[0]->setPassword($this->crypto($username, $password));
			$objects[0]->save();
			
			if ( hash_equals($objects[0]->getPassword(), crypt($password, $objects[0]->getPassword())) ) {
				return $objects[0];
			}
		}
	}
	function logout() {
		$rest = new REST();
		
		$response = $rest->request("api/authentication/users?UserName=" . $username, "GET");
		
		$restTransformer = new REST_Transformer();
		$result = $restTransformer->deserialize_JSON($response, "User");
		
		return $result;
	}
}
?>