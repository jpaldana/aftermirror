<?php
class Debug {
	var $debug;
	
	function add($msg) {
		$this->debug[] = $msg;
	}
	
	function getLog() {
		return $this->debug;
	}
}

// mini- engine.php
function print_a($a) {
	echo "<pre>"; print_r($a); echo "</pre>";
}
function isSomething(&$obj) {
	if (!isset($obj)) return false;
	if ($obj === NULL) return false;
	if ($obj === "") return false;
	if (is_array($obj) && count($obj) === 0) return false;
	if (!$obj) return false;
	return true;
}
function fext($file) {
	$a = strtolower(substr($file, strripos($file, ".") + 1));
	if (strc($a, "?")) {
		$a = substr($a, 0, stripos($a, "?"));
	}
	if (strc($a, "#")) {
		$a = substr($a, 0, stripos($a, "#"));
	}
	return $a;
}
function fextIsVideo($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "mkv":
		case "avi":
		case "mp4":
		case "m4v":
		case "mov":
		case "3gp":
		case "flv":
		case "wmv":
		case "mpg":
		case "webm":
			return true;
		break;
	}
	return false;
}
function fextIsImage($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "jpg":
		case "jpeg":
		case "png":
		case "bmp":
		case "gif":
			return true;
		break;
	}
	return false;
}
function fextIsHTML($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "part":
		case "htm":
		case "html":
		case "php":
		case "css":
		case "js":
			return true;
		break;
	}
	return false;
}
function fextIsMusic($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "mp3":
		case "wav":
		case "m4a":
		case "ogg":
			return true;
		break;
	}
	return false;
}
function fextIsText($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	if (fextIsHTML($fext)) {
		return true;
	}
	switch($fext) {
		case "txt":
		case "log":
		case "c":
			return true;
		break;
	}
	return false;
}
function basenamex($str) {
	$DL = basename($str);
	if (strripos($DL, "?") !== false) {
		$DL = substr($DL, 0, strripos($DL, "?"));
	}
	if (strripos($DL, "#") !== false) {
		$DL = substr($DL, 0, strripos($DL, "#"));
	}
	return $DL;
}
function strc($haystack, $needle) {
	if (is_array($needle)) {
		foreach ($needle as $searchFor) {
			if (strc($haystack, $searchFor)) {
				return true;
			}
		}
		return false;
	}
	if (strpos($haystack, $needle) === false) { return false; }
	return true;
}
function randomStr() {
	return base_convert(mt_rand(0x19A100, 0x39AA3FF), 10, 36);
}
function formatBytes($size) {
	if ($size === 0) {
		return "0 Bytes";
	}
	$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]);
}
function knatsort(&$array){
	$array_keys = array_keys($array);
	natsort($array_keys);
	$new_natsorted_array = array();
	foreach($array_keys as $array_keys_2) {
		$new_natsorted_array[$array_keys_2] = $array[$array_keys_2];
	}
	$array = $new_natsorted_array;
	return true;
}
function time_since($since, $short = false) {
	if ($short) {
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'y'),
			array(60 * 60 * 24 * 30 , 'm'),
			array(60 * 60 * 24 * 7, 'w'),
			array(60 * 60 * 24 , 'd'),
			array(60 * 60 , 'h'),
			array(60 , 'm'),
			array(1 , 's')
		);
	}
	else {
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'year'),
			array(60 * 60 * 24 * 30 , 'month'),
			array(60 * 60 * 24 * 7, 'week'),
			array(60 * 60 * 24 , 'day'),
			array(60 * 60 , 'hour'),
			array(60 , 'minute'),
			array(1 , 'second')
		);
	}

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

	if ($short) {
		$print = ($count == 1) ? '1'.$name : "{$count}{$name}";
	}
	else {
		$print = ($count == 1) ? '1 '.$name : "{$count} {$name}s";
	}
    return $print;
}
function iTF($inputFileName, $fileName, $maxSize, $quality = 100) {
	$info = getimagesize($inputFileName);
	$type = isset($info['type']) ? $info['type'] : $info[2];
	$ext = strtolower(substr($fileName, strrpos($fileName, '.')));
	if (!(imagetypes() & $type)) {
		 return false;
	}
	$width = isset($info['width']) ? $info['width'] : $info[0];
	$height = isset($info['height']) ? $info['height'] : $info[1];
	$wRatio = $maxSize / $width;
	$hRatio = $maxSize / $height;
	$sourceImage = imagecreatefromstring(file_get_contents($inputFileName));
	if (($width <= $maxSize) && ($height <= $maxSize)) {
		$tHeight = $height;
		$tWidth = $width;
	}
	elseif (($wRatio * $height) < $maxSize) {
		$tHeight = ceil($wRatio * $height);
		$tWidth = $maxSize;
	}
	else {
		$tWidth = ceil($hRatio * $width);
		$tHeight = $maxSize;
	}
	$thumb = imagecreatetruecolor($tWidth, $tHeight);
	if ($sourceImage === false) {
		 return false;
	}
	imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
	imagedestroy($sourceImage);
	$im = $thumb;
	if (!$im || file_exists($fileName)) {
		 return false;
	}
	switch ($ext) {
		 case '.gif':
			imagegif($im, $fileName);
		break;
		case '.jpg':
		case '.jpeg':
			imagejpeg($im, $fileName, $quality);
		break;
		case '.png':
			imagepng($im, $fileName);
		break;
		case '.bmp':
			imagewbmp($im, $fileName);
		break;
		default:
			imagepng($im, $fileName);
	}
	return true;
}

?>