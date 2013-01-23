<?php

require_once "abstract_object.php";

class Gamme extends AbstractObject {

	public $type = "gamme";
	public $table = "dt_gammes";
	public $images_table = "dt_images_gammes";
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
		parent::delete($data);

		$q = <<<SQL
DELETE FROM dt_gammes_attributs WHERE id_gamme = {$this->id}
SQL;
		$this->sql->query($q);
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

	public function add_attribut($data) {
		$attribut = $this->attributs();
		if (!isset($attribut[$data['new_attribut']])) {
			$q = <<<SQL
INSERT INTO dt_gammes_attributs (id_attributs, id_gammes) VALUES ({$data['new_attribut']}, {$data['gamme']['id']}) 
SQL;
			$res = $this->sql->query($q);
		}
	}

	public function delete_attribut($data, $attribut_id) {
		$q = <<<SQL
DELETE FROM dt_gammes_attributs WHERE id_attributs = {$attribut_id} AND id_gammes = {$data['gamme']['id']}
SQL;
		$res = $this->sql->query($q);
	}

	public function attributs() {
		$attributs = array();
		$q = <<<SQL
SELECT id_attributs, valeur_numerique, phrase_valeur, classement FROM dt_gammes_attributs
WHERE id_gammes = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$value = $row['phrase_valeur'] ?  $row['phrase_valeur'] : $row['valeur_numerique'];
			$attributs[$row['id_attributs']][$row['classement']] = $value;
		}
		return $attributs;
	}

	public function save_attributs($data, $id) {
		if (isset($data['attributs'])) {
			$q = <<<SQL
DELETE FROM dt_gammes_attributs WHERE id_gammes = $id AND classement > 0
SQL;
			$this->sql->query($q);
	
			ksort($data['attributs']);
			foreach ($data['attributs'] as $attribut_id => $valeurs) {
				foreach ($valeurs as $classement => $valeur) { 
					$type_valeur = "valeur_numerique";
					if (isset($data['phrases']['valeurs_attributs'][$attribut_id])) {
						$type_valeur = "phrase_valeur";
						if (is_array($data['phrases']['valeurs_attributs'][$attribut_id])) {
							foreach ($data['phrases']['valeurs_attributs'][$attribut_id] as $lang => $phrase) {
								$valeur = $this->phrase->save($lang, $phrase, $valeur);
							}
						}
						$valeur = (int)$valeur;
					}
					else {
						$valeur = (float)str_replace(" ", "", str_replace(",", ".", $valeur));
					}
					if ($classement == 0) {
						$q = <<<SQL
UPDATE dt_gammes_attributs SET $type_valeur = $valeur
WHERE id_attributs = $attribut_id AND id_gammes = $id AND classement = 0
SQL;
					}
					else {
						$q = <<<SQL
INSERT INTO dt_gammes_attributs (id_attributs, id_gammes, $type_valeur, classement)
VALUES ($attribut_id, $id, $valeur, $classement)
SQL;
					}
					$this->sql->query($q);
				}
			}
		}
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

}

