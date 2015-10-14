<?php

function get_contact_connect($api, $compte = "") {
	$contact = new API_Contact($api);
	$correspondants = $contact->identify($_GET);
	switch (count($correspondants)) {
		case 0 :
			return $api->error(701, "Aucun contact ne correspond aux critères d'identification.");
		case 1 :
			$id_correspondant = current($correspondants);
			break;
		default :
			return $api->error(702, "Plusieurs contacts correspondent aux critères d'identification.");	
	}

	$password = isset($_GET['password']) ? $_GET['password'] : "";
	if (!$contact->check_password($id_correspondant, $password)) {
		return $api->error(703, "Mot de passe invalide.");
	}

	$comptes = $contact->find_account($id_correspondant);

	$infos = array();
	if ($compte) {
		if (in_array($compte, $comptes)) {
			$infos = $contact->infos($id_correspondant, array_search($compte, $comptes));
		}
		else {
			return $api->error(705, "Le compte {$compte} n'existe pas pour ce correspondant.");	
		}
	}
	else {
		switch (count($comptes)) {
			case 0 :
				return $api->error(704, "Aucun compte n'est rattaché à ce correspondant.");
			case 1 :
				$id_compte = key($comptes);
				$infos = $contact->infos($id_correspondant, $id_compte);
				break;
			default :
				$liste_comptes = implode(", ", $comptes);
				return $api->error(706, "Plusieurs comptes sont rattachés à ce correspondant. Veuillez en choisir un parmi les suivants : $liste_comptes.");	
		}
	}

	return $infos;
}
