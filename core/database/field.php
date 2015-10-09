<?php

class DBField {
	
	public $name;
	private $options;
	private $columns;
	private $table;
	private $phrases;
	
	private $db;
	
	public function __construct($db, $row, $table, $columns) {
		$this->db = $db;
		$this->table = $table;
		$this->prefixe = strstr($table, "_", true);
		$this->columns = $columns;
		$this->name = $row['Field'];
		$this->type = $row['Type'];
		if (preg_match("/^enum\('(.*)'\)$/", $row['Type'], $matches)) {
			$this->options = $this->options_enum($matches[1]);
		}
		else if (preg_match("/^id_(.*)$/", $this->name, $matches)) {
			$this->options = $this->options_id($matches[1]);
		}
	}
	
	public function name() {
		return $this->name;
	}
	
	public function input() {
		$html = "";
		if (isset($this->options)) {
			$html .= "<select name='field[{$this->name}]' class='db-field db-select'>";
			$html .= "<option value=''>-</option>";
			foreach ($this->options as $value => $option) {
				$selected = (isset($this->db->record[$this->name]) and ($this->db->record[$this->name] == $value)) ? "selected='selected'" : "";
				$html .= "<option value='{$value}' $selected>{$option}</option>";
			}
			$html .= "</select>";
		}
		else if (preg_match("/^phrase_/", $this->name)) {
			$html .= "<input type='hidden' name='phrase[{$this->name}]' value='{$this->get_value()}' />";
			$html .= "<ul class='db-field'>";
			foreach ($this->langues() as $id => $code) {
				$value = htmlspecialchars($this->db->get_phrase($this->get_value(), $code), ENT_QUOTES);
				$html .= "<li><input name='field[{$this->name}][{$id}]' class='db-field db-input' value='{$value}' />({$code})</li>";
			}
			$html .= "</ul>";
		}
		else if (preg_match("/^date/", $this->name)) {
			$date = "";
			$timestamp = $this->get_value();
			if ($timestamp) {
				if ($GLOBALS['config']->get("langue") == "fr_FR") {
					$date = date("d/m/Y", $timestamp);
				}
				else {
					$date = date("m/d/Y", $timestamp);
				}
			}
			$value = htmlspecialchars($this->get_value(), ENT_QUOTES);
			$html .= "<input id='field-{$this->name}-visible' name='' class='db-field db-input db-date' value='{$date}' />";
			$html .= "<input id='field-{$this->name}' name='field[{$this->name}]' type='hidden' class='db-field db-input' value='{$value}' />";
		}
		else if ($this->name == "id") {
			$html .= $this->get_value();
		}
		elseif ($this->type == "tinyint(1)") {
			$checked = $this->get_value() ? "checked='checked'" : "";	
			$html .= "<input type='checkbox' name='field[{$this->name}]' class='db-field db-input' value='1' $checked />";
			$html .= "<input type='hidden' name='field[__checkboxes][{$this->name}]' value='{$this->name}' />";
		}
		else {
			$value = htmlspecialchars($this->get_value(), ENT_QUOTES);
			$html .= "<input name='field[{$this->name}]' class='db-field db-input' value='{$value}' />";
		}
		
		return $html;
	}
	
	public function get_value() {
		return isset($this->db->record[$this->name]) ? $this->db->record[$this->name] : "";
	}
	
	private function langues() {
		static $langues = null;
		if ($langues !== null) {
			return $langues;
		}
		$q = "SELECT id, code_langue FROM dt_langues";
		$res = $this->db->sql->query($q);
		$langues = array();
		while ($row = $this->db->sql->fetch($res)) {
			$langues[$row['id']] = $row['code_langue'];
		}
		return $langues;
	}
	
	private function options_enum($enum) {
		$options = array();
		foreach (explode("','", $enum) as $option) {
			$options[$option] = $option;
		}
		return $options;
	}
	
	private function options_id($keyword) {
		if ($keyword == "parent") {
			$table_name = $this->table;
			$field_id = $this->columns[0];
			$field_label = $this->columns[1];
		}
		else {
			$table_name = $this->prefixe."_".$keyword;
			$table = $this->db->table($table_name);
			if (!$table) {
				return null;
			}
			$field_id = $table[0]->name;
			$field_label = $table[1]->name;
		}
		$q = "SELECT $field_id AS id, $field_label AS label FROM $table_name ORDER BY id";
		$res = $this->db->sql->query($q);
		$options = array();
		while ($row = $this->db->sql->fetch($res)) {
			if (preg_match("/^phrase_/", $field_label)) {
				
				$label = $this->db->get_phrase($row['label']);
			}
			else {
				$label = $row['label'];
			}
			$options[$row['id']] = "({$row['id']}) {$label}";
		}
		return $options;
	}
	
}

?>
