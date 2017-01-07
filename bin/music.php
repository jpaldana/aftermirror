<?php
requireLogin();

if (isset($_GET["m"])) {
	$file = "data/music/" . base64_decode($_GET["m"]);
	if (file_exists($file) && fextIsMusic($file)) {
		$page->block("streamfile", array("file" => $file, "type" => "audio/mp3"));
	}
}
else {
	$page->block("header", array("css" => array("/assets/css/music.css")));

	$page->block("spanner-largeoffset", array("image" => "images/bg-music.jpg", "title" => "listen to music?", "content" => "Did you want to listen to that wonderful soundtrack that brought tears to your eyes?", "href" => false, "text" => false));

	$page->block("spanner", array("image" => false, "title" => "current listing", "content" => "Please note that user uploads are currently not available.<br/>Ask the site admin if there are any music requests.", "href" => false, "text" => false, "article" => ""));

	echo "
	<section class='wrapper style1'>
	<div class='inner'>
	<article>
		<div class='row uniform 50%'>
	";

	$listing = array_diff(scandir("data/music"), array(".", ".."));

	foreach ($listing as $title) {
		$hash = "m_" . substr(sha1($title), 0, 8);
		$img = false;
		$scanner = scandir("data/music/".$title);
		
		foreach ($scanner as $file) {
			if (fextIsImage($file)) {
				$img = "data/music/" . $title . "/" . $file;
				break;
			}
		}
		
		echo "
			<div class='3u 12u$(small)' style='text-align: center;'>
				<img class='artwork' src='{$img}' alt='' />
			</div>
			<div class='9u$ 12u$(small)'>
				<h3>{$title}</h3>
				<a href='#' class='button small alt folderToggle' data-toggle='{$hash}'>Listen</a>
			</div>
			<div class='12u$' id='{$hash}' style='display: none;'>
				<table class='table alt'>
		";
		
		foreach ($scanner as $file) {
			if (fextIsMusic($file)) {
				$hash2 = "d_" . substr(sha1($file), 0, 8);
				$fileEnc = base64_encode($title . "/" . $file);
				$fileExtless = substr($file, 0, strripos($file, "."));
				echo "
					<tr><td><span class='mediaitem' data-container='{$hash2}' data-src='{$fileEnc}'>{$fileExtless}<div id='{$hash2}'></div></td></tr>
				";
			}
		}
		
		echo "
				</table>
			</div>
		";
	}

	echo "
		</div>
	</article>
	</div>
	</section>
	";

	$page->block("footer", array("js" => array("/assets/js/music.js")));
}
?>