<?php
if (isset($_GET["pic"])) {
	if (file_exists("data/account/profile/" . basename($_GET["pic"]) . ".jpg")) {
		header("Content-Type: image/jpg");
		readfile("data/account/profile/" . basename($_GET["pic"]) . ".jpg");
	}
	else {
		header("Content-Type: image/png");
		readfile("images/user.png");
	}
}
elseif (isset($_GET["up"])) {
	if (!empty($_FILES)) {
		$tmp_name = $_FILES["file"]["tmp_name"];
		$file = AUTH_USER . ".jpg";
		$target = "data/account/profile/{$file}";
		if (file_exists($target)) {
			unlink($target);
			$auth->modPoints(AUTH_USER, 200);
		}
		else {
			$auth->modPoints(AUTH_USER, 2000);
		}
		iTF($tmp_name, $target, 640, 92);
	}
	echo "
	<section class='wrapper'>
	<div class='inner'>
	<article>
		<p>Please wait... <span class='fa fa-spinner fa-spin'></span></p>
		<br />
		<a href='?app=Account' class='btn btn-info'>Click me if you are not redirected within a few seconds</a>
	</article>
	</div>
	</section>
	<script>
		function refresher() {
			window.location = '?app=Account&refresh';	
		}
		setTimeout('refresher()', 2500);
	</script>
	";
}
else {
	requireLogin();

	$page->show("header");

	$page->block("spanner-largeoffset", array("image" => false, "title" => "my account", "content" => "Manage your account here.", "href" => false, "text" => false));

	echo "
	<section class='wrapper'>
	<div class='inner'>
	<article>
	<div class='row 50% uniform'>
	";

	// picture
	echo "
	<div class='4u 12u$(small)'>
		<h2>Profile Picture</h2>
		<span class='image fit'>
			<img src='/account.ps?pic=" . AUTH_USER . "' alt='' />
		</span>
		<span class='button special small fit' id='profile-image-upload-btn'>Change</span>
		<progress id='profile-picture-file-upl-progress' value='0' max='1' style='width: 100%; display: none;'></progress>
	</div>
	<div class='6u 12u$(small)'>
		<h2>Statistics</h2>
		<p>Nothing here, yet!</p>
	</div>
	";

	echo "
	</div>
	</article>
	</div>
	</section>
	
	<div style='display: none;'>
		<form action='/account.ps?up' method='post' enctype='multipart/form-data' id='profile-picture-file-upl-form'>
			<input type='file' name='file' id='profile-picture-file-upl'>
		</form>
	</div>
	";

	$page->block("footer", array("js" => array("https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.0/notify.min.js", "/assets/js/account.js")));	
}
?>