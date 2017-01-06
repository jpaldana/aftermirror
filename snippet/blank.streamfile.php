<?php
// https://gist.github.com/codler/3906826

if (!isset($_SERVER['PATH_INFO'])) {
	$_SERVER['PATH_INFO'] = substr($_SERVER["ORIG_SCRIPT_FILENAME"], strlen($_SERVER["SCRIPT_FILENAME"]));
}
$request = substr($_SERVER['PATH_INFO'], 1);
$file = $data["file"];
$fp = @fopen($file, 'rb');
$size   = filesize($file); // File size
$length = $size;           // Content length
$start  = 0;               // Start byte
$end    = $size - 1;       // End byte
header("Content-Type: " . $data["type"]);
header("Accept-Ranges: 0-{$length}");
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end   = $end;
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes {$start}-{$end}/{$size}");
        exit;
    }
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    }else{
        $range  = explode('-', $range);
        $c_start = $range[0];
        $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes {$start}-{$end}/{$size}");
        exit;
    }
    $start  = $c_start;
    $end    = $c_end;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}
header("Content-Range: bytes {$start}-{$end}/{$size}");
header("Content-Length: ".$length);
$buffer = 1024 * 8;
while(!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $buffer);
    flush();
}
fclose($fp);
exit();
?>