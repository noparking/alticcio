<?php

$menu->current('main/content/assets');

$config->core_include("produit/asset", "outils/langue", "outils/phrase", "outils/form");
$config->core_include("outils/filter", "outils/pager", "assets/assets_import");

$page->css[] = $config->media("jquery-ui.min.css");
$page->css[] = $config->media("ui.multiselect.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->media("asset.js");
$page->javascript[] = $config->media("jquery-ui.min.js");
$page->javascript[] = $config->media("ui.multiselect.js");
$page->post_javascript["multiselect"] = <<<JAVASCRIPT
$(document).ready(function() {
	$(".multiselect").multiselect();
});
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

$sources = $config->get('asset_import');
$assets_import = new AssetsImport($sql, array('sources' => $sources));

$assets_attributs = $config->param("assets_attributs");
$assets_links = $config->param("assets_links");

$pager_assets = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_assets_schema = array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
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
$filter_assets_schema += array(
	'tags' => array(
		'title' => $dico->t('Tags'),
		'type' => 'select',
		'field' => 'at.id',
		'options' => $asset->all_tags(),
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

if ($action == "import") {
	$assets_import->import();
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
		case "import":
			$id_import = $form->action_arg();
			$asset_to_import = $assets_import->load(array('id' => $id_import));
			$savedata['asset'] = $data['assets'][$id_import];
			if ($id_assets = $data['existing-asset'][$id_import]) {
				$savedata['asset']['id'] = $id_assets;
			}
			else {
				$savedata['asset']['id'] = $asset->save($savedata);
			}
			if (isset($data['gammes'][$id_import])) {
				foreach ($data['gammes'][$id_import] as $id_gammes) {
					$savedata['asset_links']['gamme'][$id_gammes]['classement'] = 0;
				}
			}
			if (isset($data['produits'][$id_import])) {
				foreach ($data['produits'][$id_import] as $id_produits) {
					$savedata['asset_links']['produit'][$id_produits]['classement'] = 0;
				}
			}
			if (isset($data['skus'][$id_import])) {
				foreach ($data['skus'][$id_import] as $id_sku) {
					$savedata['asset_links']['sku'][$id_sku]['classement'] = 0;
				}
			}
			if (isset($data['attributs'][$id_import])) {
				foreach ($data['attributs'][$id_import] as $id_attributs => $options) {
					foreach ($options as $option) {
						$savedata['asset_links']['attribut-'.$id_attributs][$option]['classement'] = 0;
					}
				}
			}
			if (isset($data['tags'][$id_import])) $savedata['tags'] = $data['tags'][$id_import];
			if (isset($data['langues'][$id_import])) $savedata['langues'] = $data['langues'][$id_import];
			$savedata['file'] = $sources[$asset_to_import['source']].$asset_to_import['fichier'];
			$savedata['path'] =  $config->get("asset_path");
			$asset->save($savedata);
			$assets_import->save(array('id' => $id_import, 'action' => "", 'id_assets' => $asset->id));
			unlink($savedata['file']);
			$url->redirect("current", array('action' => "edit", 'id' => $asset->id));
			break;
		case "discard":
			$id_import = $form->action_arg();
			$asset_to_import = $assets_import->load(array('id' => $id_import));
			unlink($sources[$asset_to_import['source']].$asset_to_import['fichier']);
			$assets_import->save(array('id' => $id_import, 'action' => ""));
			break;
		case "import-selected":
			foreach ($data['assets-import-select'] as $id_import => $rien) {
				$asset_to_import = $assets_import->load(array('id' => $id_import));
				$savedata['asset'] = $data['assets'][$id_import];
				if ($id_assets = $data['existing-asset'][$id_import]) {
					$savedata['asset']['id'] = $id_assets;
				}
				else {
					$savedata['asset']['id'] = $asset->save($savedata);
				}
				if (isset($data['gammes'][$id_import])) {
					foreach ($data['gammes'][$id_import] as $id_gammes) {
						$savedata['asset_links']['gamme'][$id_gammes]['classement'] = 0;
					}
				}
				if (isset($data['produits'][$id_import])) {
					foreach ($data['produits'][$id_import] as $id_produits) {
						$savedata['asset_links']['produit'][$id_produits]['classement'] = 0;
					}
				}
				if (isset($data['skus'][$id_import])) {
					foreach ($data['skus'][$id_import] as $id_sku) {
						$savedata['asset_links']['sku'][$id_sku]['classement'] = 0;
					}
				}
				if (isset($data['attributs'][$id_import])) {
					foreach ($data['attributs'][$id_import] as $id_attributs => $options) {
						foreach ($options as $option) {
							$savedata['asset_links']['attribut-'.$id_attributs][$option]['classement'] = 0;
						}
					}
				}
				if (isset($data['tags'][$id_import])) $savedata['tags'] = $data['tags'][$id_import];
				if (isset($data['langues'][$id_import])) $savedata['langues'] = $data['langues'][$id_import];
				$savedata['file'] = $sources[$asset_to_import['source']].$asset_to_import['fichier'];
				$savedata['path'] =  $config->get("asset_path");
				$asset->save($savedata);
				unlink($savedata['file']);
				$assets_import->save(array('id' => $id_import, 'action' => "", 'id_assets' => $asset->id));
			}
			break;
		case "discard-selected":
			foreach ($data['assets-import-select'] as $id_import => $delete) {
				if ($delete) {
					unlink($config->get('asset_import')."/".$fichier);
					$assets_import->save(array('id' => $id_import, 'action' => ""));
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

if ($action == "import") {
	$form->reset();
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}
else if ($action == 'edit') {
	$asset_links = $asset->links();
}

if ($action == 'edit') {
	$form->default_values['asset'] = $asset->values;
	$form->default_values['phrases'] = $phrase->get($asset->phrases());
	$form->default_values['asset_links'] = $asset->links();
	$form->default_values['tags'] = $asset->tags();
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
	$buttons['import'] = $page->l($dico->t('ImporterAssets'), $url->make("current", array('action' => "import", 'id' => "")));
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

	$src = $config->get("asset_url").$asset->id."?dl=1";
	if ($asset->is_image() and $asset->values['actif'] and $asset->values['public']) {
		$apercu = <<<HTML
<a href="{$src}">
<img alt="{$asset->values['titre']}" title="{$dico->t("Telecharger")}" class="asset" src="{$src}" />
</a>
HTML;
	}
	else if($asset->values['actif']) {
		$apercu = <<<HTML
<ul class="buttons_actions"><li>
<a href="{$src}">
{$dico->t("Telecharger")}
</a>
</li></ul>
HTML;
	}
}
$all_tags = $asset->all_tags();
$all_langues = $asset->all_langues();

foreach ($asset->all_links_gamme() as $key => $value) {
	$all_gammes[$key] = "{$value['nom']} ({$value['ref']})";
}

foreach ($asset->all_links_produit() as $key => $value) {
	$all_produits[$key] = "{$value['nom_gamme']} {$value['nom']} ({$value['ref']})";
}

foreach ($asset->all_links_sku() as $key => $value) {
	$all_skus[$key] = "{$value['nom']} ({$value['ref']})";
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
{$form->input(array('name' => "asset[copyright]", 'label' => $dico->t('Copyright')))}
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
	case "import" :
		$titre_page = $dico->t('ImporterAssets');
		$main = <<<HTML
<div>
Pour la selection :
{$form->input(array('type' => "submit", 'name' => "import-selected", 'value' => "Importer"))}
{$form->input(array('type' => "submit", 'name' => "discard-selected", 'value' => "Ignorer"))}
{$form->input(array('type' => "submit", 'name' => "edit-selected", 'class' => "assets-import-edit-selected", 'value' => "Editer les informations"))}
<div class="assets-import-edit-selected-form" style="display: none;">
	<table>
HTML;
		if (in_array("gamme", $assets_links)) {
			$main .= <<<HTML
		<tr><td>{$dico->t('Gammes')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-gammes", 'options' => $all_gammes, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
HTML;
		}
		if (in_array("produit", $assets_links)) {
			$main .= <<<HTML
		<tr><td>{$dico->t('Produits')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-produits", 'options' => $all_produits, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
HTML;
		}
		if (in_array("sku", $assets_links)) {
			$main .= <<<HTML
		<tr><td>{$dico->t('SKU')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-skus", 'options' => $all_skus, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
HTML;
		}
		if ($assets_attributs) {
			foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
				$main .= <<<HTML
		<tr><td>{$attribut['nom']}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-attribut-{$id_attributs}", 'options' => $attribut['options'], 'multiple' => true, 'template' => "#{field}"))}</td></tr>
HTML;
			}
		}
		$main .= <<<HTML
		<tr><td>{$dico->t('Tags')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-tags", 'options' => $all_tags, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Langues')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-langues", 'options' => $all_langues, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Actif')}</td><td>{$form->input(array('class' => "copy-all", 'type' => "checkbox", 'name' => "asset-import-actif", 'checked' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Public')}</td><td>{$form->input(array('class' => "copy-all", 'type' => "checkbox", 'name' => "asset-import-public", 'checked' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Copyright')}</td><td>{$form->input(array('class' => "copy-all", 'name' => "asset-import-copyright", 'value' => "Dickson-Constant", 'template' => "#{field}"))}</td></tr>
	</table>
	{$form->input(array('type' => "submit", 'name' => "copy-all", 'class' => "assets-import-copy-all", 'value' => "Valider"))}
</div>
</div>
<table>
<tr>
	<th><input type="checkbox" name="assets-import-select-all" class="assets-import-select-all" /></th>
	<th>{$dico->t('Source')}</th>
	<th>{$dico->t('NouvelAsset')}</th>
	<th>{$dico->t('AssetExistant')}</th>
	<th>{$dico->t('Infos')}</th>
	<th>{$dico->t('Actions')}</th>
</tr>
HTML;
		$something_to_import = false;
		$options = array(0 => "");
		$options_values = array();
		foreach ($asset->liste() as $row) {
			$options[$row['id']] = "{$row['id']}) {$row['fichier']}";
			$options_values[$row['fichier']] = $row['id'];
		}
		foreach ($assets_import->liste() as $id_import => $asset_to_import) {
			$dir = $sources[$asset_to_import['source']];
			if (file_exists($dir.$asset_to_import['fichier'])) {
				$something_to_import = true;
				$main .= <<<HTML
<tr>
	<td><input type="checkbox" name="assets-import-select[{$asset_to_import['id']}]" class="assets-import-select" /></td>
	<td>{$asset_to_import['source']}</td>
	<td>{$asset_to_import['fichier']}</td>
	<td>{$form->select(array('name' => "existing-asset[{$asset_to_import['id']}]", 'options' => $options, 'forced_value' => $asset_to_import['id_assets'], 'class' => "assets-options", 'template' => "#{field}"))}</td>
	<td class="assets-import-infos">
		<div class="assets-import-infos-form" style="display: none;">
			<a class="assets-import-infos-switch" href="#">Masquer</a>
			<table>
				<tr><td>{$dico->t('Titre')}</td><td>{$form->input(array('name' => "assets[$id_import][titre]", 'value' => isset($asset_to_import['asset_data']['fichier']) ? $asset_to_import['asset_data']['fichier'] : $asset_to_import['fichier'], 'template' => "#{field}"))}</td></tr>
HTML;
				if (in_array("gamme", $assets_links)) {
					$main .= <<<HTML
				<tr><td>{$dico->t('Gammes')}</td><td>{$form->select(array('name' => "gammes[$id_import][]", 'class' => "asset-import-gammes", 'options' => $all_gammes, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
HTML;
				}
				if (in_array("produit", $assets_links)) {
					$main .= <<<HTML
				<tr><td>{$dico->t('Produits')}</td><td>{$form->select(array('name' => "produits[$id_import][]", 'class' => "asset-import-produits", 'options' => $all_produits, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
HTML;
				}
				if (in_array("sku", $assets_links)) {
					$main .= <<<HTML
				<tr><td>{$dico->t('SKU')}</td><td>{$form->select(array('name' => "skus[$id_import][]", 'class' => "asset-import-skus", 'options' => $all_skus, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
HTML;
				}
				if ($assets_attributs) {
					foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
						$main .= <<<HTML
					<tr><td>{$attribut['nom']}</td><td>{$form->select(array('name' => "attributs[$id_import][$id_attributs][]", 'class' => "asset-import-attribut-{$id_attributs}", 'options' => $attribut['options'], 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
HTML;
					}
				}
				$main .= <<<HTML
				<tr><td>{$dico->t('Tags')}</td><td>{$form->select(array('name' => "tags[$id_import][]", 'class' => "asset-import-tags", 'options' => $all_tags, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Langues')}</td><td>{$form->select(array('name' => "langues[$id_import][]", 'class' => "asset-import-langues", 'options' => $all_langues, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Actif')}</td><td>{$form->input(array('type' => "checkbox", 'name' => "assets[$id_import][actif]", 'class' => "asset-import-actif", 'checked' => true, 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Public')}</td><td>{$form->input(array('type' => "checkbox", 'name' => "assets[$id_import][public]", 'class' => "asset-import-public", 'checked' => true, 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Copyright')}</td><td>{$form->input(array('name' => "assets[$id_import][copyright]", 'class' => "asset-import-copyright", 'value' => "Dickson-Constant", 'template' => "#{field}"))}</td></tr>
			</table>
		</div>
		<div class="assets-import-infos-noform">
			<a class="assets-import-infos-switch" href="#">Afficher</a>
		</div>
	</td>
	<td>
		{$form->input(array('type' => "submit", 'name' => "import[$id_import]", 'value' => "Importer"))}
		{$form->input(array('type' => "submit", 'name' => "discard[$id_import]", 'value' => "Ignorer"))}
	</td>
</tr>
HTML;
			}
		}
		$main .= <<<HTML
</table>		
HTML;
		if (!$something_to_import) {
			$main = <<<HTML
<p>{$dico->t("RienAImporter")}</p>
HTML;
		}
		break;
	default :
		$titre_page = $dico->t('ListeOfAssets');
		$filter = $filter_assets;
		$pager = $pager_assets;
		$asset->liste($assets_links, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

