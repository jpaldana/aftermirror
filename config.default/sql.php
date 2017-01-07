<?php
die("config/sql.php is not set up. Please remove line 2 in config/sql.php. Be sure to generate a random salt in config/system.key as well.");
define("SQL_HOST", "");
define("SQL_USER", "");
define("SQL_PASS", "");
define("SQL_TABLE", "")

$mysqli = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_TABLE);
if ($mysqli->connect_error) {
    die('SQL Error: ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}
?>