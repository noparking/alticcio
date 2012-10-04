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

$buttons_style = 'image';
$buttons_images_size = '16x16';
$buttons = array(
	'image' => array(
		'em' => "<img src='{$config->core_media("dteditor/$buttons_images_size/italic.png")}' alt='{$dico->t('Emphase')}' />",
		'strong' => "<img src='{$config->core_media("dteditor/$buttons_images_size/bold.png")}' alt='{$dico->t('Evidence')}' />",
		'u' => "<img src='{$config->core_media("dteditor/$buttons_images_size/underline.png")}' alt='{$dico->t('Souligne')}' />",
		'strike' => "<img src='{$config->core_media("dteditor/$buttons_images_size/strike_trough.png")}' alt='{$dico->t('Barre')}' />",
		'more' => "<img src='{$config->core_media("dteditor/$buttons_images_size/cut.png")}' alt='{$dico->t('Barre')}' />",
		'ul' => "<img src='{$config->core_media("dteditor/$buttons_images_size/bulleted_list.png")}' alt='{$dico->t('Liste')}' />",
		'ol' => "<img src='{$config->core_media("dteditor/$buttons_images_size/numbered_list.png")}' alt='{$dico->t('Liste')}123' />",
		'img' => "<img src='{$config->core_media("dteditor/$buttons_images_size/image.png")}' alt='{$dico->t('Image')}' />",
		'a' => "<img src='{$config->core_media("dteditor/$buttons_images_size/insert_link.png")}' alt='{$dico->t('Lien')}' />",
		'bloc' => "<img src='{$config->core_media("dteditor/$buttons_images_size/form.png")}' alt='Bloc' />",
		'preview' => "<img src='{$config->core_media("dteditor/$buttons_images_size/preview.png")}' alt='{$dico->t('Apercu')}' />",
		'close' => "<img src='{$config->core_media("dteditor/$buttons_images_size/undo.png")}' alt='{$dico->t('Fermer')}' />",
	),
	'text' => array(
		'em' => "<em>{$dico->t('Emphase')}</em>",
		'strong' => "<strong>{$dico->t('Evidence')}</strong>",
		'u' => "<u>{$dico->t('Souligne')}</u>",
		'strike' => "<strike>{$dico->t('Barre')}</strike>",
		'more' => "More",
		'ul' => "{$dico->t('Liste')}",
		'ol' => "{$dico->t('Liste')}123",
		'img' => "{$dico->t('Image')}",
		'a' => "{$dico->t('Lien')}",
		'bloc' => "Bloc",
		'preview' => "{$dico->t('Apercu')}",
		'close' => "{$dico->t('Fermer')}",
	),
);

$javascript = <<<JAVASCRIPT
$(document).ready(function () {
	$("textarea.dteditor").dteditor({
		'tags' : [
			{'tag' : "em", 'button' : "{$buttons[$buttons_style]['em']}", 'title' : "{$dico->t('Emphase')}"},
			{'tag' :	"strong", 'button' : "{$buttons[$buttons_style]['strong']}", 'title' : "{$dico->t('Evidence')}"},
			{'tag' :	"u", 'button' : "{$buttons[$buttons_style]['u']}", 'title' : "{$dico->t('Souligne')}"},
			{'tag' :	"strike", 'button' : "{$buttons[$buttons_style]['strike']}", 'title' : "{$dico->t('Barre')}"}
		],
		'comments' : [
			{'tag' :	"more", 'button' : "{$buttons[$buttons_style]['more']}", 'title' : "More"}
		],
		'lists' : [
			{'tag' : "ul", 'button' : "{$buttons[$buttons_style]['ul']}", 'title' : "{$dico->t('Liste')}"},
			{'tag' : "ol", 'button' : "{$buttons[$buttons_style]['ol']}", 'title' : "{$dico->t('Liste')}123"}
		],
		'forms' : [
			{
				'tag' : "img",
				'title' : "{$dico->t('Image')}",
				'button' : "{$buttons[$buttons_style]['img']}",
				'confirm' : "{$dico->t('Confirmer')}",
				'cancel' : "{$dico->t('Annuler')}",
				'ajax' : "{$url->make('DTEditorImage')}",
				'waiting' : "{$dico->t('AttenteTelechargement')}",
				'callback' : insertImage,
				'fields' : [
					{'name' : "image", 'type' : "file", 'label' : "{$dico->t('Image')} : "},
					{'name' : "title", 'type' : "text", 'label' : "{$dico->t('Titre')} : ", 'selection' : true},
					{'name' : "size", 'type' : "select", 'label' : "{$dico->t('Taille')} : ", 'options' : [{$types_images}]}
				]
			},
			{
				'tag' : "a",
				'title' : "{$dico->t('Lien')}",
				'button' : "{$buttons[$buttons_style]['a']}",
				'confirm' : "{$dico->t('Confirmer')}",
				'cancel' : "{$dico->t('Annuler')}",
				'callback' : insertLink,
				'fields' : [
					{'name' : "href", 'type' : "text", 'label' : "URL : "},
					{'name' : "text", 'type' : "hidden", 'selection' : true}
				]
			},
			{
				'button' : "{$buttons[$buttons_style]['bloc']}",
				'title' : "Bloc",
				'confirm' : "{$dico->t('Confirmer')}",
				'cancel' : "{$dico->t('Annuler')}",
				'callback' : insertBloc,
				'fields' : [
					{'name' : "bloc", 'type' : "text", 'label' : "Bloc : "}
				]
			}
		],
		'preview' : {'open' : "{$buttons[$buttons_style]['preview']}", 'close' : "{$buttons[$buttons_style]['close']}", 'title' : "{$dico->t('Apercu')}", 'close_title' : "{$dico->t('Fermer')}"}
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

var insertBloc = function (data) {
	var html = '{bloc=' + data.bloc + '}';
	return {'html' : html, 'offset' : html.length};
}
JAVASCRIPT;
