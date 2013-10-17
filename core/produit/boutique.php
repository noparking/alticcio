<?php

require_once "abstract_object.php";

class Boutique extends AbstractObject {

	public $type = "boutique";
	public $table = "dt_boutiques";
	public $id_field = "id_boutiques";

	function liste($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}

		$q = <<<SQL
SELECT b.id, b.nom, c.nom AS catalogue, k.name AS api
FROM dt_boutiques AS b
LEFT OUTER JOIN dt_catalogues AS c ON c.id = b.id_catalogues
LEFT OUTER JOIN api_keys AS k ON k.id = b.id_api_keys
SQL;
		$res = $filter->query($q);
		$boutiques = array();

		while ($row = $filter->fetch($res)) {
			$boutiques[$row['id']] = $row;
		}

		return $boutiques;
	}

	function catalogues() {
		$q = <<<SQL
SELECT id, nom FROM dt_catalogues
SQL;
		$res = $this->sql->query($q);
		$catalogues = array();
		while ($row = $this->sql->fetch($res)) {
			$catalogues[$row['id']] = $row['nom'];
		}

		return $catalogues;
	}

	function api_keys() {
		$q = <<<SQL
SELECT id, name FROM api_keys
SQL;
		$res = $this->sql->query($q);
		$api_keys = array();
		while ($row = $this->sql->fetch($res)) {
			$api_keys[$row['id']] = $row['name'];
		}

		return $api_keys;
	}

	function load($id) {
		$result = parent::load($id);
		$this->values['data'] = array();
		if ($result) {
			$q = <<<SQL
SELECT data_key, data_value FROM dt_boutiques_data WHERE id_boutiques = {$id}
SQL;
			$res = $this->sql->query($q);
			while ($row = $this->sql->fetch($res)) {
				$this->values['data'][$row['data_key']] = $row['data_value'];
			}
		}

		return $result;
	}

	function save($data) {
		$boutique_data = array();
		if (isset($data['boutique']['data'])) {
			foreach ($data['boutique']['data'] as $data_key => $data_value) {
				if ($data_value !== "") {
					$boutique_data[$data_key] = $data_value;
				}
			}
		}
		if (isset($data['boutique']['new_data_key']) and isset($data['boutique']['new_data_value'])) {
			if ($data['boutique']['new_data_key'] !== "" and $data['boutique']['new_data_value'] !== "") {
				$boutique_data[$data['boutique']['new_data_key']] = $data['boutique']['new_data_value'];
			}
		}
		unset($data['boutique']['data']);
		unset($data['boutique']['new_data_key']);
		unset($data['boutique']['new_data_value']);
		
		$this->id = parent::save($data);

		$q = <<<SQL
DELETE FROM dt_boutiques_data WHERE id_boutiques = {$this->id}
SQL;
		$this->sql->query($q);

		if (count($boutique_data)) {
			$values = array();
			foreach ($boutique_data as $key => $value) {
				$values[] = "({$this->id}, '$key', '$value')";
			}
			$values_list = implode(",", $values);

			$q = <<<SQL
INSERT INTO dt_boutiques_data (id_boutiques, data_key, data_value) VALUES $values_list
SQL;
			$this->sql->query($q);
		}
		
		return $this->id;
	}

	function delete($data) {
		$q = <<<SQL
DELETE FROM dt_boutiques_data WHERE id_boutiques = {$data['boutique']['id']}
SQL;
		$this->sql->query($q);

		return parent::delete($data);
	}

	function find_and_load($name) {
		$q = <<<SQL
SELECT id FROM dt_boutiques WHERE nom = '$name'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $this->load($row['id']);
		}
		else {
			return false;
		}
	}

	function api_key() {
		$id_api_keys = (int)$this->values['id_api_keys'];
		$q = <<<SQL
SELECT `key` FROM api_keys WHERE id = {$id_api_keys}
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['key'];
		}
		return "";
	}
}
