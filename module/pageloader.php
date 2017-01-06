<?php
define("DEFAULT_PAGE", "home");
define("DEFAULT_UI", "default");

class Page {
	var $page;
	var $ui;
	
	function __construct($page = false, $ui = false) {
		if ($page) {
			$this->page = $page;
			if ($ui) {
				$this->ui = $ui;
			}
			else {
				$this->ui = DEFAULT_UI;
			}
		}
		elseif (isset($_GET["page"])) {
			$this->page = $_GET["page"];
			$this->ui = DEFAULT_UI;
		}
		elseif (isset($_GET["post"])) {
			$this->page = $_GET["post"];
			$this->ui = "blank";
		}
		else {
			$this->page = DEFAULT_PAGE;
			$this->ui = DEFAULT_UI;
		}
		
		if (!file_exists("bin/" . $this->page . ".php")) {
			$this->page = DEFAULT_PAGE;
		}
	}
	
	function setPage($page) {
		$this->page = $page;
	}
	function setUI($ui) {
		$this->ui = $ui;
	}
	function getPage() {
		return $this->page;
	}
	function getUI() {
		return $this->ui;
	}
	function getFile() {
		return "bin/" . $this->page . ".php";
	}
	
	function show($part) {
		$local = sprintf("snippet/%s.%s.php", $this->ui, $part);
		if (file_exists($local)) {
			include($local);
		}
		else {
			echo "no such file, {$local}.";
		}
	}
	function block($part, $data) {
		$local = sprintf("snippet/%s.%s.php", $this->ui, $part);
		if (file_exists($local)) {
			include($local);
		}
		else {
			echo "no such file, {$local}.";
		}
	}
}
?>