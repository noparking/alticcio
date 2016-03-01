<?php

$menu->current('main/products/catalogs');

$config->core_include("produit/catalogue", "produit/catalogue_categorie");
$config->core_include("database/tools", "outils/form", "outils/mysql", "outils/url_redirection");
$config->core_include("outils/filter", "outils/pager", "outils/langue", "outils/phrase");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->media("catalogue.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->media("dteditor.css");

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$url_redirection = new UrlRedirection($sql);

$object = $categorie = new CatalogueCategorie($sql, $phrase, $id_langues);
$catalogue = new Catalogue($sql);

$translate = $config->param('catalogues_translate');
$symlink = $config->param('catalogues_symlink');

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$categorie->load($id);
	$catalogue->load($categorie->original_id_catalogues);
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
$traduction = $form->value("lang");

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
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'ph.phrase',
	),
	'gamme' => array(
		'title' => $dico->t('Gamme'),
		'type' => 'select',
		'field' => 'pr.id_gammes',
		'values' => array(0 => ""),
		'options' => $categorie->gammes(),
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
$pager = new Pager($sql, array(10, 30, 50, 100, 200), "pager_produit");
$produits = $categorie->produits();
$filter = $filter_catalogue_categorie = new Filter($pager, $filter_schema_produits, array_keys($produits), "filter_produits_$id", true);

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
		case "add-bloc" :
			$categorie->add_bloc($data);
			$form->forget_value("new_bloc");
			break;
		case "delete-bloc" :
			$categorie->delete_bloc($data, $form->action_arg());
			break;
		default :
			if ($action == "edit") {
				$page->inc("snippets/assets");
				$filter_assets->clean_data($data, 'assets');

				if ($form->validate()) {
					$selected_produits = $filter_catalogue_categorie->selected();
					if (isset($data['produits'])) {
						foreach ($data['produits'] as $id_produits => $p) {
							if (!in_array($id_produits, $selected_produits)) {
								unset($data['produits'][$id_produits]);
							}
						}
					}
					if ($translate) {
						$id_saved = $url_redirection->save_object($categorie, $data, array('phrase_titre_url' => "phrase_nom"));
					}
					else {
						$id_saved = $url_redirection->save_object($categorie, $data, array('titre_url' => "nom"));
					}
					if ($id_saved === false) {
						$messages[] = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
					}
					else if ($id_saved > 0) {
						$categorie->load($id);
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
	$categorie_assets = $categorie->assets();
	$form->default_values['assets'] = $categorie_assets;
	$assets_selected = array_keys($categorie_assets);
	$form->default_values['phrases'] = $phrase->get($categorie->phrases());
	if ($categorie->is_symlink) {
		$messages[] = '<p class="message">'."Cette catégorie est un lien symbolique.".'</p>';
		$form->default_values['symlink']['catalogue'] = $categorie->values['id_catalogues'];
		$form->default_values['catalogue_categorie']['id_symlink'] = $categorie->id;
		$form->default_values['catalogue_categorie']['id_parent'] = $categorie->original_id_parent;
	}
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = "";
$left = "";

if ($translate) {
	// variable $displayed_lang définie dans ce snippet
	$main .= $page->inc("snippets/translate");
}

$main .= $page->inc("snippets/messages");

if ($action == "edit") {
	$bloc_options = $categorie->bloc_options();
	$titre_page = $dico->t('EditerCategorie')." # ID : ".($categorie->is_symlink ? $categorie->original_id." -&gt; " : "").$categorie->id;
	$sections = array(
		'informations' => $dico->t('Informations'),
		'produits' => $dico->t('Produits'),
		'referencement' => $dico->t('Referencement'),
		'blocs' => $dico->t('Blocs'),
	);
	if ($config->param('assets')) {
		$sections['assets'] = $dico->t('Assets');
	}
	if ($symlink) {
		$sections['symlink'] = $dico->t('Lien symbolique');
	}
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
	$categories_options = options_select_tree(DBTools::tree($catalogue->categories(), $categorie->original_id), $form, "categories");
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "catalogue_categorie[id]"))}
{$form->input(array('type' => "hidden", 'name' => "catalogue_categorie[id_catalogues]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
{$form->fieldset_start(array('legend' => $dico->t('Informations'), 'class' => "produit-section produit-section-informations".$hidden['informations'], 'id' => "produit-section-informations"))}
HTML;
	if ($translate) {
		$main .= <<<HTML
{$form->input(array('name' => "catalogue_categorie[phrase_nom]", 'type' => "hidden" ))}
{$form->input(array('name' => "phrases[phrase_nom]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->input(array('name' => "catalogue_categorie[phrase_description]", 'type' => "hidden" ))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang))}
{$form->input(array('name' => "catalogue_categorie[phrase_titre_url]", 'type' => "hidden" ))}
{$form->input(array('name' => "phrases[phrase_titre_url]", 'label' => "Titre URL", 'items' => $displayed_lang))}
HTML;
	}
	else {
		$main .= <<<HTML
{$form->input(array('name' => "catalogue_categorie[nom]", 'label' => $dico->t('Nom') ))}
{$form->input(array('name' => "catalogue_categorie[titre_url]", 'label' => "Titre URL" ))}
HTML;
	}
//{$form->input(array('name' => "catalogue_categorie[correspondance]", 'label' => $dico->t('CorrespondanceMagento') ))}
	$main .= <<<HTML
{$form->select(array('name' => "catalogue_categorie[id_parent]", 'label' => $dico->t('CategorieParent'), 'options' => $categories_options))}
{$form->input(array('name' => "catalogue_categorie[classement]", 'label' => $dico->t('Classement') ))}
{$form->select(array('name' => "catalogue_categorie[statut]", 'label' => $dico->t('Statut'), 'options' => array($dico->t('Desactive'), $dico->t('Active') )))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
<p>{$dico->t('ListeOfProduitsCategories')}</p>
HTML;
	$filter = $filter_catalogue_categorie;
	$categorie->all_produits($filter);
	$main .= $page->inc("snippets/filter-form");
	foreach ($filter->selected() as $selected_produit) {
		$main .= "\n".$form->hidden(array('name' => "produits[$selected_produit][classement]"));
	}
	$main .= <<<HTML
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Referencement'), 'class' => "produit-section produit-section-referencement".$hidden['referencement'], 'id' => "produit-section-referencement"))}
HTML;
	if ($translate) {
		$main .= <<<HTML
{$form->input(array('name' => "catalogue_categorie[phrase_meta_title]", 'type' => "hidden" ))}
{$form->textarea(array('name' => "phrases[phrase_meta_title]", 'label' => $dico->t('MetaTitle'), 'class' => "dteditor", 'items' => $displayed_lang))}
{$form->input(array('name' => "catalogue_categorie[phrase_meta_keywords]", 'type' => "hidden" ))}
{$form->textarea(array('name' => "phrases[phrase_meta_keywords]", 'label' => $dico->t('MetaKeywords'), 'class' => "dteditor", 'items' => $displayed_lang))}
{$form->input(array('name' => "catalogue_categorie[phrase_meta_description]", 'type' => "hidden" ))}
{$form->textarea(array('name' => "phrases[phrase_meta_description]", 'label' => $dico->t('MetaDescription'), 'class' => "dteditor", 'items' => $displayed_lang))}
HTML;
	}
	else {
		$main .= <<<HTML
{$form->textarea(array('name' => "catalogue_categorie[meta_title]", 'label' => $dico->t('MetaTitle'), 'class' => "dteditor"))}
{$form->textarea(array('name' => "catalogue_categorie[meta_keywords]", 'label' => $dico->t('MetaKeywords'), 'class' => "dteditor"))}
{$form->textarea(array('name' => "catalogue_categorie[meta_description]", 'label' => $dico->t('MetaDescription'), 'class' => "dteditor"))}
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;
	if ($config->param('assets')) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Assets'), 'class' => "produit-section produit-section-assets".$hidden['assets'], 'id' => "produit-section-assets"))}
{$page->inc("snippets/assets")}
{$form->fieldset_end()}
HTML;
		foreach (array_intersect($filter_assets->selected(), array_keys($categorie->all_assets())) as $selected_asset) {
			$main .= $form->hidden(array('name' => "assets[$selected_asset][classement]", 'if_not_yet_rendered' => true));
		}
	}
	if ($symlink) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => "Lien symbolique", 'class' => "produit-section produit-section-symlink".$hidden['symlink'], 'id' => "produit-section-assets"))}
{$form->select(array('class' => "symlink-catalogue", 'name' => "symlink[catalogue]", 'label' => "Catalogue", 'options' => $categorie->catalogues()))}</td>
<div id="categories-for-catalogue">
HTML;
		$categories_by_catalogues = $categorie->categories_by_catalogues();
		if ($categorie->is_symlink) {
			$categories_options = options_select_tree(DBTools::tree($categories_by_catalogues[$categorie->values['id_catalogues']]), $form, "categories");
			$main .= <<<HTML
{$form->select(array('name' => "catalogue_categorie[id_symlink]", 'label' => "Catégorie", 'options' => $categories_options))}</td>
HTML;
		}
		$main .= <<<HTML
</div>
{$form->fieldset_end()}
HTML;
	}
	$buttons['back'] = $page->l($dico->t('RetourCatalogue'), $url2->make("current", array('type' => "catalogues", 'action' => "edit", 'id' => $catalogue->id)));
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "create", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Blocs'), 'class' => "produit-section produit-section-blocs".$hidden['blocs'], 'id' => "produit-section-blocs"))}
<table id="blocs">
<thead>
<tr>
	<th>{$dico->t('Utilisation')}</th>
	<th>{$dico->t('Bloc')}</th>
	<td></td>
</tr>
</thead>
<tbody>
HTML;
	foreach ($categorie->blocs() as $utilisation => $blocs) {
		foreach ($blocs as $id => $id_blocs) {
			$main .= <<<HTML
<tr>
	<td>{$utilisation}</td>
	<td>{$bloc_options[$id_blocs]}</td>
	<td>
		{$form->input(array('type' => "submit", 'name' => "delete-bloc[{$id}]", 'class' => "delete", 'value' => "X"))}
	</td>
</tr>
HTML;
		}
	}
	$main .= <<<HTML
</tbody>
</table>
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('AjouterUnBloc'), 'class' => "produit-section produit-section-blocs".$hidden['blocs'], 'id' => "produit-section-blocs-new"))}
{$form->input(array('type' => "text", 'name' => "new_bloc[utilisation]", 'label' => $dico->t('Utilisation')))}
{$form->select(array('name' => "new_bloc[id_blocs]", 'label' => "Bloc associé", 'options' => $bloc_options))}
{$form->input(array('type' => "submit", 'name' => "add-bloc", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
HTML;
}

$form_end = $form->form_end();

if ($symlink) {
	foreach ($categories_by_catalogues as $id_catalogues => $categories) {
		$categories_options = options_select_tree(DBTools::tree($categories), $form, "categories");
		$form_end .= <<<HTML
<div id="categories-for-catalogue-{$id_catalogues}" style="display: none;">
{$form->select(array('name' => "catalogue_categorie[id_symlink]", 'label' => "Catégorie", 'options' => $categories_options))}</td>
</div>
HTML;
	}
}
