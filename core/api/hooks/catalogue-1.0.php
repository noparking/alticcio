<?php

// Retrourne le catalogue lié à la clé avec des catégories comme feuilles
function get_catalogue($api) {
	$catalogue = new API_Catalogue($api);
	if ($id_catalogue = $catalogue->id()) {
		$ret = array();
		$categories = $catalogue->tree($id_catalogue);
		$ret['nb'] = count($categories);
		$ret['categories'] = $categories;
		$ret['home'] =  $catalogue->home($id_catalogue);
		return $ret;
	}
	else {
		return $api->error(304); // Aucun catalogue accessible
	}
}

// Retourne le catalogue lié à la clé avec les produits comme feuilles
function get_catalogue_products($api) {
	$catalogue = new API_Catalogue($api);
	if ($id_catalogue = $catalogue->id()) {
		$ret = array();
		$products = $catalogue->products_tree($id_catalogue);
		$ret['nb'] = count($products);
		$ret['categories'] = $products;
		return $ret;
	}
	else {
		return $api->error(304); // Aucun catalogue accessible
	}
}

function get_catalogue_category($api, $id_category, $prices = "HT", $limit = 0, $offset = 0) {
	$catalogue = new API_Catalogue($api);
	if ($name = $catalogue->categorie_name($id_category)) {
		$api->track("category", $id_category);
		$ret = $catalogue->produits($id_category, $limit, $offset);
		$ret['bloc'] = $catalogue->bloc($id_category);
		$ret['name'] = $name;
		$ret['prices'] = $prices;
		return $ret;
	}
	else {
		return $api->error(302); // référence catégorie invalide
	}
}

function get_catalogue_product($api, $id_product, $prices = "HT") {
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

function get_catalogue_commande($api, $token) {
	$catalogue = new API_Catalogue($api);
	return $catalogue->commande($token);
}
