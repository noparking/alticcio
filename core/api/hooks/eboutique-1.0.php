<?php

function get_eboutique_category($api, $id_catalogue, $id_category, $prices = "HT", $limit = 0, $offset = 0) {
	$catalogue = new API_Catalogue($api);
	if ($name = $catalogue->categorie_name($id_category)) {
		$api->track("category", $id_category);
		$ret = $catalogue->produits($id_category, $limit, $offset, $id_catalogue);
		$ret['id'] = $id_category;
		$ret['bloc'] = $catalogue->bloc($id_category);
		$ret['name'] = $name;
		$ret['prices'] = $prices;
		return $ret;
	}
	else {
		return $api->error(302); // référence catégorie invalide
	}
}

function get_eboutique_product($api, $id_product, $prices = "HT") {
	$produit = new API_Produit($api);
	$fiche = $produit->fiche($id_product);
	if ($fiche !== false) {
		$api->track("product", $id_product);
		$fiche['prices'] = $prices;
		return $fiche;
	}
	else {
		return $api->error(303); // référence produit invalide
	}
}

function get_eboutique_prix($api, $id_sku, $qte, $id_catalogue = 0) {
	$produit = new API_Produit($api);
	$prix = $produit->prix($id_sku, $qte, $id_catalogue);
	if (!$prix['prix_ht'] and $id_catalogue) {
		$prix = $produit->prix($id_sku, $qte);
	}

	return $prix;
}

