<?php

$menu->current('main/products/fiches-matieres');

$config->core_include("database/tools");
$config->core_include("produit/fiche_matiere", "outils/form", "outils/mysql");
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
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$fiche = new FicheMatiere($sql, $phrase, $id_langues);

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
), array(), "filter_fiches_matieres");

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
\$nom_matiere 
\$famille
\$ecolabel
\$recyclage
\$description_courte
\$description
\$entretien
\$marques_fournisseurs
\$attributs[n]
\$images[n]
\$applications[n]
{$form->fieldset_end()}
{$form->textarea(array('name' => "fiche[html]", 'label' => $dico->t('TemplateHTML'), 'class' => "htmleditor"))}
HTML;
	$right .= <<<HTML
{$form->textarea(array('name' => "fiche[css]", 'label' => $dico->t('CSS'), 'class' => "fiche-css"))}
HTML;
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "fiche[id]"))}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauModeleFicheMatiere');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerModeleFicheMatiere')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfModelesFichesMatieres');
		$fiche->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

$buttons['newmodel'] = $page->l($dico->t('NouveauModeleFiche'), $url2->make("current", array('action' => "create", 'id' => "")));
