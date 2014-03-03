<?php

require_once "abstract_object.php";

class Matiere extends AbstractObject {

	public $type = "matiere";
	public $table = "dt_matieres";
	public $images_table = "dt_images_matieres";
	public $phrase_fields = array('phrase_nom', 'phrase_description_courte', 'phrase_description', 'phrase_entretien', 'phrase_marques_fournisseurs');

	public function liste($id_langues, &$filter = null) {
		$q = <<<SQL
SELECT m.id, m.ref_matiere, p.phrase 
FROM dt_matieres AS m
LEFT OUTER JOIN dt_familles_matieres AS f ON f.id = m.id_familles_matieres 
LEFT OUTER JOIN dt_phrases AS p ON p.id = f.phrase_nom AND p.id_langues = $id_langues
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
	
	public function familles_matieres($id_langues) {
		$q = "SELECT f.id, p.phrase FROM dt_familles_matieres AS f INNER JOIN dt_phrases AS p ON p.id = f.phrase_nom AND p.id_langues = ".$id_langues;
		$res = $this->sql->query($q);
		$familles = array('...');
		while($row = $this->sql->fetch($res)) {
			$familles[$row['id']] = $row['phrase'];
		}
		return $familles;
	}

	public function famille_matiere($id_langues) {
		$familles = $this->familles_matieres($id_langues);
		return $familles[$this->values['id_familles_matieres']];
	}

	public function ecolabels($id_langues) {
		$q = "SELECT e.id, p.phrase FROM dt_ecolabels AS e INNER JOIN dt_phrases AS p ON p.id = e.phrase_nom AND p.id_langues = ".$id_langues;
		$res = $this->sql->query($q);
		$ecolabels = array('...');
		while($row = $this->sql->fetch($res)) {
			$ecolabels[$row['id']] = $row['phrase'];
		}
		return $ecolabels;
	}

	public function ecolabel($id_langues) {
		$ecolabels = $this->ecolabels($id_langues);
		return $ecolabels[$this->values['id_ecolabels']];
	}

	public function recyclages($id_langues) {
		$q = "SELECT r.id, p.phrase 
				FROM dt_recyclage AS r 
				LEFT JOIN dt_phrases AS p 
				ON p.id = r.phrase_nom
				AND p.id_langues = ".$id_langues;
		$res = $this->sql->query($q);
		$recycle = array('...');
		while($row = $this->sql->fetch($res)) {
			$recycle[$row['id']] = $row['phrase'];
		}
		return $recycle;
	}

	public function recyclage($id_langues) {
		$recyclages = $this->recyclages($id_langues);
		return $recyclages[$this->values['id_recyclage']];
	}
	
	public function all_attributs() {
		$q = <<<SQL
SELECT a.id FROM dt_attributs AS a WHERE a.matiere = TRUE
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while($row = $this->sql->fetch($res)) {
			$liste[] = $row['id'];
		}
		
		return $liste;
	}

	public function attributs() {
		$attributs = array();
		$q = <<<SQL
SELECT id_attributs, valeur_numerique, phrase_valeur, classement FROM dt_matieres_attributs
WHERE id_matieres = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$value = $row['phrase_valeur'] ?  $row['phrase_valeur'] : $row['valeur_numerique'];
			$attributs[$row['id_attributs']][$row['classement']] = $value;
		}

		return $attributs;
	}

	public function attributs_names() {
		$attributs = array();
		$q = <<<SQL
SELECT a.phrase_nom, ma.id_attributs FROM dt_matieres_attributs AS ma
INNER JOIN dt_attributs AS a ON a.id = ma.id_attributs
WHERE ma.id_matieres = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$attributs[$row['id_attributs']] = $row['phrase_nom'];
		}

		return $attributs;
	}

	public function attributs_data() {
		$attributs = array();
		$q = <<<SQL
SELECT a.phrase_nom, ma.id_attributs, ma.valeur_numerique, ma.phrase_valeur FROM dt_matieres_attributs AS ma
INNER JOIN dt_matieres AS m ON ma.id_matieres = m.id
INNER JOIN dt_attributs AS a ON a.id = ma.id_attributs
WHERE ma.id_matieres = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$attributs[] = $row;
		}

		return $attributs;
	}

	public function fiche_attributs($attribut, $langue) {
		$infos = array();
		$phrases = $this->phrase->get($this->phrases());
		foreach ($this->attributs_data() as $data) {
			$attribut->load($data['id_attributs']);

			$unites = $attribut->unites();
			$unite = null;
			if ($attribut->values['id_unites_mesure']) {
				$unite = $unites[$attribut->values['id_unites_mesure']];
			}

			$types = $attribut->types();
			$type = $types[$attribut->values['id_types_attributs']];
			

			if ($data['phrase_valeur']) {
				$valeur = $phrases['valeurs_attributs'][$data['id_attributs']][$langue];
			}
			else {
				$valeur = $data['valeur_numerique'];
			}

			$infos[] = array(
				'nom' => $phrases['attributs'][$data['id_attributs']][$langue],
				'valeur' => $valeur,
				'type' => $type,
				'unite' => $unite,
			);
		}
		return $infos;
	}
	
	public function liste_attributs($id_langues) {
		$q = "SELECT ma.id_attributs, ma.valeur_numerique, ma.phrase_valeur, p.phrase, um.unite, a.id_types_attributs 
				FROM dt_matieres_attributs AS ma
				INNER JOIN dt_attributs AS a ON a.id = ma.id_attributs
				LEFT OUTER JOIN dt_unites_mesure AS um ON um.id = a.id_unites_mesure
				INNER JOIN dt_phrases AS p ON p.id = a.phrase_nom
				AND p.id_langues = ".$id_langues."
				AND ma.id_matieres =  ".$this->id;
		$res = $this->sql->query($q);
		$html = '<ul>';
		while ($row = $this->sql->fetch($res)) {
			$value = $row['phrase_valeur'] ?  $row['phrase_valeur'] : $row['valeur_numerique'];
			$html .= '<li><strong>'.$row['phrase'].'</strong> : ';
			if ($row['phrase_valeur'] > 0) {
				$qph = "SELECT * FROM dt_phrases WHERE id_langues = ".$id_langues." AND id = ".$row['phrase_valeur'];
				$rsph = $this->sql->query($qph);
				$rowph = $this->sql->fetch($rsph);
				$html .= $rowph['phrase'];
			}
			else {
				switch($row['id_types_attributs']) {
					case 1:
						// un choix O/N
						break;
					case 4:
						// un nombre
						$html .= $row['valeur_numerique'];
						break;
					case 2:
						// une note
						break;
					case 5:
						// une phrase d'un select
						break;
				}
			}
			if (!empty($row['unite'])) {
				$html .= ' '.$row['unite'];
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	public function applications() {
		$matiere_id = isset($this->id) ? $this->id : 0;
		$q = <<<SQL
SELECT a.id, p.phrase AS name, ma.id AS checked FROM dt_applications AS a
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
LEFT OUTER JOIN dt_matieres_applications AS ma ON ma.id_applications = a.id AND id_matieres = $matiere_id
ORDER BY name
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$row['checked'] = $row['checked'] ? true : false;
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}
	
	public function liste_applications($id_langues) {
		$q = "SELECT ma.id_matieres, ma.id_applications, p.phrase 
				FROM dt_matieres_applications AS ma 
				INNER JOIN dt_applications AS ap ON ap.id = ma.id_applications
				INNER JOIN dt_phrases AS p ON p.id = ap.phrase_nom
				AND p.id_langues = ".$id_langues."
				AND ma.id_matieres = ".$this->id;
		$res = $this->sql->query($q);
		$html = '<ul>';
		while ($row = $this->sql->fetch($res)) {
			$html .= '<li>'.$row['phrase'].'</li>';
		}
		$html .= '</ul>';
		return $html;
	}

	public function save($data) {
		$data['matiere']['date_modification'] = $_SERVER['REQUEST_TIME'];
		$id = parent::save($data);
		if (isset($data['attributs'])) {
			$q = <<<SQL
DELETE FROM dt_matieres_attributs WHERE id_matieres = $id
SQL;
			$this->sql->query($q);

			ksort($data['attributs']);
			foreach ($data['attributs'] as $attribut_id => $valeurs) {
				foreach ($valeurs as $classement => $valeur) { 
					$type_valeur = "valeur_numerique";
					if (isset($data['phrases']['attributs'][$attribut_id])) {
						$type_valeur = "phrase_valeur";
						if (is_array($data['phrases']['attributs'][$attribut_id])) {
							foreach ($data['phrases']['attributs'][$attribut_id] as $lang => $phrase) {
								$valeur = $this->phrase->save($lang, $phrase, $attribut);
							}
						}
					}
					else {
						$valeur = (float)str_replace(" ", "", str_replace(",", ".", $valeur));
					}
					$q = <<<SQL
INSERT INTO dt_matieres_attributs (id_attributs, id_matieres, $type_valeur, classement)
VALUES ($attribut_id, $id, $valeur, $classement)
SQL;
					$this->sql->query($q);
				}
			}
		}

		if (isset($data['applications'])) {
			$q = <<<SQL
DELETE FROM dt_matieres_applications WHERE id_matieres = $id
SQL;
			$this->sql->query($q);
			foreach ($data['applications'] as $application_id => $application) {
				if ($application['checked']) {
					$q = <<<SQL
INSERT INTO dt_matieres_applications (id_applications, id_matieres)
VALUES ($application_id, $id)
SQL;
					$this->sql->query($q);
				}
			}
		}

		return $id;
	}

	public function delete($data) {
		$id = parent::delete($data);

		$q = "DELETE FROM dt_matieres_attributs WHERE id_matieres = {$this->id}";
		$this->sql->query($q);
	}
	
	
	public function fiche($user_id, $default) {
		$q = <<<SQL
SELECT * FROM dt_fiches_produits WHERE id_users = $user_id ORDER BY classement
SQL;
		$res = $this->sql->query($q);
		$fiche = array();
		while ($row = $this->sql->fetch($res)) {
			$fiche[$row['zone']][$row['element']] = array('name' => $row['element']);;
		}

		if (!count($fiche)) {
			foreach ($default as $zone => $elements) {
				$fiche[$zone] = array();
				foreach ($elements as $element) {
					$fiche[$zone][$element] = array('name' => $element);
				}
			}
		}
		foreach ($fiche as $zone => $data) {
			$i = 0;
			foreach ($data as $element => $tab) {
				$fiche[$zone][$element]['classement'] = $i;
				$fiche[$zone][$element]['zone'] = $zone;
				$i++;
			}
		}

		return $fiche;
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
		foreach ($attributs as $attribut) {
			if ($attribut['phrase_valeur']) {
				$ids['valeurs_attributs'][$attribut['id_attributs']] = $attribut['phrase_valeur'];
			}
		}
		return $ids;
	}
}
