<?php

require_once "abstract_contact.php";

class Organisation extends AbstractContact {

	public $type = "organisation";
	public $types = "organisations";
	public $table = "dt_contacts_organisations";
	public $id_field = "id_contacts_organisations";
	public $links = array(
		'correspondants' => array("organisations", "correspondants"),
	);
	public $table_links_organisations = null;
	public $table_links_correspondants = "dt_contacts_organisations_correspondants";
	public $table_links_comptes = null;

	public function liste($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}
		$q = <<<SQL
SELECT id, nom, id_contacts_organisations_types, statut FROM dt_contacts_organisations
SQL;
		$res = $filter->query($q);
		$organisations = array();
		while ($row = $filter->fetch($res)) {
			$organisations[$row['id']] = $row;
		}

		return $organisations;
	}

	public function organisations() {
		$organisations = array();
		$q = <<<SQL
SELECT id, nom, id_parent, statut FROM dt_contacts_organisations
SQL;
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$organisations[] = $row;
		}
		return $organisations;
	}

	public function types() {
		$types = array();
		$q = <<<SQL
SELECT coc.id, coc.code, p.phrase FROM dt_contacts_organisations_types AS coc
LEFT OUTER JOIN dt_phrases AS p ON p.id = coc.phrase_nom AND p.id_langues = {$this->id_langues}
ORDER BY id ASC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$types[$row['id']] = $row['phrase'] ? $row['phrase'] : $row['code'];
		}

		return $types;
	}

	public function organisations_correspondants() {
		return $this->links("dt_contacts_organisations_correspondants", "id_contacts_correspondants");
	}

	public function adresses() {
		$adresses = array();
		$q = <<<SQL
SELECT * FROM dt_contacts_organisations_adresses WHERE id_contacts_organisations = {$this->id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$adresses[$row['id']] = $row;
		}

		return $adresses;
	}

	public function save($data) {
		$id = parent::save($data);
		if (isset($data['adresse'])) {
			foreach ($data['adresse'] as $id_adresse => $adresse) {
				$adresse['id_contacts_organisations'] = $this->id;
				if ($id_adresse) {
					$fields_values = array();
					foreach ($adresse as $field => $value) {
						$fields_values[] = in_array($field, array('id_pays', 'statut')) ? "$field = $value" : "$field = '$value'"; 
					}
					$fields_values_list = implode(",", $fields_values);
					$q = <<<SQL
UPDATE dt_contacts_organisations_adresses SET $fields_values_list WHERE id = {$id_adresse}
SQL;
					$this->sql->query($q);

				}
				else {
					$fields = array();
					$values = array();
					foreach ($adresse as $field => $value) {
						$fields[] = $field;
						$values[] = in_array($field, array('id_pays', 'statut')) ? $value : "'$value'"; 
					}
					$fields_list = implode(",", $fields);
					$values_list = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_contacts_organisations_adresses ($fields_list) VALUES ($values_list)
SQL;
					$this->sql->query($q);
				}
			}
		}

		return $id;
	}

	public function delete_adresse($id) {
		$q = <<<SQL
DELETE FROM dt_contacts_organisations_adresses WHERE id = {$id}
SQL;
		$this->sql->query($q);
	}
}
