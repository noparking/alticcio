<?php

$config->core_include("outils/mysql", "stats/statsapi");
$config->core_include("produit/produit", "outils/phrase", "outils/langue");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$produit = new Produit($sql, $phrase, $id_langues);

$produit->load($data['product']);
$phrases = $phrase->get($produit->phrases());

$params = array(
	'id_keys' => $api->key_id(),
	'annee' => $data['year'],
);
$stats = new StatsApi($sql, $params);

$jq = array();

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

$liste_mois = implode("</th><th>", $mois);

$mois_encoded = array();
foreach ($mois as $un_mois) {
	$mois_encoded[] = urlencode($un_mois);
}

$debug = print_r($phrases, true);
$src = $stats->graphic(array($data['viewed'], $data['ordered']), $mois_encoded, array('Vu', urlencode('Commandé')));

$liste_produit_vu = implode("</td><td>", $data['viewed']);
$liste_produit_commande = implode("</td><td>", $data['ordered']);

$jq['html'] = <<<HTML
<h2>{$phrases['phrase_nom'][$api->info('language')]} ({$data['year']})</h2>
<table class="doublet-stats-table">
<tr><td>Vues totales</td><td>{$data['total_viewed']}</td></tr>
<tr><td>Commandes totales</td><td>{$data['total_ordered']}</td></tr>
</table>
<img alt="Produit vu et commandé" src="{$src}" />
<table class="doublet-stats-table">
<tr><td/><th>{$liste_mois}</th></tr>
<tr><td>Vu</td><td>{$liste_produit_vu}</td></tr>
<tr><td>Commandé</td><td>{$liste_produit_commande}</td></tr>
</table>
HTML;

$data['jq'] = $jq;
