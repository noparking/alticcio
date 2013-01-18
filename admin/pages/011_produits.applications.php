<?php

$menu->current('main/products/applications');

$config->core_include("produit/application", "outils/form", "outils/mysql");
$config->core_include("outils/phrase", "outils/langue", "outils/url_redirection");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");

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

$application = new Application($sql, $phrase, $id_langue);

$url_redirection = new UrlRedirection($sql);

$pager_applications = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_applications = new Filter($pager_applications, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
	),
	'phrase' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
), array(), "filter_applications");

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$application->load($id);
}

$form = new Form(array(
	'id' => "form-edit-application-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel"),
));

$pager_attributs = new Pager($sql, array(10, 30, 50, 100, 200), "pager_application_attributs");
$filter_attributs = new Filter($pager_attributs, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
	),
	'name' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'aa.classement',
		'form' => array(
			'name' => "attributs[%id%][classement]",
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
			'class' => "input-text-numeric",
		),
	),
	'fiche_technique' => array(
		'title' => $dico->t('FicheTechnique'),
		'type' => 'select',
		'options' => array(0 => $dico->t("Non"), 1 => $dico->t("Oui")),
		'field' => 'aa.fiche_technique',
		'form' => array(
			'name' => "attributs[%id%][fiche_technique]",
			'method' => 'input',
			'type' => 'checkbox',
			'template' => '#{field}',
		),
	),
	'pictos_vente' => array(
		'title' => $dico->t('PictosVente'),
		'type' => 'select',
		'options' => array(0 => $dico->t("Non"), 1 => $dico->t("Oui")),
		'field' => 'aa.pictos_vente',
		'form' => array(
			'name' => "attributs[%id%][pictos_vente]",
			'method' => 'input',
			'type' => 'checkbox',
			'template' => '#{field}',
		),
	),
	'top' => array(
		'title' => $dico->t('Top'),
		'type' => 'select',
		'options' => array(0 => $dico->t("Non"), 1 => $dico->t("Oui")),
		'field' => 'aa.top',
		'form' => array(
			'name' => "attributs[%id%][top]",
			'method' => 'input',
			'type' => 'checkbox',
			'template' => '#{field}',
		),
	),
	'comparatif' => array(
		'title' => $dico->t('Comparatif'),
		'type' => 'select',
		'options' => array(0 => $dico->t("Non"), 1 => $dico->t("Oui")),
		'field' => 'aa.comparatif',
		'form' => array(
			'name' => "attributs[%id%][comparatif]",
			'method' => 'input',
			'type' => 'checkbox',
			'template' => '#{field}',
		),
	),
	'filtre' => array(
		'title' => $dico->t('Filtre'),
		'type' => 'select',
		'options' => array(0 => $dico->t("Non"), 1 => $dico->t("Oui")),
		'field' => 'aa.filtre',
		'form' => array(
			'name' => "attributs[%id%][filtre]",
			'method' => 'input',
			'type' => 'checkbox',
			'template' => '#{field}',
		),
	),
), $application->attributs(), "filter_applications_attributs", true);
	

$section = "presentation";
if ($form->value('section')) {
	$section = $form->value('section');
}
$traduction = $form->value("lang");

$messages = array();

if ($form->is_submitted()) {
	$data = $form->escape_values();
	switch ($form->action()) {
		case "translate":
		case "filter":
			break;
		case "reset":
			$form->reset();
			$traduction = null;
			break;
		case "delete":
			$application->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			if ($action == "edit" or $action == "create") {
				if (isset($data['attributs'])) {
					$filter_selected = $filter_attributs->selected();
					foreach ($data['attributs'] as $element_id => $element) {
						if (!in_array($element_id, $filter_selected)) {
							unset($data['attributs'][$element_id]);
						}
					}
				}
				$id = $url_redirection->save_object($application, $data, array('phrase_url_key' => 'phrase_nom'));
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$application->load($id);
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}
else {
	$filter_attributs->select($application->attributs());
}

if ($action == 'edit') {
	$form->default_values['application'] = $application->values;
	$form->default_values['phrases'] = $phrase->get($application->phrases());
	$form->default_values['attributs'] = $application->all_attributs();
}
else {
	$page->javascript[] = $config->core_media("filter-edit.js");
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

// variable $displayed_lang définie dans ce snippet
$main = $page->inc("snippets/translate");

$main .= $page->inc("snippets/messages");

$hidden = array('presentation' => "");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url2->make("current", array('action' => "", 'id' => "")));
}
$buttons['new'] = $page->l($dico->t('NouvelleApplication'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'attributs' => $dico->t('Attributs'),
		'produits' => $dico->t('Produits'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$form->input(array('name' => "application[phrase_nom]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_nom]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->input(array('name' => "application[ref]", 'label' => $dico->t('Reference') ))}
{$form->input(array('name' => "application[phrase_url_key]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_url_key]", 'label' => $dico->t('UrlKey'), 'items' => $displayed_lang))}
{$form->input(array('name' => "application[phrase_description_courte]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description_courte]", 'label' => $dico->t('DescriptionCourte'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->input(array('name' => "application[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
{$dico->t('CochezAttributs')}
HTML;

	$pager = $pager_attributs;
	$filter = $filter_attributs;
	$application->all_attributs($filter);
	$main .= $page->inc("snippets/filter-form");
	foreach ($filter->selected() as $selected_attribut) {
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][classement]", 'forced_default' => true));
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][fiche_technique]", 'forced_default' => true));
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][pictos_vente]", 'forced_default' => true));
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][top]", 'forced_default' => true));
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][comparatif]", 'forced_default' => true));
		$main .= $form->hidden(array('name' => "attributs[$selected_attribut][filtre]", 'forced_default' => true));
	}
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer')));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser')));
}

if ($action == "edit") {
	$pager = new Pager($sql, array(20, 30, 50, 100, 200));
	$filter = new Filter($pager, array(
		'id' => array(
			'title' => 'ID',
			'type' => 'between',
			'order' => 'DESC',
			'field' => 'pr.id',
		),
		'ref' => array(
			'title' => $dico->t('Reference'),
			'type' => 'contain',
		),
		'nom' => array(
			'title' => $dico->t('Nom'),
			'type' => 'contain',
			'field' => 'ph.phrase',
		),
		'actif' => array(
			'title' => $dico->t('Nom'),
			'type' => 'select',
			'options' => array(1 => "Oui", 0 => "Non"),
		),
	), array(), "filter_application_produits");
	
	$application->liste_produits($filter);
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
{$page->inc("snippets/filter-simple")}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "application[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer')));
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleApplication');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerApplication')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfApplications');
		$filter = $filter_applications;
		$pager = $pager_applications;
		$application->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

