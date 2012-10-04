<?php

$titre_page = "Démo de l'API Catalogue";

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->api_widget("catalogue");
$page->css[] = $config->api_media("catalogue.css");

$main = <<<HTML
<div id="catalogue">
</div>
HTML;

$page->post_javascript[] = <<<JAVASCRIPT
$("#catalogue").doubletWidgetCatalogue({
	'api' : "{$config->get('api_url')}",
	'key' : "{$config->get('api_key')}",
	'catalog' : 1,
	'products_per_page' : 12,
	'products_hide' : ['benefits']
});
JAVASCRIPT;
