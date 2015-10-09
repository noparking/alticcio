<?php

require_once "abstract_contact.php";

class Compte extends AbstractContact {

	public $type = "compte";
	public $types = "comptes";
	public $table = "dt_contacts_comptes";
	public $id_field = "id_contacts_comptes";
	public $links = array(
		'correspondants' => array("correspondants", "comptes"),
	);
	public $table_links_organisations = null;
	public $table_links_correspondants = "dt_contacts_correspondants_comptes";
	public $table_links_comptes = null;

	public function liste($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}
		$q = <<<SQL
SELECT ccpt.id, ccpt.nom, ccpt.statut,
GROUP_CONCAT(DISTINCT co.nom ORDER BY co.nom ASC SEPARATOR ', ') AS organisations,
GROUP_CONCAT(DISTINCT CONCAT(cc.nom, ' ', cc.prenom) ORDER BY cc.nom ASC, cc.prenom ASC SEPARATOR ', ') AS correspondants
FROM dt_contacts_comptes AS ccpt
LEFT OUTER JOIN dt_contacts_organisations AS co ON co.id = ccpt.id_contacts_organisations
LEFT OUTER JOIN dt_contacts_correspondants_comptes AS ccc ON ccc.id_contacts_comptes = ccpt.id
LEFT OUTER JOIN dt_contacts_correspondants AS cc ON cc.id = ccc.id_contacts_correspondants
WHERE 1
GROUP BY ccpt.id
SQL;
		$res = $filter->query($q);
		$comptes = array();
		while ($row = $filter->fetch($res)) {
			$comptes[$row['id']] = $row;
		}

		return $comptes;
	}

	public function correspondants_comptes() {
		return $this->links("dt_contacts_correspondants_comptes", "id_contacts_correspondants");
	}
}
