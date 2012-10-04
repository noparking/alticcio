<?php

class Langue {
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function liste() {
		static $langues = null;
		if ($langues !== null) {
			return $langues;
		}
		$q = "SELECT id, code_langue FROM dt_langues";
		$res = $this->sql->query($q);
		$langues = array();
		while ($row = $this->sql->fetch($res)) {
			$langues[$row['id']] = $row['code_langue'];
		}
		return $langues;
	}
	
	public function nom($id) {
		$q = "SELECT code_langue FROM dt_langues WHERE id=".(int)$id;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row['code_langue'];
	}

	public function id($code) {
		$q = "SELECT id FROM dt_langues WHERE code_langue='$code'";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row['id'];
	}
	
	public function format($id) {
		$q = "SELECT format FROM dt_langues WHERE id=".(int)$id;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row['format'];
	}
	
	public function flag($id) {
		
	}
}
