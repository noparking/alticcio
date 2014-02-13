<?php

require dirname(__FILE__)."/field.php";

class Database {

	public $sql;
	public $record = array();
	
	public function __construct($sql = null) {
		$this->sql = $sql;
	}
	
	public function tables($options = array()) {
		static $tables = null;
		
		$exluded = isset($options['exclude']) ? $options['exclude'] : array();
		
		if ($tables !== null) {
			return array_diff($tables, $exluded);
		}
		$q = "SHOW TABLES";
		$res = $this->sql->query($q);
		$tables = array();
		while ($row = $this->sql->fetch($res)) {
			$tables[] = array_pop($row);
		}
		
		return array_diff($tables, $exluded);
	}
	
	public function table($table) {
		static $tables = array();
		if (isset($tables[$table])) {
			return $tables[$table];
		}
		if (!in_array($table, $this->tables())) {
			return null;
		}
		$q = "SHOW COLUMNS FROM $table";
		$res = $this->sql->query($q);
		$columns = array();
		$rows = array();
		while ($row = $this->sql->fetch($res)) {
			$rows[] = $row;
			$columns[] = $row['Field'];
		}
		$fields = array();
		foreach ($rows as $row) {
			$field = new DBField($this, $row, $table, $columns);
			$fields[] = $field;
		}
		$tables[$table] = $fields;
		
		return $fields;
	}
	
	public function records($table) {
		$records = array();
		$q = "SELECT * FROM $table";
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$table_jointure = false;
			$label = array();
			foreach ($row as $field => $value) {
				if ($field == 'id') {
					$id = $value;
				}
				else if (preg_match("/^id_(.*)/", $field, $matches)) {
					$table_jointure = true;
					$table2 = "dt_".$matches[1];
					$q = "SELECT * FROM $table2 WHERE id = $value";
					$res2 = $this->sql->query($q);
					$row2 = $this->sql->fetch($res2);
					$l = "";
					if (is_array($row2)) {
						foreach ($row2 as $field2 => $value2) {
							if ($field2 == 'id') {
								$l .= "[".$value2."] ";
							}
							else if (preg_match("/^phrase_/", $field2)) {
								$l .= $this->get_phrase($value2);
								break;
							}
							else {
								$l .= $value2;
								break;
							}
						}
					}
					$label[] = $l;
				}
				else if ($table_jointure) {
					break;
				}
				else if (preg_match("/^phrase_/", $field)) {
					$label[] = $this->get_phrase($value);
					break;
				}
				else {
					$label[] = $value;
					break;
				}
			}
			$records[$id] = implode(" - ", $label);
		}
		return $records;
	}
	
	public function get_phrase($id_phrase, $langue = null) {
		if ($id_phrase == "") {
			return "";
		}
		$langue = $langue ? $langue : $GLOBALS['config']->get("langue");
		$q = "SELECT phrase FROM dt_phrases AS p INNER JOIN dt_langues AS l ON p.id_langues = l.id";
		$q .= " WHERE l.code_langue = '$langue' AND p.id = $id_phrase";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['phrase'];
	}
	
	public function load($table, $id) {
		$q = "SELECT * FROM $table WHERE id = $id";
		$res = $this->sql->query($q);
		$this->record = $this->sql->fetch($res);
	}
	
	public function save($table, $fields, $phrases = array()) {
		if (isset($fields['__checkboxes'])) {
			foreach ($fields['__checkboxes'] as $checkbox) {
				if (!isset($fields[$checkbox])) {
					$fields[$checkbox] = 0;
				}
			}
			unset($fields['__checkboxes']);
		}
		if (isset($fields['id'])) {
			$this->update($table, $fields, $phrases);
		}
		else {
			$this->insert($table, $fields);
		}
	}
	
	public function insert($table, $record) {
		$values = array();
		$fields = array();
		foreach ($record as $field => $value) {
			if (preg_match("/^phrase_/", $field)) {
				$id_phrases = $this->max_phrases_id() + 1;
				$timestamp = time();
				$phrases_values = array();
				foreach ($value as $langue => $phrase) {
					$phrase = addslashes($phrase);
					$phrases_values[] = "($id_phrases, '$phrase', $langue, $timestamp, $timestamp)";
				}
				$q = "INSERT INTO dt_phrases (id, phrase, id_langues, date_creation, date_update) VALUES ".implode(",", $phrases_values);
				$this->sql->query($q);
				$value = $id_phrases;
			}
			$fields[] = $field;
			$values[] = $this->sql->quote_string($table, $field, $value);
		}
		$q = "INSERT INTO $table (".implode(",", $fields).") VALUES (".implode(",", $values).")";
		$this->sql->query($q);
	}
	
	public function update($table, $record, $phrases) {
		$values = array();
		foreach ($record as $field => $value) {
			if ($field == 'id') {
				$id = $value;
			}
			else if (preg_match("/^phrase_/", $field)) {
				$id_phrases = $phrases[$field];
				$new_id_phrases = false;
				if ($id_phrases == 0) {
					$id_phrases = $this->max_phrases_id() + 1;
					$new_id_phrases = true;
				}
				foreach ($value as $langue => $phrase) {
					$phrase = addslashes($phrase);
					$timestamp = time();

					$q = "DELETE FROM dt_phrases WHERE id = $id_phrases AND id_langues = $langue";
					$this->sql->query($q);

					$q = "INSERT INTO dt_phrases (id, phrase, id_langues, date_creation, date_update) VALUES ($id_phrases, '$phrase', $langue, $timestamp, $timestamp)";
					$this->sql->query($q);
				}
				$values[] = $field."=$id_phrases";
			}
			else {
				$values[] = $field."='$value'";
			}
		}
		if (count($values)) {
			$q = "UPDATE $table SET ".implode(",", $values)." WHERE id = ".$id;
			$this->sql->query($q);
		 }
	}
	
	public function delete($table, $id, $phrases) {
		foreach ($phrases as $id_phrase) {
			$q = "DELETE FROM dt_phrases WHERE id=$id_phrase";
			$this->sql->query($q);
		}
		$q = "DELETE FROM $table WHERE id=$id";
		$this->sql->query($q);
	}
	
	public function max_phrases_id() {
		$q = "SELECT MAX(id) AS max FROM dt_phrases";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row['max'];
	}
}

?>
