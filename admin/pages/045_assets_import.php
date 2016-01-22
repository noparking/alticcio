<?php

$menu->current('main/content/assets');

$config->core_include("produit/asset", "assets/assets_import", "outils/form");

$page->css[] = $config->media("jquery-ui.min.css");
$page->css[] = $config->media("autocomplete.min.css");
$page->css[] = $config->media("multicombobox.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->media("asset.js");
$page->javascript[] = $config->media("jquery-ui.min.js");
$page->javascript[] = $config->media("autocomplete.min.js");
$page->javascript[] = $config->media("multicombobox.js");

$asset = new Asset($sql);

$all_tags = $asset->all_tags();
$all_targets = $asset->all_targets();
$all_langues = $asset->all_langues();

$assets_attributs = $config->param("assets_attributs");
$assets_links = $config->param("assets_links");

$sources = $config->get('asset_import');
$assets_import = new AssetsImport($sql, array('sources' => $sources));

$titre_page = $dico->t("ImporterAssets");

$form = new Form(array(
	'id' => "form-import-assets",
	'actions' => array(
		"import",
		"discard",
		"import-selected",
		"discard-selected",
	),
));

$form_action = null;
if ($form->is_submitted() and $form->validate()) {
	$data = $form->escape_values();
	switch ($form_action = $form->action()) {
		case "import":
			$id_import = $form->action_arg();
			$asset_to_import = $assets_import->load(array('id' => $id_import));
			$savedata['asset'] = $data['assets'][$id_import];
			if (isset($data['existing-asset'][$id_import])) {
				$savedata['asset']['id'] = $data['existing-asset'][$id_import];
			}
			else {
				$savedata['asset']['id'] = $asset->save($savedata);
			}
			if (isset($data['categories'][$id_import])) {
				foreach ($data['categories'][$id_import] as $id_categories) {
					$savedata['asset_links']['categorie'][$id_categories]['classement'] = 0;
				}
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
			if (isset($data['targets'][$id_import])) $savedata['targets'] = $data['targets'][$id_import];
			$savedata['file'] = $sources[$asset_to_import['source']].$asset_to_import['fichier'];
			$savedata['path'] =  $config->get("asset_path");
			$asset->save($savedata);
			$assets_import->save(array('id' => $id_import, 'action' => "", 'id_assets' => $asset->id));
			if ($asset_to_import['source'] != "ft") {
				unlink($savedata['file']);
			}
			$url->redirect("current", array('action' => "edit", 'id' => $asset->id));
			break;
		case "discard":
			$id_import = $form->action_arg();
			$asset_to_import = $assets_import->load(array('id' => $id_import));
			if ($asset_to_import['source'] != "ft") {
				unlink($sources[$asset_to_import['source']].$asset_to_import['fichier']);
			}
			$assets_import->save(array('id' => $id_import, 'action' => ""));
			break;
		case "import-selected":
			if (isset($data['assets-import-select'])) {
				foreach ($data['assets-import-select'] as $id_import => $rien) {
					$asset_to_import = $assets_import->load(array('id' => $id_import));
					$savedata = array(
						'asset' => $data['assets'][$id_import],
					);
					if (isset($data['existing-asset'][$id_import]) and $data['existing-asset'][$id_import]) {
						$savedata['asset']['id'] = $data['existing-asset'][$id_import];
					}
					else {
						$savedata['asset']['id'] = $asset->save($savedata);
					}
					if (isset($data['categories'][$id_import])) {
						foreach ($data['categories'][$id_import] as $id_categories) {
							$savedata['asset_links']['catalogue_categorie'][$id_categories]['classement'] = 0;
						}
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
					if (isset($data['targets'][$id_import])) $savedata['targets'] = $data['targets'][$id_import];
					$savedata['file'] = $sources[$asset_to_import['source']].$asset_to_import['fichier'];
					$savedata['path'] =  $config->get("asset_path");
					$asset->save($savedata);
					if ($asset_to_import['source'] != "ft") {
						unlink($savedata['file']);
					}
					$assets_import->save(array('id' => $id_import, 'action' => "", 'id_assets' => $asset->id));
				}
			}
			break;
		case "discard-selected":
			if (isset($data['assets-import-select'])) {
				foreach ($data['assets-import-select'] as $id_import => $delete) {
					if ($delete) {
						$asset_to_import = $assets_import->load(array('id' => $id_import));
						if ($asset_to_import['source'] != "ft") {
							unlink($sources[$asset_to_import['source']].$asset_to_import['fichier']);
						}
						$assets_import->save(array('id' => $id_import, 'action' => ""));
					}
				}
			}
			break;
	}
}

$form->reset();

$assets_import->import();

$form_start = $form->form_start();
	
$buttons['listassets'] = $page->l($dico->t('ListeOfAssets'), $url->make("assets", array('action' => "", 'id' => "")));

foreach ($asset->all_links_catalogue_categorie() as $key => $value) {
	if (isset($value['path'])) {
		$all_categories[$key] = "{$value['path']}";
	}
}

foreach ($asset->all_links_gamme() as $key => $value) {
	$all_gammes[$key] = "{$value['nom']} ({$value['ref']})";
}

foreach ($asset->all_links_produit() as $key => $value) {
	$all_produits[$key] = "{$value['nom_gamme']} {$value['nom']} ({$value['ref']})";
}

foreach ($asset->all_links_sku() as $key => $value) {
	$all_skus[$key] = "{$value['nom']} ({$value['ref']})";
}

if ($source = $url->get('action')) {
	$titre_page .= " (source : {$source})";
	$main = <<<HTML
<div>
Pour la selection :
{$form->input(array('type' => "submit", 'name' => "import-selected", 'value' => "Importer"))}
{$form->input(array('type' => "submit", 'name' => "discard-selected", 'value' => "Ignorer"))}
{$form->input(array('type' => "submit", 'name' => "edit-selected", 'class' => "assets-import-edit-selected", 'value' => "Editer les informations"))}
<div class="assets-import-edit-selected-form" style="display: none;">
	<table>
HTML;
	if (in_array("catalogue_categorie", $assets_links) and $source != "ft") {
		$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-categories", 'name' => "copy-asset-import-categories", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Categories')}</td><td><div class="multicombobox copy-all" list="categories" items="" name="asset-import-categories"></div></td>
		</tr>
HTML;
	}
	if (in_array("gamme", $assets_links)) {
		$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-gammes", 'name' => "copy-asset-import-gammes", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Gammes')}</td><td><div class="multicombobox copy-all" list="gammes" items="" name="asset-import-gammes"></div></td>
		</tr>
HTML;
	}
	if (in_array("produit", $assets_links) and $source != "ft") {
		$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-produits", 'name' => "copy-asset-import-produits", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Produits')}</td><td><div class="multicombobox copy-all" list="produits" items="" name="asset-import-produits"></div></td>
		</tr>
HTML;
	}
	if (in_array("sku", $assets_links) and $source != "ft") {
		$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-skus", 'name' => "copy-asset-import-skus", 'template' => "#{field}"))}</td>
			<td>{$dico->t('SKU')}</td><td><div class="multicombobox copy-all" list="skus" items="" name="asset-import-skus"></div></td>
		</tr>
HTML;
	}
	if ($assets_attributs and $source != "ft") {
		foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
			$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-attribut-{$id_attributs}", 'name' => "copy-asset-import-attribut-{$id_attributs}", 'template' => "#{field}"))}</td>
			<td>{$attribut['nom']}</td><td><div class="multicombobox copy-all" list="attribut-{$id_attributs}" items="" name="asset-import-attribut-{$id_attributs}"></div></td>
		</tr>
HTML;
		}
	}
	$main .= <<<HTML
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-tags", 'name' => "copy-asset-import-tags", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Tags')}</td><td><div class="multicombobox copy-all" list="tags" items="" name="asset-import-tags"></div></td>
		</tr>
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-langues", 'name' => "copy-asset-import-langues", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Langues')}</td><td><div class="multicombobox copy-all" list="langues" items="" name="asset-import-langues"></div></td>
		</tr>
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-actif", 'name' => "copy-asset-import-actif", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Actif')}</td><td>{$form->input(array('class' => "copy-all", 'type' => "checkbox", 'name' => "asset-import-actif", 'checked' => true, 'template' => "#{field}"))}</td>
		</tr>
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-public", 'name' => "copy-asset-import-public", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Public')}</td><td>{$form->input(array('class' => "copy-all", 'type' => "checkbox", 'name' => "asset-import-public", 'checked' => true, 'template' => "#{field}"))}</td>
		</tr>
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-targets", 'name' => "copy-asset-import-targets", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Targets')}</td><td><div class="multicombobox copy-all" list="targets" items="" name="asset-import-targets"></div></td>
		</tr>
		<tr>
			<td>{$form->input(array('type' => "checkbox", 'id' => "copy-asset-import-copyright", 'name' => "copy-asset-import-copyright", 'template' => "#{field}"))}</td>
			<td>{$dico->t('Copyright')}</td><td>{$form->input(array('class' => "copy-all", 'name' => "asset-import-copyright", 'value' => "Dickson-Constant", 'template' => "#{field}"))}</td>
		</tr>
	</table>
	{$form->input(array('type' => "submit", 'name' => "copy-all", 'class' => "assets-import-copy-all", 'value' => "Appliquer à la selection"))}
</div>
</div>
<table>
<tr>
	<th><input type="checkbox" name="assets-import-select-all" class="assets-import-select-all" /></th>
	<th></th>
	<th></th>
	<th></th>
	<th></th>
</tr>
HTML;
	$something_to_import = false;
	foreach ($assets_import->liste($source) as $id_import => $asset_to_import) {
		$default_items = array();
		$targets = $asset->all_targets();
		if ($asset_to_import['id_assets']) {
			$asset->load($asset_to_import['id_assets']);
			$form->default_values['assets'][$id_import] = $asset->values;
			foreach ($asset->links() as $link_type => $links) {
				if (strpos($link_type, "attribut") === 0) {
					$id_attributs = str_replace("attribut-", "", $link_type);
					$default_items['attributs'][$id_attributs] = implode_keys($links);
				}
				else {
					$default_items[$link_type."s"] = implode_keys($links);
				}
			}
			$default_items['tags'] = implode(",", $asset->tags());
			$default_items['langues'] = implode(",", $asset->langues());
			$targets = array();
			foreach ($asset->selected_targets() as $id_target => $bool) {
				if ($bool) {
					$targets[] = $id_target;
				}
			}
			$default_items['targets'] = implode(",", $targets);
		}
		else {
			$default_values = array(
				'titre' => $asset_to_import['fichier'],
				'copyright' => "Dickson-Constant",
				'actif' => true,
				'public' => true,
				'infos' => "",
			);
			foreach (array('titre', 'copyright', 'actif', 'public', 'infos') as $element) {
				$value = isset($asset_to_import['asset_data'][$element]) ? $asset_to_import['asset_data'][$element] : $default_values[$element];
				$form->default_values['assets'][$id_import][$element] = $value;
			}
			$default_items = array('categories' => "", 'gammes' => "", 'produits' => "", 'skus' => "", 'tags' => "", 'targets' => "");
			if (isset($asset_to_import['asset_data']['categories'])) {
				$default_items['categories'] = implode(",", $asset_to_import['asset_data']['categories']);
			}
			if (isset($asset_to_import['asset_data']['gammes'])) {
				$default_items['gammes'] = implode(",", $asset_to_import['asset_data']['gammes']);
			}
			if (isset($asset_to_import['asset_data']['produits'])) {
				$default_items['produits'] = implode(",", $asset_to_import['asset_data']['produits']);
			}
			if (isset($asset_to_import['asset_data']['skus'])) {
				$default_items['skus'] = implode(",", $asset_to_import['asset_data']['skus']);
			}
			if (isset($asset_to_import['asset_data']['tags'])) {
				$default_items['tags'] = implode(",", $asset_to_import['asset_data']['tags']);
			}
			$default_items['langues'] = "";
			if (isset($asset_to_import['asset_data']['langues'])) {
				$default_items['langues'] = implode(",", $asset_to_import['asset_data']['langues']);
			}
			if (isset($asset_to_import['asset_data']['targets'])) {
				$default_items['targets'] = implode(",", $asset_to_import['asset_data']['targets']);
			}
			else {
				$default_items['targets'] = implode_keys($targets);
			}
			if (isset($asset_to_import['asset_data']['attributs'])) {
				foreach ($asset_to_import['asset_data']['attributs'] as $id_attributs => $values) {
					$default_items['attributs'][$id_attributs] = implode(",", $values);
				}
			}
		}
		$dir = $sources[$asset_to_import['source']];
		if (file_exists($dir.$asset_to_import['fichier'])) {
			$something_to_import = true;
			$main .= <<<HTML
<tr class="asset-import-line">
	<th><input type="checkbox" name="assets-import-select[{$asset_to_import['id']}]" class="assets-import-select" /></th>
	<td>
		<img src="{$config->media("icon-file.png")}" truesrc="{$url->make("assetthumbnail", array('action' => $asset_to_import['source'], 'id' => $asset_to_import['fichier']))}" alt="" />
		<p>Source : <b>{$asset_to_import['source']}</b></p><br>
		<p>Fichier : {$asset_to_import['fichier']}</p><br>
		<p>{$dico->t('AssetExistant')} :</p>
		<select name="existing-asset[{$asset_to_import['id']}]">
			<option value=""></option>
HTML;
			if ($asset_to_import['id_assets']) {
				$main .= <<<HTML
			<option value="{$asset_to_import['id_assets']}" selected="selected">{$asset_to_import['id_assets']}) {$asset_to_import['fichier']}</option>
HTML;
			}
			$main .= <<<HTML
		</select>
		<p>{$dico->t('Titre')} :</p>{$form->input(array('name' => "assets[$id_import][titre]", 'template' => "#{field}"))}<br>
		<table>
			<tr>
				<td>
					{$form->input(array('type' => "checkbox", 'name' => "assets[$id_import][actif]", 'class' => "asset-import-actif", 'template' => "#{field}"))}&nbsp;{$dico->t('Actif')}<br>
				</td>
				<td>
					{$form->input(array('type' => "checkbox", 'name' => "assets[$id_import][public]", 'class' => "asset-import-public", 'template' => "#{field}"))}&nbsp;{$dico->t('Public')}<br>
				</td>
			</tr>
		</table>
		{$form->input(array('type' => "submit", 'name' => "import[$id_import]", 'value' => "Importer"))}
		{$form->input(array('type' => "submit", 'name' => "discard[$id_import]", 'value' => "Ignorer"))}
	</td>
	<td>
HTML;
			if (in_array("catalogue_categorie", $assets_links) and $source != "ft") {
				$items = isset($default_items['categories']) ? $default_items['categories'] : "";
				$main .= <<<HTML
		<p>{$dico->t('Categories')} :</p><div class="multicombobox asset-import-categories" list="categories" items="{$items}" name="categories[$id_import][]"></div><br>
HTML;
			}
			if (in_array("gamme", $assets_links)) {
				$items = isset($default_items['gammes']) ? $default_items['gammes'] : "";
				$main .= <<<HTML
		<p>{$dico->t('Gammes')} :</p><div class="multicombobox asset-import-gammes" list="gammes" items="{$items}" name="gammes[$id_import][]"></div><br>
HTML;
			}
			if (in_array("produit", $assets_links) and $source != "ft") {
				$items = isset($default_items['produits']) ? $default_items['produits'] : "";
				$main .= <<<HTML
		<p>{$dico->t('Produits')} :</p><div class="multicombobox asset-import-produits" list="produits" items="{$items}" name="produits[$id_import][]"></div><br>
HTML;
			}
			if (in_array("sku", $assets_links) and $source != "ft") {
				$items = isset($default_items['skus']) ? $default_items['skus'] : "";
				$main .= <<<HTML
		<p>{$dico->t('SKU')} :</p> <div class="multicombobox asset-import-skus" list="skus" items="{$items}" name="skus[$id_import][]"></div><br>
HTML;
			}
			if ($assets_attributs and $source != "ft") {
				foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
					$items = isset($default_items['attributs'][$id_attributs]) ? $default_items['attributs'][$id_attributs] : "";
					$main .= <<<HTML
		<p>{$attribut['nom']} :</p><div class="multicombobox asset-import-attribut-{$id_attributs}" list="attribut-{$id_attributs}" items="{$items}" name="attributs[$id_import][$id_attributs][]"></div><br>
HTML;
				}
			}
			$main .= <<<HTML
	</td>
	<td>
		<p>{$dico->t('Tags')} :</p><div class="multicombobox asset-import-tags" list="tags" items="{$default_items['tags']}" name="tags[$id_import][]"></div><br>
		<p>{$dico->t('Langues')} :</p><div class="multicombobox asset-import-langues" list="langues" items="{$default_items['langues']}" name="langues[$id_import][]"></div><br>
		<p>{$dico->t('CanauxDiffusion')} :</p><div class="multicombobox asset-import-targets" list="targets" items="{$default_items['targets']}" name="targets[$id_import][]"></div><br>
		<p>{$dico->t('Copyright')} :</p>{$form->input(array('name' => "assets[$id_import][copyright]", 'class' => "asset-import-copyright", 'template' => "#{field}"))}<br>
	</td>
	<td>
		{$dico->t('Infos')} : {$form->textarea(array('name' => "assets[$id_import][infos]", 'class' => "asset-import-infos", 'template' => "#{field}"))}
	</td>
</tr>
HTML;
		}
	}
	$main .= <<<HTML
</table>		
HTML;
	if ($something_to_import) {
		$all_categories_json = json_encode($all_categories);
		$all_gammes_json = json_encode($all_gammes);
		$all_produits_json = json_encode($all_produits);
		$all_skus_json = json_encode($all_skus);
		$all_tags_json = json_encode($all_tags);
		$all_langues_json = json_encode($all_langues);
		$all_targets_json = json_encode($all_targets);
		$javascript = <<<JAVASCRIPT
var multicombobox_list = [];
multicombobox_list['categories'] = {$all_categories_json};
multicombobox_list['gammes'] = {$all_gammes_json};
multicombobox_list['produits'] = {$all_produits_json};
multicombobox_list['skus'] = {$all_skus_json};
multicombobox_list['tags'] = {$all_tags_json};
multicombobox_list['langues'] = {$all_langues_json};
multicombobox_list['targets'] = {$all_targets_json};
JAVASCRIPT;
		if ($assets_attributs) {
			foreach ($asset->all_links_attributs($assets_attributs) as $id_attributs => $attribut) {
				$options = json_encode($attribut['options']);
				$javascript .= <<<JAVASCRIPT
multicombobox_list['attribut-' + {$id_attributs}] = {$options};
JAVASCRIPT;
			}
		}
		$javascript .= <<<JAVASCRIPT
$(".multicombobox").multicombobox();
JAVASCRIPT;
		$page->post_javascript[] = $javascript;
	}
	else {
		$main = <<<HTML
<p>{$dico->t("RienAImporter")}</p>
HTML;
	}
}
else {
		$main = <<<HTML
<p>{$dico->t("ChoisissezSourceImport")}</p>
HTML;
}

$form_end = $form->form_end();

function implode_keys($array) {
	return implode(",", array_keys($array));
}
