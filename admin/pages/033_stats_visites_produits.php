<?php
$menu->current('main/stats/products');

$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$config->core_include("outils/filter", "outils/pager");
$config->core_include("stats/statsproduits");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery-ui.datepicker.min.js");
if ($config->get("langue") != "en_UK") {
	$lang = substr($config->get("langue"), 0, 2);
	$page->javascript[] = $config->core_media("ui.datepicker-".$lang.".js");
}
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'produit', 'id' => "")),	
);
$page->css[] = $config->core_media("jquery-ui.custom.css");

$phrase = new Phrase($sql);

$stats = new StatsProduits($sql);

$titre_page = "Statistiques sur les visites des produits";

$form = new Form(array(
	'id' => "form-stats-visites-produits",
	'class' => "form-edit",
));

$code_langue = $traduction = $form->value("lang");
if (!$code_langue) {
	$code_langue = $config->get("langue");
}

$langue = new Langue($sql);
$id_langues = $langue->id($code_langue);

$filter_schema = array(
	'from' => array(
		'title' => "Origine",
		'type' => 'select',
		'options' => $stats->get_froms(),
	),
	'nom' => array(
		'title' => "Nom du produit",
		'type' => 'contain',
		'field' => 'ph.phrase',
	),
	'hits' => array(
		'title' => "Visites",
		'type' => 'between',
		'field' => 'hits',
		'order' => 'DESC',
		'group' => true,
	),
	'first_hit' => array(
		'title' => "Date de début",
		'type' => 'date_from',
		'field' => 'date_requete',
	),
	'last_hit' => array(
		'title' => "Date de fin",
		'type' => 'date_to',
		'field' => 'date_requete',
	),
);

$form_start = $form->form_start();

$pager = new Pager($sql, array(10, 30, 50, 100, 200), "pager_stats_visites_produits");
$filter = new Filter($pager, $filter_schema, array(), "filter_stats_visites_produits");
$stats->visites($id_langues, $filter);

// variable $displayed_lang définie dans ce snippet
$not_all_traductions = true;
$main = $page->inc("snippets/translate");

$main .= $page->inc("snippets/filter");

$form_end = $form->form_end();

if ($url->get("action") == "produit" and $url->get("id")) {
	$titre_page = "Statistiques sur les visites du produit #{$url->get("id")} ({$stats->nom_produit($url->get("id"), $id_langues)})";
	$main = "";
	$buttons[] = $page->l("Retour", $url->make("current", array('action' => "", 'id' => "")));

	$mois = array(
		"Janvier",
		"Février",
		"Mars",
		"Avril",
		"Mai",
		"Juin",
		"Juillet",
		"Août",
		"Septembre",
		"Octobre",
		"Novembre",
		"Décembre",
	);
	
	foreach (array_reverse($stats->annees()) as $annee) {
		$start = mktime(0, 0, 0, 1, 1, $annee);
		$stop = mktime(23, 59, 59, 12, 31, $annee);
		$stats_visites = $stats->visites_produit($url->get("id"), $start, $stop);
		$liste_mois = implode("</th><th>", $mois);
		$liste_visites = implode("</td><td>", $stats_visites);

		$main .= <<<HTML
<h3>Visites en $annee</h3>
<img alt="Visites" src="{$stats->graphic($stats_visites, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_visites}</td></tr>
</table>
HTML;
	}
}

