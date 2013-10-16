<?php

$menu->current('main/params/boutiques');

$config->core_include("produit/boutique", "outils/form", "outils/mysql");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$boutique = new Boutique($sql);

$pager_boutiques = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_boutiques = new Filter($pager_boutiques, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'b.id',
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'b.nom',
	),
	'catalogue' => array(
		'title' => $dico->t('Catalogue'),
		'type' => 'contain',
		'field' => 'c.nom',
	),
	'api' => array(
		'title' => $dico->t('UtilisateurAPI'),
		'type' => 'contain',
		'field' => 'k.name',
	),
), array(), "filter_boutiques");

$action = $url->get('action');
if ($id = $url->get('id')) {
	$boutique->load($id);
}

$form = new Form(array(
	'id' => "form-edit-boutique-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"cancel",
	),
));

$section = "presentation";
if ($form->value('section')) {
	$section = $form->value('section');
}
$traduction = $form->value("lang");

$messages = array();

if ($form->is_submitted() and $form->validate()) {
	$data = $form->escape_values();
	switch ($form->action()) {
		case "translate":
		case "filter":
		case "pager":
		case "reaload":
			break;
		case "reset":
			$form->reset();
			$traduction = null;
			break;
		case "delete":
			$boutique->delete($data);
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			if ($action == "edit" or $action == "create") {
				if ($id = $boutique->save($data)) {
					$form->reset();
					if ($action != "edit") {
						$url->redirect("current", array('action' => "edit", 'id' => $id));
					}
					$boutique->load($id);
				}
				else {
					$messages[] = '<p class="message_error">'."Sauvegarde impossible !".'</p>';	
				}
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['boutique'] = $boutique->values;
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = $page->inc("snippets/messages");

$hidden = array('presentation' => "");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}

$buttons['new'] = $page->l($dico->t('NouvelleBoutique'), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'parametres' => $dico->t('Parametres'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
	$main .= <<<HTML
{$form->input(array('name' => "boutique[id]", 'type' => "hidden"))}
HTML;
}
if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$form->input(array('name' => "boutique[nom]", 'label' => $dico->t('Nom')))}
{$form->select(array('name' => "boutique[id_catalogues]", 'label' => $dico->t('Catalogue'), 'options' => $boutique->catalogues()))}
{$form->select(array('name' => "boutique[id_api_keys]", 'label' => $dico->t('UtilisateurAPI'), 'options' => $boutique->api_keys()))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Parametres'), 'class' => "produit-section produit-section-parametres".$hidden['parametres'], 'id' => "produit-section-parametres"))}
HTML;
	foreach ($boutique->values['data'] as $data_key => $data_value) {
			$main .= <<<HTML
{$form->input(array('name' => "boutique[data][$data_key]", 'label' => $data_key))}
HTML;

	}
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('NouveauParametre'), 'class' => "produit-section produit-section-parametres".$hidden['parametres'], 'id' => "produit-section-parametres"))}
{$form->input(array('name' => "boutique[new_data_key]", 'label' => $dico->t("Nom")))}
{$form->input(array('name' => "boutique[new_data_value]", 'label' => $dico->t("Valeur")))}
{$form->fieldset_end()}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;

	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer')));
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleBoutique');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerBoutique')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfBoutiques');
		$filter = $filter_boutiques;
		$pager = $pager_boutiques;
		$boutique->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
