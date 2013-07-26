<?php

abstract class AbstractExport {

	public $sql;
	public $sql_export;
	public $excluded = array();

	public function __construct($sql, $sql_export) {
		$this->sql = $sql;
		$this->sql_export = $sql_export;
	}

	function insert_values($fields, $values) {
		$fields_list = array();
		foreach ($fields as $i => $field) {
			if (!in_array($i, $this->excluded)) {
				$fields_list[] = $field;
			}
		}
		$fields_list = implode(",", $fields_list);

		$values_list = array();
		foreach ($values as $value) {
			$value_list = array();
			foreach ($value as $i => $data) {
				if (!in_array($i, $this->excluded)) {
					$value_list[] = addslashes($data);
				}
			}
			$values_list[] = "('".implode("','", $value_list)."')";
		}
		$values_list = implode(",", $values_list);
		$q = <<<SQL
INSERT INTO {$this->export_table} ($fields_list) VALUES $values_list
SQL;
		$this->sql_export->query($q);
	}
}
