<?php

require_once "abstract_object.php";

class Catalogue extends AbstractObject {

	public $type = "catalogue";
	public $table = "dt_catalogues";

	public function load($id) {
		parent::load($id);
		$q = <<<SQL
SELECT data FROM dt_exports_catalogues WHERE id_catalogues = {$this->id}
ORDER BY id DESC
LIMIT 0, 1
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		$this->values['export_data'] = unserialize($row['data']);
	}

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT c.id, c.nom, c.id_langues, c.type, c.statut, COUNT(DISTINCT(ccp.id_produits)) AS nb_produits
FROM dt_catalogues AS c
LEFT OUTER JOIN dt_catalogues_categories AS cc ON cc.id_catalogues = c.id
LEFT OUTER JOIN dt_catalogues_categories_produits AS ccp ON cc.id = ccp.id_catalogues_categories
SQL;
		if ($filter === null) {
			$filter = $this->sql;
			$q .= <<<SQL
GROUP BY c.id
SQL;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function code_langue() {
		$q = <<<SQL
SELECT code_langue FROM dt_langues WHERE id = {$this->values['id_langues']}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['code_langue'];
	}

	public function add_categorie($data) {
		$fields = array();
		$values = array();
		foreach ($data['categorie'] as $field => $value) {
			$fields[] = $field;
			$values[] = "'$value'";
		}
		$fields = implode(",", $fields);
		$values = implode(",", $values);
		$q = <<<SQL
INSERT INTO dt_catalogues_categories ({$fields}) VALUES ({$values})
SQL;
		$res = $this->sql->query($q);

		return $this->sql->insert_id();
	}

	public function delete($data) {
		foreach ($this->categories() as $categorie) {
			$data_categorie = array();
			$data_categorie['catalogue_categorie']['id'] = $categorie['id'];
			$categorie_object = new CatalogueCategorie($this->sql);
			$categorie_object->load($categorie['id']);
			$categorie_object->delete($data_categorie);
		}
		parent::delete($data);
	}

	public function categories() {
		$categories = array();
		$q = <<<SQL
SELECT id, nom, id_parent FROM dt_catalogues_categories
WHERE id_catalogues = {$this->id}
ORDER BY classement ASC
SQL;
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$categories[] = $row;
		}
		return $categories;
	}

	public function premieres_categories() {
		$categories = array();
		$q = <<<SQL
SELECT id, nom, titre_url FROM dt_catalogues_categories
WHERE id_catalogues = {$this->id} AND id_parent = 0
ORDER BY classement ASC
SQL;
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$categories[] = $row;
		}
		return $categories;
	}

	public function nb_produits() {
		$q = <<<SQL
SELECT COUNT(DISTINCT(id_produits)) AS nb FROM dt_catalogues_categories_produits AS ccp
INNER JOIN dt_catalogues_categories AS cc ON cc.id = ccp.id_catalogues_categories
WHERE cc.id_catalogues = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['nb'];
	}

	public function duplicate() {
		$values = $this->values;
		$data = array();
		$data['catalogue']['nom'] = addslashes($values['nom']." (duplicated)");
		$data['catalogue']['id_langues'] = $values['id_langues'];
		$data['catalogue']['type'] = $values['type'];
		$data['catalogue']['statut'] = $values['statut'];
		
		$new_catalog = new Catalogue($this->sql);
		
		$id = $new_catalog->save($data);
		$new_catalog->load($id);
		
		$parents_ids = array(0);
		$parent_correspondances = array(0 => 0);
		while (count($parents_ids)) {
			$new_parents_ids = array();
			foreach ($parents_ids as $id_parent) {
				$q = <<<SQL
SELECT * FROM dt_catalogues_categories WHERE id_catalogues = {$this->id} AND id_parent = $id_parent
SQL;
				$res = $this->sql->query($q);
				while ($row = $this->sql->fetch($res)) {
					$id_parent = $parent_correspondances[$row['id_parent']];
					$nom = addslashes($row['nom']);
					$q = <<<SQL
INSERT INTO dt_catalogues_categories (id_parent, id_catalogues, nom, correspondance, statut)
VALUES ($id_parent, $id, '{$nom}', 0, {$row['statut']})
SQL;
					$this->sql->query($q);
					$catalogues_categories_id = $this->sql->insert_id();
					$parent_correspondances[$row['id']] = $catalogues_categories_id;
					$new_parents_ids[] = $row['id'];

					$q = <<<SQL
SELECT id_produits FROM dt_catalogues_categories_produits WHERE id_catalogues_categories = {$row['id']}
SQL;
					$res2 = $this->sql->query($q);
					$values = array();
					while ($row = $this->sql->fetch($res2)) {
						$values[] = "(".$catalogues_categories_id.",".$row['id_produits'].")";
					}
					if (count($values)) {
						$values = implode(",", $values);
						$q = <<<SQL
INSERT INTO dt_catalogues_categories_produits (id_catalogues_categories, id_produits)
VALUES $values
SQL;
						$this->sql->query($q);
					}
				}
			}
			$parents_ids = $new_parents_ids;
		}

		return $id;
	}

	public function find($name) {
		$q = <<<SQL
SELECT id FROM dt_catalogues WHERE nom LIKE '$name'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['id'];
		}
		else {
			return false;
		}
	}

	function save($data) {
		if (isset($this->id) and $this->id) {
			$q = <<<SQL
DELETE FROM dt_exports_catalogues WHERE id_catalogues = {$this->id} AND auto <> 0
SQL;
			$this->sql->query($q);
		}

		if (isset($data['catalogue']['export_frequency']) and $data['catalogue']['export_frequency']) {
			$this->export($data, $data['catalogue']['export_frequency']);
		}

		return parent::save($data);
	}

	function export($data = array(), $auto = 0) {
		if (isset($data['export_data'])) {
			$data = $data['export_data'];
		}
		$data = serialize($data);

		$complement = $auto ? "" : "_".date("YmdHis");
		$date = time();
		
		$file = "catalogue_".preg_replace("/[^a-z0-9]+/", "_", strtolower($this->values['nom'])).$complement.".csv";
		$q = <<<SQL
INSERT INTO dt_exports_catalogues (id_catalogues, etat, fichier, date_export, data, auto) VALUES ({$this->id}, 'tobuild', '$file', $date, '$data', $auto)
SQL;
		$this->sql->query($q);
	}

	function get_export() {
		$q = <<<SQL
SELECT * FROM dt_exports_catalogues WHERE id_catalogues = {$this->id}
ORDER BY date_export DESC
LIMIT 0, 1
SQL;
		$res = $this->sql->query($q);

		return $this->sql->fetch($res);
	}

	function exports_to_build() {
		$q = <<<SQL
SELECT id, id_catalogues, fichier FROM dt_exports_catalogues WHERE etat = 'tobuild'
SQL;
		$res = $this->sql->query($q);
		$exports = array();
		while ($row = $this->sql->fetch($res)) {
			$exports[] = $row;
		}

		return $exports;
	}

	function export_mark($id, $state, $no_date = false) {
		$date = time();
		if ($no_date) {
			$q = <<<SQL
UPDATE dt_exports_catalogues SET etat = '$state' WHERE id = $id
SQL;
		} else {
			$q = <<<SQL
UPDATE dt_exports_catalogues SET date_export = $date, etat = '$state' WHERE id = $id
SQL;
		}
		$this->sql->query($q);
	}
}
