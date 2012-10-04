<?php

function get_variante($api, $ref) {
	$sku = new API_Sku();
	$produit = new API_Produit($api->sql);
	if ($id_sku = $sku->get_id_by_ref($ref)) {
		$fiches = array();
		foreach ($sku->vary_from($id_sku) as $id_produits) {
			$fiches[] = $produit->fiche($id_produits);
		}
		switch (count($fiches)) {
			case 0 : return $api->error(202); // aucun produit n'a cette référence pour variante
			case 1 : return $fiches[0];
			default : return $fiches;
		}
	}
	else {
		return $api->error(201); // référence invalide
	}
}
