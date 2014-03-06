<?php

require_once "abstract_export.php";

class ExportMatiere extends AbstractExport {

	public $export_table = "fiches_matieres";

	private $applications = array();

	public function __construct($sql, $sql_export) {
		parent::__construct($sql, $sql_export);
		$q = <<<SQL
SELECT a.id, ph.phrase, ph.id_langues
FROM dt_applications AS a
INNER JOIN dt_phrases AS ph ON ph.id = a.phrase_nom
SQL;
		$res = $this->sql->query($q);
		$this->applications = array();
		while ($row = $this->sql->fetch($res)) {
			$this->applications[$row['id']][$row['id_langues']] = $row['phrase'];
		}
	}

	public function export() {
		$date_export = time();
		$fields = $this->fields();
		$this->prepare($fields);

		$matieres = $this->matieres_a_exporter();
		if (count($matieres)) {
			$ids_matieres = array();
			foreach($matieres as $matiere_data) {
				$ids_matieres[] = $matiere_data['id'];
			}
			$ids_matieres_list = implode(",", $ids_matieres);

			$q = <<<SQL
DELETE FROM `{$this->export_table}` WHERE id_matiere IN ($ids_matieres_list)
SQL;
			$this->sql_export->query($q);
		}

		if (count($matieres)) {
			$ids_matieres = array();
			$value_list = array();
			foreach($matieres as $matiere_data) {
				$ids_matieres[] = $matiere_data['id'];
				$value_list[] = "({$matiere_data['id']}, {$date_export})";
			}
			$ids_matieres_list = implode(",", $ids_matieres);

			$q = <<<SQL
DELETE FROM dt_exports_matieres WHERE id_matieres IN ($ids_matieres_list) 
SQL;
			$this->sql->query($q);

			$values_list = implode(",", $value_list); 
			$q = <<<SQL
INSERT INTO dt_exports_matieres (id_matieres, date_export) VALUES $values_list
SQL;
			$this->sql->query($q);

			$values = array();
			$i = 1;
			foreach ($this->data($matieres) as $data) {
				$values[] = $data;
				if ($i % 200 == 0) {
					$this->insert_values($fields, $values);
					$values = array();
				}
				$i++;
			}
			$this->insert_values($fields, $values);
		}
	}

	public function fields() {
		$fields = array(
			'id_langue',
			'code_langue',
			'id_matiere',
			'ref_matiere',
			'nom',
			'description_courte',
			'description',
			'entretien',
			'marques_fournisseurs',
			'attributs_matieres',
			'id_applications',
			'applications',
		);
		for ($i = 1; $i <= $this->max_images(); $i++) {
			$fields[] = "image$i";
		}

		return $fields;
	}

	function data($matieres) {
		$phrase = new Phrase($this->sql);
		$matiere = new Matiere($this->sql, $phrase);

		$data_lignes = array();
		
		foreach ($this->langues() as $id_langues => $code_langue) {
			foreach ($matieres as $data) { 	
				$matiere->load($data['id']);
				$matiere_values = $matiere->values;
				$matiere_phrases = $phrase->get($matiere->phrases());

				$applications = $this->applications($data['id'], $id_langues);


				$data_ligne = array(
					$id_langues,
					$code_langue,
					$matiere_values['id'],
					$matiere_values['ref_matiere'],
					$this->phrase('phrase_nom', $matiere_phrases, $code_langue),
					$this->phrase('phrase_description_courte', $matiere_phrases, $code_langue),
					$this->phrase('phrase_description', $matiere_phrases, $code_langue),
					$this->phrase('phrase_entretien', $matiere_phrases, $code_langue),
					$this->phrase('phrase_marques_fournisseurs', $matiere_phrases, $code_langue),

					$this->attributs($matiere->attributs_data(), $matiere_phrases, $code_langue),
					
					implode("\n", array_keys($applications)),
					implode("\n", $applications),
				);
				$images = $this->images($matiere, $this->max_images());
				$data_ligne = array_merge($data_ligne, $images);
				$data_lignes[] = $data_ligne;
			}
		}

		return $data_lignes;
	}

	public function matieres_a_exporter() {
		$q = <<<SQL
SELECT DISTINCT(id_matiere) FROM {$this->export_table}
SQL;
		$already_exported_matieres = array();
		$res = $this->sql_export->query($q);
		while ($row = $this->sql_export->fetch($res)) {
			$already_exported_matieres[] = $row['id_matiere'];
		}
		if (count($already_exported_matieres)) {
			$already_exported_matieres = implode(",", $already_exported_matieres);
			$old_date_export = time() - 3600;

			$q = <<<SQL
DELETE FROM dt_exports_matieres WHERE id_matieres NOT IN ($already_exported_matieres) AND date_export < $old_date_export
SQL;
			$this->sql->query($q);
		}

		$q = <<<SQL
SELECT m.id, em.date_export, m.date_modification
FROM dt_matieres AS m
LEFT OUTER JOIN dt_exports_matieres AS em ON em.id_matieres = m.id
GROUP BY m.id
HAVING em.date_export IS NULL
OR em.date_export < date_modification
LIMIT 200
SQL;
		$res = $this->sql->query($q);
		$matieres = array();
		while ($row = $this->sql->fetch($res)) {
			$matieres[] = array(
				'id' => $row['id'],
				'last_update' => $row['date_modification'],
				'last_export' => $row['date_export'],
			);
		}

		return $matieres;

	}
	
	public function prepare($fields) {
		$field_list = "";
		foreach ($fields as $i => $field) {
			if ($field != "id_matiere" and $field != "id_langue") {
				$field_list .= "`$field` mediumtext NOT NULL,";
			}
			else {
				$field_list .= "`$field` int(11) NOT NULL,";
				$this->$field = $i;
			}
		}
		$q = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->export_table}` (
  $field_list
  PRIMARY KEY (`id_langue`,`id_matiere`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
SQL;
		$this->sql_export->query($q);
	}

	function max_images() {
		return 10; // POur le moment, on fixe la valeur à 10
	}

	function applications($id_matieres, $id_langues) {
		$q = <<<SQL
SELECT a.id, p.phrase AS name FROM dt_applications AS a
INNER JOIN dt_matieres_applications AS ma ON ma.id_applications = a.id AND id_matieres = $id_matieres
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$id_langues}
ORDER BY name
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[$row['id']] = $row['name'];
		}
		
		return $liste;
	}

}
