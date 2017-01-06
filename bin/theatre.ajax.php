<?php
enforceLogin();

//+$urusai = new Urusai($mysqli);

if ($_GET["do"] === "listing") {
	
	$json = json_decode(file_get_contents("https://owl.aftermirror.com/json.php"), true);
	$col = array();
	foreach ($json as $file) {
		$bn = trim(substr(preg_replace('#\s*\[.+\]\s*#U', ' ', strtr($file["file"], array("_Ep" => " - ", "_" => " "))), 0, -4), " .");
		if (substr($bn, -3) == "480") {
			$col[trim(substr($bn, 0, -3))]["SD"] = "https://owl.aftermirror.com/" . $file["src"];
		}
		else {
			$col[$bn]["HD"] = "https://owl.aftermirror.com/" . $file["src"];
		}
	}
	knatsort($col);
	
	foreach ($col as $bn => $blob) {
		echo "
		<a href='javascript:;' onclick=\"kazokuLoadMedia('{$bn}', 0, { ";
		$qualities = "";
		
		
		if (isset($blob["HD"])) {
			$qualities .= "'HD': '{$blob['HD']}',";
		}
		if (isset($blob["SD"])) {
			$qualities .= "'SD': '{$blob['SD']}',";
		}
		
		$qualities = substr($qualities, 0, -1);
		echo "{$qualities} });\" style='color: black !important;'>{$bn}</a>
		";
	}
}

/*
if ($_GET["do"] === "listing") {
	$listing = $urusai->getListing();
	natsort($listing);

	echo "<ul class='nav nav-stacked'>";
	foreach ($listing as $id => $title) {
		$data = $urusai->getAnimeAttributes($id);
		if ($data["status"] === -1 || $data["status"] === 0) continue;
		if ($data["airing"] === 2) continue;
		echo "<li><a href='javascript:;' onclick=\"$.featherlight.current().close(); $.featherlight('/do/KazokuAjax?do=animeEpisodeListing&id={$id}');\">{$title}</a></li>";
	}
	echo "</ul>";
}
elseif ($_GET["do"] === "animeEpisodeListing") {
	$data = $urusai->getAnimeAttributes($_GET["id"]);
	$titleSafe = htmlentities($data["title"]);
	echo "
		<h4 style='color: black;'>{$data['title']}</h4>
		<h6 style='color: black;'>{$data['alt_title']}</h6>
		<br />
	";
	$episodes = $urusai->getEpisodes($_GET["id"]);
	echo "<ul class='nav nav-stacked'>";
	$episodes_sort = array();
	foreach ($episodes as $blob) {
		$mirrors = $urusai->getEpisodeMirrors($blob["id"]);
		foreach ($mirrors as $mirror) {
			$episodes_sort[(string) $blob["absolute_episode"]][] = "{$mirror['quality']}|{$mirror['source']}";
		}
	}
	knatsort($episodes_sort);
	foreach ($episodes_sort as $episode => $edata) {
		echo "
			<li>
				<a href='javascript:;' onclick=\"kazokuLoadMedia('{$titleSafe}', {$episode}, { ";
		$qualities = "";
		foreach ($edata as $src) {
			$src = explode("|", $src);
			
			if (substr($src[1], 0, 5) == "data/") {
				$src[1] = "http://yui.urusai.ninja/" . substr($src[1], 12); // assume it's on mirror. (deprecated)
			}
			
			if (!strc($qualities, $src[0])) {
				// @ add mirror switch here eventually
				echo "{$src[0]}: '{$src[1]}',";
				$qualities .= "{$src[0]}, ";
			}
		}
		$qualities = substr($qualities, 0, -2);
		echo " });\">Episode {$episode} <small style='font-size: 0.6em;'>{$qualities}</small></a>
			</li>
		";
	}
	echo "</ul>";
}
*/
?>