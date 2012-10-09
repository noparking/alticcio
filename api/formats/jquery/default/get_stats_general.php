<?php

$config->core_include("outils/mysql", "stats/statsapi");

$sql = new Mysql($config->db());

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

$liste_visites = implode("</td><td>", $data['visits']);
$liste_commandes = implode("</td><td>", $data['orders']);
$liste_clients = implode("</td><td>", $data['clients']);
$liste_ca = implode("</td><td>", $data['turnover']);
$liste_panier_moyen = implode("</td><td>", $data['average']);

$jq['html'] = <<<HTML
<h2>Statistiques générales pour l'année {$data['year']}</h2>
<table class="doublet-stats-table">
<tr>
	<th>Visites</th>
	<th>Commandes</th>
	<th>Clients</th>
	<th>Chiffre d'affaire</th>
	<th>Panier moyen</th>
</tr>
<tr>
	<td>{$data['total_visits']}</td>
	<td>{$data['total_orders']}</td>
	<td>{$data['total_clients']}</td>
	<td>{$data['total_turnover']}</td>
	<td>{$data['total_average']}</td>
</tr>
</table>
<h3>Visites</h3>
<img alt="Visites" src="{$stats->graphic($data['visits'], $mois_encoded)}" />
<table class="doublet-stats-table">
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_visites}</td></tr>
</table>
<h3>Commandes</h3>
<img alt="Commandes" src="{$stats->graphic($data['orders'], $mois_encoded)}" />
<table class="doublet-stats-table">
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_commandes}</td></tr>
</table>
<h3>Clients</h3>
<img alt="Clients" src="{$stats->graphic($data['clients'], $mois_encoded)}" />
<table class="doublet-stats-table">
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_clients}</td></tr>
</table>
<h3>Chiffre d'affaire</h3>
<img alt="Chiffre d'affaire" src="{$stats->graphic($data['turnover'], $mois_encoded)}" />
<table class="doublet-stats-table">
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_ca}</td></tr>
</table>
<h3>Panier moyen</h3>
<img alt="Panier moyen" src="{$stats->graphic($data['average'], $mois_encoded)}" />
<table class="doublet-stats-table">
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_panier_moyen}</td></tr>
</table>
HTML;

$data['jq'] = $jq;
