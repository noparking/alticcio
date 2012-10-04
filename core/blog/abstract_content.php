<?php

abstract class AbstractContent {
	
	protected $sql;
	protected $table;
	protected $type;

	public function __construct($sql) {
		$this->sql = $sql;
	}

	public function load($id) {
		$q = "SELECT * FROM {$this->table} WHERE id = $id";
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			foreach ($row as $key => $value) {
				$this->$key = $value;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function attr($value) {
		return isset($this->$value) ? $this->$value : null;
	}
	
	public function save($data) {
		if (isset($data[$this->type]['id'])) {
			$id = $data[$this->type]['id'];

			$values = array();
			foreach ($data[$this->type] as $field => $value) {
				if ($field != 'id') {
					$values[] = "$field = '$value'";
				}
			}
			if (count($values)) {
				$q = "UPDATE {$this->table} SET ".implode(",", $values);
				$q .= " WHERE id=".$id;
				$this->sql->query($q);
			}
		}
		else {
			$fields = array();
			$values = array();
			foreach ($data[$this->type] as $field => $value) {
				$fields[] = $field;
				$values[] = "'$value'";
			}
			if (count($fields)) {
				$q = "INSERT INTO {$this->table} (".implode(",", $fields).") VALUES (".implode(",", $values).")";
				$this->sql->query($q);
			}
			$id = $this->sql->insert_id();
		}

		return $id;
	}

	public function delete($data) {
		$q = "DELETE FROM {$this->table} WHERE id = {$data['commentaire']['id']}";
		$this->sql->query($q);
	}
}
