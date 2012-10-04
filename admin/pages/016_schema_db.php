<?php
/*
 * Configuration
 */
$config->core_include("database/schema", "outils/mysql");

$params_config = $config->db();
$sql = new Mysql($params_config);
$schema = new Schema($sql, $params_config['database']);

$menu->current('main/params/schema');

$page->css[] = $config->media("schema.css");
$lien_retour = $page->l($dico->t("Fermer"), $url->make("SchemaDb"));

if ($url->get("action") == "html") {
	$page->template("simple");
	$schema->back = "top";
	$main = <<<HTML
<div class="lien_retour">{$lien_retour}</div>
<div class="schema_menu">
	<a name="top"></a>
	{$schema->menu()}
	<div class="spacer"></div>
</div>
	{$schema->lister()}
<div class="spacer"></div>
<div class="lien_retour">{$lien_retour}</div>
HTML;
}
else if ($url->get("action") == "xml") {
	$page->template("simple");
	$main = <<<HTML
<div class="lien_retour">{$lien_retour}</div>
<textarea class="area_export">{$schema->lister("XML")}</textarea>
<div class="lien_retour">{$lien_retour}</div>
HTML;
}
else {
	$titre_page = "Database architecture";
	$main = <<<HTML
<ul class="liens_schemas">
	<li>{$page->l($dico->t("VoirSchemaHTML"), $url->make("SchemaDb", array('action' => "html")))}</li> 
	<li>{$page->l($dico->t("VoirSchemaXML"), $url->make("SchemaDb", array('action' => "xml")))}</li>
</ul>
HTML;
}
?>
