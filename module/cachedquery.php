<?php
if (!file_exists("cache/picker.cache") || time() - filemtime("cache/picker.cache") > 3600) {
	$json = json_decode(cfgc(AM_WATCH_JSON), true);
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
		if (substr($title, 0, 6) == "Ghibli") {
			$prefix = substr($title, stripos($title, " - ") + 3);
		}
		elseif (strc($title, " - ")) {
			$prefix = substr($title, 0, strripos($title, " - "));
			$suffix = substr($title, strripos($title, " - ") + 3);
			if (strc($suffix, "(")) {
				// 01 (720p Blu-ray ....)
				$suffix = substr($suffix, 0, stripos($suffix, "("));
			}
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
		//var_dump($file);
		$col[$prefix][$suffix][$type] = AM_WATCH_MIRROR . $file["src"];
		
		$sfx = "{$prefix}{$suffix}";
		if (!isset($colt[$sfx]) || $colt[$sfx] < $file["time"]) $colt[$sfx] = $file["time"];
		if (!isset($colt["__sort"][$prefix]) || $colt["__sort"][$prefix] < $file["time"]) $colt["__sort"][$prefix] = $file["time"];
	}
	knatsort($col);
	file_put_contents("cache/picker.cache", serialize(array("col" => $col, "colt" => $colt)));
}
else {
	$cache = unserialize(file_get_contents("cache/picker.cache"));
	$col = $cache["col"];
	$colt = $cache["colt"];
}
?>