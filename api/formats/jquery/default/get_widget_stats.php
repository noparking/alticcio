<?php

$jq = array();

$logo = "http://".$_SERVER['SERVER_NAME'].$config->media('logo-doublet.jpg');

$years_options = "";
foreach ($data['years'] as $year) {
	$selected = ($year == date("Y")) ? "selected=\"selected\"" : "";
	$years_options .= "<option value=\"$year\" $selected>$year</option>\n";
}

$jq['html'] = <<<HTML
<div id="doublet-stats-top" class="statistiques-doublet">
	<div class="doublet-stats-title">
		<h1 id="doublet-stats-title">Les stats de la boutique doublet</h1>
		<p id="doublet-stats-slogan">Consultez les statistiques de la boutique Doublet</p>
	</div>
	<div class="doublet-stats-container clearfix">
		<div class="doublet-stats-left">
			<div class="doublet-stats-buttons">
				<button type="button" class="doublet-stats-action doublet-stats-action-general">Général</button>
				<button type="button" class="doublet-stats-action doublet-stats-action-clients">Clients</button>
				<select id="doublet-stats-year">
					{$years_options}
				</select>
			</div>
			<div class="doublet-stats-area-produits">
				<div class="doublet-logo"><a href="http://www.doublet.fr/" title="" target="_blank"><img src="{$logo}" alt="Logo Doublet"/></a></div>
			</div>
			
		</div>
		<div class="doublet-stats-area doublet-stats-area-general doublet-stats-right"></div>
		<div class="doublet-stats-area doublet-stats-area-produit doublet-stats-right" style="display: none;"></div>
		<div class="doublet-stats-area doublet-stats-area-clients doublet-stats-right" style="display: none;"></div>
		<div class="doublet-stats-bottom"></div>
	</div>
</div>
HTML;

$jq['items']['widget'][] = array(
	'selector' => ".statistiques-doublet",
	'right' => ".doublet-stats-right",
	'title' => "#doublet-stats-title",
	'slogan' => "#doublet-stats-slogan",
);
$jq['items']['products_area'][] = array('selector' => ".doublet-stats-area-produits");
$jq['items']['general_area'][] = array('selector' => ".doublet-stats-area-general");
$jq['items']['product_area'][] = array('selector' => ".doublet-stats-area-produit");
$jq['items']['clients_area'][] = array('selector' => ".doublet-stats-area-clients");
$jq['items']['year_switch'][] = array('selector' => "#doublet-stats-year");
$jq['items']['general_switch'][] = array('selector' => ".doublet-stats-action-general");
$jq['items']['clients_switch'][] = array('selector' => ".doublet-stats-action-clients");

$data['jq'] = $jq;
