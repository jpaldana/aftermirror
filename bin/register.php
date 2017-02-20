<?php
$page->block("header", array());
?>
<section class='wrapper'>
<div class='inner'>
<article>
	<h2>after|mirror: register</h2>
<?php
$do = (isset($_GET["do"])) ? $do = $_GET["do"] : "register";

switch($do) {
	case "proc-register":
		$auth = new Auth($mysqli);
		$protectedUsers = array(
			"admin",
			"administrator",
			"webmaster",
			"postmaster",
			"system",
			"sys",
			"debug",
			"help",
			"support",
			"",
			" "
		);
		if (cleanANString(strtolower($_POST["username"])) !== strtolower($_POST["username"])) {
			echo "
				<h3>Please enter a valid username. Only a-z and 0-9.</h3>
				<a href='/register.do?do=register' class='btn btn-lg btn-danger'>Return</a>
			";
		}
		elseif (in_array(cleanANString(strtolower($_POST["username"])), $protectedUsers)) {
			echo "
				<h3>You cannot use this username. Please try something else.</h3>
				<a href='/register.do?do=register' class='btn btn-lg btn-danger'>Return</a>
			";
		}
		else {
			$act = $auth->registerNewUser(cleanANString(strtolower($_POST["username"])), $_POST["password"]);
			switch ($act) {
				case AUTH_ERROR_NO_ERROR:
					$dm = base64_encode($_SERVER["HTTP_HOST"]);
					echo "
					<h3>Successfully registered.</h3>
					<a href='/login.do?do=login&dm={$dm}' class='btn btn-lg btn-success'>Login</a>
					";
				break;
				case AUTH_ERROR_USER_EXISTS:
					echo "
					<h3>Username exists.</h3>
					<a href='/register.do?do=register' class='btn btn-lg btn-danger'>Return</a>
					";
				break;
				default:
				case AUTH_ERROR_GENERIC:
					echo "
					<h3>Unknown error: {$act}</h3>
					<a href='/register.do?do=register' class='btn btn-lg btn-danger'>Return</a>
					";
				break;
			}
		}
		break;
	case "register":
		echo "
			<form action='register.do?do=proc-register' method='post'>
				<div class='form-group'>
					<label>Username <span style='color: red;'>*</span></label>
					<input type='text' name='username' placeholder='Username' class='form-control' />
					<small>Only [a-z] and [0-9] characters are allowed.</small>
				</div>
				<div class='form-group'>
					<label>Password (optional) &mdash; Yes, you can have a password-less account.</label>
					<input type='password' name='password' placeholder='Password' class='form-control' />
				</div>
				<br />
				<div class='form-group'>
					<input type='submit' value='Register' class='btn btn-warning form-control' />
				</div>
			</form>
		";
	break;
}
?>
</article>
</div>
</section>
<?php
$page->block("footer", array());
?>