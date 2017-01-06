<?php
class GitStatus {
	var $gitHeadRef = ".git/refs/heads/master";
	var $versionDataFile = "config/version.dat";
	
	function getGitCommitHead() {
		return substr(file_get_contents($this->gitHeadRef), 0, 8);
	}
	function getVersion() {
		return file_get_contents($this->versionDataFile);
	}
	function lastModified() {
		return time_since(time() - filemtime($this->gitHeadRef));
	}
}
?>