<?php
if (isset($_POST["loginUser"]) && isset($_POST["loginPass"])) {
	$username = $_POST["loginUser"];
	$password = $_POST["loginPass"];
	//$username = fnDecrypt(base64_decode($username));
	//$password = fnDecrypt(base64_decode($password));
	
	$auth = new Auth($mysqli);
	if ($auth->getUserPassword($username) == $auth->getNewPasswordHash($password)) {
		// password match (includes password-less accounts)
		$session = $auth->getUserSessionKey($username);
		setcookie("auth", $session, time() + (3600 * 24 * 30), "/");
		
		$dm = base64_decode($_GET["dm"]);
		if ($dm !== $_SERVER["HTTP_HOST"]) {
			if (isSomething($_GET["return"])) {
				header("Location: https://{$dm}/auth.ps?qs=" . $_GET["return"] . "&_u={$username}&_s=" . base64_encode(fnEncrypt($session)));
			}
			else {
				header("Location: https://{$dm}/home.do?welcome&_u={$username}");
			}
		}
		else {
			if (isSomething($_GET["return"])) {
				header("Location: /passthru.do?qs=" . $_GET["return"]);
			}
			else {
				header("Location: /home.do?welcome");
			}
		}
	}
	else {
		if (isSomething($_GET["return"])) {
			header("Location: /login.do?username={$username}&qs={$_GET['return']}&error=" . AUTH_ERROR_CRYPT_INVALID_ERROR . "&dm={$_GET['dm']}#login");
		}
		else {
			header("Location: /login.do?username={$username}&error=" . AUTH_ERROR_CRYPT_INVALID_ERROR . "&dm={$_GET['dm']}#login");
		}
	}
}
elseif (isset($_GET["logout"])) {
	setcookie("auth", "", time() - 3600, "/");
	//session_start();
	//session_destroy();
	//print_a($_COOKIE);
	header("Location: /home.do?goodbye");
}
else {
if (defined("AUTH_USER")) {
	if (isset($_GET["return"])) {
		$dm = base64_decode($_GET["dm"]);
		if ($dm !== $_SERVER["HTTP_HOST"]) {
			if (isSomething($_GET["return"])) {
				header("Location: https://{$dm}/auth.ps?qs=" . $_GET["return"] . "&_u=" . AUTH_USER . "&_s=" . base64_encode(fnEncrypt($session)));
			}
			else {
				header("Location: https://{$dm}/home.do?welcome&_u=" . AUTH_USER);
			}
		}
		else {
			if (isSomething($_GET["return"])) {
				header("Location: /passthru.do?qs=" . $_GET["return"]);
			}
			else {
				header("Location: /home.do?welcome");
			}
		}
	}
	else {
		// return to home.
		header("Location: /home.do?welcome");
	}
}
$page->show("header_banner");
?>

<section class="wrapper">
<div style="text-align: center;">
	<div style="width: 50%; min-width: 300px; max-width: 600px; display: inline-block;">
		<article>
		<h1><i class="fa fa-lock"></i> login</h1>
		<form action="/login.ps?dm=<?php echo isset($_GET["dm"]) ? $_GET["dm"] : base64_encode($_SERVER["HTTP_HOST"]); ?>&return=<?php if (isset($_GET["return"])) echo $_GET["return"]; ?>" method="post">
			<?php
				if (isset($_GET["error"])) {
					echo "
						<div>Invalid login.</div>
					";
				}
			?>
				<div class="row uniform 50%">
					<div class="12u">
						<input type="text" name="loginUser" id="loginUser" placeholder="Login" value="<?php if (isset($_GET["username"])) echo $_GET["username"]; ?>" />
					</div>
					<div class="12u">
						<input type="password" name="loginPass" id="loginPass" placeholder="Password" />
					</div>
					<div class="12u">
						<input type="submit" class="button special fit" value="Login" />
					</div>
					<div class="12u">
						<a href="/register.do" class="button fit">No account? Register here!</a>
					</div>
				</div>
		</form>
		</article>
	</div>
</div>
</section>

<?php
$page->show("footer");
}
?>