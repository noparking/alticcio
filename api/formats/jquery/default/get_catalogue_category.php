<?php

$jq = array();
$api_data = $api->data();

$produits = '<ul class="doublet-products-list clearfix">';
foreach ($data['products'] as $product) {
	$produit = '<div class="doublet-product-infos">';
	$class_more = "doublet-product-more-".$product['id'];
	if (isset($product['thumbnail'])) {
		$src = $config->core_media("produits/".$product['thumbnail']);
		$value = '<a href="#doublet-catalogue-top" class="'.$class_more.'"><img src="'.$src.'" alt="'.$product['name'].'" /></a>';
		$produit .= '<div class="doublet-product-infos-thumbnail">'.$value.'</div>';
	}
	foreach (array('name' => 'titre', 'short_description' => 'description', 'benefits' => 'benefices') as $key => $value) {
		$estimate = false;
		if (isset($product[$key])) {
			$class = "doublet-product-infos-$value";
			$produit .= '<p class="'.$class.'">'.$product[$key].'</p>';
			$jq['items']['element'][$key] = array('selector' =>	".$class", 'element' => $key);
		}
	}
	$produit .= '<a href="#doublet-catalogue-top" class="'.$class_more.' doublet-product-more corner-4">En savoir plus</a>';
	$jq['items']['more'][] = array('selector' => ".$class_more", 'id' => $product['id']);
	if ($estimate) {
		$id = "doublet-product-estimate-".$product['id'];
		$produit .= '<li id="'.$id.'" class="doublet-product-button">Demander un devis</li>';
		$jq['items']['estimate'][] = array('selector' => "#$id", 'id' => $product['id']);
	}
	$produit .= '</div>';
	$buttons = '<div class="doublet-product-buttons">';
	$price_ht = (float)$product['price'];
	$price_ttc = ($price_ht ? number_format($price_ht * (1 + $api_data['tva'] / 100), 2) : "");
	if ($data['prices'] == "TTC") {
		$buttons .= '<p class="doublet-product-infos-price">À partir de '.$price_ttc.'&nbsp;€ TTC <small>('.$price_ht.'&nbsp;€ HT)</small></p>';
	}
	else {
		$buttons .= '<p class="doublet-product-infos-price">À partir de '.$price_ht.'&nbsp;€ HT <small>('.$price_ttc.'&nbsp;€ TTC)</small></p>';
	}
	$buttons .= '</div>';
	$produits .= '<li class="doublet-product-item">'.$produit.$buttons.'</li>';
}
$produits .= '</ul>';

$bloc = "";
if ($data['bloc']) {
	$q = <<<SQL
SELECT contenu FROM dt_blocs WHERE id = {$data['bloc']} AND actif = 1
SQL;
	$res = $sql->query($q);
	if ($row = $sql->fetch($res)) {
		$bloc = $row['contenu'];
	}
}

$jq['html'] = <<<HTML
<h2>{$data['name']}</h2>
<div class="doublet-category-bloc">
{$bloc}
</div>
<div class="doublet-pager">
	<span class="doublet-pager-previous"></span>
	<span class="doublet-pager-infos"></span>
	<span class="doublet-pager-next"></span>
</div>
{$produits}
HTML;

$jq['items']['pager_previous'][] = array('selector' => ".doublet-pager-previous");
$jq['items']['pager_next'][] = array('selector' => ".doublet-pager-next");
$jq['items']['pager_infos'][] = array('selector' => ".doublet-pager-infos");

$data['jq'] = $jq;
