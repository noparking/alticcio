<?php
$menu->current('main/products');

$titre_page = $dico->t('Produits');

$main = $dico->t('ChoisissezProduit');

$buttons = 	array();

if ($type = $url2->get("type")) {
	require include_path($page->part($type));
}
