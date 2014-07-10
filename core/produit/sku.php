<?php

require_once "abstract_object.php";

class Sku extends AbstractObject {

	public $type = "sku";
	public $table = "dt_sku";
	public $id_field = "id_sku";
	public $images_table = "dt_images_sku";
	public $documents_table = "dt_documents_sku";
	public $attributs_table = "dt_sku_attributs";
	public $phrase_fields = array('phrase_ultralog', 'phrase_commercial', 'phrase_path');

	public function liste($id_langues, &$filter = null) {
		$q = <<<SQL
SELECT s.id, s.ref_ultralog, p1.phrase AS phrase_ultralog, p2.phrase AS phrase_commercial, s.actif
FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = s.phrase_ultralog AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = s.phrase_commercial AND p2.id_langues = $id_langues
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

	public function references_catalogues() {
		$q = <<<SQL
SELECT id_catalogues, reference FROM dt_references_catalogues WHERE id_sku = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		$references_catalogues = array();
		while ($row = $this->sql->fetch($res)) {
			$references_catalogues[$row['id_catalogues']] = $row['reference'];
		}

		return $references_catalogues;
	}

	public function save($data = null) {
		if ($data === null) {
			$data = array('sku' => array('id' => $this->id));
		}
		$data['sku']['date_modification'] = $_SERVER['REQUEST_TIME'];
		$id = parent::save($data);
		if (isset($data['sku']['id'])) {
			$this->save_prix($data);
			$this->save_attributs($data, $id);
		}
		else {
			$q = "INSERT INTO dt_prix (id_sku) VALUES ($id)";
			$this->sql->query($q);
		}

		if (isset($data['references_catalogues'])) {
			$q = <<<SQL
DELETE FROM dt_references_catalogues WHERE id_sku = $id
SQL;
			$this->sql->query($q);
			$values = array();
			foreach ($data['references_catalogues'] as $id_catalogues => $ref) {
				if ($ref) {
					$values[] = "($id, $id_catalogues, '$ref')";
				}
			}
			if ($values = implode(",", $values)) {
				$q = <<<SQL
INSERT INTO dt_references_catalogues (id_sku, id_catalogues, reference) VALUES $values
SQL;
				$this->sql->query($q);
			}
		}

		return $id;
	}

	public function delete($data) {
		parent::delete($data);

		$q = <<<SQL
DELETE FROM dt_prix WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_prix_degressifs WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_accessoires WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_attributs WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_composants WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_variantes WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_gabarits_sku WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);
	}

	public function prix($data = null, $id_catalogues = 0) {
		$id = isset($data['sku']['id']) ? $data['sku']['id'] : $this->id;
		$q = "SELECT * FROM dt_prix WHERE id_sku = $id AND id_catalogues= $id_catalogues";
		$res = $this->sql->query($q);
		$prix = $this->sql->fetch($res);

		return $prix;
	}

	public function prix_catalogue($id_catalogues) {
		$prix = $this->prix(null, $id_catalogues);
		if ($prix === false) {
			return $this->prix();
		}
		else {
			return $prix;
		}
	}

	public function prix_catalogues() {
		$q = "SELECT * FROM dt_prix WHERE id_sku = {$this->id}";
		$res = $this->sql->query($q);
		$prix = array();
		while ($row = $this->sql->fetch($res)) {
			$prix[$row['id_catalogues']] = $row;
		}

		return $prix;

	}

	public function delete_prix($id_catalogues) {
		if ($id_catalogues != 0) {
			$q = <<<SQL
DELETE FROM dt_prix WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
			$this->sql->query($q);
		
			$q = <<<SQL
DELETE FROM dt_prix_degressifs WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
			$this->sql->query($q);
		}
	}

	public function save_prix($data) {
		if (isset($data['prix'])) {
			$id_sku = $data['sku']['id'];
			$set = array();
			foreach ($data['prix'] as $id_catalogues => $prix_catalogue) {
				foreach ($prix_catalogue as $field => $value) {
					$set[] = "$field = '$value'";
				}
				$q = "UPDATE dt_prix SET ".implode(',', $set)." WHERE id_sku = $id_sku AND id_catalogues = {$id_catalogues}";
				$this->sql->query($q);

				$montant_ht = (float)$data['prix'][$id_catalogues]['montant_ht'];
				$q = <<<SQL
UPDATE dt_prix_degressifs SET montant_ht = {$montant_ht} * (1 - (pourcentage / 100))
WHERE id_sku = {$id_sku} AND id_catalogues = {$id_catalogues}
SQL;
				$this->sql->query($q);
			}
		}
	}

	public function add_prix_degressif($data, $id_catalogues = 0) {
		$prix = $this->prix($data, $id_catalogues);

		if ($prix['montant_ht'] == 0) {
			return false;
		}
		if ($data['new_prix_degressif'][$id_catalogues]['quantite'] == "") {
			return false;
		}
		$prix_degressif = array(
			'id_sku' => $data['sku']['id'],
			'id_catalogues' => $id_catalogues,
			'quantite' => $data['new_prix_degressif'][$id_catalogues]['quantite'],
		);
		if ($data['new_prix_degressif'][$id_catalogues]['prix']) {
			$prix_degressif['montant_ht'] = (float)$data['new_prix_degressif'][$id_catalogues]['prix'];
			$prix_degressif['pourcentage'] = (1 - $prix_degressif['montant_ht'] / $prix['montant_ht']) * 100;
		}
		else {
			switch ($data['new_prix_degressif'][$id_catalogues]['type']) {
				case "pourcentage" :
					$prix_degressif['pourcentage'] = $data['new_prix_degressif'][$id_catalogues]['reduction'];
					$prix_degressif['montant_ht'] = $prix['montant_ht'] - ($prix['montant_ht'] * $prix_degressif['pourcentage'] / 100.0);
					break;
				case "montant" :
					$prix_degressif['montant_ht'] = $prix['montant_ht'] - $data['new_prix_degressif'][$id_catalogues]['reduction'];
					$prix_degressif['pourcentage'] = $data['new_prix_degressif'][$id_catalogues]['reduction'] * 100.0 / $prix['montant_ht'];
					break;
			}
		}
		
		$q = <<<SQL
DELETE FROM dt_prix_degressifs
WHERE id_sku = {$prix_degressif['id_sku']} AND id_catalogues = {$id_catalogues} AND quantite = {$prix_degressif['quantite']}
SQL;
		$this->sql->query($q);

		$values = array();
		$fields = array();
		foreach ($prix_degressif as $field => $value) {
			$values[] = $value;
			$fields[] = $field;
		}
		$q = "INSERT dt_prix_degressifs (".implode(',', $fields).") VALUES ('".implode("','", $values)."')";
		$this->sql->query($q);

		return true;
	}

	public function delete_prix_degressif($id) {
		$q = <<<SQL
DELETE FROM dt_prix_degressifs
WHERE id = {$id}
SQL;
		$this->sql->query($q);
	}

	public function prix_degressifs($id_catalogues = 0) {
		$q = <<<SQL
SELECT * FROM dt_prix_degressifs
WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
ORDER BY quantite ASC
SQL;
		$res = $this->sql->query($q);

		$prix = array();
		while($row = $this->sql->fetch($res)) {
			$prix[] = $row;
		}

		return $prix;
	}

	public function prix_degressifs_catalogue($id_catalogues) {
		$prix_degressifs = $this->prix_degressifs($id_catalogues);
		if (count($prix_degressifs) == 0) {
			$prix_degressifs = $this->prix_degressifs(0);
		}

		return $prix_degressifs;
	}

	public function prix_unitaire_pour_qte($id_sku, $qte, $id_catalogues = 0) {
		$q = <<<SQL
SELECT MIN(montant_ht) AS prix FROM dt_prix_degressifs
WHERE id_sku = $id_sku AND quantite <= $qte AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		if ($row['prix']) {
			$prix = $row['prix'];
		}
		else {
			$q = <<<SQL
SELECT montant_ht FROM dt_prix
WHERE id_sku = $id_sku AND id_catalogues = $id_catalogues
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$prix = $row['montant_ht'];
		}

		return $prix;
	}

	public function prix_unitaire_min($id_catalogues = 0) {
		$q = <<<SQL
SELECT MIN(montant_ht) AS prix FROM dt_prix_degressifs
WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		if ($row['prix']) {
			$prix = $row['prix'];
		}
		else {
			$q = <<<SQL
SELECT montant_ht FROM dt_prix
WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$prix = $row['montant_ht'];
		}

		return $prix ? $prix : ($id_catalogues ? $this->prix_unitaire_min(0) : 0);
	}

	public function prix_pour_qte($id_sku, $qte, $id_catalogues = 0) {
		return $qte * $this->prix_unitaire_pour_qte($id_sku, $qte, $id_catalogues);
	}
	
	public function get_familles_ventes($id_langues) {
		$q = <<<SQL
SELECT f.id, f.code, f.id_parent, p.phrase AS nom FROM dt_familles_ventes AS f
LEFT JOIN dt_phrases AS p ON p.id = f.phrase_famille AND p.id_langues = $id_langues
ORDER BY f.id
SQL;
		$res = $this->sql->query($q);
		$familles = array();
		while ($row = $this->sql->fetch($res)) {
			$familles[] = array(
				'id' => $row['id'],
				'id_parent' => $row['id_parent'],
				'nom' => $row['code']." : ".$row['nom'],
			);
		}
		return $familles;
	}
	
	public function get_montages($lang) {
		$q = "SELECT m.id, p.phrase  
				FROM dt_montages AS m
				INNER JOIN dt_phrases AS p 
				ON p.id = m.phrase_montages
				INNER JOIN dt_langues AS l
				ON l.id = p.id_langues AND l.code_langue = '".$lang."'
				ORDER BY p.phrase ";
		$res = $this->sql->query($q);
		$montages = array('...');
		while($row = $this->sql->fetch($res)) {
			$montages[$row['id']] = $row['phrase'];
		}
		return $montages;
	}
	
	public function get_matieres() {
		$q = "SELECT m.id, m.ref_matiere  
				FROM dt_matieres AS m
				ORDER BY m.ref_matiere ";
		$res = $this->sql->query($q);
		$matieres = array('...');
		while($row = $this->sql->fetch($res)) {
			$matieres[$row['id']] = $row['ref_matiere'];
		}
		return $matieres;
	}
	
	
	private function supp_decimale($nbre) {
		return str_replace('.00', '', $nbre);
	}
	
	public function get_dimensions() {
		$q = "SELECT d.id, d.largeur, d.longueur, d.profondeur, u.unite  
				FROM dt_dimensions AS d
				INNER JOIN dt_unites_mesure AS u
				ON u.id = d.id_unites_mesure
				ORDER BY d.largeur ";
		$res = $this->sql->query($q);
		$dimensions = array('...');
		while($row = $this->sql->fetch($res)) {
			$dim = $this->supp_decimale($row['largeur'])." x ".$this->supp_decimale($row['longueur']);
			if ($row['profondeur'] > 0) {
				$dim .= " x ".$this->supp_decimale($row['profondeur']);
			}
			$dim .= " cm";
			$dimensions[$row['id']] = $dim;
		}
		return $dimensions;
	}

	public function get_id_by_ref($ref) {
		$q = <<<SQL
SELECT id FROM dt_sku WHERE ref_ultralog = '$ref'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return isset($row['id']) ? $row['id'] : false;
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

	public function duplicate($data) {
		unset($data['sku']['id']);
		$data['sku']['ref_ultralog'] = "";
		return parent::duplicate($data);
	}

	public function unites_vente($id_langues) {
		$q = <<<SQL
SELECT uv.id, p.phrase
FROM dt_unites_vente AS uv
INNER JOIN dt_phrases AS p ON p.id = uv.phrase_unite
WHERE p.id_langues = {$id_langues}
SQL;
		$res = $this->sql->query($q);
		$unites_vente = array();
		while ($row = $this->sql->fetch($res)) {
			$unites_vente[$row['id']] = $row['phrase'];
		}

		return $unites_vente;
	}

	public function unite_vente($id_langues, $id_sku = null) {
		if ($id_sku === null) {
			$id_sku = $this->id;
		}
		$q = <<<SQL
SELECT p.phrase
FROM dt_sku AS s
INNER JOIN dt_unites_vente AS uv ON uv.id = s.id_unites_vente
INNER JOIN dt_phrases AS p ON p.id = uv.phrase_unite
WHERE p.id_langues = {$id_langues} AND s.id = $id_sku
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['phrase'];
	}

	public function all_catalogues($catalogues = array()) {
		$q = <<<SQL
SELECT id, nom FROM dt_catalogues ORDER BY nom ASC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$catalogues[$row['id']] = $row['nom']; 
		}

		return $catalogues;
	}

	public function catalogues($catalogues = array()) {
		$q = <<<SQL
SELECT c.id, c.nom FROM dt_catalogues AS c
INNER JOIN dt_prix AS p ON p.id_catalogues = c.id
WHERE p.id_sku = {$this->id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$catalogues[$row['id']] = $row['nom']; 
		}

		return $catalogues;
	}

	public function duplicate_prix($id_catalogues) {
		foreach (array("dt_prix", "dt_prix_degressifs") as $table) {
			$q = <<<SQL
DELETE FROM $table WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
			$this->sql->query($q);

			$q = <<<SQL
SELECT * FROM $table WHERE id_sku = {$this->id} AND id_catalogues = 0
SQL;
			$res = $this->sql->query($q);
			
			while ($row = $this->sql->fetch($res)) {
				unset($row['id']);
				$row['id_catalogues'] = $id_catalogues;
				$fields = implode("`,`", array_keys($row));
				$values = implode("','", $row);
				$q = <<<SQL
INSERT INTO $table (`$fields`) VALUES ('$values')
SQL;
				$this->sql->query($q);
			}
		}
	}

	public function image_hd($image_id) {
		return "sku_{$this->id}_{$image_id}";
	}
	
	public function add_gabarit($data, $file, $dir) {
		if (is_array($file)) {
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			$file_name = md5_file($file['tmp_name']).$ext;
			move_uploaded_file($file['tmp_name'], $dir.$file_name);
		}
		else if (file_exists($file)) {
			preg_match("/(\.[^\.]*)$/", $file, $matches);
			$ext = $matches[1];
			$file_name = md5_file($file).$ext;
			copy($file, $dir.$file_name);
		}

		$q = <<<SQL
DELETE FROM dt_gabarits_sku WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
INSERT INTO dt_gabarits_sku (id_sku, ref)
VALUES ({$this->id}, '$file_name')
SQL;
		$this->sql->query($q);
	}

	public function delete_gabarit() {
		$q = <<<SQL
DELETE FROM dt_gabarits_sku WHERE id_sku = {$this->id}
SQL;
		$this->sql->query($q);
	}

	public function gabarit() {
		$q = <<<SQL
SELECT ref FROM dt_gabarits_sku WHERE id_sku = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row ? $row['ref'] : "";
	}

	public function nom_commercial($phrases, $code_langue) {
		foreach (array('phrase_commercial', 'phrase_ultralog') as $phrase) {
			if (isset($phrases[$phrase][$code_langue]) && $phrases[$phrase][$code_langue]) {
				return $phrases[$phrase][$code_langue];
			}
		}
		
		return "";
	}

	public function variante_for() {
		$q = <<<SQL
SELECT id_produits FROM dt_sku_variantes WHERE id_sku = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[] = $row['id_produits'];
		}

		return $produits;
	}

	public function accessoire_for() {
		$q = <<<SQL
SELECT id_produits FROM dt_sku_accessoires WHERE id_sku = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[] = $row['id_produits'];
		}

		return $produits;
	}

	public function get_familles_taxes($id_langues) {
		$q = <<<SQL
SELECT ft.id, p.phrase
FROM dt_familles_taxes AS ft
INNER JOIN dt_phrases AS p ON p.id = ft.phrase_taxe
WHERE p.id_langues = {$id_langues}
SQL;
		$res = $this->sql->query($q);
		$familles_taxes = array();
		while ($row = $this->sql->fetch($res)) {
			$familles_taxes[$row['id']] = $row['phrase'];
		}

		return $familles_taxes;
	}

	public function add_ecotaxe($data, $id_catalogues = 0) {
		$ecotaxe = array(
			'id_sku' => $data['sku']['id'],
			'id_catalogues' => $id_catalogues,
			'id_pays' => $data['new_ecotaxe'][$id_catalogues]['id_pays'],
			'id_familles_taxes' => $data['new_ecotaxe'][$id_catalogues]['id_familles_taxes'],
			'montant' => $data['new_ecotaxe'][$id_catalogues]['montant'],
		);
		$q = <<<SQL
DELETE FROM dt_ecotaxes
WHERE id_sku = {$ecotaxe['id_sku']}
AND id_catalogues = {$id_catalogues}
AND id_pays = {$ecotaxe['id_pays']}
AND id_familles_taxes = {$ecotaxe['id_familles_taxes']}
SQL;
		$this->sql->query($q);

		$values = array();
		$fields = array();
		foreach ($ecotaxe as $field => $value) {
			$values[] = $value;
			$fields[] = $field;
		}
		$q = "INSERT dt_ecotaxes (".implode(',', $fields).") VALUES ('".implode("','", $values)."')";
		$this->sql->query($q);

		return true;
		
	}

	public function delete_ecotaxe($id) {
		$q = <<<SQL
DELETE FROM dt_ecotaxes
WHERE id = {$id}
SQL;
		$this->sql->query($q);
	}

	public function ecotaxes($id_langues, $id_catalogues = 0) {
		$q = <<<SQL
SELECT e.id, e.montant, e.id_pays, e.id_familles_taxes, ph1.phrase AS pays, ph2.phrase AS famille_taxes FROM dt_ecotaxes AS e
LEFT OUTER JOIN dt_pays AS p ON p.id = e.id_pays
LEFT OUTER JOIN dt_phrases AS ph1 ON ph1.id = p.phrase_nom AND ph1.id_langues = $id_langues
LEFT OUTER JOIN dt_familles_taxes AS ft ON ft.id = e.id_familles_taxes
LEFT OUTER JOIN dt_phrases AS ph2 ON ph2.id = ft.phrase_taxe AND ph2.id_langues = $id_langues
WHERE id_sku = {$this->id} AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);

		$ecotaxes = array();
		while($row = $this->sql->fetch($res)) {
			$ecotaxes[] = $row;
		}

		return $ecotaxes;
	}

	public function ecotaxes_pour_qte($id_sku, $qte, $id_pays, $id_catalogues = 0) {
		$q = <<<SQL
SELECT SUM(montant) AS ecotaxe FROM dt_ecotaxes
WHERE id_sku = $id_sku AND id_pays = $id_pays AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $qte * $row['ecotaxe'];
	}
}
