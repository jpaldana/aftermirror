<?php
requireLogin();

$page->show("header");

$page->block("spanner", array("image" => "images/bg-anime.jpg", "title" => "watch stuff?", "content" => "Watch a select collection of anime and movies, with friends (if that's what you prefer!)", "href" => false, "text" => false));

$page->block("spanner", array("image" => false, "title" => "current listing", "content" => "Here is the current listing on after|mirror.", "href" => false, "text" => false, "article" => ""));

$json = json_decode(file_get_contents("https://owl.aftermirror.com/json.php"), true);
$col = array();
$colt = array();
foreach ($json as $file) {
	$bn = trim(substr(preg_replace('#\s*\[.+\]\s*#U', ' ', strtr($file["file"], array("_Ep" => " - ", "_" => " "))), 0, -4), " .");
	$title = $bn;
	$type = "HD";
	if (substr($bn, -3) == "480") {
		$title = trim(substr($bn, 0, -3));
		$type = "SD";
	}
	
	$prefix = $title;
	$suffix = -1;
	if (strc($title, " - ")) {
		$prefix = substr($title, 0, strripos($title, " - "));
		$suffix = substr($title, strripos($title, " - ") + 3);
	}
	elseif (is_numeric(substr($title, strripos($title, " ")))) {
		// Title of anime 01
		$prefix = substr($title, 0, strripos($title, " "));
		$suffix = substr($title, strripos($title, " ") + 1);
	}
	elseif (strc(substr($title, strripos($title, " ")), "v") && is_numeric(strtr(substr($title, strripos($title, " ")), array("v" => "")))) {
		// Title of anime 01v1
		$prefix = substr($title, 0, strripos($title, " "));
		$suffix = substr($title, strripos($title, " ") + 1);
	}
	$col[$prefix][$suffix][$type] = "https://owl.aftermirror.com/" . $file["src"];
	
	if (!isset($colt[$bn]) || $colt[$bn] < $file["time"]) $colt[$bn] = $file["time"];
}
knatsort($col);

foreach ($col as $title => $blob) {
	$background = false;
	$content = "";
	$titleHash = substr(sha1($title), 0, 8);
	$bgPath = strtr("data/media/{$titleHash}.background.jpg", array(" " => "_"));
	if (file_exists($bgPath)) {
		$background = $bgPath;
	}
	
	$content .= "<table class='alt'>";
	foreach ($blob as $episode => $links) {
		if ($episode > 0) {
			$content .= "<tr><td><b>Episode {$episode}</b></td><td>";
		}
		else {
			$content .= "<tr><td><b>Movie</b></td><td>";
		}
		foreach ($links as $type => $src) {
			$media_enc = base64_encode(serialize(array("src" => $src, "title" => $title, "episode" => $episode, "hash" => $titleHash)));
			$content .= "<a href='/theatre.do?m={$media_enc}' class='button alt'>{$type}</a> ";
		}
		$content .= "</td></tr>";
	}
	$content .= "</table>";
	$content .= "<h6>ID: {$titleHash}</h6>";
	
	$page->block("spanner", array("image" => $background, "title" => $title, "content" => $content, "href" => false, "text" => false));
}


$page->show("footer");
?>