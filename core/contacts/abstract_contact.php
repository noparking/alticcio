<?php

abstract class AbstractContact {

	public $sql;
	public $id_langues;
	public $type;
	public $types;
	public $table;
	public $id_field;
	public $table_links_organisations;
	public $table_links_correspondants;

	public function __construct($sql, $id_langues = 1) {
		$this->sql = $sql;
		$this->id_langues = $id_langues;
	}

	public function save($data) {
		if (isset($data[$this->type])) {
			$orga = $data[$this->type];
			if (isset($data[$this->type]['id']) and ($id = $data[$this->type]['id'])) {
				$id = $data[$this->type]['id'];
				if (!isset($data['save_again'])) {
					$sets = array();
					foreach ($data[$this->type] as $field => $value) {
						$sets[] = "{$field} = '{$value}'";
					}
					$sets_list = implode(",", $sets);
					$q = <<<SQL
UPDATE {$this->table} SET $sets_list
WHERE id = {$id}
SQL;
					$this->sql->query($q);
				}

				$this->save_links($data);
				
				return $id;
			}
			else {
				$fields = array();
				$values = array();
				unset($orga['id']);
				foreach ($orga as $field => $value) {
					$fields[] = $field;
					$values[] = strpos($field, "id") === 0 ? $value : "'{$value}'";
				}
				$fields_list = implode(",", $fields);
				$values_list = implode(",", $values);
				$q = <<<SQL
INSERT INTO {$this->table} ($fields_list) VALUES ($values_list)
SQL;
				$this->sql->query($q);
				$data[$this->type]['id'] = $this->id = $this->sql->insert_id();
				$data['save_again'] = true;

				return $this->save($data);
			}
		}

		return false;
	}

	public function save_links($data) {
		foreach ($this->links as $link => $elements) {
			$joined_elements = implode("_", $elements);
			if (isset($this->id) and $this->id) {
				if (!isset($data['keep'][$link])) {
					$q = <<<SQL
DELETE FROM dt_contacts_{$joined_elements} WHERE {$this->id_field} = {$this->id}
SQL;
					$this->sql->query($q);
				}
				if (isset($data[$link])) {
					foreach ($data[$link] as $link_id => $link_data) {
						$fields = array("id_contacts_".$elements[0], "id_contacts_".$elements[1]);
						if ($link_id) {
							$values = null;
							if ($link == $elements[0]) {
								$values = array($link_id, $this->id);
							} else if ($link == $elements[1]) {
								$values = array($this->id, $link_id);
							}
							if ($values) {
								foreach ($link_data as $k => $v) {
									$fields[] = $k;
									$values[] = "'$v'";
								}
								$fields_list = implode(",", $fields);
								$values_list = implode(",", $values);
								$q = <<<SQL
INSERT INTO dt_contacts_{$joined_elements} ($fields_list) VALUES ($values_list)
SQL;
								$this->sql->query($q);
							}
						}
					}
				}
			}
		}
	}

	public function load($id) {
		$q = <<<SQL
SELECT * FROM {$this->table} WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		$this->values = array();
		if ($row = $this->sql->fetch($res)) {
			$this->values = $row;
			$this->id = $id;
			return true;
		}
		return false;
	}

	public function delete($data) {
		if (isset($data[$this->type]['id'])) {
			$q = <<<SQL
DELETE FROM {$this->table} WHERE id = {$data[$this->type]['id']}
SQL;
			$this->sql->query($q);
		}
	}

	public function options($type, $field = "nom", $selection = null) {
		$q = <<<SQL
SELECT id, $field FROM dt_contacts_{$type} WHERE statut = 1 
SQL;
		$res = $this->sql->query($q);
		$options = array(0 => "--");
		while ($row = $this->sql->fetch($res)) {
			if (!$selection or isset($selection[$row['id']])) {
				$options[$row['id']] = trim($row[$field]);
			}
		}

		return $options;
	}
	
	public function links($table, $key) {
		$links = array();
		if (isset($this->id) and $this->id) {
			$q = <<<SQL
SELECT * FROM {$table} WHERE {$this->id_field} = {$this->id}
SQL;
			$res = $this->sql->query($q);
			while ($row = $this->sql->fetch($res)) {
				$links[$row[$key]] = $row;
			}
		}

		return $links;
	}

	public function organisations() {
		$links = array();
		if (isset($this->id) and $this->id) {
			$q = <<<SQL
SELECT co.id, co.nom FROM dt_contacts_organisations AS co
INNER JOIN {$this->table_links_organisations} AS tl ON tl.id_contacts_organisations = co.id
WHERE tl.id_contacts_{$this->types} = {$this->id}
SQL;
			$res = $this->sql->query($q);
			while ($row = $this->sql->fetch($res)) {
				$links[$row['id']] = $row['nom'];
			}
		}

		return $links;
	}

	public function correspondants() {
		$links = array();
		if (isset($this->id) and $this->id) {
			$q = <<<SQL
SELECT cc.id, CONCAT(cc.nom, ' ', cc.prenom) AS nom FROM dt_contacts_correspondants AS cc
INNER JOIN {$this->table_links_correspondants} AS tl ON tl.id_contacts_correspondants = cc.id
WHERE tl.id_contacts_{$this->types} = {$this->id}
SQL;
			$res = $this->sql->query($q);
			while ($row = $this->sql->fetch($res)) {
				$links[$row['id']] = trim($row['nom']);
			}
		}

		return $links;
	}
}
