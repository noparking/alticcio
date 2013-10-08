<?php

include dirname(__FILE__)."/../includes/config.php";

$config->core_include("api/api", "outils/mysql", "outils/phrase", "outils/langue");
$config->core_include("produit/produit");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

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

$include_path = dirname(__FILE__)."/../";

include include_path("pages/page.php");

if (isset($_GET['format']) and !isset($data['error'])) {
	$format = "formats/{$_GET['format']}";
	if (file_exists(include_path($format))) {
		$theme = isset($_GET['theme']) ? $_GET['theme'] : "default";
		$file = "$format/$theme/{$api->func()}.php";
		if (!file_exists(include_path($file))) {
			$file = "$format/default/{$api->func()}.php";
		}
		if (file_exists(include_path($file))) {
			include include_path($file);
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
	global $include_path;
	if (file_exists($include_path."../../api/".$path)) {
		return $include_path."../../api/".$path;
	}
	else {
		return $include_path.$path;
	}
}
