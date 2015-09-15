<?php

abstract class AbstractContact {

	public $sql;
	public $id_langues;
	public $type;
	public $types;
	public $table;
	public $id_field;

	public function __construct($sql, $id_langues = 1) {
		$this->sql = $sql;
		$this->id_langues = $id_langues;
	}

	public function save($data) {
		if (isset($data[$this->type])) {
			$orga = $data[$this->type];
			if (isset($orga['id'])) {
				$id = $orga['id'];
				if (!isset($data['save_again'])) {
					$sets = array();
					foreach ($orga as $field => $value) {
						$sets[] = "{$field} = '{$value}'";
					}
					$sets_list = implode(",", $sets);
					$q = <<<SQL
UPDATE {$this->table} SET $sets_list
WHERE id = {$id}
SQL;
					$this->sql->query($q);
				}

				$this->save_organisations_correspondants($data);
				
				return $id;
			}
			else {
				$fields = array();
				$values = array();
				foreach ($orga as $field => $value) {
					$fields[] = $field;
					$values[] = "'{$value}'";
				}
				$fields_list = implode(",", $fields);
				$values_list = implode(",", $values);
				$q = <<<SQL
INSERT INTO {$this->table} ($fields_list) VALUES ($values_list)
SQL;
				$this->sql->query($q);
				$data[$this->type]['id'] = $this->sql->insert_id();
				$data['save_again'] = true;

				return $this->save($data);
			}
		}

		return false;
	}

	public function save_organisations_correspondants($data) {
		$q = <<<SQL
DELETE FROM dt_contacts_organisations_correspondants WHERE {$this->id_field} = {$this->id}
SQL;
		$this->sql->query($q);
		if (isset($data['organisations_correspondants'])) {
			foreach ($data['organisations_correspondants'] as $id_organisation => $organisations_correspondants) {
				foreach ($organisations_correspondants as $id_correspondant => $organisation_correspondant) {
					$fields = array("id_contacts_organisations", "id_contacts_correspondants");
					$values = array($id_organisation, $id_correspondant);
					foreach ($organisation_correspondant as $field => $value) {
						$fields[] = $field;
						$values[] = "'$value'";
					}
					$fields_list = implode(",", $fields);
					$values_list = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_contacts_organisations_correspondants ($fields_list) VALUES ($values_list)
SQL;
					$this->sql->query($q);
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

	public function options($type, $field = "nom") {
		$q = <<<SQL
SELECT id, $field FROM dt_contacts_{$type} WHERE statut = 1 
SQL;
		$res = $this->sql->query($q);
		$options = array(0 => "--");
		while ($row = $this->sql->fetch($res)) {
			$options[$row['id']] = $row[$field];
		}

		return $options;
	}
	
	public function links($table, $key) {
		$q = <<<SQL
SELECT * FROM {$table} WHERE {$this->id_field} = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$links = array();
		while ($row = $this->sql->fetch($res)) {
			$links[$row[$key]] = $row;
		}

		return $links;
	}

	public function organisations() {
		$q = <<<SQL
SELECT co.id, co.nom FROM dt_contacts_organisations AS co
INNER JOIN dt_contacts_organisations_correspondants AS cot ON cot.id_contacts_organisations = co.id
WHERE cot.id_contacts_{$this->types} = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$links = array();
		while ($row = $this->sql->fetch($res)) {
			$links[$row['id']] = $row['nom'];
		}

		return $links;
	}

	public function correspondants() {
		$q = <<<SQL
SELECT cc.id, CONCAT(cc.nom, ' ', cc.prenom) AS nom FROM dt_contacts_correspondants AS cc
INNER JOIN dt_contacts_organisations_correspondants AS cot ON cot.id_contacts_correspondants = cc.id
WHERE cot.id_contacts_{$this->types} = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$links = array();
		while ($row = $this->sql->fetch($res)) {
			$links[$row['id']] = trim($row['nom']);
		}

		return $links;
	}
}
