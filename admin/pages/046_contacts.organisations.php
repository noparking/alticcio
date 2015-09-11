<?php

$menu->current('main/contacts/organisations');

$config->core_include("outils/form", "outils/mysql", "contacts/organisations");
$config->core_include("outils/filter", "outils/pager", "outils/langue", "database/tools");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$orga = new Organisation($sql, $id_langues);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$orga->load($id);
}

$types_organisations = $orga->types();

$form = new Form(array(
	'id' => "form-edit-organisation-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"reset",
		"add-compte", // TODO à laisser ?
		"delete-compte", // TODO à laisser ?
		"add-adresse", // TODO à laisser ?
		"delete-adresse", // TODO à laisser ?
	),
));

$section = "presentation";
if ($form->value('section')) {
	$section = $form->value('section');
}

if ($form->is_submitted() and $form->validate()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "translate":
		case "filter":
		case "pager":
		case "reload":
			break;
		case "reset":
			$form->reset();
			break;
		case "delete":
			$orga->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-compte" :
			//TODO
			break;
		case "delete-compte" :
			//TODO
			break;
		case "add-adresse" :
			//TODO
			break;
		case "delete-adresse" :
			//TODO
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $orga->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$orga->load($id);
			}
			break;
	}
}

$messages = array();

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['organisation'] = $orga->values;
	$form->default_values['adresses'] = $orga->adresses();
	$form->default_values['comptes'] = $orga->comptes();
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = "";
$main .= $page->inc("snippets/messages");

$hidden = array('presentation' => "");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url2->make("current", array('action' => "", 'id' => "")));
}

$buttons['new'] = $page->l($dico->t('NouvelleOrganisation'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'adresses' => $dico->t('Adresses'),
		'comptes' => $dico->t('Comptes'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = <<<HTML
{$page->inc("snippets/produits-sections")}
HTML;

	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "organisation[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => "Adresses", 'class' => "produit-section produit-section-adresses".$hidden['adresses']))}
Les adresses
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => "Adresses", 'class' => "produit-section produit-section-comptes".$hidden['comptes']))}
Les comptes
{$form->fieldset_end()}
HTML;

	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "create" or $action == "edit") {
	$statut_options = array(
		1 => $dico->t("Active"),
		0 => $dico->t("Desactive"),
	);

	$organisations_options = options_select_tree(DBTools::tree($orga->organisations(), $id));
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation']))}
{$form->select(array('name' => "organisation[id_contacts_organisations_types]", 'label' => $dico->t('TypeOrganisation'), 'options' => $types_organisations))}
{$form->input(array('name' => "organisation[nom]", 'label' => $dico->t('Nom')))}
{$form->input(array('name' => "organisation[complement]", 'label' => $dico->t('Complement')))}
{$form->select(array('name' => "organisation[id_parent]", 'label' => $dico->t('OrganisationParent'), 'options' => $organisations_options))}
{$form->input(array('name' => "organisation[email]", 'label' => $dico->t('Email')))}
{$form->input(array('name' => "organisation[www]", 'label' => $dico->t('SiteInternet')))}
{$form->select(array('name' => "organisation[statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
{$form->fieldset_end()}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleOrganisation');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerOrganisation')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfOrganisation');
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'title' => 'ID',
				'type' => 'between',
				'order' => 'DESC',
			),
			'nom' => array(
				'title' => $dico->t('Nom'),
				'type' => 'contain',
			),
			'id_contacts_organisations_types' => array(
				'title' => $dico->t('Type'),
				'type' => 'select',
				'options' => $types_organisations,
			),
			'statut' => array(
				'title' => $dico->t('Active'),
				'type' => 'select',
				'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
			),
		), array(), "filter_organisations");
		$orga->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

