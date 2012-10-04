<?php

class Mysql {
	
	private $db;
	
	public function __construct($params) {
		$server = isset($params['server']) ? $params['server'] : "localhost";
		$user = isset($params['user']) ? $params['user'] : "root";
		$password = isset($params['password']) ? $params['password'] : "";
		if (!($this->db = mysql_connect($server, $user, $password))) {
			throw new Exception(mysql_error($this->db));
		}
		mysql_set_charset("utf8", $this->db);
		if (!mysql_select_db ($params['database'], $this->db)) {
			throw new Exception(mysql_error($this->db));
		}
	}
	
	public function query($q, $args = array()) {
		if (!$result = mysql_query($q, $this->db)) {
			throw new Exception(mysql_error($this->db)." Query: $q");
		}
		
		return $result;
	}
	
	public function fetch($result) {
		if (!$result) {
			throw new Exception(mysql_error($this->db));
		}
		return mysql_fetch_assoc($result);
	}
	
	public function insert_id() {
		return mysql_insert_id($this->db);
	}
	
	public function found_rows() {
		$res = $this->query("SELECT FOUND_ROWS() AS total");
		$row = $this->fetch($res);
		
		return $row['total'];
	}

	public function limit($limit, $offset) {
		$q_limit = "";
		if ($limit) {
			if ($offset) {
				$q_limit = "LIMIT $offset, $limit";
			}
			else {
				$q_limit = "LIMIT $limit";
			}
		}
		return $q_limit;
	}
}

?>
