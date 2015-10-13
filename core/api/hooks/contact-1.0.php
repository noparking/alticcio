<?php

function get_contact_connect($api, $id_contacts_comptes = 0) {
	$contact = new API_Contact($api);
	$correspondants = $contact->idendify($_GET);
	switch (count($correspondants)) {
		case 0 :
			return $api->error(701, "Aucun contact ne correspond aux critères d'identification.");
		case 1 :
			$id_correspondant = $correspondants[0];
			break;
		default :
			return $api->error(702, "Plusieurs contacts correspondent aux critères d'identification.");	
	}

	if (!$contact->check_password($id_correspondant, $_GET['password'])) {
		return $api->error(703, "Mot de passe invalide.");
	}

	$comptes = $contact->find_account($id_correspondant);

	switch (count($correspondants)) {
		case 0 :
			return $api->error(704, "Aucun compte n'est rattaché à ce contact.");
		case 1 :
			$id_comptes = $comptes[0];
			break;
		default :
			if ($id_contacts_comptes) {
				if (in_array()) {

				}
				else {
					return $api->error(705, "Le compte #{$id_contacts_comptes} n'existe pas pour ce contact.");	
				}
			}
			else {
				$liste_ids = implode(", ", $comptes);
				return $api->error(706, "Plusieurs comptes sont rattachés à ce contact. Veuillez en choisir un parmi les suivants : $liste_ids.");	
			}
	}

	$infos = $contact->infos($id_correspondant, $id_compte);

	return $infos;
}
