<?php
enforceLogin();

if (isset($_GET["ax"])) {
	setcookie("auth", $_GET["ax"], time() + (3600 * 24));
}

$page->show("header_banner");

$qs = base64_decode($_GET["qs"]);
echo "
<section class='wrapper'>
<div style='text-align: center;'>
	<div style='width: 50%; min-width: 300px; max-width: 600px; display: inline-block;'>
		<article>
			<h1>welcome back, " . AUTH_USER . "</h1>
			<p>Returning you to your previous page... <span class='fa fa-spinner fa-spin'></span></p>
			<br />
			<a href='{$qs}' class='btn btn-info'>Click me if you are not redirected within a few seconds</a>
			<script>
				setTimeout(\"location.href = '{$qs}'\", 2500);
			</script>
		</article>
	</div>
</div>
</section>
";

$page->show("footer");
?>