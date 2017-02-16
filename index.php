<?php
include("config/conf.php");
include("config/sql.php");
include("module/core.php");
include("module/login.php");
include("module/pageloader.php");

$page = new Page();

include($page->getFile());
?>