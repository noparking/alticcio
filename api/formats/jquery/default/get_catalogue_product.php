<?php

$sql = new Mysql($config->db());
$phrase = new Phrase($sql);
$produit = new Produit($sql, $phrase, $config->get("langue"));
$api_data = $api->data();

$jq = array();

function _get_catalogue_product_associated($title, $products) {
	global $data, $jq, $produit, $api_data;

	if (count($products) == 0) {
		return "";
	}
	$associated = '<table class="doublet-product-detail-table"><tr><th>'.$title.'</th><th></th><th>Prix</th><th /></tr>';
	foreach ($products as $product) {
		$produit->load($data['id']);
		$personnalisation = "";
		foreach ($produit->personnalisation() as $type => $perso) {
			if ($type == "texte") {
				$id_perso_texte = "doublet-product-perso-{$type}-{$product['id']}";
				$personnalisation .= <<<HTML
<label for="{$id_perso_texte}">{$perso['libelle']}</label><br />
<textarea id="{$id_perso_texte}"></textarea>
HTML;
			}
		}
		$price_ht = (float)(isset($product['price']) ? $product['price'] : "");
		$price_ttc = ($price_ht ? number_format($price_ht * (1 + $api_data['tva'] / 100), 2) : "");
		$id = "doublet-product-addtocart-".$product['id'];
		$button = '<button id="'.$id.'" class="doublet-product-addtocart clearfix">Ajouter au panier</button>';
		$addtocart = array('selector' => "#$id", 'id' => $product['id'], 'product_id' => $data['id']);
		if (isset($id_perso_texte)) {
			$addtocart['selector_perso_texte'] = "#$id_perso_texte";
		}
		$jq['items']['addtocart'][] = $addtocart;
		if ($data['prices'] == "TTC") {
			$associated .= "<tr><td>{$product['name']}</td><td>{$personnalisation}</td><td>{$price_ttc}&nbsp;€ TTC <small>({$price_ht}&nbsp;€ HT)</small></td><td>$button</td></tr>";
		}
		else {
			$associated .= "<tr><td>{$product['name']}</td><td>{$personnalisation}</td><td>{$price_ht}&nbsp;€ HT <small>({$price_ttc}&nbsp;€ TTC)</small></td><td>$button</td></tr>";
		}
	}
	$associated .= "</table>";

	return $associated;
}

$variantes = _get_catalogue_product_associated("Variantes", $data['variants']);
$accessoires = _get_catalogue_product_associated("Accesoires", $data['accessories']);
$composants = _get_catalogue_product_associated("Composants", $data['components']);
$complementaires = _get_catalogue_product_associated("Produits complémentaires", $data['complementary']);
$similaires = _get_catalogue_product_associated("Produits similaires", $data['similar']);

$jq['html'] = <<<HTML
<div class="doublet-product-detail">
<div class="doublet-product-detail-image">
<ul>
HTML;
$class = ' class="active"';
foreach ($data['images'] as $image) {
	$src = $config->core_media("produits/".$image);
    $jq['html'] .= '<li'.$class.'><img src="'.$src.'" alt="" width="300" height="300" /></li>';
	$class = "";
}
$jq['html'] .= <<<HTML
</ul>
HTML;
if (count($data['images']) > 1) {
	$jq['html'] .= <<<HTML
<div class="doublet-product-detail-image-navigation">
	<a href="#" class="prev" title="précédent"></a>
	<a href="#" class="next" title="suivant"></a>
</div>
HTML;
}
$jq['html'] .= <<<HTML
</div>
</div>
<h2>{$data['name']}</h2>
<p>
{$data['description']}
</p>
$variantes
$accessoires
$composants
$complementaires
$similaires
</div>
<div class="doublet-product-actions">
<button type="button" class="doublet-catalogue-action doublet-catalogue-close">Retour à la liste</button>
</div>
HTML;

$jq['items']['image_prev'][] = array('selector' => ".doublet-product-detail-image-navigation .prev", 'active' => ".doublet-product-detail-image li.active");
$jq['items']['image_next'][] = array('selector' => ".doublet-product-detail-image-navigation .next", 'active' => ".doublet-product-detail-image li.active");
$jq['items']['close'][] = array('selector' => ".doublet-catalogue-close");

$data['jq'] = $jq;
