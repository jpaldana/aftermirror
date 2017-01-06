<?php
define("AUTH_ACCESS_GUEST", 0);
define("AUTH_ACCESS_NORMAL", 1);
define("AUTH_ACCESS_VERIFIED", 2);
define("AUTH_ACCESS_BANNED", 10);
define("AUTH_ACCESS_ADMIN", 29);

define("AUTH_ERROR_USER_EXISTS", 100);
define("AUTH_ERROR_NO_ERROR", 190);
define("AUTH_ERROR_GENERIC", 199);
define("AUTH_ERROR_CRYPT_INVALID_ERROR", 120);

define("AUTH_SALT", sha1("pepppper"));

class Auth {
	var $db;
	
	function __construct($mysqli) {
		$this->db = $mysqli;
	}
	
	function getPoints($username) {
		$uid = $this->getUserID($username);
		return $this->getProfileAttribute($uid, "points");
	}
	function modPoints($username, $value) {
		$uid = $this->getUserID($username);
		$points = $this->getPoints($username);
		if (!$points) {
			$points = 0;
		}
		$points += $value;
		$this->setProfileAttribute($uid, "points", $points, SQLITE3_INTEGER);
	}
	
	
	function getProfileAttribute($uid, $attribute) {
		$stmt = $this->db->prepare("SELECT {$attribute} FROM profile WHERE uid = ?;");
		$stmt->bind_param('i', $uid);
		$stmt->execute() or die($this->db->error);
		$stmt->bind_result($res);
		$stmt->fetch();
		return $res;
	}
	function setProfileAttribute($uid, $attribute, $value, $type) {
		$stmt = $this->db->prepare("UPDATE profile SET " . $attribute . " = ? WHERE uid = ?;");
		switch ($type) {
			case SQLITE3_INTEGER:
				$stmt->bind_param('ii', $value, $uid);
			break;
			case SQLITE3_TEXT:
				$stmt->bind_param('si', $value, $uid);
			break;
		}
		
		$res = $stmt->execute() or die($this->db->error);
	}
	
	function getUsername($uid) {
		$stmt = $this->db->prepare("SELECT username FROM users WHERE uid = ?;");
		$stmt->bind_param('i', $uid);
		$stmt->execute() or die($this->db->error);
		$stmt->bind_result($res);
		$stmt->fetch();
		return $res;
	}
	function getUserAttribute($username, $attribute) {
		$stmt = $this->db->prepare("SELECT {$attribute} FROM users WHERE username = ?;");
		$stmt->bind_param('s', $username);
		$stmt->execute() or die($this->db->error);
		$stmt->bind_result($res);
		$stmt->fetch();
		return $res;
	}
	function getUserID($username) {
		return $this->getUserAttribute($username, "uid");
	}
	function getUserSessionKey($username) {
		return $this->getUserAttribute($username, "session");
	}
	function getUserPassword($username) {
		return $this->getUserAttribute($username, "password");
	}
	function getUserAccess($username) {
		return $this->getUserAttribute($username, "access");
	}
	
	function getNewSessionKey($username) {
		return substr(sha1($username), 5, 5).':'.sha1(uniqid().$username);
	}
	function getNewPasswordHash($password) {
		return sha1($password.AUTH_SALT);
	}
	
	function setUserAttribute($username, $attribute, $value, $type) {
		$stmt = $this->db->prepare("UPDATE users SET " . $attribute . " = ? WHERE username = ?;");
		switch ($type) {
			case SQLITE3_INTEGER:
				$stmt->bind_param('is', $value, $username);
			break;
			case SQLITE3_TEXT:
				$stmt->bind_param('ss', $value, $username);
			break;
		}
		
		$res = $stmt->execute() or die($this->db->error);
	}
	function setUserSessionKey($username, $key) {
		$this->setUserAttribute($username, "session", $key, SQLITE3_TEXT);
	}
	function setUserPassword($username, $key) {
		$this->setUserAttribute($username, "password", $this->getNewPasswordHash($key), SQLITE3_TEXT);
	}
	function setUserAccess($username, $key) {
		$this->setUserAttribute($username, "access", $key, SQLITE3_INTEGER);
	}
	
	function checkUserExists($username) {
		return $this->getUserID($username) ? true : false;
	}
	function checkIfPasswordless($username) {
		return ($this->getUserPassword($username) == $this->getNewPasswordHash('')) ? true : false;
	}
	function checkUserSession($session) {
		$stmt = $this->db->prepare("SELECT username FROM users WHERE session = ?;");
		$stmt->bind_param('s', $session);
		$stmt->execute() or die($this->db->error);
		$stmt->bind_result($username);
		
		while ($stmt->fetch()) {
			return $username;
		}
	}
	function checkIfProfilePictureExists($username) {
		return file_exists("data/account/profile/{$username}.jpg");
	}
	
	function registerNewUser($username, $password) {
		if ($this->checkUserExists($username)) {
			return AUTH_ERROR_USER_EXISTS;
		}
		$stmt = $this->db->prepare("INSERT INTO users (username, password, session, access) VALUES (?, ?, ?, ?);");
		if (strlen($password) > 0) {
			$pw_hash = $this->getNewPasswordHash($password);
		}
		else {
			$pw_hash = $this->getNewPasswordHash('');
		}
		$sess_key = $this->getNewSessionKey($username);
		$auth_access = AUTH_ACCESS_NORMAL;
		$stmt->bind_param('sssi', $username, $pw_hash, $sess_key, $auth_access);
		
		$stmt->execute() or die($this->db->error);
		
		$uid = $this->getUserID($username);
		
		unset($stmt);
		$stmt = $this->db->prepare("INSERT INTO profile (uid, points) VALUES (?, 0);");
		$stmt->bind_param('i', $uid);
		$stmt->execute() or die($this->db->error);
		
		return AUTH_ERROR_NO_ERROR;
	}
	
	function getAllUsers() {
		$stmt = $this->db->prepare("SELECT username FROM users;");
		$stmt->execute() or die($this->db->error);
		$stmt->bind_result($username);
		
		$return = array();
		
		while ($stmt->fetch()) {
			$return[] = $username;
		}
		return $return;
	}
}

// DEFINE our cipher
define('AES_256_CBC', 'aes-256-cbc');
define('AUTH_SYSTEM_KEY', file_get_contents("config/system.key"));

function fnEncrypt($data) {
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));
	$encrypted = openssl_encrypt($data, AES_256_CBC, AUTH_SYSTEM_KEY, 0, $iv);
	return $encrypted . ':' . base64_encode($iv);
}
function fnDecrypt($data) {
	$parts = explode(':', $data);
	return openssl_decrypt($parts[0], AES_256_CBC, AUTH_SYSTEM_KEY, 0, base64_decode($parts[1]));
}

// --

if (!isset($auth)) $auth = new Auth($mysqli);
// auto login
if (isset($_COOKIE["auth"])) {
	$_ses = $auth->checkUserSession($_COOKIE["auth"]);
	if ($_ses) {
		define("AUTH_USER", $_ses);
		define("AUTH_UID", $auth->getUserID($_ses));
	}
}

function requireLogin() {
	if (!defined("AUTH_USER")) {
		$qs = base64_encode(strtr($_SERVER["REQUEST_URI"], array("do/" => "app/")));
		$dm = base64_encode($_SERVER["HTTP_HOST"]);
		$host = $_SERVER["HTTP_HOST"];
		if ($host !== "aftermirror.com" && $host !== "fate.aldana.pw:81") {
			$host = "aftermirror.com";
		}
		if (!headers_sent()) {
			header("Location: //{$host}/auth.ps?return={$qs}&dm={$dm}");
		}
		echo "
		<script>location.href = '//{$host}/auth.ps?return={$qs}&dm={$dm}';</script>
		<h1>Login Required.</h1>
		<a href='//{$host}/auth.ps?return={$qs}&dm={$dm}'>Click me to go to the login page.</a>
		";
		die();
	}
}
function enforceLogin() {
	if (!defined("AUTH_USER")) {
		die();
	}
}
function requireAccess($auth, $level) {
	$authLevel = $auth->getUserAccess(AUTH_USER);
	if ($level !== $authLevel) {
		die("You do not have permission to access this page.");
	}
}
?>