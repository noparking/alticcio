<?php

class API_Contact {
	public function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;
	}

	public function check_password($id_correspondant, $hash) {
		$q = <<<SQL
SELECT `password` FROM dt_contacts_correspondants WHERE id = {$id_correspondant}
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return crypt($password, $hash) == $hash;
		}
		
		return $false;
	}

	public function identify($params) {
		$correspondants = array();
		foreach ($params as $key => $value) {
			$key = addslashes($key);
			$value = addslashes($value);
			$q = <<<SQL
SELECT id_contacts_correspondants AS id FROM dt_contacts_correspondants_donnees AS ccd
INNER JOIN dt_contacts_donnees AS cd ON cd.id = ccd.id_contacts_donnees
INNER JOIN dt_contacts_correspondants AS cc ON cc.id = ccd.id_contacts_correspondants
WHERE cd.nom = '$key' AND ccd.valeur = '$value'
AND cd.statut = 1 AND cc.statut = 1 AND ccd.statut = 1
SQL;
			$res = $this->sql->query($q);
			while ($row = this->sql->fetch($res)) {
				$correspondants[$row['id']] = $row['id'];
			}

			return $correspondants;
	}

	public function find_account($id_correspondant) {
		$comptes = array();
		$q = <<<SQL
SELECT id_contacts_comptes AS id FROM dt_contacts_correspondants_comptes
WHERE id_contacts_correspondants = $id_correspondant AND statut = 1
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$comptes[$row['id']] = $row['id'];
		}

		return $comptes;
	}

	public function infos($id_correspondant, $id_compte) {
		$q = <<<SQL
SELECT cpt.id, cc.nom, cc.prenom, cpt.nom AS compte, co.nom AS organisation,
co.email AS organisation_email, co.www AS organisation_www,
cd.nom AS donnee_nom, ccd.valeur AS donnee_valeur
FROM dt_contacts_correspondants_comptes AS ccc
INNER JOIN dt_contacts_comptes AS cpt ON cpt.id = ccc.id_contacts_comptes
INNER JOIN dt_contacts_correspondants AS cc ON cc.id = ccc.id_contacts_correspondants
INNER JOIN dt_contacts_organisations AS co ON co.id = cpt.id_contacts_organisations
LEFT OUTER JOIN dt_contacts_correspondants_donnees AS ccd ON ccd.id_contacts_correspondants = cc.id
LEFT OUTER JOIN dt_contacts_donnees AS cd ON cd.id = ccd.id_contacts_donnees
WHERE ccc.id_contacts_comptes = {$id_compte} AND ccc.id_contacts_correspondants = $id_correspondant
AND cc.statut = 1 AND ccc.statut = 1 AND cpt.statut = 1
SQL;
		$infos = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			if (!isset($infos[$row['id']])) {
				$infos[$row['id']] = array(
					'organisation' => array(
						'nom' => $row['organisation_nom'],
						'email' => $row['organisation_email'],
						'www' => $row['organisation_www'],
					),
					'correspondant' => array(
						'nom' => $row['nom'],
						'prenom' => $row['prenom'],
					),
					'compte' => $row['compte'],
				);
			}
			$infos[$row['id']]['correspondants'][$row['donnee_nom']] = $row['donnee_valeur'];
		}

		return $infos;
	}
}
