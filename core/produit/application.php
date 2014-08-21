<?php

require_once "abstract_object.php";

class Application extends AbstractObject {

	public $type = "application";
	public $table = "dt_applications";
	public $phrase_fields = array(
		'phrase_nom',
		'phrase_description_courte',
		'phrase_description',
		'phrase_url_key',
		'phrase_produit_description_courte',
		'phrase_produit_description',
	);

	public function liste(&$filter = null, $select_options = false, $ordered = false) {
		$q = <<<SQL
SELECT a.id, p.phrase FROM dt_applications AS a
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
SQL;
		if ($ordered) {
			$q .= " ORDER BY p.phrase ASC";
		}
		if ($filter === null) {
			$filter = $this->sql;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $select_options ? $row['phrase'] : $row;
		}
		
		return $liste;
	}

	public function select() {
		return $this->liste($this->sql, true, true);
	}


	public function all_attributs($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}
		$application_id = isset($this->id) ? $this->id : 0;
		$q = <<<SQL
SELECT a.id, p.phrase AS name, aa.classement, aa.fiche_technique, aa.pictos_vente, aa.top, aa.comparatif, aa.filtre FROM dt_attributs AS a
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
LEFT OUTER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND id_applications = $application_id
SQL;
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function attributs($grouped = "") {
		$application_id = isset($this->id) ? $this->id : 0;
		$q = <<<SQL
SELECT a.id FROM dt_attributs AS a
INNER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND aa.id_applications = $application_id
ORDER BY aa.classement
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[] = $row['id'];
		}
		
		return $liste;
	}

	public function tokens_attributs($id_langues = 0) {
		$application_id = isset($this->id) ? $this->id : 0;
		if ($id_langues) {
			$q = <<<SQL
SELECT a.ref, ph.phrase AS nom FROM dt_attributs AS a
INNER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND aa.id_applications = $application_id
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = a.phrase_nom
WHERE a.ref <> ''
ORDER BY aa.classement
SQL;
		}
		else {
			$q = <<<SQL
SELECT a.ref, a.ref AS nom FROM dt_attributs AS a
INNER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND aa.id_applications = $application_id
WHERE a.ref <> ''
ORDER BY aa.classement
SQL;
		}
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[$row['ref']] = $row['nom'];
		}
		
		return $liste;
	}

	public function produits($id_applications, $produits = array(), $actif = null) {
		$where = "";
		if (count($produits)) {
			$where .= " AND id IN (".implode(",", $produits).")";
		}
		if ($actif !== null) {
			$where .= " AND actif = $actif";
		}
		$q = <<<SQL
SELECT id FROM dt_produits WHERE id_applications = {$id_applications} {$where}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[] = $row['id'];
		}
		return $produits;
	}

	public function liste_produits(&$filter = null) {
		$q = <<<SQL
SELECT pr.id, pr.ref, ph.phrase AS nom, pr.actif FROM dt_produits AS pr
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = pr.phrase_nom AND ph.id_langues = {$this->langue}
WHERE id_applications = {$this->id}
SQL;
		if ($filter === null) {
			$filter = $this->sql;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function save($data) {
		$id = parent::save($data);
		$q = <<<SQL
DELETE FROM dt_applications_attributs WHERE id_applications = {$id}
SQL;
		$this->sql->query($q);

		if (isset($data['attributs'])) {
			foreach ($data['attributs'] as $id_attribut => $attribut) {
				$classement = (int)$attribut['classement'];
				$fiche_technique = (int)$attribut['fiche_technique'];
				$pictos_vente = (int)$attribut['pictos_vente'];
				$top = (int)$attribut['top'];
				$comparatif = (int)$attribut['comparatif'];
				$filtre = (int)$attribut['filtre'];
				$q = <<<SQL
INSERT INTO dt_applications_attributs (id_applications, id_attributs, classement, fiche_technique, pictos_vente, top, comparatif, filtre)
VALUES ({$id}, {$id_attribut}, {$classement}, {$fiche_technique}, {$pictos_vente}, {$top}, {$comparatif}, {$filtre})
SQL;
				$this->sql->query($q);
			}
		}
		return $id;
	}
}
