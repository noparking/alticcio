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

$pager_assets = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_assets = new Filter($pager_assets, array(
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
	'tags' => array(
		'title' => $dico->t('Tags'),
		'type' => 'select',
		'field' => 'at.id',
		'options' => $asset->all_tags(),
	),
), array(), "filter_assets");

if ($id = $url->get('id')) {
	$asset->load($id);
}

$action = $url->get('action');
if ($id = $url->get('id')) {
	$asset->load($id);
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
		case "import":
			$fichier = $form->action_arg();
			$savedata['asset'] = $data['assets'][$fichier];
			if ($id_assets = $data['existing-asset'][$fichier]) {
				$savedata['asset']['id'] = $id_assets;
			}
			else {
				$savedata['asset']['id'] = $asset->save($savedata);
			}
			foreach ($data['gammes'][$fichier] as $id_gammes) {
				$savedata['asset_links']['gamme'][$id_gammes]['classement'] = 0;
			}
			foreach ($data['produits'][$fichier] as $id_produits) {
				$savedata['asset_links']['produit'][$id_produits]['classement'] = 0;
			}
			$savedata['tags'] = $data['tags'][$fichier];
			$savedata['langues'] = $data['langues'][$fichier];
			$savedata['file'] = $config->get('asset_import')."/".$fichier;
			$savedata['path'] =  $config->get("asset_path");
			$asset->save($savedata);
			unlink($savedata['file']);
			$url->redirect("current", array('action' => "edit", 'id' => $asset->id));
			break;
		case "discard":
			unlink($config->get('asset_import')."/".$form->action_arg());
			break;
		case "import-selected":
			foreach ($data['assets-import-select'] as $fichier => $rien) {
				$savedata['asset'] = $data['assets'][$fichier];
				if ($id_assets = $data['existing-asset'][$fichier]) {
					$savedata['asset']['id'] = $id_assets;
				}
				else {
					$savedata['asset']['id'] = $asset->save($savedata);
				}
				foreach ($data['gammes'][$fichier] as $id_gammes) {
					$savedata['asset_links']['gamme'][$id_gammes]['classement'] = 0;
				}
				foreach ($data['produits'][$fichier] as $id_produits) {
					$savedata['asset_links']['produit'][$id_produits]['classement'] = 0;
				}
				$savedata['tags'] = $data['tags'][$fichier];
				$savedata['langues'] = $data['langues'][$fichier];
				$savedata['file'] = $config->get('asset_import')."/".$fichier;
				$savedata['path'] =  $config->get("asset_path");
				$asset->save($savedata);
				unlink($savedata['file']);
			}
			break;
		case "discard-selected":
			foreach ($data['assets-import-select'] as $fichier => $delete) {
				if ($delete) {
					unlink($config->get('asset_import')."/".$fichier);
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
}

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

$apercu = "";
$attributs_for_assets = $config->get("attributs_for_assets");
if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'gammes' => $dico->t('Gammes'),
		'produits' => $dico->t('Produits'),
		'sku' => $dico->t('SKU'),
		);
	if ($attributs_for_assets) {
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
foreach (array("gamme", "produit", "sku") as $link_type) {
	$var = "all_{$link_type}s";
	foreach ($asset->all_links_by_type($link_type) as $key => $value) {
		${$var}[$key] = "{$value['nom']} ({$value['ref']})";
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
{$form->input(array('name' => "asset[copyright]", 'label' => $dico->t('Copyright')))}
{$form->fieldset_end()}
HTML;
}
if ($action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Gammes'), 'class' => "produit-section produit-section-gammes".$hidden['gammes'], 'id' => "produit-section-gammes"))}
{$page->inc("snippets/assets-links", array('link_type' => "gamme"))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
{$page->inc("snippets/assets-links", array('link_type' => "produit"))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('SKU'), 'class' => "produit-section produit-section-sku".$hidden['sku'], 'id' => "produit-section-sku"))}
{$page->inc("snippets/assets-links", array('link_type' => "sku"))}
{$form->fieldset_end()}

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

	if ($attributs_for_assets) {
		$possible_refs = implode("','", $attributs_for_assets);
		$q = <<<SQL
SELECT a.ref, oa.id_attributs, oa.id, p1.phrase AS phrase_option, p2.phrase AS phrase_nom FROM dt_options_attributs AS oa
INNER JOIN dt_attributs AS a ON a.id = oa.id_attributs
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = oa.phrase_option AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = a.phrase_nom AND p2.id_langues = $id_langues
WHERE a.ref IN ('$possible_refs')
SQL;
		$res = $sql->query($q);
		$attributs = array();
		$attributs_options = array();
		while ($row = $sql->fetch($res)) {
			$attributs[$row['id_attributs']] = array('ref' => $row['ref'], 'nom' => $row['phrase_nom']);
			$attributs_options[$row['id_attributs']][$row['id']] = $row['phrase_option'];
		}

		$links = $asset->links();
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
HTML;
		foreach ($attributs as $id_attributs => $attribut) {
			$values = isset($links[$attribut['ref']]) ? array_keys($links[$attribut['ref']]) : array();
			$main .= <<<HTML
{$form->select(array('name' => "asset_links[{$attribut['ref']}][]", 'options' => $attributs_options[$id_attributs], 'label' => "{$attribut['nom']}", 'forced_value' => $values, 'multiple' => true))}
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
		<tr><td>{$dico->t('Gammes')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-gammes", 'options' => $all_gammes, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Produits')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-produits", 'options' => $all_produits, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Tags')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-tags", 'options' => $all_tags, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Langues')}</td><td>{$form->select(array('class' => "copy-all", 'name' => "asset-import-langues", 'options' => $all_langues, 'multiple' => true, 'template' => "#{field}"))}</td></tr>
		<tr><td>{$dico->t('Copyright')}</td><td>{$form->input(array('class' => "copy-all", 'name' => "asset-import-copyright", 'value' => "Dickson-Constant", 'template' => "#{field}"))}</td></tr>
	</table>
	{$form->input(array('type' => "submit", 'name' => "copy-all", 'class' => "assets-import-copy-all", 'value' => "Valider"))}
</div>
</div>
<table>
<tr>
	<th><input type="checkbox" name="assets-import-select-all" class="assets-import-select-all" /></th>
	<th>{$dico->t('NouvelAsset')}</th>
	<th>{$dico->t('AssetExistant')}</th>
	<th>{$dico->t('Infos')}</th>
	<th>{$dico->t('Actions')}</th>
</tr>
HTML;
		$something_to_import = false;
		$options = array(0 => "");
		$options_values = array();
		foreach ($asset->liste($id_langues) as $row) {
			$options[$row['id']] = "{$row['id']}) {$row['fichier']}";
			$options_values[$row['fichier']] = $row['id'];
		}
		foreach (scandir($config->get('asset_import')) as $fichier) {
			if (strpos($fichier, ".") !== 0) {
				$value = 0;
				foreach ($options_values as $key => $val) {
					if (preg_replace("/\-.*/", "", $fichier) == preg_replace("/\-.*/", "", $key)) {
						$value = $val;
						break;
					}
				}
				$something_to_import = true;
				$main .= <<<HTML
<tr>
	<td><input type="checkbox" name="assets-import-select[{$fichier}]" class="assets-import-select" /></td>
	<td>$fichier</td>
	<td>{$form->select(array('name' => "existing-asset[$fichier]", 'options' => $options, 'forced_value' => $value, 'class' => "assets-options", 'template' => "#{field}"))}</td>
	<td class="assets-import-infos">
		<div class="assets-import-infos-form" style="display: none;">
			<a class="assets-import-infos-switch" href="#">Masquer</a>
			<table>
				<tr><td>{$dico->t('Titre')}</td><td>{$form->input(array('name' => "assets[$fichier][titre]", 'value' => $fichier, 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Gammes')}</td><td>{$form->select(array('name' => "gammes[$fichier][]", 'class' => "asset-import-gammes", 'options' => $all_gammes, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Produits')}</td><td>{$form->select(array('name' => "produits[$fichier][]", 'class' => "asset-import-produits", 'options' => $all_produits, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Tags')}</td><td>{$form->select(array('name' => "tags[$fichier][]", 'class' => "asset-import-tags", 'options' => $all_tags, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Langues')}</td><td>{$form->select(array('name' => "langues[$fichier][]", 'class' => "asset-import-langues", 'options' => $all_langues, 'multiple' => "normal", 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Actif')}</td><td>{$form->input(array('type' => "checkbox", 'name' => "assets[$fichier][actif]", 'checked' => true, 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Public')}</td><td>{$form->input(array('type' => "checkbox", 'name' => "assets[$fichier][public]", 'checked' => true, 'template' => "#{field}"))}</td></tr>
				<tr><td>{$dico->t('Copyright')}</td><td>{$form->input(array('name' => "assets[$fichier][copyright]", 'class' => "asset-import-copyright", 'value' => "Dickson-Constant", 'template' => "#{field}"))}</td></tr>
			</table>
		</div>
		<div class="assets-import-infos-noform">
			<a class="assets-import-infos-switch" href="#">Afficher</a>
		</div>
	</td>
	<td>
		{$form->input(array('type' => "submit", 'name' => "import[$fichier]", 'value' => "Importer"))}
		{$form->input(array('type' => "submit", 'name' => "discard[$fichier]", 'value' => "Ignorer"))}
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
		$asset->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
