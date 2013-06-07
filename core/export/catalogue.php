<?php

class ExportCatalogue {

	public $sql;
	public $sql_export;
	public $excluded;
	public $num_pages;

	public function __construct($sql, $sql_export) {
		$this->sql = $sql;
		$this->sql_export = $sql_export;
	}

	public function export($config, $id_catalogues) {
		$q = <<<SQL
SELECT MAX(date_export) AS date_export FROM dt_exports_catalogues WHERE etat = 'built' AND id_catalogues = $id_catalogues
SQL;

		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$date_export = $row['date_export'];

		$q = <<<SQL
SELECT ec.fichier, c.nom FROM dt_exports_catalogues AS ec
INNER JOIN dt_catalogues AS c ON c.id = ec.id_catalogues
WHERE ec.date_export = $date_export AND ec.id_catalogues = $id_catalogues
SQL;

		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$nom_catalogue = $row['nom'];
		$nom_fichier = $row['fichier'];


		$file = fopen($config->get("medias_path")."www/exports/catalogues/".$nom_fichier, 'r');

		$i = 0;
		$fields = array();
		$values = array();
		$excluded = array();
		while ($csv = fgetcsv($file)) {
			if ($i == 0) {
				$csv[] = "id_catalogue";
				$csv[] = "catalogue";
				$csv[] = "num_pages";
				$fields = $csv;
				$this->prepare($fields, $nom_catalogue);
			}
			else {
				$csv[] = $id_catalogues;
				$csv[] = $nom_catalogue;
				$csv[] = isset($this->num_pages[$csv[$this->id_produit]][$csv[$this->id_sku]]) ? $this->num_pages[$csv[$this->id_produit]][$csv[$this->id_sku]] : "";
				$values[] = $csv;
				if ($i % 500 == 0) {
					$this->insert_values($fields, $values);
					$values = array();
				}
			}
			$i++;
		}
		$this->insert_values($fields, $values);

		fclose($file);
	}

	function prepare($fields, $catalogue) {
		$field_list = "";
		$excluded = array();
		foreach ($fields as $i => $field) {
			if ($field != "attributs_fiche") {
				if ($field != "id_produit" and $field != "id_sku" and $field != "id_catalogue") {
					$field_list .= "`$field` mediumtext NOT NULL,";
				}
				else {
					$field_list .= "`$field` int(11) NOT NULL,";
					$this->$field = $i;
				}
			}
			else {
				$excluded[] = $i;
			}
		}
		$q = <<<SQL
CREATE TABLE IF NOT EXISTS `catalogue` (
  $field_list
  PRIMARY KEY (`id_catalogue`,`id_produit`,`id_sku`)
)
SQL;
		$this->sql_export->query($q);

		$q = <<<SQL
SELECT id_produit, id_sku, num_pages FROM catalogue
SQL;
		$res = $this->sql_export->query($q);
		$this->num_pages = array();
		while ($row = $this->sql_export->fetch($res)) {
			$this->num_pages[$row['id_produit']][$row['id_sku']] = $row['num_pages'];
		}

		$q = <<<SQL
DELETE FROM catalogue WHERE catalogue = '$catalogue'
SQL;
		$this->sql_export->query($q);

		$this->excluded = $excluded;
	}

	function insert_values($fields, $values) {
		$fields_list = array();
		foreach ($fields as $i => $field) {
			if (!in_array($i, $this->excluded)) {
				$fields_list[] = $field;
			}
		}
		$fields_list = implode(",", $fields_list);

		$values_list = array();
		foreach ($values as $value) {
			$value_list = array();
			foreach ($value as $i => $data) {
				if (!in_array($i, $this->excluded)) {
					$value_list[] = addslashes($data);
				}
			}
			$values_list[] = "('".implode("','", $value_list)."')";
		}
		$values_list = implode(",", $values_list);
		$q = <<<SQL
INSERT INTO catalogue ($fields_list) VALUES $values_list
SQL;
		$this->sql_export->query($q);
	}
}
