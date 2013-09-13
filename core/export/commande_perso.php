<?php

require_once "abstract_export.php";

class ExportCommandePerso extends AbstractExport {
	
	public $export_table = "commandes_produits_perso";

	public function export() {
		$date_export = time();
		$fields = $this->fields();
		$this->prepare($fields);

		$values = array();
		$i = 1;
		foreach ($this->cmds_perso_a_exporter($date_export) as $cmd) {
			$values[] = $cmd;
			if ($i % 500 == 0) {
				$this->insert_values($fields, $values);
				$values = array();
			}
			$i++;
		}
		$this->insert_values($fields, $values);
	}

	public function fields() {
		$fields = array(
			'id',
			'id_commande',
			'id_produit',
			'id_sku',
			'ref',
			'nom',
			'quantite',
			'personnalisation_texte',
			'personnalisation_fichier',
			'date_commande',
			'date_export',
		);

		return $fields;
	}

	public function date_last_commande() {
		$q = <<<SQL
SELECT MAX(date_commande) AS date_last_commande FROM {$this->export_table}
SQL;
		$res = $this->sql_export->query($q);
		$row = $this->sql_export->fetch($res);

		return $row['date_last_commande'];
	}

# TODO Gérer les révisions
	public function cmds_perso_a_exporter($date_export) {
		$date_last_commande = $this->date_last_commande();
		$q = <<<SQL
SELECT cp.id, cp.id_commandes, cp.id_produits, cp.id_sku, cp.ref, cp.nom, cp.quantite,
cp.personnalisation_texte, cp.personnalisation_fichier, c.date_commande
FROM dt_commandes_produits AS cp
INNER JOIN dt_commandes AS c ON c.id = cp.id_commandes
WHERE (cp.personnalisation_texte <> "" OR cp.personnalisation_fichier <> "")
SQL;
		if ($date_last_commande) {
			$q .= " AND c.date_commande > $date_last_commande";
		}
		$res = $this->sql->query($q);
		$cmds = array();
		while ($row = $this->sql->fetch($res)) {
			$cmds[] = array(
				'id' => $row['id'],
				'id_commande' => $row['id_commandes'],
				'id_produit' => $row['id_produits'],
				'id_sku' => $row['id_sku'],
				'ref' => $row['ref'],
				'nom' => $row['nom'],
				'quantite' => $row['quantite'],
				'personnalisation_texte' => $row['personnalisation_texte'],
				'personnalisation_fichier' => $row['personnalisation_fichier'],
				'date_commande' => $row['date_commande'],
				'date_export' => $date_export,
			);
		}

		return $cmds;
	}

	public function prepare($fields) {
		$field_list = "";
		foreach ($fields as $field) {
			if (in_array($field, array("id", "id_commande", "id_produit", "id_sku", "quantite", "date_commande", "date_export"))) {
				$field_list .= "`$field` int(11) NOT NULL,";
			}
			else {
				$field_list .= "`$field` mediumtext NOT NULL,";
			}
		}
		$q = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->export_table}` (
  $field_list
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
SQL;
		$this->sql_export->query($q);
	}
}
