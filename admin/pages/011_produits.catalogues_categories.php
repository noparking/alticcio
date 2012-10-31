<?php

$menu->current('main/products/catalogs');

$config->core_include("produit/catalogue", "produit/catalogue_categorie");
$config->core_include("database/tools", "outils/form", "outils/mysql", "outils/url_redirection");
$config->core_include("outils/filter", "outils/pager", "outils/langue", "outils/phrase");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->media("dteditor.css");
$page->jsvars[] = array(
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$url_redirection = new UrlRedirection($sql);

$categorie = new CatalogueCategorie($sql, $phrase, $config->get("langue"));
$catalogue = new Catalogue($sql);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$categorie->load($id);
	$catalogue->load($categorie->values['id_catalogues']);
}

$form = new Form(array(
	'id' => "form-edit-catalogue-categorie-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel"),
));

$section = "informations";
if ($form->value('section')) {
	$section = $form->value('section');
}

$filter_schema_produits = array(
	'id' => array(
		'title' => 'ID', 
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'pr.id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
	),
	'phrase_ultralog' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'ph.phrase',
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'link.classement',
		'form' => array(
			'name' => "produits[%id%][classement]",
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
		),
	),
);
$pager = new Pager($sql, array(10, 30, 50, 100, 200), "pager_produits");
$produits = $categorie->produits();
$filter = new Filter($pager, $filter_schema_produits, array_keys($produits), "filter_produits_$id", true);

if ($form->is_submitted()) {
	$data = $form->escape_values();
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
			$categorie->delete($data);
			$form->reset();
			$url2->redirect("current", array('type' => "catalogues", 'action' => "edit", 'id' => $catalogue->id));
			break;
		default :
			if ($action == "edit") {
				if ($form->validate()) {
					$selected_produits = $filter->selected();
					foreach ($data['produits'] as $id_produits => $p) {
						if (!in_array($id_produits, $selected_produits)) {
							unset($data['produits'][$id_produits]);
						}
					}
					$id_saved = $url_redirection->save_object($categorie, $data, array('titre_url' => "nom"));
					if ($id_saved === false) {
						$messages[] = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
					}
					else if ($id_saved > 0) {
						$form->reset();
					}
				}
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['catalogue_categorie'] = $categorie->values;
	$form->default_values['produits'] = $categorie->produits();
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = $page->inc("snippets/messages");
$left = "";

if ($action == "edit") {
	$titre_page = $dico->t('EditerCategorie')." # ID : ".$id;
	$sections = array(
		'informations' => $dico->t('Informations'),
		'produits' => $dico->t('Produits'),
		'referencement' => $dico->t('Referencement'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
	$categories_options = options_select_tree(DBTools::tree($catalogue->categories()), $form, "categories");
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "catalogue_categorie[id]"))}
{$form->input(array('type' => "hidden", 'name' => "catalogue_categorie[id_catalogues]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
{$form->fieldset_start(array('legend' => $dico->t('Informations'), 'class' => "produit-section produit-section-informations".$hidden['informations'], 'id' => "produit-section-informations"))}
{$form->input(array('name' => "catalogue_categorie[nom]", 'label' => $dico->t('Nom') ))}
{$form->input(array('name' => "catalogue_categorie[titre_url]", 'label' => "Titre URL" ))}
HTML;
//{$form->input(array('name' => "catalogue_categorie[correspondance]", 'label' => $dico->t('CorrespondanceMagento') ))}
	$main .= <<<HTML
{$form->select(array('name' => "catalogue_categorie[id_parent]", 'label' => $dico->t('CategorieParent'), 'options' => $categories_options))}
{$form->input(array('name' => "catalogue_categorie[classement]", 'label' => $dico->t('Classement') ))}
{$form->select(array('name' => "catalogue_categorie[statut]", 'label' => $dico->t('Statut'), 'options' => array($dico->t('Desactive'), $dico->t('Active') )))}
{$form->select(array('name' => "catalogue_categorie[id_blocs]", 'label' => "Bloc associé", 'options' => $categorie->bloc_options()))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
<p>{$dico->t('ListeOfProduitsCategories')}</p>
HTML;
	$categorie->all_produits($filter);
	$main .= $page->inc("snippets/filter-form");
	foreach ($filter->selected() as $selected_produit) {
		$main .= "\n".$form->hidden(array('name' => "produits[$selected_produit][classement]"));
	}
	$main .= <<<HTML
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Referencement'), 'class' => "produit-section produit-section-referencement".$hidden['referencement'], 'id' => "produit-section-referencement"))}
{$form->textarea(array('name' => "catalogue_categorie[meta_title]", 'label' => $dico->t('MetaTitle'), 'class' => "dteditor"))}
{$form->textarea(array('name' => "catalogue_categorie[meta_keywords]", 'label' => $dico->t('MetaKeywords'), 'class' => "dteditor"))}
{$form->textarea(array('name' => "catalogue_categorie[meta_description]", 'label' => $dico->t('MetaDescription'), 'class' => "dteditor"))}
{$form->fieldset_end()}
HTML;
	$buttons[] = $page->l($dico->t('RetourCatalogue'), $url2->make("current", array('type' => "catalogues", 'action' => "edit", 'id' => $catalogue->id)));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "create", 'value' => $dico->t('Enregistrer') ));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

$form_end = $form->form_end();

