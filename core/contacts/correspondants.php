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

	public function save($data) {
		if (isset($data['correspondant']['password'])) {
			if ($data['correspondant']['password']) {
				$data['correspondant']['password'] = $this->hash_password($data['correspondant']['password']);
				$data['correspondant']['date_password'] = time();
			}
			else if (isset($data['correspondant']['id']) and $data['correspondant']['id']) {
				unset($data['correspondant']['password']);
			}
		}
		$data['keep']['comptes'] = true;
		$id = parent::save($data);

		if (!isset($data['save_again']) and isset($data['donnees'])) {
			foreach ($data['donnees'] as $id_donnee => $donnee) {
				if ($id_donnee) {
					if ($donnee['valeur']) {
						$q = <<<SQL
UPDATE dt_contacts_correspondants_donnees
SET id_contacts_correspondants = {$id},
id_contacts_donnees = {$donnee['id_contacts_donnees']},
valeur = '{$donnee['valeur']}',
statut = {$donnee['statut']}
WHERE id = {$id_donnee}
SQL;
						$this->sql->query($q);
					}
					else {
						$q = <<<SQL
DELETE FROM dt_contacts_correspondants_donnees WHERE id = $id_donnee
SQL;
						$this->sql->query($q);
					}
				}
				else if ($donnee['valeur']) {
					$q = <<<SQL
INSERT INTO dt_contacts_correspondants_donnees (id_contacts_correspondants, id_contacts_donnees, valeur, statut)
VALUES ({$id}, {$donnee['id_contacts_donnees']}, '{$donnee['valeur']}', {$donnee['statut']})
SQL;
					$this->sql->query($q);
				}
			}
		}

		return $id;
	}

	public function delete($data) {
		if (isset($data[$this->type]['id'])) {
			$q = <<<SQL
DELETE FROM dt_contacts_correspondants_donnees WHERE id_contacts_correspondants = {$data[$this->type]['id']}
SQL;
			$this->sql->query($q);
		}
		parent::delete($data);
	}

	public function donnees($statut = null) {
		$q = <<<SQL
SELECT id_contacts_donnees, valeur, COUNT(DISTINCT id_contacts_correspondants) AS nb FROM dt_contacts_correspondants_donnees
WHERE id_contacts_correspondants <> {$this->id}
GROUP BY id_contacts_donnees, valeur
SQL;
		$doublons = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$doublons[$row['id_contacts_donnees']][$row['valeur']] = $row['nb'];
		}

		$q = <<<SQL
SELECT ccd.id, ccd.id_contacts_donnees, ccd.valeur, ccd.statut, cd.nom FROM dt_contacts_correspondants_donnees AS ccd
INNER JOIN dt_contacts_donnees AS cd ON cd.id = ccd.id_contacts_donnees
WHERE ccd.id_contacts_correspondants = {$this->id}
ORDER BY ccd.id ASC
SQL;
		if ($statut) {
			$q .= <<<SQL
AND cd.statut = 1 AND ccd.statut = 1
SQL;
		}
		$res = $this->sql->query($q);
		$donnees = array();
		while ($row = $this->sql->fetch($res)) {
			$donnees[$row['id']] = $row;
			$donnees[$row['id']]['doublon'] = 0;
			if (isset($doublons[$row['id_contacts_donnees']][$row['valeur']])) {
				$donnees[$row['id']]['doublon'] = $doublons[$row['id_contacts_donnees']][$row['valeur']];
			}
		}

		return $donnees;
	}

	public function hash_password($password) {
		$salt = bin2hex(openssl_random_pseudo_bytes(22));

		return crypt($password, '$2y$12$'.$salt);
	}
}

