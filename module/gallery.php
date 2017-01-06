<?php
class Gallery {
	private $db;
	
	function __construct($mysqli) {
		$this->db = $mysqli;
	}
	
	function getCollections() {
		$res = $this->db->query("SELECT * FROM gallery_collection;");
		
		$return = array();
		if ($res) while ($row = $res->fetch_array()) {
			$return[$row["id"]] = $row["title"];
		}
		return $return;
	}
	function createCollection($title, $description, $creator) {
		$stmt = $this->db->prepare("INSERT INTO gallery_collection (title, description, creator, password, public, visible) VALUES (?, ?, ?, ?, ?, ?);") or die($this->db->error);
		$one = 1;
		$blank = "";
		$stmt->bind_param('ssisii', $title, $description, $creator, $blank, $one, $one);
		
		$stmt->execute() or die($this->db->error);
	}
	function setCollectionAttribute($id, $attribute, $value, $type) {
		$stmt = $this->db->prepare("UPDATE gallery_collection SET " . $attribute . " = ? WHERE id = ?;") or die($this->db->error);
		switch ($type) {
			case SQLITE3_INTEGER:
				$stmt->bind_param('ii', $value, $id);
			break;
			case SQLITE3_TEXT:
				$stmt->bind_param('si', $value, $id);
			break;
		}
		$res = $stmt->execute() or die($this->db->error);
	}
	function getCollectionAttributes($id) {
		$stmt = $this->db->prepare("SELECT * FROM gallery_collection WHERE id = ?;") or die($this->db->error);
		$stmt->bind_param('i', $id);
		$stmt->execute() or die($this->db->error);
		
		$res = $stmt->get_result();
		if ($res->num_rows == 1) {
			// exists
			return $res->fetch_array();
		}
		return false;
	}
	
	function getCollectionMedia($collection_id) {
		$stmt = $this->db->prepare("SELECT * FROM gallery_media WHERE collection_id = ?;") or die($this->db->error);
		$stmt->bind_param('i', $collection_id);
		$stmt->execute() or die($this->db->error);
		
		$return = array();
		
		$res = $stmt->get_result();
		if ($res->num_rows >= 1) while($row = $res->fetch_array()) {
			$return[$row["id"]] = $row;
		}
		return $return;
	}
	function getMediaAttributes($id) {
		$stmt = $this->db->prepare("SELECT * FROM gallery_media WHERE id = ?;") or die($this->db->error);
		$stmt->bind_param('i', $id);
		$stmt->execute() or die($this->db->error);
		
		$res = $stmt->get_result();
		$row = $res->fetch_array();
		if ($row !== false) {
			// exists
			return $row;
		}
		return false;
	}
	function addMedia($collection_id, $file, $title, $tags, $creator) {
		$stmt = $this->db->prepare("INSERT INTO gallery_media (collection_id, title, source, tags, uploader, time) VALUES (?, ?, ?, ?, ?, ?);") or die($this->db->error);
		$time = time();
		$stmt->bind_param('issssi', $collection_id, $title, $file, $tags, $creator, $time);
		
		$stmt->execute() or die($this->db->error);
	}
	function setMediaAttribute($id, $attribute, $value, $type) {
		$stmt = $this->db->prepare("UPDATE gallery_media SET " . $attribute . " = ? WHERE id = ?;") or die($this->db->error);
		switch ($type) {
			case SQLITE3_INTEGER:
				$stmt->bind_param('ii', $value, $id);
			break;
			case SQLITE3_TEXT:
				$stmt->bind_param('si', $value, $id);
			break;
		}
		
		$res = $stmt->execute() or die($this->db->error);
	}
	function delMedia($id) {
		$stmt = $this->db->prepare("DELETE FROM gallery_media WHERE id = ?;") or die($this->db->error);
		$stmt->bind_param('i', $id);
		
		$stmt->execute() or die($this->db->error);
	}
}
?>