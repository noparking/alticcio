<?php

$menu->current('main/products/fiches');

$config->core_include("database/tools");
$config->core_include("produit/fiche", "outils/form", "outils/mysql");
$config->core_include("outils/filter", "outils/pager", "outils/langue", "outils/phrase");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $url->make("HTMLEditor");
$page->css[] = $config->media("htmleditor.css");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$fiche = new Fiche($sql, $phrase, $langue);

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
	),
	'phrase' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
), array(), "filter_fiches");

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$fiche->load($id);
}

$form = new Form(array(
	'id' => "form-edit-fiche-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel"),
));

$traduction = $form->value("lang");

if ($form->is_submitted()) {
	$data = $form->escape_values();
	switch ($form->action()) {
		case "translate":
			break;
		case "reset":
			$form->reset();
			$traduction = null;
			break;
		case "delete":
			$fiche->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $fiche->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$fiche->load($id);
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['fiche'] = $fiche->values;
	$form->default_values['phrases'] = $phrase->get($fiche->phrases());
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

// variable $displayed_lang définie dans ce snippet
$main = $page->inc("snippets/translate");

$main .= $page->inc("snippets/messages");
$right = "";

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->input(array('name' => "fiche[phrase_nom]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_nom]", 'class' => 'fiche-nom', 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->fieldset_start(array('legend' => "Variables", 'class' => "fiche-variables"))}
\$nom \$commercial \$ref \$offre \$description_courte \$description \$url_key \$meta_title \$meta_description \$meta_keywords \$date_modification \$entretien \$mode_emploi \$avantages_produit \$attributs[n] \$images[n] \$variantes \$accessoires \$composants
{$form->fieldset_end()}
{$form->textarea(array('name' => "fiche[html]", 'label' => $dico->t('TemplateHTML'), 'class' => "htmleditor"))}
HTML;
	$right .= <<<HTML
{$form->textarea(array('name' => "fiche[css]", 'label' => $dico->t('CSS'), 'class' => "fiche-css"))}
HTML;
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "fiche[id]"))}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauModeleFiche');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerModeleFiche')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfModelesFiches');
		$fiche->liste($config->get('langue'), $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

$buttons[] = $page->l($dico->t('NouveauModeleFiche'), $url2->make("current", array('action' => "create", 'id' => "")));
