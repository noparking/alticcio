<?php

require_once "abstract_export.php";

class ExportCatalogue extends AbstractExport {

	public $excluded;
	public $num_pages;
	public $export_table = "catalogue";

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
				$this->prepare($fields, $id_catalogues);
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

	function prepare($fields, $id_catalogues) {
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
CREATE TABLE IF NOT EXISTS `{$this->export_table}` (
  $field_list
  PRIMARY KEY (`id_catalogue`,`id_produit`,`id_sku`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
SQL;
		$this->sql_export->query($q);

		$q = <<<SQL
SELECT id_produit, id_sku, num_pages FROM catalogue WHERE id_catalogue = '$id_catalogues'
SQL;
		$res = $this->sql_export->query($q);
		$this->num_pages = array();
		while ($row = $this->sql_export->fetch($res)) {
			$this->num_pages[$row['id_produit']][$row['id_sku']] = $row['num_pages'];
		}

		$q = <<<SQL
DELETE FROM `{$this->export_table}` WHERE id_catalogue = '$id_catalogues'
SQL;
		$this->sql_export->query($q);

		$this->excluded = $excluded;
	}
}
