<?php

class Mysql extends Mysqli {
	
	private $types;
	
	public function __construct($params) {
		$server = isset($params['server']) ? $params['server'] : "localhost";
		$user = isset($params['user']) ? $params['user'] : "root";
		$password = isset($params['password']) ? $params['password'] : "";
		
		parent::__construct($server, $user, $password, $params['database']);
		
		$this->set_charset("utf8");
	}

	public function query($q) {
		if (!$result = parent::query($q)) {
			throw new Exception($this->error." Query: $q");
		}
		
		return $result;
	}
	
	public function fetch($result) {
		return $result->fetch_assoc();
	}

	public function fetch_row($result) {
		return $result->fetch_row();
	}
	
	public function insert_id() {
		return $this->insert_id;
	}

	public function num_rows($result) {
		return $result->num_rows();
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

    function quote($value) {
        $type = gettype($value);
        switch ($type) {
            case 'boolean':
                $value = (int) $value;
                break;
            case 'NULL':
                $value = 'NULL';
                break;
            case 'string':
                $value = "'".$this->real_escape_string($value)."'";
                break;
        }
        return $value;
    }

	function quote_string($table, $field, $value, $escape = false) {
		if (!isset($this->types[$table])) {
			$q = "SHOW COLUMNS FROM $table";
			$res = $this->query($q);
			while ($row = $this->fetch($res)) {
				$this->types[$table][$row['Field']] = strtoupper($row['Type']);
			}
		}

		if (isset($this->types[$table][$field])) {
			foreach (array('CHAR', 'VARCHAR', 'BINARY', 'VARBINARY', 'BLOB', 'TEXT', 'ENUM', 'SET') as $type) {
				if (strpos($this->types[$table][$field], $type) === 0) {
					return "'".($escape ? $this->real_escape_string($value) : $value)."'";
				}
			}
		}

		return $value ? $value : 0;
	}

	function file($file) {
		$queries = file_get_contents($file);
		$queries = preg_replace("/^--(.*)$/m", "", $queries);
		$queries = preg_replace("/^\/\*(.*)\*\/;$/m", "", $queries);
		$queries = str_replace(";\n", "MYSQL_QUERY_END", $queries);
		$queries = str_replace("\n", " ", $queries);
		foreach(explode("MYSQL_QUERY_END", $queries) as $q) {
			if ($q = trim($q)) {
				$this->query($q);
			}
		}
	}

	function update($table, $key_field, $conditions, $data) {
		$where = array();
		foreach ($conditions as $field => $value) {
			$where[] = "$field = ".$this->quote($value);
		}
		$where = implode(" AND ", $where);
		$before = array();
		$q = <<<SQL
SELECT `$key_field` FROM `$table` WHERE 1 AND $where
SQL;
		$res = $this->query($q);
		while ($row = $this->fetch($res)) {
			$before[] = $row[$key_field];
		}
		$deleted = array_diff($before, array_keys($data));
		if (count($deleted)) {
			$list_deleted = implode(",", $deleted);
			$q = <<<SQL
DELETE FROM `$table` WHERE 1 AND $where AND `$key_field` IN ($list_deleted)
SQL;
			$this->query($q);
		}
		$news = array();
		foreach ($data as $key => $fields) {
			if (in_array($key, $before)) {
				$set = array();
				foreach ($fields as $field => $value) {
					$set[] = "`$field` = ".$this->quote_string($table, $field, $value, true);
				}
				$set = implode(",", $set);
				$q = <<<SQL
UPDATE `$table` SET $set WHERE 1 AND $where AND $key_field = $key
SQL;
				$this->query($q);
			}
			else {
				$values = array();
				foreach ($fields as $field => $value) {
					$values[] = $this->quote_string($table, $field, $value, true);
				}
				$news[] = "({$key},".implode(",", array_merge($conditions, $values)).")";
			}
		}
		if (count($news)) {
			$values = implode(",", $news);
			$fields = implode("`,`", array_merge(array_keys($conditions), array_keys(array_pop($data))));
			$q = <<<SQL
INSERT INTO `$table` (`$key_field`,`$fields`) VALUES $values
SQL;
			$this->query($q);
		}
    }

	function duplicate($tables, $column, $old_id, $new_id) {
		foreach ($tables as $table) {
			// Nettoyage éventuel
			$q = <<<SQL
DELETE FROM `$table` WHERE `$column` = $new_id
SQL;
			$res = $this->query($q);

			$q = <<<SQL
SELECT * FROM `$table` WHERE `$column` = $old_id
SQL;
			$res = $this->query($q);
			$fields = array();
			$values = array();
			$first = true;
			while ($row = $this->fetch($res)) {
				unset($row['id']);
				$row[$column] = $new_id;
				$vals = array();
				foreach ($row as $field => $value) {
					if ($first) {
						$fields[] = $field;
					}
					$vals[] = $this->quote_string($table, $field, $value, true);
				}
				$first = false;
				$values[] = "(".implode(",", $vals).")";
			}
			if (count($values)) {
				$fields_list = implode(",", $fields);
				$values_list = implode(",", $values);
				$q = <<<SQL
INSERT INTO `$table` ($fields_list) VALUES $values_list
SQL;
				$this->query($q);
			}
		}
	}
}

?>
