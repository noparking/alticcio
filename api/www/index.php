<?php

include dirname(__FILE__)."/../includes/config.php";

$config->core_include("api/api", "outils/mysql", "outils/phrase");
$config->core_include("produit/produit");

$sql = new Mysql($config->db());

$api = new API("api_", $sql);

$api->prepare($config->get("base_url"));

$api->errors(array(
	101 => "Clé incorrecte",
	102 => "Cette clé est désactivée",
	103 => "Cette fonctionnalité n'est pas disponible",
	104 => "Cette fonctionnalité n'est pas authorisée",
	105 => "Paramètre(s) manquant(s) pour cette fonctionnalité",
	106 => "IP non autorisée",
	107 => "Domaine non autorisé",

	201 => "Référence Ultralog invalide",
	202 => "Aucun produit n'a cette référence pour variante",

	301 => "Référence catalogue invalide",
	302 => "Référence catégorie invalide",
	303 => "Référence produit invalide",
	304 => "Aucun catalogue accessible",
));

$data = $api->execute();

include dirname(__FILE__)."/../pages/page.php";

if (isset($_GET['format']) and !isset($data['error'])) {
	$format = include_path("formats/{$_GET['format']}");
	if (file_exists($format)) {
		$theme = isset($_GET['theme']) ? $_GET['theme'] : "default";
		$file = "$format/$theme/{$api->func()}.php";
		if (!file_exists($file)) {
			$file = "$format/default/{$api->func()}.php";
		}
		if (file_exists($file)) {
			include $file;
		}
	}
}

if (!isset($output)) {
	$output = json_encode($data);
}

if (isset($_GET['callback'])) {
	echo "{$_GET['callback']}({$output});";
}
else {
	echo $output;
}

function include_path($path) {
	if (file_exists(dirname(__FILE__)."/../../../api/".$path)) {
		return dirname(__FILE__)."/../../../api/".$path;
	}
	else {
		return dirname(__FILE__)."/../".$path;
	}
}
