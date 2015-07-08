<?php

$menu->current('main/products/sku');

$config->core_include("outils/form", "outils/mysql", "outils/langue", "outils/phrase", "outils/pays", "outils/region", "outils/organisation");
$config->core_include("produit/sku", "produit/produit", "produit/attribut", "produit/mesure", "produit/duocouleurs");
$config->core_include("database/tools", "outils/filter", "outils/pager", "produit/couleurs");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");
$page->javascript[] = $config->media("produit.js");
$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());
$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 's.id',
	),
	'ref_ultralog' => array(
		'title' => $dico->t('Reference'),
	),
	'phrase_ultralog' => array(
		'title' => $dico->t('Nom (Ultralog)'),
		'type' => 'contain',
		'field' => 'p1.phrase',
	),
	'phrase_commercial' => array(
		'title' => $dico->t('Nom (Commercial)'),
		'type' => 'contain',
		'field' => 'p2.phrase',
	),
	'actif' => array(
		'title' => $dico->t('Active'),
		'type' => 'select',
		'field' => 's.actif',
		'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
	),
), array(), "filter_sku");

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);
$pays = new Pays($sql);

$liste_pays = $pays->liste($id_langues);
$selected_pays_id = $pays->get_id('code_iso', $url->get('pays'));

$region = new Region($sql);
$organisation = new Organisation($sql);
$couleur = new Couleurs($sql);
$duocouleurs = new Duocouleurs($sql);
$sku = new Sku($sql, $phrase, $id_langues);
$mesure = new Mesure($sql);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$sku->load($id);
}

$form = new Form(array(
	'id' => "form-edit-sku-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"reset",
		"add-image",
		"delete-image",
		"add-document",
		"delete-document",
		"add-prix-degressif",
		"delete-prix-degressif",
		"duplicate",
	),
	'files' => array("new_image_file", "new_document_file", "new_document_vignette", "new_gabarit_file"),
	'date_format' => $dico->d('FormatDate'),
));

$section = "presentation";
if ($form->value('section')) {
	$section = $form->value('section');
}
$traduction = $form->value("lang");

$attribut_management_filter_pager_name = "attribut_management_sku";
$object = $sku;

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
			$traduction = null;
			break;
		case "delete":
			$sku->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-image" :
			if ($file = $form->value('new_image_file')) {
				$dir = $config->get("medias_path")."www/medias/images/produits/";
				$sku->add_image($data, $file, $dir);
			}
			$form->forget_value("new_image");
			break;
		case "delete-image" :
			$sku->delete_image($data, $form->action_arg());
			break;
		case "add-document" :
			if ($file = $form->value('new_document_file')) {
				$dir = $config->get("medias_path")."www/medias/docs/";
				$files_dirs["fichier"] = array('file' => $file, 'dir' => $dir);
				if ($file = $form->value('new_document_vignette')) {
					$dir = $config->get("medias_path")."www/medias/images/documents/";
					$files_dirs["vignette"] = array('file' => $file, 'dir' => $dir);
				}
				$sku->add_document($data, $files_dirs);
			}
			$form->forget_value("new_document");
			break;
		case "delete-document" :
			$sku->delete_document($data, $form->action_arg());
			break;
		case "add-gabarit" :
			if ($file = $form->value('new_gabarit_file')) {
				$dir = $config->get("medias_path")."www/medias/gabarits/";
				$sku->add_gabarit($data, $file, $dir);
			}
			break;
		case "delete-gabarit" :
			$sku->delete_gabarit();
			break;
		case "add-prix-degressif" :
			$id_catalogues = $form->action_arg();
			if (!$sku->add_prix_degressif($data, $id_catalogues)) {
				$messages[] = $dico->t('ImpossibleAjoutPrixNul');
			}
			$form->forget_value("new_prix_degressif[$id_catalogues][prix]");
			$form->forget_value("new_prix_degressif[$id_catalogues][quantite]");
			$form->forget_value("new_prix_degressif[$id_catalogues][reduction]");
			break;
		case "delete-prix-degressif" :
			$sku->delete_prix_degressif($form->action_arg());
			break;
		case "add-ecotaxe" :
			$id_catalogues = $form->action_arg();
			$sku->add_ecotaxe($data, $id_catalogues);
			$form->forget_value("new_ecotaxe[$id_catalogues][montant]");
			$form->forget_value("new_ecotaxe[$id_catalogues][montant]");
			$form->forget_value("new_ecotaxe[$id_catalogues][montant]");
			break;
		case "delete-ecotaxe" :
			$sku->delete_ecotaxe($form->action_arg());
			break;
		case "duplicate" :
			$id = $sku->duplicate($data);
			$url2->redirect("current", array('action' => "edit", 'id' => $id));
			break;
		case "duplicate-prix" :
			if ($data['id_catalogues']) {
				$sku->duplicate_prix($data['id_catalogues']);
			}
			break;
		case "delete-prix" :
			$sku->delete_prix($form->action_arg());
			break;
		default :
			if ($action == "edit" or $action == "create") {
				if ($action == "edit") {
					$page->inc("snippets/attribut_management");
					$filter_attributs_management->clean_data($data, 'attributs_management');

					$page->inc("snippets/assets");
					$filter_assets->clean_data($data, 'assets');
				}
				$id = $sku->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$sku->load($id);
			}
			break;
	}
}

$messages = array();

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}
else if ($action == 'edit') {
	$attribut_management_selected = array_keys($sku->attributs());
	$assets_selected = array_keys($sku->assets());
}

if ($action == 'edit') {
	$form->default_values['sku'] = $sku->values;
	$images = $sku->images();
	$form->default_values['image'] = $images;
	$documents = $sku->documents();
	$form->default_values['document'] = $documents;
	$form->default_values['references_catalogues'] = $sku->references_catalogues();
	$form->default_values['phrases'] = $phrase->get($sku->phrases());
	$form->default_values['prix'] = $sku->prix_catalogues();
	$form->default_values['attributs_management'] = $sku->attributs_management();
	$form->default_values['attributs'] = $sku->attributs();
	$form->default_values['assets'] = $sku->assets();
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

$buttons['new'] = $page->l($dico->t('NouveauSku'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['duplicate'] = $form->input(array('type' => "submit", 'name' => "duplicate", 'value' => $dico->t('Dupliquer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'personnalisation' => $dico->t('Personnalisation'),
		'attributs_management' => $dico->t('AttributsManagement'),
		'attributs' => $dico->t('Attributs'),
		'images' => $dico->t('Images'),
		'documents' => $dico->t('Documents'),
		'references' => $dico->t('ReferencesCatalogues'),
		'produits' => $dico->t('Produits'),
	);
	if ($config->param('assets')) {
		$sections['assets'] = $dico->t('Assets');
	}
	foreach ($sku->catalogues(array(0 => "standard")) as $id_catalogues => $nom_catalogue) {
		$sections['prix-'.$id_catalogues] = $dico->t('Prix')." $nom_catalogue";
	}
	// variable $hidden mise à jour dans ce snippet
	$left = <<<HTML
{$page->inc("snippets/produits-sections")}
{$form->select(array('name' => "id_catalogues", 'options' => $sku->all_catalogues(array(0 => "Changer de prix pour...")), 'forced_value' => 0, 'template' => "#{field}"))}
{$form->input(array('type' => "submit", 'name' => "duplicate-prix", 'value' => $dico->t('Valider')))}
HTML;

	if ($config->param('assets')) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Assets'), 'class' => "produit-section produit-section-assets".$hidden['assets'], 'id' => "produit-section-assets"))}
{$page->inc("snippets/assets")}
{$form->fieldset_end()}
HTML;
		foreach (array_intersect($filter_assets->selected(), array_keys($sku->all_assets())) as $selected_asset) {
			$main .= $form->hidden(array('name' => "assets[$selected_asset][classement]", 'if_not_yet_rendered' => true));
		}
	}
	
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "sku[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => "Gabarit", 'class' => "produit-section produit-section-personnalisation".$hidden['personnalisation'], 'id' => "produit-section-gabarit-new"))}
HTML;

	if ($gabarit = $sku->gabarit()) {
		$main .= <<<HTML
<div>{$page->l("Gabarit", $config->get("medias_url")."medias/gabarits/$gabarit")}</div>
{$form->input(array('type' => "submit", 'name' => "delete-gabarit", 'class' => "delete", 'value' => "Supprimer"))}
HTML;
	}

	$main .= <<<HTML
{$form->input(array('type' => "file", 'name' => "new_gabarit_file", 'label' => $dico->t('SelectFichier') ))}
{$form->input(array('type' => "submit", 'name' => "add-gabarit", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AttributsManagement'), 'class' => "produit-section produit-section-attributs_management".$hidden['attributs_management'], 'id' => "produit-section-new-attribut"))}
{$page->inc("snippets/attribut_management")}
{$form->fieldset_end()}
HTML;
	foreach ($filter_attributs_management->selected() as $selected_attribut) {
		$main .= $form->hidden(array('name' => "attributs_management[$selected_attribut][classement]", 'if_not_yet_rendered' => true));
		$main .= $form->hidden(array('name' => "attributs_management[$selected_attribut][groupe]", 'if_not_yet_rendered' => true));
	}

	$attributs = $sku->attributs('grouped');
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
{$page->inc("snippets/attributs")}
{$form->fieldset_end()}
HTML;

	$options_frais_port = array(
		0 => $dico->t('PrixNonFranco'),
		1 => $dico->t('PrixFranco'),
		2 => $dico->t('FraisPortFixe'),
	);
	$template_frais_de_port = str_replace("#{field}", "#{field}".$form->input(array('name' => "prix[$id_catalogues][frais_port]", 'class' => "input-text-numeric", 'template' => "#{field}")), $page->inc("snippets/produits-form-template"));
	foreach ($sku->catalogues(array(0 => "standard")) as $id_catalogues => $nom_catalogue) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Prix')." $nom_catalogue", 'class' => "produit-section produit-section-prix-$id_catalogues".$hidden['prix-'.$id_catalogues], 'id' => "produit-section-prix-$id_catalogues"))}
{$form->input(array('name' => "prix[$id_catalogues][montant_ht]", 'label' => $dico->t('PrixHT') ))} 
{$form->select(array('name' => "prix[$id_catalogues][franco]", 'label' => $dico->t('FraisPort'), 'options' => $options_frais_port, 'class' => "frais-port", 'template' => $template_frais_de_port))}
HTML;
		if ($id_catalogues == 0) {
			$main .= <<<HTML
{$form->select(array('name' => "sku[id_unites_vente]", 'label' => $dico->t('UniteVente'), 'options' => $sku->unites_vente($id_langues)))}
{$form->input(array('name' => "sku[colisage]", 'label' => $dico->t('Colisage')))}
{$form->input(array('name' => "sku[min_commande]", 'label' => $dico->t('MinimumCommande')))}
HTML;
		}
		else {
			$main .= <<<HTML
{$form->input(array('type' => "submit", 'name' => "delete-prix[$id_catalogues]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}
HTML;
		}
		$main .= <<<HTML
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('PrixDegressifs')." $nom_catalogue", 'class' => "produit-section produit-section-prix-$id_catalogues".$hidden['prix-'.$id_catalogues], 'id' => "produit-section-prix-degressifs-$id_catalogues"))}
{$form->fieldset_start(array('legend' => $dico->t('AjouterPrixDegressif') ))}
{$form->input(array('name' => "new_prix_degressif[$id_catalogues][quantite]", 'label' => $dico->t('Quantite'), 'class' => "little-text", 'template' => $template_inline))}
{$form->input(array('name' => "new_prix_degressif[$id_catalogues][prix]", 'label' => $dico->t('Nouveau prix'), 'class' => "little-text", 'template' => $template_inline))}
<strong>OU</strong>
{$form->input(array('name' => "new_prix_degressif[$id_catalogues][reduction]", 'label' => $dico->t('Reduction'), 'class' => "little-text", 'template' => $template_inline))}
{$form->select(array('name' => "new_prix_degressif[$id_catalogues][type]", 'options' => array('montant' => "€", 'pourcentage' => "%"), 'template' => "#{field}"))}
{$form->input(array('type' => "submit", 'name' => "add-prix-degressif[$id_catalogues]", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
HTML;
		$prix_degressifs = $sku->prix_degressifs($id_catalogues);
		if (count($prix_degressifs)) {
			$main .= <<<HTML
<table id="prix_degressifs">
<thead>
	<tr>
		<th>{$dico->t('Quantite')}</th>
		<th>{$dico->t('PrixUnitaire')}</th>
		<th>{$dico->t('ReductionPourcentage')}</th>
		<td></td>
	</tr>
</thead>
<tbody>
HTML;
			foreach ($prix_degressifs as $prix_degressif) {
				$main .= <<<HTML
<tr>
	<td>{$prix_degressif['quantite']}</td>
	<td>{$prix_degressif['montant_ht']} €</td>
	<td>{$prix_degressif['pourcentage']} %</td>
	<td>{$form->input(array('type' => "submit", 'name' => "delete-prix-degressif[{$prix_degressif['id']}]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}</td>
</tr>
HTML;
			}
			$main .= <<<HTML
</tbody>
</table>
HTML;
		}
		$main .= <<<HTML
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('Ecotaxe')." $nom_catalogue", 'class' => "produit-section produit-section-prix-$id_catalogues".$hidden['prix-'.$id_catalogues], 'id' => "produit-section-ecotaxe-$id_catalogues"))}
{$form->fieldset_start(array('legend' => $dico->t('AjouterEcotaxe') ))}
{$form->input(array('name' => "new_ecotaxe[$id_catalogues][montant]", 'label' => $dico->t('Montant'), 'class' => "little-text", 'template' => $template_inline))}
{$form->select(array('name' => "new_ecotaxe[$id_catalogues][id_pays]", 'label' => $dico->t('Pays'), 'class' => "little-select", 'options' => $liste_pays, 'value' => $selected_pays_id, 'template' => $template_inline))}
{$form->select(array('name' => "new_ecotaxe[$id_catalogues][id_familles_taxes]", 'label' => $dico->t('FamilleTaxes'), 'class' => "little-select", 'options' => $sku->get_familles_taxes($id_langues), 'value' => $selected_pays_id, 'template' => $template_inline))}
{$form->input(array('type' => "submit", 'name' => "add-ecotaxe[$id_catalogues]", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
HTML;
		$ecotaxes = $sku->ecotaxes($id_langues, $id_catalogues);
		if (count($ecotaxes)) {
			$main .= <<<HTML
<table id="ecotaxes">
<thead>
	<tr>
		<th>{$dico->t('Montant')}</th>
		<th>{$dico->t('Pays')}</th>
		<th>{$dico->t('FamilleTaxes')}</th>
		<td></td>
	</tr>
</thead>
<tbody>
HTML;
			foreach ($ecotaxes as $ecotaxe) {
				$main .= <<<HTML
<tr>
	<td>{$ecotaxe['montant']}</td>
	<td>{$ecotaxe['pays']}</td>
	<td>{$ecotaxe['famille_taxes']}</td>
	<td>{$form->input(array('type' => "submit", 'name' => "delete-ecotaxe[{$ecotaxe['id']}]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}</td>
</tr>
HTML;
			}
			$main .= <<<HTML
</tbody>
</table>
HTML;
		}
		$main .= <<<HTML
{$form->fieldset_end()}
HTML;
	}

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images".$hidden['images'], 'id' => "produit-section-images-new"))}
{$form->input(array('type' => "file", 'name' => "new_image_file", 'label' => $dico->t('SelectFichier')))}
{$form->input(array('name' => "new_image[phrase_legende]", 'label' => $dico->t('TexteAlternatif'), 'items' => $displayed_lang))}
{$form->input(array('name' => "new_image[classement]", 'type' => "hidden", 'forced_value' => $sku->new_classement()))}
{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter') ))}
<p class="message">{$dico->t('AttentionSuppressionImage')}</p>
<p><a href="mailto:{$config->get("photomail_email")}?Subject=sku= {$sku->values['ref_ultralog']}">{$dico->t('AjouterImagePhotomail')}</a></p>
{$form->fieldset_end()}
HTML;

	$images = $sku->images();
	if (count($images)) {
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('LesImages'), 'class' => "produit-section produit-section-images".$hidden['images'], 'id' => "produit-section-images-images"))}
<table class="sortable" id="images">
<thead>
<tr>
	<th>{$dico->t('Ordre')}</th>
	<th>{$dico->t('Apercu')}</th>
	<th>{$dico->t('TexteAlternatif')}</th>
	<th>{$dico->t('Visibilite')}</th>
	<th>{$dico->t('Diaporama')}</th>
	<th>{$dico->t('Vignette')}</th>
	<th>Image HD</th>
	<td></td>
</tr>
</thead>
<tbody>
HTML;
		$form_template = $form->template;
		$form->template = "#{field}";
		$images_rows = array();
		$hd_extensions = $dico->d('hd_extensions');
		foreach ($images as $image) {
			$order = $form->value("images[{$image['id']}]") !== null ? $form->value("images[{$image['id']}][classement]") : $image['classement'];
			$style_hd = $image['hd_extension'] ? '' : 'style="display:none;"';
			$images_rows[$order] = <<<HTML
<tr>
	<td class="drag-handle"></td>
	<td><img class="produit-image" src="{$config->core_media("produits/".$image['ref'])}" /></td>
	<td>
		{$form->input(array('name' => "image[".$image['id']."][phrase_legende]", 'type' => "hidden"))}
		{$form->input(array('name' => "phrases[image][".$image['id']."][phrase_legende]", 'items' => $displayed_lang))}
	</td>
	<td>{$form->input(array('name' => "image[".$image['id']."][affichage]", 'type' => "checkbox", 'checked' => $image['affichage']))}</td>
	<td>{$form->input(array('name' => "image[".$image['id']."][diaporama]", 'type' => "checkbox", 'checked' => $image['diaporama']))}</td>
	<td>{$form->input(array('name' => "image[".$image['id']."][vignette]", 'type' => "checkbox", 'checked' => $image['vignette']))}</td>
	<td>
		{$form->select(array('name' => "image[".$image['id']."][hd_extension]", 'options' => $hd_extensions))}
		<input class="nom_hd" name="{$sku->image_hd($image['id'])}" {$style_hd} value="{$sku->image_hd($image['id'])}.{$image['hd_extension']}" readonly="readonly" />
	</td>
	<td>
		{$form->input(array('name' => "image[".$image['id']."][classement]", 'type' => "hidden", 'forced_value' => $order))}
		{$form->input(array('type' => "submit", 'name' => "delete-image[".$image['id']."]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}
	</td>
</tr>
HTML;
		}
		ksort($images_rows);
		$main .= implode("\n", $images_rows);
		$form->template = $form_template;
		$main .= <<<HTML
</tbody>
</table>
{$form->fieldset_end()}
HTML;
	}

	// Documents
	$main .= $page->inc("snippets/documents");

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('ReferencesCatalogues'), 'class' => "produit-section produit-section-references".$hidden['references'], 'id' => "produit-section-references"))}
<table>
<tr><th>{$dico->t('Catalogue')}</th><th>{$dico->t('Reference')}</th>
HTML;

	foreach ($sku->all_catalogues() as $id_catalogues => $nom_catalogue) {
		$main .= <<<HTML
<tr>
	<td>{$nom_catalogue}</td>
	<td>{$form->input(array('name' => "references_catalogues[$id_catalogues]", 'template' => "#{field}"))}</td>
</tr>
HTML;
	}

	$main .= <<<HTML
</table>
{$form->fieldset_end()}
HTML;

	$produit = new Produit($sql);
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
<h3>Variantes</h3>
<ul>
HTML;
	foreach ($sku->variante_for() as $id_produits) {
		if ($produit->load($id_produits)) {
			$phrases = $phrase->get($produit->phrases());
			$main .= <<<HTML
<li>{$page->l($phrases['phrase_nom'][$config->get('langue')], $url2->make("produits", array('type' => "produits", 'action' => "edit", 'id' => $id_produits)))}</li>
HTML;
		}
	}
	$main .= <<<HTML
</ul>
<h3>Accessoires</h3>
<ul>
HTML;
	foreach ($sku->accessoire_for() as $id_produits) {
		if ($produit->load($id_produits)) {
			$phrases = $phrase->get($produit->phrases());
			$main .= <<<HTML
<li>{$page->l($phrases['phrase_nom'][$config->get('langue')], $url2->make("produits", array('type' => "produits", 'action' => "edit", 'id' => $id_produits)))}</li>
HTML;
		}
	}
	$main .= <<<HTML
</ul>
{$form->fieldset_end()}
HTML;

	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "create" or $action == "edit") {
	$familles_options = options_select_tree(DBTools::tree($sku->get_familles_ventes($id_langues)), $form, "familles_vente");
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$form->input(array('name' => "sku[phrase_ultralog]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_ultralog]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->text(array('name' => "phrases[phrase_commercial]", 'label' => $dico->t('DesignationCommerciale'), 'items' => $displayed_lang))}
{$form->input(array('name' => "sku[ref_ultralog]", 'label' => $dico->t('RefUltralog') ))}
{$form->select(array('name' => "sku[id_familles_vente]", 'label' => $dico->t('FamilleVente'), 'options' => $familles_options, 'enable' => "/^(----|$)/"))}
{$form->select(array('name' => "sku[actif]", 'label' => $dico->t('Statut'), 'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive') )))}
{$form->date(array('name' => "sku[date_creation]", 'label' => $dico->t('DateDebutVente') ))}
{$form->date(array('name' => "sku[date_fin]", 'label' => $dico->t('DateFinVente') ))}
{$form->input(array('name' => "sku[phrase_path]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_path]", 'label' => $dico->t('UrlKey'), 'items' => $displayed_lang))}
{$form->fieldset_end()}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauSku');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerSku')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfSku');
		$sku->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

