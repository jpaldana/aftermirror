<?php
if (defined("AUTH_USER") && ($_SERVER["HTTP_HOST"] == "aftermirror.com" || $_SERVER["HTTP_HOST"] == "fate.aldana.pw")) {
	// cross-site login
	// don't use passthru for this
	$dm = base64_decode($_GET["dm"]);
	header("Location: //{$dm}/auth.ps?qs=" . $_GET["return"] . "&_s=" . base64_encode(fnEncrypt($_COOKIE["auth"])));
}
elseif (isset($_GET["qs"]) && isset($_GET["_s"])) {
	$qs = base64_decode($_GET["qs"]);
	setcookie("auth", fnDecrypt(base64_decode($_GET["_s"])), time() + (3600 * 24 * 30), "/");
	header("Location: {$qs}");
}
else {
	header("Location: /login.do?return={$_GET['return']}&dm={$_GET['dm']}");
}
?>