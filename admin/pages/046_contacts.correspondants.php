<?php

$menu->current('main/contacts/correspondants');

$config->core_include("outils/form", "outils/mysql", "contacts/correspondants");
$config->core_include("outils/filter", "outils/pager", "outils/langue");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->media("dynamicfieldsets.js");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$correspondant = new Correspondant($sql);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$correspondant->load($id);
}

$statut_options = array(
	1 => $dico->t("Active"),
	0 => $dico->t("Desactive"),
);
$civilite_options = array(
	0 => "",
	1 => "M.",
	2 => "Mme",
	3 => "Mlle",
);
$aucun = " - {$dico->t('Aucun')} -";
$organisation_options = $correspondant->options("organisations", $aucun);
$fonction_options = $correspondant->options("fonctions", $aucun);
$service_options = $correspondant->options("services", $aucun);

$form = new Form(array(
	'id' => "form-edit-correspondant-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"reset",
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
			$correspondant->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $correspondant->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$correspondant->load($id);
			}
			break;
	}
}

if ($action == 'edit') {
	$form->default_values['correspondant'] = $correspondant->values;
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = "";

$hidden = array('presentation' => "");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url2->make("current", array('action' => "", 'id' => "")));
}

$buttons['new'] = $page->l($dico->t('NouveauCorrespondant'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'organisations' => $dico->t('Organisations'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = <<<HTML
{$page->inc("snippets/produits-sections")}
HTML;

	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "correspondant[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;

	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation']))}
{$form->select(array('name' => "correspondant[civilite]", 'label' => $dico->t('Civilite'), 'options' => $civilite_options))}
{$form->input(array('name' => "correspondant[nom]", 'label' => $dico->t('Nom')))}
{$form->input(array('name' => "correspondant[prenom]", 'label' => $dico->t('Prenom')))}
{$form->select(array('name' => "correspondant[statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('Organisations'), 'class' => "produit-section produit-section-organisations".$hidden['organisations']))}
<div id="select-organisation">
	{$form->select(array('id' => "nouvelle-organisation", 'name' => "nouvelle-organisation", 'label' => $dico->t("NouvelleOrganisation"), 'options' => $organisation_options))}
	<div class="dynamicfieldset" style="display: none;">
		{$form->fieldset_start(array('legend' => "VALUE", 'class' => "produit-section produit-section-organisations".$hidden['organisations']))}
		{$form->select(array('name' => "FIELD[KEY][id_contacts_fonctions]", 'label' => $dico->t("Fonction"), 'options' => $fonction_options))}
		{$form->select(array('name' => "FIELD[KEY][id_contacts_services]", 'label' => $dico->t("Service"), 'options' => $service_options))}
		{$form->select(array('name' => "FIELD[KEY][statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
		{$form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer')))}
		<input type="submit" name="" class="delete-fieldset form-edit-input-submit" value="{$dico->t("Supprimer")}" />
		{$form->fieldset_end()}
	</div>
</div>
{$form->fieldset_end()}
HTML;

	$json_organisations = json_encode($correspondant->organisations());
	$json_organisations_correspondants = json_encode($correspondant->organisations_correspondants());
	$page->post_javascript[] = <<<JAVASCRIPT
$("#select-organisation").dynamicfieldsets("#nouvelle-organisation", "organisations", {$json_organisations}, {$json_organisations_correspondants});
JAVASCRIPT;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauCorrespondant');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerCorrespondant')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfCorrespondants');
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
			'prenom' => array(
				'title' => $dico->t('Prenom'),
				'type' => 'contain',
			),
			'organisations' => array(
				'title' => $dico->t('Organisations'),
				'type' => 'select',
				'field' => "co.id",
				'options' => $organisation_options,
			),
			'statut' => array(
				'title' => $dico->t('Active'),
				'type' => 'select',
				'options' => $statut_options,
			),
		), array(), "filter_correspondants");
		$correspondant->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

