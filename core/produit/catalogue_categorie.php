<?php

require_once "abstract_object.php";

class CatalogueCategorie extends AbstractObject {

	public $type = "catalogue_categorie";
	public $table = "dt_catalogues_categories";
	public $phrase_fields = array(
		'phrase_nom',
		'phrase_description',
		'phrase_titre_url',
		'phrase_meta_title',
		'phrase_meta_description',
		'phrase_meta_keywords',
	);

	public function load($id) {
		$id = parent::load($id);
		if ($id) {
			$q = <<<SQL
SELECT id_langues FROM dt_catalogues WHERE id =	{$this->values['id_catalogues']}
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);

			$this->values['id_langues'] = $row['id_langues'];
		}

		return $id;
	}

	public function delete($data) {
		$q = <<<SQL
SELECT id FROM dt_catalogues_categories WHERE id_parent = {$this->id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$sub_categorie = new CatalogueCategorie($this->sql, $this->phrase, $this->langue);
			$sub_categorie->load($row['id']);
			$sub_categorie->delete($data);
		}

		$q = <<<SQL
DELETE FROM dt_catalogues_categories_produits WHERE id_catalogues_categories = {$this->id}
SQL;
		$this->sql->query($q);
		parent::delete($data);
	}

	public function all_produits(&$filter = null) {
		$q = <<<SQL
SELECT pr.id, pr.ref, ph.phrase AS nom, pr.id_gammes AS gamme, ccp.classement FROM dt_produits AS pr
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = pr.phrase_nom AND ph.id_langues = {$this->langue}
LEFT OUTER JOIN dt_catalogues_categories_produits AS ccp ON id_catalogues_categories = {$this->id} AND ccp.id_produits = pr.id
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

	public function gammes() {
		$q = <<<SQL
SELECT g.id, ph.phrase AS nom FROM dt_gammes AS g
INNER JOIN dt_phrases AS ph ON ph.id = g.phrase_nom AND ph.id_langues = {$this->langue}
SQL;
		$gammes = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$gammes[$row['id']] = $row['nom'];
		}

		return $gammes;
	}

	public function produits($nb = 0, $page = 0, $order = "classement") {
		if (!isset($this->id)) {
			return array();
		}
		$limit = "";
		if ($nb != 0) {
			$offset = $page * $nb;
			$limit = "LIMIT $offset, $nb";
		}
		$q = <<<SQL
SELECT ccp.id_produits, ccp.classement,
MIN(LEAST(IFNULL(px.montant_ht, 1E99), IFNULL(px2.montant_ht, 1E99))) AS prix_mini
FROM dt_catalogues_categories_produits AS ccp
INNER JOIN dt_produits AS p ON p.id = ccp.id_produits AND p.actif = 1
INNER JOIN dt_catalogues_categories AS cc ON cc.id = ccp.id_catalogues_categories
LEFT OUTER JOIN dt_sku_variantes AS sv ON sv.id_produits = p.id
LEFT OUTER JOIN dt_sku AS s ON s.id = sv.id_sku AND s.actif = 1
LEFT JOIN dt_prix AS px ON px.id_sku = s.id AND px.id_catalogues = cc.id_catalogues
LEFT JOIN dt_prix AS px2 ON px2.id_sku = s.id AND px2.id_catalogues = 0
WHERE id_catalogues_categories = {$this->id}
GROUP BY ccp.id_produits
ORDER BY {$order} ASC
{$limit}
SQL;
		$res = $this->sql->query($q);

		$ids = array();
		while ($row = $this->sql->fetch($res)) {
			$ids[$row['id_produits']] = $row;
		}

		return $ids;
	}

	public function count_produits() {
		if (!isset($this->id)) {
			return 0;
		}
		// La jointure interne élimine les produits absents
		$q = <<<SQL
SELECT count(ccp.id_produits) AS nb FROM dt_catalogues_categories_produits AS ccp
INNER JOIN dt_produits AS p ON p.id = ccp.id_produits AND p.actif = 1
WHERE ccp.id_catalogues_categories = {$this->id}
SQL;
		$res = $this->sql->query($q);

		$row = $this->sql->fetch($res);

		return $row['nb'];
	}

	public function save($data) {
		$data['catalogue_categorie']['date_modification'] = $_SERVER['REQUEST_TIME'];
		$id = parent::save($data);

		$q = <<<SQL
DELETE FROM dt_catalogues_categories_produits WHERE id_catalogues_categories = $id
SQL;
		$this->sql->query($q);

		if (isset($data['produits'])) {
			$values = array();
			foreach ($data['produits'] as $id_produits => $infos) {
				$classement = isset($infos['classement']) ? (int)$infos['classement'] : 0;
				$values[] = "($id, {$id_produits}, $classement)";
			}
			if (count($values)) {
				$values = implode(",", $values);
				$q = <<<SQL
INSERT INTO dt_catalogues_categories_produits (id_catalogues_categories, id_produits, classement) VALUES $values
SQL;
				$this->sql->query($q);
			}
		}

		return $id;
	}

	public function sous_categories() {
		if (!isset($this->id)) {
			return array();
		}
		$q = <<<SQL
SELECT id, nom, titre_url FROM dt_catalogues_categories
WHERE statut = 1 AND id_parent = {$this->id} 
ORDER BY classement ASC
SQL;
		$res = $this->sql->query($q);
		$sous_categories = array();
		while ($row = $this->sql->fetch($res)) {
			$sous_categories[] = $row;
		}

		return $sous_categories;
	}

	public function parente($id = null) {
		if ($id === null) {
			if (isset($this->id)) {
				$id = $this->id;
			}
			else {
				return false;
			}
		}
		$q = <<<SQL
SELECT t1.id, t1.nom, t1.titre_url, t1.id_parent FROM dt_catalogues_categories AS t1
INNER JOIN dt_catalogues_categories AS t2 ON t1.id = t2.id_parent
WHERE t2.id = {$id} 
SQL;
		$res = $this->sql->query($q);
		return $this->sql->fetch($res);
	}

	public function parentes($id = null) {
		if ($id === null) {
			if (isset($this->id)) {
				$id = $this->id;
			}
			else {
				return array();
			}
		}
		$parentes = array();
		while ($parente = $this->parente($id)) {
			$parentes[] = $parente;
			$id = $parente['id'];
		}
		return $parentes;

	}

	public function bloc_options() {
		$options = array(0 => "");
		$q = <<<SQL
SELECT b.id, b.nom FROM dt_blocs as b
INNER JOIN dt_catalogues AS c ON c.id = {$this->values['id_catalogues']} AND c.id_langues = b.id_langues
ORDER BY b.nom ASC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$options[$row['id']] = $row['nom'];
		}

		return $options;
	}

	public function blocs() {
		$q = <<<SQL
SELECT id, id_blocs, utilisation FROM dt_catalogues_categories_blocs WHERE id_catalogues_categories = {$this->id}
ORDER BY utilisation ASC 
SQL;
		$res = $this->sql->query($q);
		$blocs = array();
		while ($row = $this->sql->fetch($res)) {
			$blocs[$row['utilisation']][$row['id']] = $row['id_blocs'];
		}

		return $blocs;
	}

	public function add_bloc($data) {
		$q = <<<SQL
INSERT INTO dt_catalogues_categories_blocs (id_catalogues_categories, id_blocs, utilisation)
VALUES ({$data['catalogue_categorie']['id']}, {$data['new_bloc']['id_blocs']}, '{$data['new_bloc']['utilisation']}')
SQL;
		$this->sql->query($q);
	}

	public function delete_bloc($data, $id) {
		$q = <<<SQL
DELETE FROM dt_catalogues_categories_blocs WHERE id = {$id}
SQL;
		$this->sql->query($q);
	}

	public function active() {
		if ($id = $this->id) {
			while ($id) {
				$q = <<<SQL
SELECT id_parent, statut FROM dt_catalogues_categories WHERE id = {$id}
SQL;
				$res = $this->sql->query($q);
				$row = $this->sql->fetch($res);
				if (!$row['statut']) {
					return false;
				}
				$id = $row['id_parent'];
			}
			return true;
		}
		return false;
	}
}
