<?php

$wsdl = "http://dev.doublet.fr/api/soap/?wsdl";
$apiUser = "ameunier";
$apiKey = "07d89fb39";

$proxy = new SoapClient($wsdl);
$sid = $proxy->login($apiUser, $apiKey);

$sku_group = uniqid("perso-");
$sku = uniqid("simperso-");

function get_set_id() {
	global $proxy;
	global $sid;

	static $set_id = null;
	if ($set_id === null) {
		$attributeSets = $proxy->call($sid, 'product_attribute_set.list');
		$set_id = $attributeSets[0]['set_id'];
		foreach ($attributeSets as $set) {
			if ($set['name'] == "doublet") {
				$set_id = $set['set_id'];
			}
		}
	}
	return $set_id;
}

// Création du produit groupé

$newProductData = array(
	'websites' => array(2),
	'status' => 1,
	'name' => "Plaque de rue personnalisée",
	'description' => implode("\n", $_POST['texts']),
	'url_key' => "plaque-de-rue-".$sku_group,
);

$id = $proxy->call($sid, 'product.create', array('grouped', get_set_id(), $sku_group, $newProductData));
$proxy->call($sid, 'product_stock.update', array($sku_group, array('is_in_stock' => 1)));

// Liaison avec les produits de type vis

$proxy->call($sid, 'product_link.assign', array('grouped', $sku_group, "50122"));
$proxy->call($sid, 'product_link.assign', array('grouped', $sku_group, "80475"));

// Création de l'image du produit groupé

$font_path = $_POST['fpath'];
$sizes = $_POST['sizes'];
$fonts = $_POST['fonts'];
$texts = $_POST['texts'];

include "image.inc.php";

$image_data = get_image($sizes, $fonts, $texts, $font_path);

$image = base64_encode($image_data);
$newImage = array(
	'file' => array(
			'content' => $image,
			'mime' => 'image/png',
	),
	'label' => "plaque de rue",
	'types' => array('image', 'small_image', 'thumbnail'),
	'exclude' => 0,
);
$proxy->call($sid, 'product_media.create', array($sku_group, $newImage));

// Création du produit simple

$newProductData = array(
	'websites' => array(2),
	'status' => 1,
	'name' => "Plaque de rue personnalisée",
	'description' => implode("\n", $_POST['texts']),
	'price' => 20,
	'url_key' => "plaque-de-rue-".$sku,
);

$id = $proxy->call($sid, 'product.create', array('simple', get_set_id(), $sku, $newProductData));

$proxy->call($sid, 'product_link.assign', array('grouped', $sku_group, $sku));

$proxy->call($sid, 'product_stock.update', array($sku, array('is_in_stock' => 1)));

echo "http://dev.doublet.fr/plaque-de-rue-".$sku_group.".html";
