<?php

$menu->current('main/content/assets');

$config->core_include("produit/asset", "outils/langue", "outils/phrase", "outils/form");
$config->core_include("outils/filter", "outils/pager");

$page->css[] = $config->media("jquery-ui.min.css");
$page->css[] = $config->media("ui.multiselect.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->media("asset.js");
$page->javascript[] = $config->media("jquery-ui.min.js");
$page->javascript[] = $config->media("ui.multiselect.js");
$page->post_javascript[] = <<<JAVASCRIPT
$(".multiselect").multiselect();
JAVASCRIPT;

$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$titre_page = $dico->t("Assets");

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$asset = new Asset($sql, $phrase, $id_langues);
$all_tags = $asset->all_tags();
$all_targets = $asset->all_targets();
$all_langues = $asset->all_langues();

$assets_attributs = $config->param("assets_attributs");
$assets_links = $config->param("assets_links");

$pager_assets = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_assets_schema = array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
		'template' => "{id} <img alt=\"\" src=\"{$config->media("icon-file-tiny.png")}\" truesrc=\"{$config->get("asset_url")}{id}?thumb=1&tiny=1\" />",
	),
	'titre' => array(
		'title' => $dico->t('Titre'),
		'type' => 'contain',
		'field' => 'a.titre',
	),
);
if (in_array('gamme', $assets_links)) {
	$filter_assets_schema += array(
		'links_gamme' => array(
			'title' => $dico->t('Gammes'), 
			'type' => 'contain',
			'field' => 't_gamme.ref',
		),
	);
}
if (in_array('produit', $assets_links)) {
	$filter_assets_schema += array(
		'links_produit' => array(
			'title' => $dico->t('Produits'), 
			'type' => 'contain',
			'field' => 't_produit.ref',
		),
	);
}
if (in_array('sku', $assets_links)) {
	$filter_assets_schema += array(
		'links_sku' => array(
			'title' => $dico->t('SKU'), 
			'type' => 'contain',
			'field' => 't_sku.ref_ultralog',
		),
	);
}
foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
	$filter_assets_schema += array(
		"links_attribut_{$id_attributs}" => array(
			'title' => $attribut['nom'], 
			'type' => 'contain',
			'field' => "p_attribut_{$id_attributs}.phrase",
		),
	);
}
$filter_assets_schema += array(
	'tags' => array(
		'title' => $dico->t('Tags'),
		'type' => 'select',
		'field' => 'at.id',
		'options' => $all_tags,
	),
	'targets' => array(
		'title' => $dico->t('CanauxDiffusion'),
		'type' => 'select',
		'field' => 'atg.id',
		'options' => $all_targets,
	),
	'actif' => array(
		'title' => $dico->t('Actif'),
		'type' => 'select',
		'options' => array(1 => $dico->t("Oui"), 0 => $dico->t("Non")),
		'field' => 'a.actif',
	),
	'public' => array(
		'title' => $dico->t('Public'),
		'type' => 'select',
		'options' => array(1 => $dico->t("Oui"), 0 => $dico->t("Non")),
		'field' => 'a.public',
	),
);
$filter_assets = new Filter($pager_assets, $filter_assets_schema, array(), "filter_assets");

$action = $url->get('action');
if ($id = $url->get('id')) {
	$loaded = $asset->load($id);
	if (!$loaded) {
		$url->redirect("current", array('action' => "", 'id' => ""));
	}
}

$form = new Form(array(
	'id' => "form-edit-asset-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"cancel",
	),
	'files' => array("asset_file"),
));

if (!isset($section)) {
	$section = "presentation";
}
if ($form->value('section')) {
	$section = $form->value('section');
}

$traduction = $form->value("lang");

$messages = array();

$form_action = null;
if ($form->is_submitted() and $form->validate()) {
	$data = $form->escape_values();
	switch ($form_action = $form->action()) {
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
			$asset->delete($data);
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "delete-selected":
			foreach ($filter_assets->selected() as $id_assets) {
				if ($asset->load($id_assets)) {
					$asset->delete($data);
				}
			}
			break;
		default :
			if ($action == "edit" or $action == "create") {
				if ($action == "edit") {
					foreach (array("gamme", "produit", "sku") as $link_type) {
						$filter_name = "filter_assets_".$link_type;
						$page->inc("snippets/assets-links", array('link_type' => $link_type));
						$$filter_name->clean_data($data['asset_links'], $link_type);
					}
				}
				$data['file'] = $form->value('asset_file');
				$data['path'] =  $config->get("asset_path");
				if (!isset($data['tags'])) $data['tags'] = array();
				if (!isset($data['langues'])) $data['langues'] = array();
				if ($id = $asset->save($data)) {
					$asset_saved = true; // used for hook
					$form->reset();
					if ($action != "edit") {
						$url->redirect("current", array('action' => "edit", 'id' => $id));
					}
					$asset->load($id);
				}
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['asset'] = $asset->values;
	$form->default_values['phrases'] = $phrase->get($asset->phrases());
	$form->default_values['asset_links'] = $asset->links();
	$form->default_values['tags'] = $asset->tags();
	$form->default_values['targets'] = $asset->selected_targets();
	$form->default_values['langues'] = $asset->langues();
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

if ($action) {
	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}
else {
	$buttons['new'] = $page->l($dico->t('NouvelAsset'), $url->make("current", array('action' => "create", 'id' => "")));
	$buttons['import'] = $page->l($dico->t('ImporterAssets'), $url->make("assetsimport", array('action' => "", 'id' => "")));
	$buttons['delete-selected'] = $form->input(array('type' => "submit", 'value' => $dico->t('Supprimer'), 'name' => "delete-selected", 'class' => "delete"));
}

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

$apercu = "";
if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
	);
	if (in_array('gamme', $assets_links)) {
		$sections['gammes'] = $dico->t('Gammes');
	}
	if (in_array('produit', $assets_links)) {
		$sections['produits'] = $dico->t('Produits');
	}
	if (in_array('sku', $assets_links)) {
		$sections['sku'] = $dico->t('SKU');
	}
	if ($assets_attributs) {
		$sections['attributs'] = $dico->t('Attributs');
	}
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");

	$href = $config->get("asset_url").$asset->id."?dl=1";
	$src = $config->get("asset_url").$asset->id."?thumb=1";
	if ($asset->values['actif']) {
		$apercu = <<<HTML
<a href="{$href}">
<img alt="{$asset->values['titre']}" title="{$dico->t("Telecharger")}" class="asset" src="{$src}" />
</a>
HTML;
	}
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$apercu}
{$form->input(array('type' => "file", 'name' => "asset_file", 'label' => $dico->t('Fichier')))}
{$form->input(array('name' => "asset[titre]", 'label' => $dico->t('Titre')))}
{$form->input(array('name' => "asset[phrase_nom]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_nom]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->input(array('name' => "asset[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->select(array('name' => "tags[]", 'options' => $all_tags, 'label' => $dico->t('Tags'), 'multiple' => true))}
{$form->select(array('name' => "langues[]", 'options' => $all_langues, 'label' => $dico->t('Langues'), 'multiple' => true))}
{$form->input(array('type' => "checkbox", 'name' => "asset[actif]", 'label' => $dico->t('Actif')))}
{$form->input(array('type' => "checkbox", 'name' => "asset[public]", 'label' => $dico->t('Public')))}
{$form->input(array('type' => "checkbox", 'name' => "targets", 'items' => $all_targets, 'item_as_value' => true, 'label' => $dico->t('CanauxDiffusion')))}
{$form->input(array('name' => "asset[copyright]", 'label' => $dico->t('Copyright')))}
{$form->textarea(array('name' => "asset[infos]", 'label' => $dico->t('Infos')))}
{$form->fieldset_end()}
HTML;
}
if ($action == "edit") {
	if (in_array('gamme', $assets_links)) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Gammes'), 'class' => "produit-section produit-section-gammes".$hidden['gammes'], 'id' => "produit-section-gammes"))}
{$page->inc("snippets/assets-links", array('link_type' => "gamme"))}
{$form->fieldset_end()}
HTML;
	}
	if (in_array('produit', $assets_links)) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
{$page->inc("snippets/assets-links", array('link_type' => "produit"))}
{$form->fieldset_end()}
HTML;
	}
	if (in_array('sku', $assets_links)) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('SKU'), 'class' => "produit-section produit-section-sku".$hidden['sku'], 'id' => "produit-section-sku"))}
{$page->inc("snippets/assets-links", array('link_type' => "sku"))}
{$form->fieldset_end()}
HTML;
	}
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "asset[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer')));

	foreach (array("gamme", "produit") as $link_type) {
		$filter_name = "filter_assets_{$link_type}";
		foreach ($$filter_name->selected() as $selected_attribut) {
			$main .= $form->hidden(array('name' => "asset_{$link_type}[$selected_attribut][classement]", 'if_not_yet_rendered' => true));
			$main .= $form->hidden(array('name' => "asset_{$link_type}[$selected_attribut][groupe]", 'if_not_yet_rendered' => true));
		}
	}

	if ($assets_attributs) {		
		$links = $asset->links();
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
HTML;
		foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
			$values = isset($links['attribut-'.$id_attributs]) ? array_keys($links['attribut-'.$id_attributs]) : array();
			$main .= <<<HTML
{$form->select(array('name' => "asset_links[attribut-{$id_attributs}][]", 'options' => $attribut['options'], 'label' => "{$attribut['nom']}", 'forced_value' => $values, 'multiple' => true))}
HTML;
		}
	}
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelAsset');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerAsset')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfAssets');
		$filter = $filter_assets;
		$pager = $pager_assets;
		$asset->liste($assets_links, $assets_attributs, $id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
