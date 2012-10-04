<?php

$page->template('javascript');

$config->core_include("outils/mysql", "outils/image");

$sql = new Mysql($config->db());
$image = new Image($sql);

$types = array("{'value' : '', 'label' : '{$dico->t("TailleReelle")}'}");
foreach ($image->types($config->get('langue')) as $type) {
	$types[] = "{'value' : '{$type['largeur']}x{$type['hauteur']}', 'label' : '{$type['description']}'}";
}
$types_images = implode(",", $types);

$javascript = <<<JAVASCRIPT
$(document).ready(function () {
	$("textarea.htmleditor").dteditor({
		'tags' : [
			{'tag' : "div", 'button' : "div"},
			{'tag' : "span", 'button' : "span"},
			{'tag' : "p", 'button' : "p"},
			{'tag' : "h1", 'button' : "h1"},
			{'tag' : "h2", 'button' : "h2"},
			{'tag' : "h3", 'button' : "h3"},
			{'tag' : "em", 'button' : "<em>{$dico->t('Emphase')}</em>"},
			{'tag':	"strong", 'button' : "<strong>{$dico->t('Evidence')}</strong>"},
			{'tag':	"u", 'button' : "<u>{$dico->t('Souligne')}</u>"},
			{'tag':	"strike", 'button' : "<strike>{$dico->t('Barre')}</strike>"}
		],
		'lists' : [
			{'tag' : "ul", 'button' : "{$dico->t('Liste')}"},
			{'tag' : "ol", 'button' : "{$dico->t('Liste')}123"}
		],
	});
});

var insertLink = function (data) {
	var begin = '<a href="' + data.href + '">' + data.text;
	return {'html' : begin + '</a>', 'offset' : begin.length};
}

var insertImage = function (data) {
	var html = '<img src="' + data.src + '" title="' + data.title + '" alt="' + data.alt + '" />';
	return {'html' : html, 'offset' : html.length};
}
JAVASCRIPT;
