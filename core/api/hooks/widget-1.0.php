<?php

function get_widget_catalogue($api) {
	$ret = array();
	$api->track("visit");
	$forfait = new API_Forfait($api);
	$ret['forfaits'] = $forfait->table();
	return $ret;
}

function get_widget_catalogue_addtocart($api, $token, $product_id, $id_sku, $texte_perso = "") {
	$produit = new API_Produit($api);
	$panier = new API_Panier($api);
	$infos = $produit->infos($product_id);
	$infos['sku'] = $produit->sku($id_sku);
	$infos['texte_perso'] = array(
		'label' => $texte_perso ? $produit->texte_perso($product_id) : "",
		'value' => urldecode($texte_perso),
	);
	if ($infos !== false) {
		$api->track("addtocart", $product_id);
		$infos['item_id'] = $panier->save($token, $product_id, $id_sku, urldecode($texte_perso));

		return $infos;
	}
	else {
		return $api->error(303); // référence produit invalide
	}
}

function get_widget_catalogue_removefromcart($api, $item_id) {
	$panier = new API_Panier($api);
	$panier->delete($item_id);
	return true;
}

function get_widget_stats($api) {
	$ret = array();
	$stats = new StatsApi($api->sql);
	$ret['years'] = $stats->annees();
	return $ret;
}
