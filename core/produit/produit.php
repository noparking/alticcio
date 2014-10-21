<?php

require_once "abstract_object.php";

class Produit extends AbstractObject {

	public $type = "produit";
	public $table = "dt_produits";
	public $id_field = "id_produits";
	public $images_table = "dt_images_produits";
	public $documents_table = "dt_documents_produits";
	public $attributs_table = "dt_produits_attributs";
	public $phrase_fields = array(
		'phrase_nom',
		'phrase_commercial',
		'phrase_description_courte',
		'phrase_description',
		'phrase_url_key',
		'phrase_meta_title',
		'phrase_meta_description',
		'phrase_meta_keywords',
		'phrase_entretien',
		'phrase_mode_emploi',
		'phrase_avantages_produit',
		'phrase_designation_auto',
	);

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT pr.id, pr.ref, ph.phrase, pr.id_gammes AS gamme, pr.actif FROM dt_produits AS pr
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = pr.phrase_nom AND id_langues = {$this->langue}
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
			$data = array('produit' => array('id' => $this->id));
		}
		$data['produit']['date_modification'] = $_SERVER['REQUEST_TIME'];
		$id = parent::save($data);

		foreach (array('composants', 'variantes', 'accessoires') as $associated_sku) {
			if (isset($data[$associated_sku])) {
				$q = <<<SQL
DELETE FROM dt_sku_$associated_sku WHERE id_produits = $id
SQL;
				$this->sql->query($q);

				$values = array();
				foreach ($data[$associated_sku] as $id_sku => $sku) {
					$classement = (int)$sku['classement'];
					$values[] = "($id, {$id_sku}, {$classement})";
				}
				if (count($values)) {
					$values = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_sku_$associated_sku (id_produits, id_sku, classement) VALUES $values
SQL;
					$this->sql->query($q);
				}
			}
		}

		$produits = array('complementaires' => "id_produits_compl", 'similaires' => "id_produits_sim");
		foreach ($produits as $associated_produit => $associated_id_field) {
			if (isset($data[$associated_produit])) {
				$q = <<<SQL
DELETE FROM dt_produits_$associated_produit WHERE id_produits = $id
SQL;
				$this->sql->query($q);
				$values = array();
				foreach ($data[$associated_produit] as $id_produits => $produit) {
					$classement = (int)$produit['classement'];
					$values[] = "($id, {$id_produits}, {$classement})";
				}
				if (count($values)) {
					$values = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_produits_$associated_produit (id_produits, $associated_id_field, classement) VALUES $values
SQL;
					$this->sql->query($q);
				}
			}
		}

		$this->save_attributs($data, $id);

# Ancienne personnalisation
		if (isset($data['personnalisation'])) {
				$q = <<<SQL
DELETE FROM dt_personnalisations_produits WHERE id_produits = $id
SQL;
				$this->sql->query($q);
			foreach ($data['personnalisation'] as $type => $perso) {
				if (isset($perso['has']) and $perso['has']) {
					$q = <<<SQL
INSERT INTO dt_personnalisations_produits (`id_produits`, `type`, `libelle`)
VALUES ($id, '$type', '{$perso['libelle']}')
SQL;
					$this->sql->query($q);
				}
			}
		}

# Nouvelle personnalisation
		$this->save_personnalisations($data);

		$q = <<<SQL
SELECT id, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);
		$designations = array();
		$langues = array();
		while ($row = $this->sql->fetch($res)) {
			$designations[$row['id']] = $this->designations_auto($row['id'], $id);
			$langues[$row['id']] = $row['code_langue'];
		}
		
		$q = <<<SQL
SELECT s.phrase_commercial, s.id
FROM dt_sku AS s
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = s.id AND sv.id_produits = {$id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$id_phrase = 0;
			foreach ($langues as $id_langues => $code_langue) {
				$designation = $designations[$id_langues][$row['id']];
				if ($designation['auto']) {
					$id_phrase = $this->phrase->save($code_langue, addslashes($designation['auto']), (int)$row['phrase_commercial']);
				}
			}
			$q = <<<SQL
	UPDATE dt_sku SET phrase_commercial = {$id_phrase} WHERE id = {$row['id']}
SQL;
			$this->sql->query($q);
		}
		return $id;
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
DELETE FROM dt_gabarits_produits WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
INSERT INTO dt_gabarits_produits (id_produits, ref)
VALUES ({$this->id}, '$file_name')
SQL;
		$this->sql->query($q);
	}

	public function delete_gabarit() {
		$q = <<<SQL
DELETE FROM dt_gabarits_produits WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);
	}

	public function gabarit() {
		$q = <<<SQL
SELECT ref FROM dt_gabarits_produits WHERE id_produits = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row ? $row['ref'] : "";
	}

	public function delete($data) {
		$q = <<<SQL
DELETE FROM dt_sku_accessoires WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_composants WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_sku_variantes WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_attributs WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_complementaires WHERE id_produits = {$this->id} OR id_produits_compl = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_similaires WHERE id_produits = {$this->id} OR id_produits_sim = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_gabarits_produits WHERE id_produits = {$this->id}
SQL;
		$this->sql->query($q);

		parent::delete($data);
	}

	public function applications() {
		$q = <<<SQL
SELECT a.id, p.phrase AS nom FROM dt_applications AS a
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
ORDER BY nom
SQL;
		$res = $this->sql->query($q);
		$applications = array();
		while ($row = $this->sql->fetch($res)) {
			$applications[$row['id']] = $row['nom'];
		}

		return $applications;
	}
	
	public function gammes() {
		$q = <<<SQL
SELECT g.id, p.phrase AS nom FROM dt_gammes AS g
LEFT OUTER JOIN dt_phrases AS p ON p.id = g.phrase_nom AND p.id_langues = {$this->langue}
ORDER BY nom
SQL;
		$res = $this->sql->query($q);
		$gammes = array("...");
		while ($row = $this->sql->fetch($res)) {
			$gammes[$row['id']] = $row['nom'];
		}

		return $gammes;
	}

	public function recyclage($id_langues) {
		$q = "SELECT r.id, r.numero, p.phrase 
				FROM dt_recyclage AS r 
				LEFT JOIN dt_phrases AS p 
				ON p.id = r.phrase_nom
				AND p.id_langues = ".$id_langues;
		$res = $this->sql->query($q);
		$recycle = array('...');
		while($row = $this->sql->fetch($res)) {
			$recycle[$row['id']] = $row['numero'].' : '.$row['phrase'];
		}
		return $recycle;
	}
	
	public function attributs_names() {
		$attributs = array();
		$q = <<<SQL
SELECT a.phrase_nom, pa.id_attributs FROM dt_produits_attributs AS pa
INNER JOIN dt_attributs AS a ON a.id = pa.id_attributs
WHERE pa.id_produits = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$attributs[$row['id_attributs']] = $row['phrase_nom'];
		}

		return $attributs;
	}

	public function attributs_filtre($id_langues) {
		$q = <<<SQL
SELECT DISTINCT(a.id), a.id_types_attributs, ph.phrase AS nom
FROM dt_sku_variantes AS sv
INNER JOIN dt_sku AS s ON s.id = sv.id_sku AND s.actif = 1
INNER JOIN dt_sku_attributs AS sa ON sa.id_sku = sv.id_sku
INNER JOIN dt_attributs AS a ON a.id = sa.id_attributs
INNER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND aa.filtre = 1
INNER JOIN dt_produits AS p ON p.id = sv.id_produits AND p.id_applications = aa.id_applications
INNER JOIN dt_phrases AS ph ON ph.id = a.phrase_nom AND ph.id_langues = {$id_langues}
WHERE sv.id_produits = {$this->id}
ORDER BY aa.classement ASC
SQL;
		$attributs = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			switch ($row['id_types_attributs']) {
				case 5 : // select
					$q = <<<SQL
SELECT oa.phrase_option AS option_id, ph.phrase AS option_name
FROM dt_options_attributs AS oa
INNER JOIN dt_phrases AS ph ON ph.id = oa.phrase_option AND ph.id_langues = {$id_langues}
INNER JOIN dt_sku_attributs AS sa ON sa.phrase_valeur = oa.phrase_option AND sa.id_attributs = {$row['id']}
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku AND sv.id_produits = {$this->id}
INNER JOIN dt_sku AS s ON s.id = sv.id_sku AND s.actif = 1 
WHERE oa.id_attributs = {$row['id']}
ORDER by oa.classement ASC
SQL;
					break;
				case 6 : // reference
					$q = <<<SQL
SELECT table_name, field_label, field_value
FROM dt_attributs_references
WHERE id_attributs = {$row['id']}
SQL;
					$res2 = $this->sql->query($q);
					$row2 = $this->sql->fetch($res2);
					if (substr($row2['field_label'], 0, 6) == "phrase") {
						$q = <<<SQL
SELECT DISTINCT(t.{$row2['field_value']}) AS option_id, ph.phrase AS option_name
FROM {$row2['table_name']} AS t
INNER JOIN dt_phrases AS ph ON ph.id = t.{$row2['field_label']} AND ph.id_langues = {$id_langues}
INNER JOIN dt_sku_attributs AS sa ON sa.valeur_numerique = t.{$row2['field_value']} AND sa.id_attributs = {$row['id']}
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku AND sv.id_produits = {$this->id}
INNER JOIN dt_sku AS s ON s.id = sv.id_sku AND s.actif = 1 
ORDER BY t.{$row2['field_value']} ASC
SQL;
					}
					else {
						if ($row2['field_value'][0] != ucfirst($row2['field_value'][0])) {
							$row2['field_value'] = "t.".$row2['field_value'];
						}
						if ($row2['field_label'][0] != ucfirst($row2['field_label'][0])) {
							$row2['field_label'] = "t.".$row2['field_label'];
						}
						$q = <<<SQL
SELECT DISTINCT({$row2['field_value']}) AS option_id, {$row2['field_label']} AS option_name
FROM {$row2['table_name']} AS t
INNER JOIN dt_sku_attributs AS sa ON sa.valeur_numerique = {$row2['field_value']} AND sa.id_attributs = {$row['id']}
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku AND sv.id_produits = {$this->id}
INNER JOIN dt_sku AS s ON s.id = sv.id_sku AND s.actif = 1 
ORDER BY {$row2['field_value']} ASC
SQL;
					}
					break;
				default : $q = false;
					break;
			}
			if ($q) {
				$res2 = $this->sql->query($q);
				$options = array();
				while ($row2 = $this->sql->fetch($res2)) {
					$options[$row2['option_id']] = $row2['option_name'];
				}
				$attributs[$row['id']] = array('nom' => $row['nom'], 'options' => $options);
			}
		}

		return $attributs;
	}

	public function variantes_filtre() {
		$q = <<<SQL
SELECT sv.id_sku, a.id, sa.valeur_libre, sa.valeur_numerique, sa.phrase_valeur
FROM dt_sku_variantes AS sv
INNER JOIN dt_sku_attributs AS sa ON sa.id_sku = sv.id_sku
INNER JOIN dt_attributs AS a ON a.id = sa.id_attributs
INNER JOIN dt_applications_attributs AS aa ON aa.id_attributs = a.id AND aa.filtre = 1
WHERE sv.id_produits = {$this->id}
ORDER BY aa.classement ASC
SQL;
		$variantes = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$variantes[$row['id_sku']][$row['id']] = $row['phrase_valeur'] ? $row['phrase_valeur'] : ($row['valeur_numerique'] ?	$row['valeur_numerique'] : $row['valeur_libre']);
		}

		return $variantes;
	}

	public function attributs_data() {
		$attributs = array();
		$q = <<<SQL
SELECT a.phrase_nom, a.id_types_attributs, um.unite,
	   pa.id_attributs, pa.type_valeur,
	   pa.valeur_numerique, pa.phrase_valeur, pa.valeur_libre, pa.classement,
	   aa.fiche_technique, aa.pictos_vente, aa.top, aa.comparatif, aa.filtre
FROM dt_produits_attributs AS pa
INNER JOIN dt_produits AS p ON pa.id_produits = p.id
INNER JOIN dt_applications_attributs AS aa ON p.id_applications = aa.id_applications AND aa.id_attributs = pa.id_attributs
INNER JOIN dt_attributs AS a ON a.id = pa.id_attributs
LEFT OUTER JOIN dt_unites_mesure AS um ON um.id = a.id_unites_mesure
WHERE pa.id_produits = {$this->id}
ORDER BY aa.classement ASC
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$attributs[$row['id_attributs']][$row['classement']] = $row;
		}

		return $this->attributs_data_from_variantes($attributs);
	}

	public function attributs_data_from_variantes($attributs = array()) {
		$variantes = $this->variantes();
		if (count($variantes)) {
			$variantes_ids = implode(",", array_keys($variantes));
			$q = <<<SQL
SELECT sa.id_attributs, sa.type_valeur, sa.valeur_numerique, sa.phrase_valeur, sa.valeur_libre, sa.classement FROM dt_sku_attributs AS sa
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku
WHERE sa.id_sku IN ({$variantes_ids})
ORDER BY sv.classement ASC
SQL;
			$res = $this->sql->query($q);
			
			$valeurs_numeriques = array();
			$valeurs_libres = array();
			$phrases_valeurs = array();
			$ids_attributs = array();
			while ($row = $this->sql->fetch($res)) {
				switch ($row['type_valeur']) {
					case 'phrase_valeur' :
						$phrases_valeurs[$row['id_attributs']][] = $row['phrase_valeur'];
						break;
					case 'valeur_libre' :
						$valeurs_libres[$row['id_attributs']][] = $row['valeur_libre'];
						break;
					case 'valeur_numerique' :
						$valeurs_numeriques[$row['id_attributs']][] = $row['valeur_numerique'];
						break;
				}
				$ids_attributs[$row['id_attributs']] = $row['classement'];
			}
			foreach ($ids_attributs as $id_attributs => $classement) {
				if (isset($attributs[$id_attributs])) {
					if (isset($phrases_valeurs[$id_attributs])) {
						$attributs[$id_attributs][$classement]['valeur_numerique'] = array();
						$attributs[$id_attributs][$classement]['valeur_libre'] = array();
						$attributs[$id_attributs][$classement]['phrase_valeur'] = array_unique($phrases_valeurs[$id_attributs]);
					}
					else if (isset($valeurs_libres[$id_attributs])) {
						$attributs[$id_attributs][$classement]['valeur_numerique'] = array();
						$attributs[$id_attributs][$classement]['valeur_libre'] = array_unique($valeurs_libres[$id_attributs]);
						$attributs[$id_attributs][$classement]['phrase_valeur'] = array();;
					}
					else {
						$attributs[$id_attributs][$classement]['valeur_numerique'] = array_unique($valeurs_numeriques[$id_attributs]);
						$attributs[$id_attributs][$classement]['valeur_libre'] = array();
						$attributs[$id_attributs][$classement]['phrase_valeur'] = array();
					}
					$attributs[$id_attributs][$classement]['id_attributs'] = $id_attributs;
				}
			}
		}

		return $this->attributs_data_get_references($attributs);
	}

	public function attributs_data_get_references($attributs = array()) {
		foreach ($attributs as $id_attributs => $data) {
			foreach ($data as $classement => $attribut) {
				if (isset($attribut['id_types_attributs']) and $attribut['id_types_attributs'] == 6) {
					$q = <<<SQL
SELECT * FROM dt_attributs_references WHERE id_attributs = {$id_attributs}
SQL;
					$res = $this->sql->query($q);
					$data_ref = $this->sql->fetch($res);

					$q = <<<SQL
SELECT {$data_ref['field_label']} AS ref_value FROM `{$data_ref['table_name']}` WHERE {$data_ref['field_value']}
SQL;
					if (is_array($attribut['valeur_numerique'])) {
						$q .= " IN ('".implode("','", $attribut['valeur_numerique'])."')";
					}
					else {
						$q .= " = '{$attribut['valeur_numerique']}'";
					}

					$res = $this->sql->query($q);
					if (is_array($attribut['valeur_numerique'])) {
						$ref_value = array();
						while ($row = $this->sql->fetch($res)) {
							$ref_value[] = $row['ref_value'];
						}
					}
					else {
						$ref_value = $this->sql->fetch($res);
					}
					if (strpos($data_ref['field_label'], "phrase_") === 0) {
						$attributs[$id_attributs][$classement]['phrase_valeur'] = $ref_value;
					}
					else {
						$attributs[$id_attributs][$classement]['valeur_numerique'] = $ref_value;
					}
				 }
			}
		}
	
		return $attributs;
	}

	public function phrases() {
		$ids = parent::phrases();
		$attributs = $this->attributs_names();
		$ids['attributs'] = array();
		foreach ($attributs as $attribut_id => $value) {
			$ids['attributs'][$attribut_id] = $value;
		}
		$ids['valeurs_attributs'] = array();
		$attributs = $this->attributs_data();
		foreach ($attributs as $data) {
			foreach ($data as $classement => $attribut) {
				if (isset($attribut['phrase_valeur']) and $attribut['phrase_valeur']) {
					$ids['valeurs_attributs'][$attribut['id_attributs']][$classement] = $attribut['phrase_valeur'];
				}
			}
		}
		$ids['personnalisations'] = array('gabarits' => array());
		foreach ($this->personnalisations_gabarits() as $id_gabarit => $gabarit_fields) {
			foreach ($gabarit_fields as $key => $value) {
				if (strpos($key, "phrase_") === 0) {
					$ids['personnalisations']['gabarits'][$id_gabarit][$key] = $value;
				}
			}
		}
		return $ids;
	}

	public function types($name_as_key = false) {
		$q = <<<SQL
SELECT id, nom FROM dt_types_produits
SQL;
		$res = $this->sql->query($q);

		$types = array();
		while($row = $this->sql->fetch($res)) {
			$types[$name_as_key ? $row['nom'] : $row['id']] = $row['nom'];
		}

		return $types;
	}

	private function associated_sku($table) {
		if (!isset($this->id)) {
			return array();
		}
		$q = <<<SQL
SELECT t.id_sku, t.classement FROM $table AS t
INNER JOIN dt_sku AS s ON s.id = t.id_sku
WHERE t.id_produits = {$this->id}
ORDER BY t.classement ASC
SQL;
		$res = $this->sql->query($q);

		$ids = array();
		while ($row = $this->sql->fetch($res)) {
			$ids[$row['id_sku']] = $row;
		}

		return $ids;
	}

	public function all_associated_sku($table, &$filter = null) {
		$q = <<<SQL
SELECT s.id, s.ref_ultralog, p.phrase AS nom, link.classement FROM dt_sku AS s
LEFT OUTER JOIN $table AS link ON link.id_sku = s.id AND link.id_produits = {$this->id}
LEFT OUTER JOIN dt_phrases AS p ON p.id = s.phrase_ultralog AND p.id_langues = {$this->langue}
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

	public function composants() {
		return $this->associated_sku('dt_sku_composants');
	}

	public function all_composants(&$filter = null) {
		return $this->all_associated_sku('dt_sku_composants', $filter);
	}

	public function accessoires() {
		return $this->associated_sku('dt_sku_accessoires');
	}

	public function all_accessoires(&$filter = null) {
		return $this->all_associated_sku('dt_sku_accessoires', $filter);
	}

	public function variantes() {
		return $this->associated_sku('dt_sku_variantes');
	}

	public function all_variantes(&$filter = null) {
		return $this->all_associated_sku('dt_sku_variantes', $filter);
	}

	private function associated_produits($table, $id_field) {
		if (!isset($this->id)) {
			return array();
		}
		$q = <<<SQL
SELECT $id_field, classement FROM $table WHERE id_produits = {$this->id}
ORDER BY classement ASC
SQL;
		$res = $this->sql->query($q);

		$ids = array();
		while ($row = $this->sql->fetch($res)) {
			$ids[$row[$id_field]] = $row;
		}

		return $ids;
	}

	private function all_associated_produits($table, $id_field, &$filter = null) {
		$q = <<<SQL
SELECT pr.id, pr.ref, ph.phrase AS nom, link.classement FROM dt_produits AS pr
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = pr.phrase_nom AND ph.id_langues = {$this->langue}
LEFT OUTER JOIN {$table} AS link ON link.{$id_field} = pr.id AND link.id_produits = {$this->id}
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

	public function complementaires() {
		return $this->associated_produits("dt_produits_complementaires", "id_produits_compl");
	}

	public function all_complementaires(&$filter) {
		return $this->all_associated_produits("dt_produits_complementaires", "id_produits_compl", $filter);
	}

	public function similaires() {
		return $this->associated_produits("dt_produits_similaires", "id_produits_sim");
	}

	public function all_similaires(&$filter = null) {
		return $this->all_associated_produits("dt_produits_similaires", "id_produits_sim", $filter);
	}

	public function fiche_perso($user_id, $default) {
		$q = <<<SQL
SELECT * FROM dt_fiches_produits WHERE id_users = $user_id ORDER BY classement
SQL;
		$res = $this->sql->query($q);
		$fiche = array();
		while ($row = $this->sql->fetch($res)) {
			$fiche[$row['zone']][$row['element']] = $row;
		}

		// Si la fiche n'existe pas, on la crée.
		if (!count($fiche)) {
			$data = array();
			foreach ($default as $zone => $elements) {
				$i = 0;
				foreach ($elements as $element) {
					$data['fiche'][$element] = array(
						'zone' => $zone,
						'classement' => $i,
					);
					$i++;
				}
			}
			$this->save_fiche($data, $user_id);
			return $this->fiche($user_id, $default);
		}

		return $fiche;
	}

	public function save_fiche_perso($data, $user_id) {
		$rows = array();
		foreach ($data['fiche'] as $element => $values) {
			if (isset($values['id']) and $values['id']) {
				$q = <<<SQL
UPDATE dt_fiches_produits
SET zone = '{$values['zone']}', classement = '{$values['classement']}'
WHERE id = {$values['id']}
SQL;
			}
			else {
				$q = <<<SQL
INSERT INTO dt_fiches_produits (id_users, element, zone, classement)
VALUES ($user_id, '$element', '{$values['zone']}', {$values['classement']})
SQL;
			}
			$this->sql->query($q);
		}
	}

	public function reset_fiche_perso($user_id) {
		$q = <<<SQL
DELETE FROM dt_fiches_produits WHERE id_users = $user_id
SQL;
		$this->sql->query($q);
	}

	public function fiche_perso_element($id) {
		$q =<<<SQL
SELECT html, xml FROM dt_fiches_produits WHERE id = $id
SQL;
		$res = $this->sql->query($q);

		return $this->sql->fetch($res);
	}

	public function save_fiche_perso_element($data, $id) {
		$element = $data['fiche_element'];
		$q = <<<SQL
UPDATE dt_fiches_produits
SET html = '{$element['html']}', xml = '{$element['xml']}'
WHERE id = $id
SQL;
		$this->sql->query($q);
	}

	public function fiche_perso_attributs($attribut, $langue) {
		$infos = array();
		$phrases = $this->phrase->get($this->phrases());
		foreach ($this->attributs_data() as $tab) {
			foreach ($tab as $classement => $data) {
				$attribut->load($data['id_attributs']);

				$unites = $attribut->unites();
				$unite = null;
				if ($attribut->values['id_unites_mesure']) {
					$unite = $unites[$attribut->values['id_unites_mesure']];
				}

				$types = $attribut->types();
				$type = $types[$attribut->values['id_types_attributs']];
				

				if ($data['phrase_valeur']) {
					if (isset($phrases['valeurs_attributs'][$data['id_attributs']][$langue])) {
						$valeur = $phrases['valeurs_attributs'][$data['id_attributs']][$langue];
					}
					elseif (is_array($phrases['valeurs_attributs'][$data['id_attributs']])) {
						$valeur = array();
						foreach ($phrases['valeurs_attributs'][$data['id_attributs']] as $phrase_valeur_attribut) {
							$valeur[] = $phrase_valeur_attribut[$langue];
						}
					}
				}
				else {
					$valeur = $data['valeur_numerique'];
				}

				$infos[] = array(
					'nom' => $phrases['attributs'][$data['id_attributs']][$langue],
					'valeur' => $valeur,
					'type' => $type,
					'unite' => $unite,
					'fiche_technique' => $data['fiche_technique'],
					'pictos_vente' => $data['pictos_vente'],
					'top' => $data['top'],
					'comparatif' => $data['comparatif'],
					'filtre' => $data['filtre'],
				);
			}
		}
		return $infos;
	}

	public function get_id_by_ref($ref) {
		$q = <<<SQL
SELECT id FROM dt_produits WHERE ref = '$ref'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return isset($row['id']) ? $row['id'] : false;
	}

	public function prix_mini($id_catalogues = 0) {
		$q = <<<SQL
SELECT MIN(p.montant_ht) AS prix_mini FROM dt_prix AS p
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = p.id_sku
WHERE sv.id_produits = {$this->id} AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['prix_mini'];
	}

	public function duplicate($data) {
		unset($data['produit']['id']);
		return parent::duplicate($data);
	}

	public function categories($id_catalogues) {
		$categories = array();
		$q = <<<SQL
SELECT cc.id, cc.nom, cc.titre_url, cc.id_parent, cc.classement, cc.statut FROM dt_catalogues_categories AS cc
INNER JOIN dt_catalogues_categories_produits AS ccp ON ccp.id_catalogues_categories = cc.id AND ccp.id_produits = {$this->id}
WHERE cc.id_catalogues = {$id_catalogues}
ORDER BY id_parent DESC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$categories[] = $row;
			$id_categories = $row['id_parent'];
			while ($id_categories) {
				$q = <<<SQL
SELECT cc.id, cc.nom, cc.titre_url, cc.id_parent, cc.classement, cc.statut FROM dt_catalogues_categories AS cc
WHERE cc.id = {$id_categories} 
ORDER BY cc.classement ASC
SQL;
				$res = $this->sql->query($q);
				$row = $this->sql->fetch($res);
				$categories[] = $row;
				$id_categories = $row['id_parent'];
			}
		}
		return $categories;
	}

	public function catalogues($id_produits = null) {
		if ($id_produits === null) {
			$id_produits = $this->id;
		}
		$q = <<<SQL
SELECT DISTINCT(c.id) FROM dt_catalogues AS c
INNER JOIN dt_catalogues_categories AS cc ON cc.id_catalogues = c.id
INNER JOIN dt_catalogues_categories_produits AS ccp ON ccp.id_catalogues_categories = cc.id
WHERE ccp.id_produits = $id_produits
SQL;
		$res = $this->sql->query($q);
		$catalogues = array();
		while ($row = $this->sql->fetch($res)) {
			$catalogues[] = $row['id'];
		}

		return $catalogues;
	}

	public function image_hd($image_id) {
		return "prod_{$this->id}_{$image_id}";
	}

	public function attributs_ref() {
		$q = <<<SQL
SELECT DISTINCT a.ref FROM dt_attributs AS a
INNER JOIN dt_sku_attributs AS sa ON sa.id_attributs = a.id
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku AND sv.id_produits = {$this->id} 
WHERE ref != ''
SQL;
		$res = $this->sql->query($q);
		$refs = array();			
		while ($row = $this->sql->fetch($res)) {
			$refs[] = $row['ref'];
		}

		return $refs;
	}

	public function designations_auto($id_langues, $id = null) {
		if ($id === null) {
			$id = $this->id;
		}
		$q = <<<SQL
SELECT ph.phrase
FROM dt_produits AS p
INNER JOIN dt_phrases AS ph ON p.phrase_designation_auto = ph.id AND ph.id_langues = {$id_langues}
WHERE p.id = {$id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$pattern = $row['phrase'];

		$q = <<<SQL
SELECT id, ref FROM dt_attributs
WHERE ref != ''
SQL;
		$res = $this->sql->query($q);
		$attr_refs = array();
		while ($row = $this->sql->fetch($res)) {
			$attr_refs[$row['id']] = $row['ref'];
		}
		
		$q = <<<SQL
SELECT  sa.id_sku, sa.id_attributs, sa.valeur_numerique, ph.phrase, ar.field_label, ar.table_name, ar.field_value
FROM dt_sku_attributs AS sa
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = sa.phrase_valeur
LEFT OUTER JOIN dt_attributs_references AS ar ON ar.id_attributs = sa.id_attributs
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = sa.id_sku AND sv.id_produits = {$id}
SQL;
		$res = $this->sql->query($q);
		$valeurs_attributs = array();
		while ($row = $this->sql->fetch($res)) {
			if ($row['table_name'] and $row['field_label'] and $row['field_value']) {
				if (strpos($row['field_label'], "phrase_") === 0) {
					$q = <<<SQL
SELECT ph.phrase AS label FROM {$row['table_name']} AS t
LEFT OUTER JOIN dt_phrases AS ph ON t.{$row['field_label']} = ph.id AND ph.id_langues = {$id_langues}
WHERE t.{$row['field_value']} = {$row['valeur_numerique']}
SQL;
				}
				else {
					if ($row['field_value'][0] != ucfirst($row['field_value'][0])) {
						$row['field_value'] = "t.".$row['field_value'];
					}
					if ($row['field_label'][0] != ucfirst($row['field_label'][0])) {
						$row['field_label'] = "t.".$row['field_label'];
					}
					$q = <<<SQL
SELECT {$row['field_label']} AS label FROM {$row['table_name']} AS t
WHERE {$row['field_value']} = {$row['valeur_numerique']}
SQL;
				}
				$res2 = $this->sql->query($q);
				$row2 = $this->sql->fetch($res2);
				$valeurs_attributs[$row['id_sku']][$row['id_attributs']] = $row2['label'];
			}
			else {
				$valeurs_attributs[$row['id_sku']][$row['id_attributs']] = $row['phrase'] ? $row['phrase'] : $row['valeur_numerique'];
			}
		}
		
		$q = <<<SQL
SELECT s.id, ph.phrase
FROM dt_sku_variantes AS sv
INNER JOIN dt_sku AS s ON s.id = sv.id_sku
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = s.phrase_ultralog AND id_langues = {$id_langues}
WHERE sv.id_produits = {$id}
SQL;
		$res = $this->sql->query($q);
		$designations = array();
		while ($row = $this->sql->fetch($res)) {
			$q = <<<SQL
SELECT sa.id_attributs
FROM dt_sku_attributs AS sa
WHERE sa.id_sku = {$row['id']}
SQL;
			$res2 = $this->sql->query($q);
			$auto = $pattern;
			while ($row2 = $this->sql->fetch($res2)) {
				if (isset($attr_refs[$row2['id_attributs']]) and isset($valeurs_attributs[$row['id']][$row2['id_attributs']])) {
					$auto = str_replace("%".$attr_refs[$row2['id_attributs']], $valeurs_attributs[$row['id']][$row2['id_attributs']], $auto);
				}
			}
			$designations[$row['id']] = array(
				'actuelle' => $row['phrase'],
				'auto' => $auto,
			);
		}

		return $designations;
	}

	function phrases_dynamiques() {
		$phrases = $this->phrase->get($this->phrases());

		$phrases = $this->substitution_descriptions($phrases);
		$phrases = $this->substitution_attributs($phrases);

		return $phrases;
	}

	function substitution_descriptions($phrases) {
		$tokens = array();

		$q = <<<SQL
SELECT ph1.phrase AS description, ph2.phrase AS description_courte, l.code_langue FROM dt_applications AS a
INNER JOIN dt_produits AS p ON p.id_applications = a.id AND p.id = {$this->id}
LEFT OUTER JOIN dt_phrases AS ph1 ON ph1.id = a.phrase_produit_description
LEFT OUTER JOIN dt_phrases AS ph2 ON ph2.id = a.phrase_produit_description_courte AND ph2.id_langues = ph1.id_langues
LEFT OUTER JOIN dt_langues AS l ON l.id = ph1.id_langues
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			foreach (array('description', 'description_courte') as $field) {
				if ($row['code_langue']) {
					$tokens[$field][$row['code_langue']] = $row[$field];
				}
			}
		}

		return $this->substitutions_tokens($phrases, $tokens);
	}

	function substitution_attributs($phrases) {
		$tokens = array();

		$q = <<<SQL
SELECT  a.ref, pa.valeur_numerique, ph.phrase, l.code_langue, ar.field_label, ar.table_name, ar.field_value
FROM dt_attributs AS a
INNER JOIN dt_produits_attributs AS pa ON pa.id_attributs = a.id AND id_produits = {$this->id}
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = pa.phrase_valeur
LEFT OUTER JOIN dt_langues AS l ON l.id = ph.id_langues
LEFT OUTER JOIN dt_attributs_references AS ar ON ar.id_attributs = pa.id_attributs
WHERE a.ref <> ''
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			if ($row['table_name'] and $row['field_label'] and $row['field_value']) {
				if (strpos($row['field_label'], "phrase_") === 0) {
					$q = <<<SQL
SELECT ph.phrase AS label, l.code_langue FROM {$row['table_name']} AS t
INNER JOIN dt_phrases AS ph ON t.{$row['field_label']} = ph.id
INNER JOIN dt_langues AS l ON l.id = ph.id_langues
WHERE t.{$row['field_value']} = {$row['valeur_numerique']}
SQL;
				}
				else {
					if ($row['field_value'][0] != ucfirst($row['field_value'][0])) {
						$row['field_value'] = "t.".$row['field_value'];
					}
					if ($row['field_label'][0] != ucfirst($row['field_label'][0])) {
						$row['field_label'] = "t.".$row['field_label'];
					}
					$q = <<<SQL
SELECT {$row['field_label']} AS label FROM {$row['table_name']} AS t
WHERE {$row['field_value']} = {$row['valeur_numerique']}
SQL;
				}
				$res2 = $this->sql->query($q);
				while ($row2 = $this->sql->fetch($res2)) {
					if (isset($row2['code_langue'])) {
						$tokens[$row['ref']][$row2['code_langue']] = $row2['label'];
					}
					else {
						$tokens[$row['ref']] = $row2['label'];
					}
				}
			}
			else {
				if ($row['phrase']) {
					$tokens[$row['ref']][$row['code_langue']] = $row['phrase'];
				}
				else {
					$tokens[$row['ref']] = $row['valeur_numerique'];
				}
			}
		}

		return $this->substitutions_tokens($phrases, $tokens);
	}

# Ancienne personnalisation (un texte et/ou un fichier)
	public function personnalisation() {
		$personnalisation = array();
		$q = <<<SQL
SELECT * FROM dt_personnalisations_produits WHERE id_produits = {$this->id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$personnalisation[$row['type']] = array('has' => 1, 'libelle' => $row['libelle']);
		}

		return $personnalisation;
	}
	
# Nouvelle personnalisation (plusieurs textes et/ou plusieurs fichiers)
	function personnalisations($id_produits = null) {
		$personnalisations = array(
			'gabarits' => array(),
			'textes' => array(),
			'images' => array(),
		);
		if ($id_produits === null) {
			$id_produits = $this->id;
		}
		$q = <<<SQL
SELECT * FROM dt_produits_perso_gabarits WHERE id_produits = {$id_produits}
ORDER BY id
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$id_gabarit = $row['id'];
			$personnalisations['gabarits'][$id_gabarit] = $row;
			$personnalisations['textes'][$id_gabarit] = array();
			$personnalisations['images'][$id_gabarit] = array();
			$q = <<<SQL
SELECT * FROM dt_produits_perso_textes WHERE id_produits_perso_gabarits = {$id_gabarit}
ORDER BY id
SQL;
			$res2 = $this->sql->query($q);
			while ($row2 = $this->sql->fetch($res2)) {
				$personnalisations['textes'][$id_gabarit][$row2['id']] = $row2;
			}

			$q = <<<SQL
SELECT * FROM dt_produits_perso_images WHERE id_produits_perso_gabarits = {$id_gabarit}
ORDER BY id
SQL;
			$res2 = $this->sql->query($q);
			while ($row2 = $this->sql->fetch($res2)) {
				$personnalisations['images'][$id_gabarit][$row2['id']] = $row2;
			}
		}

		return $personnalisations;
	}

	function personnalisations_gabarits($id_produits = null) {
		if ($id_produits === null) {
			$id_produits = $this->id;
		}
		$q = <<<SQL
SELECT * FROM dt_produits_perso_gabarits WHERE id_produits = $id_produits
ORDER BY id
SQL;
		$gabarits = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$gabarits[$row['id']] = $row;
		}

		return $gabarits;
	}

	function save_personnalisations($data) {
		if (isset($data['personnalisations']['gabarits'])) {
			foreach ($data['personnalisations']['gabarits'] as $id_gabarit => $gabarit) {
				foreach ($data['phrases']['personnalisations']['gabarits'][$id_gabarit]['phrase_nom'] as $lang => $phrase) {
					$this->phrase->save($lang, $phrase, $gabarit['phrase_nom']);
				}
				$values = array();
				$values[] = "ref = '{$gabarit['ref']}'";
				if (isset($data['_FILES']['personnalisations']['name']['gabarits'][$id_gabarit]['apercu'])) {
					if ($name = $data['_FILES']['personnalisations']['name']['gabarits'][$id_gabarit]['apercu']) {
						$tmp_name = $data['_FILES']['personnalisations']['tmp_name']['gabarits'][$id_gabarit]['apercu'];
						preg_match("/(\.[^\.]*)$/", $name, $matches);
						$ext = $matches[1];
						$file_name = md5_file($tmp_name).$ext;
						move_uploaded_file($tmp_name, $data['dir_personnalisations'].$file_name);
						$values[] = "apercu = '$file_name'";
					}
				}
				$values_list = implode(",", $values);
				$q = <<<SQL
UPDATE dt_produits_perso_gabarits SET $values_list WHERE id = $id_gabarit
SQL;
				$this->sql->query($q);
			}
		}

		if (isset($data['personnalisations']['textes'])) {
			foreach ($data['personnalisations']['textes'] as $id_gabarit => $textes) {
				foreach ($textes as $id => $perso) {
					$values = array();
					foreach ($perso as $key => $value) {
						$values[] = "$key = ".(in_array($key, array('css', 'contenu')) ? "'$value'" : (int)$value);
					}
					$values_list = implode(",", $values);
					$q = <<<SQL
UPDATE dt_produits_perso_textes SET $values_list WHERE id = $id
SQL;
					$this->sql->query($q);
				}
			}
		}

		if (isset($data['personnalisations']['images'])) {
			foreach ($data['personnalisations']['images'] as $id_gabarit => $images) {
				foreach ($images as $id => $perso) {
					$values = array();
					foreach ($perso as $key => $value) {
						$values[] = "$key = ".(in_array($key, array('css', 'fichier', 'formats')) ? "'$value'" : (int)$value);
					}

					if (isset($data['_FILES']['personnalisations']['name']['images'][$id_gabarit][$id]['fichier'])) {
						if ($name = $data['_FILES']['personnalisations']['name']['images'][$id_gabarit][$id]['fichier']) {
							$tmp_name = $data['_FILES']['personnalisations']['tmp_name']['images'][$id_gabarit][$id]['fichier'];
							preg_match("/(\.[^\.]*)$/", $name, $matches);
							$ext = $matches[1];
							$file_name = md5_file($tmp_name).$ext;
							move_uploaded_file($tmp_name, $data['dir_personnalisations'].$file_name);
							$values[] = "fichier = '$file_name'";
						}
					}

					$values_list = implode(",", $values);
					$q = <<<SQL
UPDATE dt_produits_perso_images SET $values_list WHERE id = $id
SQL;
					$this->sql->query($q);
				}
			}
		}
	}

	function add_personnalisation_gabarit($data) {
		if (isset($data['new_personnalisation_gabarit'])) {
			$id_phrase = 0;
			foreach ($data['new_personnalisation_gabarit']['phrase_nom'] as $lang => $phrase) {
				$id_phrase = $this->phrase->save($lang, $phrase, $id_phrase);
			}
            
			$apercu = "";
			if (isset($data['_FILES']['new_personnalisation_gabarit']['name']['apercu'])) {
				if ($name = $data['_FILES']['new_personnalisation_gabarit']['name']['apercu']) {
					$tmp_name = $data['_FILES']['new_personnalisation_gabarit']['tmp_name']['apercu'];
					preg_match("/(\.[^\.]*)$/", $name, $matches);
					$ext = $matches[1];
					$apercu = md5_file($tmp_name).$ext;
					move_uploaded_file($tmp_name, $data['dir_personnalisations'].$apercu);
				}
			}
            
			$q = <<<SQL
INSERT INTO dt_produits_perso_gabarits (id_produits, ref, phrase_nom, apercu)
VALUES ({$this->id}, '{$data['new_personnalisation_gabarit']['ref']}', {$id_phrase}, '{$apercu}')
SQL;
			$this->sql->query($q);

			return $this->sql->insert_id();
		}
	}

	function add_personnalisation_texte($data) {
		if ($id_gabarit = $data['personnalisation_gabarit']) {
			if (isset($data['new_personnalisation_texte'])) {
				$fields = array('id_produits_perso_gabarits');
				$values = array($id_gabarit);
				foreach ($data['new_personnalisation_texte'][$id_gabarit] as $key => $value) {
					$fields[] = $key;
					$values[] = in_array($key, array('css', 'contenu')) ? "'$value'" : (int)$value;
				}
				$fields_list = implode(",", $fields);
				$values_list = implode(",", $values);
				$q = <<<SQL
INSERT INTO dt_produits_perso_textes ($fields_list) VALUES ($values_list)
SQL;
				$this->sql->query($q);
			}
		}
	}

	function add_personnalisation_image($data) {
		if ($id_gabarit = $data['personnalisation_gabarit']) {
			if (isset($data['new_personnalisation_image'])) {
				$fields = array('id_produits_perso_gabarits');
				$values = array($id_gabarit);
				foreach ($data['new_personnalisation_image'][$id_gabarit] as $key => $value) {
					$fields[] = $key;
					$values[] = in_array($key, array('css', 'formats')) ? "'$value'" : (int)$value;
				}

				$fichier_ok = false;
				if (isset($data['_FILES']['new_personnalisation_image']['name'][$id_gabarit]['fichier'])) {
					if ($name = $data['_FILES']['new_personnalisation_image']['name'][$id_gabarit]['fichier']) {
						$tmp_name = $data['_FILES']['new_personnalisation_image']['tmp_name'][$id_gabarit]['fichier'];
						preg_match("/(\.[^\.]*)$/", $name, $matches);
						$ext = $matches[1];
						$file_name = md5_file($tmp_name).$ext;
						move_uploaded_file($tmp_name, $data['dir_personnalisations'].$file_name);
						$fields[] = "fichier";
						$values[] = "'$file_name'";
						$fichier_ok = true;
					}
				}
				if (!$fichier_ok) {
					$fields[] = "fichier";
					$values[] = "''";
				}

				$fields_list = implode(",", $fields);
				$values_list = implode(",", $values);
				$q = <<<SQL
INSERT INTO dt_produits_perso_images ($fields_list) VALUES ($values_list)
SQL;
				$this->sql->query($q);
			}
		}
	}

	function delete_personnalisation_gabarit($data, $id) {
		$q = <<<SQL
DELETE FROM dt_produits_perso_gabarits WHERE id = $id
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_perso_textes WHERE id_produits_perso_gabarits = $id
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_perso_images WHERE id_produits_perso_gabarits = $id
SQL;
		$this->sql->query($q);
	}

	function delete_personnalisation_texte($data, $id) {
		$q = <<<SQL
DELETE FROM dt_produits_perso_textes WHERE id = $id
SQL;
		$this->sql->query($q);
	}

	function delete_personnalisation_image($data, $id) {
		$q = <<<SQL
DELETE FROM dt_produits_perso_images WHERE id = $id
SQL;
		$this->sql->query($q);
	}

	function display_personnalisation($images_url, $id_gabarit, $perso = array(), $nl_tag = false) {
		$html = "";

		$q = <<<SQL
SELECT id_produits FROM dt_produits_perso_gabarits WHERE id = $id_gabarit
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		if ($id_produits = $row['id_produits']) {
			$personnalisations = $this->personnalisations($id_produits);
			$textes = $personnalisations['textes'];
			$images = $personnalisations['images'];
			if ((isset($textes[$id_gabarit]) and count($textes[$id_gabarit]))
				or (isset($images[$id_gabarit]) and count($images[$id_gabarit]))) {

				$html = <<<HTML
<div class="personnalisation-produit personnalisation-produit-{$id_produits}" style="text-align: center;">
<div class="personnalisation-produit-element" style="display: inline-block; position: relative;">
HTML;
				foreach($personnalisations['textes'][$id_gabarit] as $id_texte => $texte) {
					$css = "";
					$css .= <<<CSS
position: absolute;
resize: none;
overflow: hidden;
color: black;
border: none;
box-sizing: border-box;
CSS;
					$css .= $texte['css'];
					$css .= preg_replace("/edit:[^;]*;/", "", $texte['css']);
					$css = preg_replace("/\s+/", " ", $css);
					$contenu = $texte['contenu'];
					if (isset($perso['textes'][$id_texte])) {
						$contenu = $perso['textes'][$id_texte];
					}
					if ($nl_tag) {
						$contenu = str_replace("\n", $nl_tag, $contenu);
					}
					$html .= <<<HTML
<textarea readonly disabled="disabled" class="personnalisation-produit-texte" style="{$css}">{$contenu}</textarea>
HTML;
				}
				foreach($personnalisations['images'][$id_gabarit] as $id_image => $image) {
					$css = "";
					if (!$image['background']) {
						$css .= "position: absolute;";
					}
					$apercu = $image['fichier'];
					if (isset($perso['images'][$id_image]['apercu'])) {
						$apercu = $perso['images'][$id_image]['apercu'];
					}
					$bg_size = $image['contain'] ? "contain" : "cover";
					$css .= <<<CSS
background-image: url({$images_url}{$apercu});
background-size: {$bg_size};
background-position: center;
background-repeat: no-repeat;
box-sizing: border-box;
CSS;
					$css .= $image['css'];
					$css .= preg_replace("/edit:[^;]*;/", "", $image['css']);
					$css = preg_replace("/\s+/", " ", $css);
					$html .= <<<HTML
<div class="personnalisation-produit-image" style="{$css}"></div>
HTML;
				}
				$html .= <<<HTML
</div>
</div>
HTML;
			}
		}
		return $html;
	}
}
