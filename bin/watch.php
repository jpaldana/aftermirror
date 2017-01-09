<?php
requireLogin();

$page->show("header");

$page->block("spanner-largeoffset", array("image" => "images/bg-anime.jpg", "title" => "watch stuff?", "content" => "Watch a select collection of anime and movies, with friends (if that's what you prefer!)", "href" => false, "text" => false));

$json = json_decode(cfgc("https://owl.aftermirror.com/json.php"), true);
$col = array();
$colt = array();
include("module/cachedquery.php");

if (isset($_GET["sort"])) {
	switch ($_GET["sort"]) {
		case "new":
			$col2 = $col;
			$col = array();
			natsort($colt["__sort"]);
			foreach (array_reverse($colt["__sort"]) as $k => $v) {
				$col[$k] = $col2[$k];
			}
			$page->block("spanner", array("image" => false, "title" => "current listing", "content" => "Here is the current listing on after|mirror.", "href" => "/watch.do?sort=abc", "text" => "sort: newest", "article" => ""));
		break;
		case "abc":
		default:
			// do nothing, $col already in alphabetical order.
			$page->block("spanner", array("image" => false, "title" => "current listing", "content" => "Here is the current listing on after|mirror.", "href" => "/watch.do?sort=new", "text" => "sort: alphabetical", "article" => ""));
		break;
	}
}

foreach ($col as $title => $blob) {
	$background = false;
	$content = "";
	$subtitle = time_since(time() - $colt["__sort"][$title]) . " ago<br/>";
	$titleHash = substr(sha1($title), 0, 8);
	$bgPath = strtr("data/media/{$titleHash}.background.jpg", array(" " => "_"));
	if (file_exists($bgPath)) {
		$background = $bgPath;
	}
	
	$content .= "<table class='alt'>";
	foreach ($blob as $episode => $links) {
		$ts = time_since(time() - $colt["{$title}{$episode}"]);
		if ($episode > 0) {
			$content .= "<tr><td><b>Episode {$episode}</b> &mdash; {$ts} ago</td><td>";
		}
		else {
			$content .= "<tr><td style='padding: 100px 0;'><b>Movie</b> &mdash; {$ts} ago</td><td>";
		}
		foreach ($links as $type => $src) {
			$media_enc = base64_encode(serialize(array("src" => $src, "title" => $title, "episode" => $episode, "hash" => $titleHash)));
			$content .= "<a href='{$src}' alt-href='/kazoku.do?m={$media_enc}' class='button alt'>{$type}</a> ";
		}
		$content .= "</td></tr>";
	}
	$content .= "</table>";
	$content .= "<h6>ID: {$titleHash}</h6>";
	
	$page->block("spanner-toggle", array("image" => $background, "title" => $title, "subtitle" => $subtitle, "content" => $content, "text" => "view"));
}


$page->block("footer", array("js" => array("/assets/js/watch.js", "/assets/js/toggle.js")));
?>