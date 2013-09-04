<?php

$page->template('javascript');

include(dirname(__FILE__)."/100_dteditor.php");

$config->core_include("outils/phrase");

$phrase = new Phrase($sql);

$tokens = array();

$code_langue = $url->get("langue")."_".$url->get("pays");

if (isset($_GET['id_applications']) and $_GET['id_applications']) {
	$config->core_include("produit/application");
	$application = new Application($sql, $phrase, $id_langues);
	$application->load($_GET['id_applications']);
	foreach ($application->tokens_attributs($id_langues) as $token => $nom) {
		$tokens[] = array(
			'token' => $token,
			'button' => $token,
			'title' => $nom,
		);
	}
}

if (isset($_GET['id_produits']) and $_GET['id_produits']) {
	$config->core_include("produit/produit", "produit/application");
	$produit = new Produit($sql, $phrase, $id_langues);
	$produit->load($_GET['id_produits']);

	$application = new Application($sql, $phrase, $id_langues);
	$application->load($produit->values['id_applications']);
	$phrases = $phrase->get($application->phrases());

	$phrases = $produit->substitution_attributs($phrases);

	$tokens_description = array();
	$tokens_description[] = array(
		'token' => "description",
		'button' => $dico->t("DescriptionAuto"),
		'title' => str_replace("\n", "<br>", get_phrase($phrases, 'phrase_produit_description', $code_langue)),
	);
	$tokens_list_description = json_encode($tokens_description);

	$tokens_description_courte = array();
	$tokens_description_courte[] = array(
		'token' => "description_courte",
		'button' => $dico->t("DescriptionAuto"),
		'title' => str_replace("\n", "<br>", get_phrase($phrases, 'phrase_produit_description_courte', $code_langue)),
	);
	$tokens_list_description_courte = json_encode($tokens_description_courte);

	$javascript .= <<<JAVASCRIPT

$(document).ready(function () {
	$("textarea.dteditor-tokens-description").dteditor({
		'tokens' : $tokens_list_description,
	});
	$("textarea.dteditor-tokens-description_courte").dteditor({
		'tokens' : $tokens_list_description_courte,
	});
});

JAVASCRIPT;
}

if (count($tokens)) {
	$tokens_list = json_encode($tokens);

	$javascript .= <<<JAVASCRIPT

$(document).ready(function () {
	$("textarea.dteditor-tokens").dteditor({
		'tokens' : $tokens_list,
	});
});

JAVASCRIPT;
}

function get_phrase($phrases, $phrase, $code_langue) {
	return isset($phrases[$phrase][$code_langue]) ? $phrases[$phrase][$code_langue] : "";
}

