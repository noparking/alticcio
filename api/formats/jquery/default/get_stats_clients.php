<?php

$jq = array();

$liste_clients = "";
foreach ($data['clients'] as $client) {
	$liste_clients .= <<<HTML
<tr>
	<td>{$client['nom_complet']}</td>
	<td>{$client['email']}</td>
	<td>{$client['commandes']}</td>
	<td>{$client['montant']}</td>
</tr>
HTML;
}

if ($liste_clients) {
	$jq['html'] = <<<HTML
<h2>Clients de l'année {$data['year']}</h2>
<table class="doublet-stats-table">
<tr><th>Client</th><th>Email</th><th>Commandes</th><th>Montant</th></tr>
{$liste_clients}
</table>
HTML;
}
else {
	$jq['html'] = <<<HTML
<h2>Aucun client en {$data['year']}</h2>
HTML;
}

$data['jq'] = $jq;
