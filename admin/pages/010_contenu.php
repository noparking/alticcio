<?php

$menu->current('main/content');

$titre_page =  $dico->t("GestionContenu");

$main = $dico->t("ChoisissezTypeContenu");

$buttons = array();

if ($type = $url2->get("type")) {
	require include_path($page->part($type));
}

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery-ui.datepicker.min.js");
if ($config->get("langue") != "en_UK") {
	$lang = substr($config->get("langue"), 0, 2);
	$page->javascript[] = $config->core_media("ui.datepicker-".$lang.".js");
}
$page->javascript[] = $config->media("contenu.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->css[] = $config->core_media("jquery-ui.custom.css");

$page->javascript[] = $config->core_media("ckeditor/ckeditor.js");
$page->jsvars[] = array(
	'upload_url' => $url2->make("current", array('action' => "image-upload")),
	'lang' => substr($config->get('langue'), 0, 2),
);
