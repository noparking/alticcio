<?php

$menu->current('main/customers/organisations');

$config->core_include("outils/form", "outils/mysql", "contacts/organisations", "outils/pays");
$config->core_include("outils/filter", "outils/pager", "outils/langue", "database/tools");
$config->base_include("functions/tree");

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

$pays = new Pays($sql);
$liste_pays = $pays->liste($id_langues);

$organisation = new Organisation($sql, $id_langues);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$organisation->load($id);
}

$statut_options = array(
	1 => $dico->t("Active"),
	0 => $dico->t("Desactive"),
);
$type_options = $organisation->types();
$correspondant_options = $organisation->options("correspondants", "CONCAT(nom, ' ', prenom)");
$fonction_options = $organisation->options("fonctions");
$service_options = $organisation->options("services");

$form = new Form(array(
	'id' => "form-edit-organisation-$id",
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
			$organisation->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "delete-adresse":
			$organisation->delete_adresse($form->action_arg());
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $organisation->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$organisation->load($id);
			}
			break;
	}
}

if ($action == 'edit') {
	$form->default_values['organisation'] = $organisation->values;
	$form->default_values['adresse'] = $organisation->adresses();
}
else {
	$form->reset();
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

$buttons['new'] = $page->l($dico->t('NouvelleOrganisation'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "create" or $action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'correspondants' => $dico->t('Correspondants'),
		'adresses' => $dico->t('Adresses'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = <<<HTML
{$page->inc("snippets/produits-sections")}
HTML;

	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "organisation[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
{$form->fieldset_start(array('legend' => "Adresses", 'class' => "produit-section produit-section-adresses".$hidden['adresses']))}
HTML;

	$adresse_statuts = array(
		1 => 'actif',
		0 => 'inactif',
	);
	$adresses_ids = array_keys($form->default_values['adresse']);
	$adresses_ids[] = 0;
	foreach ($adresses_ids as $i) {
		$legend = "Nouvelle adresses";
		$button_delete = "";
		if ($i) {
			$legend = $form->default_values['adresse'][$i]['nom'];
			$button_delete = <<<HTML
{$form->input(array('type' => "submit", 'class' => "delete", 'name' => "delete-adresse[$i]", 'value' => "Supprimer"))}			
HTML;
		}
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $legend))}
{$form->input(array('name' => "adresse[$i][nom]", 'label' => "Nom"))}
{$form->input(array('name' => "adresse[$i][societe]", 'label' => "Raison sociale"))}
{$form->input(array('name' => "adresse[$i][complement]", 'label' => "Complément"))}
{$form->input(array('name' => "adresse[$i][adresse_1]", 'label' => "N° et libelle de la voie"))}
{$form->input(array('name' => "adresse[$i][adresse_2]", 'label' => "Complément géographique"))}
{$form->input(array('name' => "adresse[$i][adresse_3]", 'label' => "BP / CS / lieu-dit..."))}
{$form->input(array('name' => "adresse[$i][code_postal]", 'label' => "Code postal"))}
{$form->input(array('name' => "adresse[$i][ville]", 'label' => "Ville"))}
{$form->input(array('name' => "adresse[$i][etat]", 'label' => "État"))}
{$form->select(array('name' => "adresse[$i][id_pays]", 'label' => "Pays", 'options' => $liste_pays))}
{$form->input(array('name' => "adresse[$i][telephone]", 'label' => "Téléphone"))}
{$form->input(array('name' => "adresse[$i][fax]", 'label' => "Fax"))}
{$form->select(array('name' => "adresse[$i][statut]", 'label' => "Statut", 'options' => $adresse_statuts))}
{$form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer')))}
{$button_delete}
{$form->fieldset_end()}
HTML;
	}

	$organisations_options = options_select_tree(DBTools::tree($organisation->organisations(), $id));
	$main .= <<<HTML
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation']))}
{$form->select(array('name' => "organisation[id_contacts_organisations_types]", 'label' => $dico->t('TypeOrganisation'), 'options' => $type_options))}
{$form->input(array('name' => "organisation[nom]", 'label' => $dico->t('Nom')))}
{$form->input(array('name' => "organisation[complement]", 'label' => $dico->t('Complement')))}
{$form->select(array('name' => "organisation[id_parent]", 'label' => $dico->t('OrganisationParent'), 'options' => $organisations_options))}
{$form->input(array('name' => "organisation[email]", 'label' => $dico->t('Email')))}
{$form->input(array('name' => "organisation[www]", 'label' => $dico->t('SiteInternet')))}
{$form->select(array('name' => "organisation[statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
{$form->input(array('name' => "organisation[tiers_id]", 'label' => $dico->t('IdTiers')))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('Correspondants'), 'class' => "produit-section produit-section-correspondants".$hidden['correspondants']))}
<div id="select-correspondant">
	{$form->select(array('id' => "nouveau-correspondant", 'name' => "nouveau-correspondant", 'label' => $dico->t("NouveauCorrespondant"), 'options' => $correspondant_options))}
	<div class="dynamicfieldset" style="display: none;">
		{$form->fieldset_start(array('legend' => "VALUE", 'class' => "produit-section produit-section-correspondants".$hidden['correspondants']))}
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

	$json_correspondants = json_encode($organisation->correspondants());
	$json_organisations_correspondants = json_encode($organisation->organisations_correspondants());
	$page->post_javascript[] = <<<JAVASCRIPT
$("#select-correspondant").dynamicfieldsets("#nouveau-correspondant", "correspondants", {$json_correspondants}, {$json_organisations_correspondants});
JAVASCRIPT;
}

if ($action == "edit") {
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
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
				'options' => $type_options,
			),
			'statut' => array(
				'title' => $dico->t('Active'),
				'type' => 'select',
				'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
			),
		), array(), "filter_organisations");
		$organisation->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

