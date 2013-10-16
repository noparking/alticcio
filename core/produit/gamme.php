<?php

require_once "abstract_object.php";

class Gamme extends AbstractObject {

	public $type = "gamme";
	public $table = "dt_gammes";
	public $id_field = "id_gammes";
	public $images_table = "dt_images_gammes";
	public $documents_table = "dt_documents_gammes";
	public $attributs_table = "dt_gammes_attributs";
	public $phrase_fields = array(
		'phrase_nom',
		'phrase_description_courte',
		'phrase_description',
		'phrase_url_key',
	);

	public function liste($id_langues, &$filter = null) {
		$q = <<<SQL
SELECT g.id, p.phrase, g.ref FROM dt_gammes AS g
LEFT OUTER JOIN dt_phrases AS p ON p.id = g.phrase_nom AND p.id_langues = {$id_langues}
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

	public function save($data = null) {
		if ($data === null) {
			$data = array('gamme' => array('id' => $this->id));
		}
		$id = parent::save($data);
		if (isset($data['gamme']['id'])) {
			$this->save_attributs($data, $id);
		}

		return $id;
	}
	
	public function delete($data) {
		$q = <<<SQL
DELETE FROM dt_gammes_attributs WHERE id_gammes = {$data['gamme']['id']}
SQL;
		$this->sql->query($q);

		return parent::delete($data);
	}
	
	public function all_attributs($option = "") {
		$q = <<<SQL
SELECT a.id, p.phrase AS name, u.unite FROM dt_attributs AS a
INNER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
LEFT JOIN dt_unites_mesure AS u ON u.id = a.id_unites_mesure
ORDER BY name
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			if ($row['unite'] != NULL AND $option == "unite") {
				$liste[$row['id']] = $row['name'].' ('.$row['unite'].')';
			}
			else {
				$liste[$row['id']] = $row['name'];
			}
		}
		
		return $liste;
	}

	public function phrases() {
		$ids = parent::phrases();
		$ids['attributs'] = array();
		$ids['valeurs_attributs'] = array();
		$q = <<<SQL
SELECT id_attributs, phrase_valeur, classement FROM dt_gammes_attributs
WHERE id_gammes = {$this->id} AND phrase_valeur <> 0
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$ids['attributs'][$row['id_attributs']][$row['classement']] = $row['phrase_valeur'];
			$ids['valeurs_attributs'][$row['id_attributs']][$row['classement']] = $row['phrase_valeur'];
		}
		return $ids;
	}

	public function produits($id_gammes, $produits = array(), $actif = null) {
		$where = "";
		if (count($produits)) {
			$where .= " AND id IN (".implode(",", $produits).")";
		}
		if ($actif !== null) {
			$where .= " AND actif = $actif";
		}
		$q = <<<SQL
SELECT id FROM dt_produits WHERE id_gammes = {$id_gammes} {$where}
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
WHERE id_gammes = {$this->id}
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

	public function image_hd($image_id) {
		return "gamme_{$this->id}_{$image_id}";
	}

}

