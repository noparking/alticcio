<?php

$menu->current('main/content/diaporamas');

$titre = "Diaporamas";
$config->core_include("outils/form", "outils/mysql", "outils/langue", "outils/phrase");
$config->core_include("contenu/diaporama");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->core_media("form.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $config->core_media("jquery.colorbox-min.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->core_media("colorbox.css");
$page->css[] = $config->media("dteditor.css");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$sql = new Mysql($config->db());
$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));
$phrase = new Phrase($sql);

$diaporama = new Diaporama($sql, $phrase, $id_langue);

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'd.id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
		'type' => 'contain',
	),
	'titre' => array(
		'title' => $dico->t('Titre'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
	'section' => array(
		'title' => $dico->t('Section'),
		'type' => 'select',
		'field' => 'd.section',
		'options' => $diaporama->sections(),
	),
	'actif' => array(
		'title' => $dico->t('Active'),
		'type' => 'select',
		'field' => 'p.actif',
		'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
	),
), array(), "filter_diaporama");

$titre_page = "Diaporamas";

$id = 0;

$action = $url->get('action');
if ($id = $url->get('id')) {
	$diaporama->load($id);
}

$form = new Form(array(
	'id' => "form-edit-diaporama-$id",
	'class' => "form-edit-diaporama",
	'actions' => array("save", "delete", "cancel"),
	'files' => array("vignette_file"),
));

$traduction = $form->value("lang");

$messages = array();

$vignette_dir = $config->get("medias_path")."www/medias/images/diaporamas/";

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "translate":
			break;
		case "delete":
			$diaporama->delete();
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "save":
			if ($form->validate()) {
				$id_saved = $diaporama->save($data, $vignette_dir);
				if ($id_saved == -1) {
					$messages[] = '<p class="message">Il existe déjà un diaporama ayant cette référence</p>';
				}
				else if ($id_saved > 0) {
					$form->reset();
					if ($id_saved != $id) {
						$url->redirect("current", array('action' => "", 'id' => $id_saved));
					}
					$diaporama->load($id);
				}
			}
			break;
	}
}
else {
	$form->reset();
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['diaporama'] = $diaporama->values;
	$form->default_values['phrases'] = $phrase->get($diaporama->phrases());
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

// variable $displayed_lang définie dans ce snippet
$main = $page->inc("snippets/translate");

$main .= $page->inc("snippets/messages");

if ($action == "create" or $action == "edit") {
	$buttons[] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}
$buttons[] = $page->l($dico->t("Nouveau"), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'class' => "delete", 'name' => "delete", 'value' => $dico->t('Supprimer') ));
	$main .= <<<HTML
{$form->input(array('name' => "diaporama[id]", 'type' => "hidden"))}
HTML;
}

if ($action == "create" or $action == "edit") {
	$vignette = "";
	if ($diaporama->values['vignette']) {
		$vignette = <<<HTML
<img src="{$config->core_media("diaporamas/".$diaporama->values['vignette'])}" alt="Vignette" />
HTML;
	}
	$main .= <<<HTML
{$form->input(array('name' => "diaporama[ref]", 'label' => $dico->t('Reference')))}
{$form->input(array('name' => "diaporama[phrase_titre]", 'type' => "hidden"))}
{$vignette}
{$form->input(array('type' => "file", 'name' => "vignette_file", 'label' => $dico->t('Vignette')))}
{$form->select(array('name' => "diaporama[actif]", 'label' => $dico->t('Actif'), 'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive') )))}
{$form->input(array('name' => "phrases[phrase_titre]", 'label' => $dico->t('Titre'), 'items' => $displayed_lang))}
{$form->input(array('name' => "diaporama[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->selectoptgroup(array('name' => "diaporama[id_themes_photos]", 'label' => $dico->t('Thème'), 'options' => $diaporama->themes_photos($config->get('langue'))))}
{$form->select(array('name' => "diaporama[section]", 'label' => $dico->t('Section'), 'options' => $diaporama->sections()))}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = "Créer un nouveau diaporama";
		break;
	case "edit" :
		$titre_page = "Editer le diaporama # ID : ".$id;
		break;
	default :
		$titre_page = "Liste des diaporamas";
		$diaporama->liste($config->get('langue'), $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
