<?php

require_once "abstract_contact.php";

class Correspondant extends AbstractContact {

	public $type = "correspondant";
	public $types = "correspondants";
	public $table = "dt_contacts_correspondants";
	public $id_field = "id_contacts_correspondants";
	public $links = array(
		'organisations' => array("organisations", "correspondants"),
		'comptes' => array("correspondants", "comptes"),
	);
	public $table_links_organisations = "dt_contacts_organisations_correspondants";
	public $table_links_correspondants = null;
	public $table_links_comptes = "dt_contacts_correspondants_comptes";

	public function liste($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}
		$q = <<<SQL
SELECT cc.id, cc.nom, cc.prenom, cc.statut,
GROUP_CONCAT(DISTINCT co.nom ORDER BY co.nom ASC SEPARATOR ', ') AS organisations
FROM dt_contacts_correspondants AS cc
LEFT OUTER JOIN dt_contacts_organisations_correspondants AS coc ON coc.id_contacts_correspondants = cc.id
LEFT OUTER JOIN dt_contacts_organisations AS co ON co.id = coc.id_contacts_organisations
WHERE 1
GROUP BY cc.id
SQL;
		$res = $filter->query($q);
		$correspondants = array();
		while ($row = $filter->fetch($res)) {
			$correspondants[$row['id']] = $row;
		}

		return $correspondants;
	}

	public function organisations_correspondants() {
		return $this->links("dt_contacts_organisations_correspondants", "id_contacts_organisations");
	}
}

