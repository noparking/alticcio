<?php

require_once "abstract_contact.php";

class Organisation extends AbstractContact {

	public $type = "organisation";
	public $types = "organisations";
	public $table = "dt_contacts_organisations";
	public $id_field = "id_contacts_organisations";

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

	public function save($data) {
		if (isset($data[$this->type]['id'])) {
			if (isset($data['correspondants'])) {
				foreach ($data['correspondants'] as $id_correspondant => $infos) {
					$data['organisations_correspondants'][$data[$this->type]['id']][$id_correspondant] = $infos;
				}
			}
		}
		return parent::save($data);
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
		return array();
	}

	public function comptes() {
		return array();
	}
}
