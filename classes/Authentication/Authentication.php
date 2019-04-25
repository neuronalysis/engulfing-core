<?php
class Authentication {
	use Helper;
	
	var $showRegister 	= true;
	var $showNewUser 	= true;
	var $userclass		= "User";
	
	public static $instance;
	
	function __construct() {
		self::$instance = $this;
	}
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	function isLogged() {
	    if(!isset($_SESSION)) {
	        session_start();
	    }
	    
	    if (isset($_SESSION['logged'])) {
	        if (($_SESSION['logged'] == 1 || gettype($_COOKIE['logged']) == "array")) {
				if (isset($_COOKIE['UserID'])) {
					return $_COOKIE['UserID'];
				}
			}
		}
		
		return false;
	}
	function recoverPassword() {
		$rest = \REST::getInstance();
		$config = $rest->getConfig();
		
		$email = $_GET['email'];
		
		if (isset($email)) {
			$ormr = new \ORM_Request("User", array("eMail" => $email));
			
			$objects = $rest->orm->getByNamedFieldValues($ormr);
			
			
			if (isset($objects[0])) {
				$user = $objects[0];
			}
				
			$app = \Slim\Slim::getInstance();
			if (isset($user)) {
				try {
					$user->setRecoveryToken(md5(uniqid($user->name, true)));
					
					$rest->orm->save($user);
					
					$mail = new PHPMailer();
					$mail->isSMTP();
					$mail->SMTPOptions = array(
							'ssl' => array(
									'verify_peer' => false,
									'verify_peer_name' => false,
									'allow_self_signed' => true
							)
					);
					$mail->Host = $config['mailing']['smtp'];
					$mail->Port = 587;
					// if need auth
					$mail->SMTPAuth = true;
					$mail->setFrom($config['frontend']['siteAdmin'], 'Mailer');
					$mail->addAddress($email);     // Add a recipient
					$mail->isHTML(true);
					
					$mail->Username = $config['mailing']['username'];
					$mail->Password = $config['mailing']['password'];
					$mail->Subject = 'Password Recovery Instructions';
					$mail->Body = 'Hello ' . $user->name . ',<br>
<br>
Someone has requested a link to change your password on ' . $config['frontend']['url'] . '. You can do this by clicking on the link below:<br>
<br>
<a href="' . $config['frontend']['url'] . 'usermanagement/#recovery/' . $user->getRecoveryToken() . '">Change your password</a><br>
<br>
If you did not request this, please ignore this email. Your password will not change unless you access the link above and create a new one.<br>
<br>
Thank you for using our service! <br>
<br>
Questions? Suggestions? ' . $config['frontend']['siteAdmin'];
					
					$response = new Response();
					
					if(!$mail->send()) {
						$response->error = $mail->ErrorInfo;
					} else {
						$response->message = "new password sent";
					}
					
					
					echo json_encode($response, JSON_PRETTY_PRINT);
				} catch (Exception $e) {
					exit;
				}
			} else {
				$response = new Response();
				
				echo json_encode($response, JSON_PRETTY_PRINT);
			}
		}
	}
	function resetPassword() {
		$rest = \REST::getInstance();
		
		$app = \Slim\Slim::getInstance ();
		$request = $app->request ();
		
		$restTransformer = new REST_Transformer ();
		
		$result = $restTransformer->deserialize_JSON ( $request->getBody (), 'Recovery' );
		
		if (isset($result->recoveryToken)) {
			$ormr = new \ORM_Request("User", array("recoveryToken" => $result->recoveryToken));
			
			$objects = $rest->orm->getByNamedFieldValues($ormr);
			
			$objects[0] = $rest->orm->getById("User", $objects[0]->id);
			
			if (isset($objects[0])) {
				$user = $objects[0];
			}
			
			if (isset($user)) {
				try {
					$user->setRecoveryToken('');
					
					$user->setPassword($this->crypto($user->name, $result->password));
					
					$rest->orm->save($user);
					
					session_start();
					
					setcookie("logged", "1", time()+3600, "/", null);
					setcookie("UserName", "" . $user->name, time()+3600, "/", null);
					setcookie("UserID", "" . $user->id, time()+3600, "/", null);
					setcookie("UserRoleID", "" . $user->Role->id, time()+3600, "/", null);
					setcookie("UserLanguage", "" . $user->Language->id, time()+3600, "/", null);
					setcookie("UserEMail", "" . $user->eMail, time()+3600, "/", null);
					
					//$app->redirect($_SERVER['HTTP_REFERER']);
					
					$response = new Response();
					
					$response->message = "New Password Defined.";
					
					echo json_encode($response, JSON_PRETTY_PRINT);
				} catch (Exception $e) {
					$extract = new Extract();
					$extract->error= new Error();
					$extract->error->details = $e->getMessage();
					
					
					//print_r($extract);
					exit;
				}
			} else {
				$response = new Response();
				unset($response->message);
				$response->warning = "No New Password Defined. Invalid Recovery Token.";
				
				echo json_encode($response, JSON_PRETTY_PRINT);
			}
		}
	}
	function signupUser() {
		$rest = \REST::getInstance();
		
		$app = \Slim\Slim::getInstance ();
		$request = $app->request ();
		
		$restTransformer = new REST_Transformer ();
		$newUser = $restTransformer->deserialize_JSON ( $request->getBody (), "User" );
		$newUser->Language->id = 0;
		
		$password = $newUser->getPassword();
		
		$newUser->setPassword($this->crypto($newUser->name, $password));
		
		if (isset($newUser->name) && isset($password) && isset($newUser->eMail)) {
			$newUser->id = $rest->orm->save($newUser);
			
			if (isset($newUser)) {
				session_start();
					
				setcookie("logged", "1", time()+3600, "/", null);
				setcookie("UserName", "" . $newUser->name, time() + (3600 * 1), "/", null);
				setcookie("UserID", "" . $newUser->id, time()+3600, "/", null);
				setcookie("UserRoleID", "" . $newUser->Role->id, time()+3600, "/", null);
				setcookie("UserLanguageID", "" . 0, time()+3600, "/", null);
				setcookie("UserEMail", "" . $newUser->eMail, time()+3600, "/", null);
					
						
				return $newUser;
			} else {
				//$app->redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	function login() {
		$rest = \REST::getInstance();
		$config = $rest->getConfig();
		
		if (isset($_POST['LoginUserName']) && isset($_POST['LoginUserPassword'])) {
			$orm_req = new ORM_Request("User", array("name" => $_POST['LoginUserName']), array("roleID", "languageID"));
			$orm_req->includeProtectedFields = true;
			
			$objects = $rest->orm->getByNamedFieldValues($orm_req);
			
			if (isset($objects[0])) {
				if ( hash_equals($objects[0]->getPassword(), crypt($_POST['LoginUserPassword'], $objects[0]->getPassword())) ) {
					$user = $rest->orm->getById("User", $objects[0]->id);
				}
			}
			
			$app = \Slim\Slim::getInstance();
			if (isset($user)) {
			    if(!isset($_SESSION)) {
			        session_start();
			    }
				
				$_SESSION['logged'] = '1';
				
				setcookie("logged", "1", time()+3600, "/", null);
				setcookie("UserName", "" . $_POST['LoginUserName'], time() + (3600 * 1), "/", null);
				setcookie("UserID", "" . $user->id, time()+3600, "/", null);
				setcookie("UserRoleID", "" . $user->Role->id, time()+3600, "/", null);
				setcookie("UserLanguageID", "" . $user->Language->id, time()+3600, "/", null);
				setcookie("UserEMail", "" . $user->eMail, time()+3600, "/", null);
				
				$app->redirect($_SERVER['HTTP_REFERER']);
			} else {
			    if(!isset($_SESSION)) {
			        session_start();
			    }
			    setcookie("logged", "0", time()+3600, "/", null);
			    
				
				$home_url = $config['frontend']['url'];
				
				if (strpos($_POST['refererURL'], "?") !== false) {
					$app->redirect($_POST['refererURL'] . "&login=failed");
				} else {
					$app->redirect($home_url. "?login=failed");
				}
			}
		}
	}
	function logout() {
	    $app = \Slim\Slim::getInstance();

	    if(!isset($_SESSION)) {
	        session_start();
	        
	        $_SESSION['logged'] = '0';
	        
	        session_destroy();
	    } else {
	        $_SESSION['logged'] = '0';
	        
	        session_destroy();
	    }
	    
	    
		setcookie("logged", "" . "0", -1, "/", null);
		setcookie("UserName", "", -1, "/", null);
		setcookie("UserPassword", "", -1, "/", null);
		setcookie("UserID", "", -1, "/", null);
		setcookie("UserRoleID", "", -1, "/", null);
		setcookie("UserLanguageID", "", -1, "/", null);
		setcookie("UserEMail", "", -1, "/", null);
		
		//$app->redirect(str_ireplace("?logout=true", "", $_SERVER['HTTP_REFERER']) . "?logout=true");
		//$app->redirect(str_ireplace("?logout=true", "", $_SERVER['HTTP_REFERER']));
		$app->redirect($_SERVER['HTTP_REFERER']);
	}
}
class Recovery {
	var $recoveryToken;
	
	var $password;
	
}
?>