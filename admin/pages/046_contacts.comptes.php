<?php

$menu->current('main/contacts/comptes');

$config->core_include("outils/form", "outils/mysql", "contacts/comptes", "contacts/organisations");
$config->core_include("outils/filter", "outils/pager", "outils/langue");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->media("contact.js");
$page->javascript[] = $config->media("dynamicfieldsets.js");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$compte = new Compte($sql, $id_langues);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$compte->load($id);
}

$statut_options = array(
	1 => $dico->t("Active"),
	0 => $dico->t("Desactive"),
);
$organisation_options = $compte->options("organisations");
$correspondant_options = array(0 => "--");
if (isset($compte->values['id_contacts_organisations']) and $compte->values['id_contacts_organisations']) {
	$organisation = new Organisation($sql, $id_langues);
	$organisation->load($compte->values['id_contacts_organisations']);
	$correspondant_options = $organisation->options("correspondants", "CONCAT(nom, ' ', prenom)", $organisation->organisations_correspondants());
}

$form = new Form(array(
	'id' => "form-edit-compte-$id",
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
			$compte->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $compte->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$compte->load($id);
			}
			break;
	}
}

if ($action == 'edit') {
	$form->default_values['compte'] = $compte->values;
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

$buttons['new'] = $page->l($dico->t('NouveauCompte'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));

	$sections = array(
		'presentation' => $dico->t('Presentation'),
	);
	if ($action == "edit") {
		$sections['correspondants'] = $dico->t('Correspondants');
	}
	// variable $hidden mise à jour dans ce snippet
	$left = <<<HTML
{$page->inc("snippets/produits-sections")}
HTML;

	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "compte[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation']))}
{$form->input(array('name' => "compte[nom]", 'label' => $dico->t('Nom')))}
{$form->select(array('name' => "compte[id_contacts_organisations]", 'class' => "organisation",'label' => $dico->t('Organisation'), 'options' => $organisation_options))}
{$form->select(array('name' => "compte[statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
{$form->fieldset_end()}
HTML;
}

if ($action == "edit") {
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Correspondants'), 'class' => "produit-section produit-section-correspondants".$hidden['correspondants']))}
<div id="select-correspondant">
	{$form->select(array('id' => "nouveau-correspondant", 'name' => "nouveau-correspondant", 'label' => $dico->t("NouveauCorrespondant"), 'options' => $correspondant_options))}
	<div class="dynamicfieldset" style="display: none;">
		{$form->fieldset_start(array('legend' => "VALUE", 'class' => "produit-section produit-section-correspondants".$hidden['correspondants']))}
		{$form->select(array('name' => "FIELD[KEY][statut]", 'label' => $dico->t("Statut"), 'options' => $statut_options))}
		{$form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer')))}
		<input type="submit" name="" class="delete-fieldset form-edit-input-submit" value="{$dico->t("Supprimer")}" />
		{$form->fieldset_end()}
	</div>
</div>
<div id="select-correspondant-warning" class="message" style="display: none;">
L'organisation liée à ce compte a changé : vous devez l'enregistrer avant de pouvoir associer des correspondants faisant partie de cette organisation.
</div>
{$form->fieldset_end()}
HTML;

	$json_correspondants = json_encode($compte->correspondants());
	$json_organisations_correspondants = json_encode($compte->correspondants_comptes());
	$page->post_javascript[] = <<<JAVASCRIPT
$("#select-correspondant").dynamicfieldsets("#nouveau-correspondant", "correspondants", {$json_correspondants}, {$json_organisations_correspondants});
JAVASCRIPT;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauCompte');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerCompte')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfComptes');
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
			'organisations' => array(
				'title' => $dico->t('Organisations'),
				'type' => 'select',
				'field' => "co.id",
				'options' => $organisation_options,
			),
			'correspondants' => array(
				'title' => $dico->t('Correspondants'),
				'type' => 'select',
				'field' => "cc.id",
				'options' => $correspondant_options,
			),
			'statut' => array(
				'title' => $dico->t('Active'),
				'type' => 'select',
				'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
			),
		), array(), "filter_organisations");
		$compte->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();


