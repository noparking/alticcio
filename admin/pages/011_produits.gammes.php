<?php

$menu->current('main/products/gammes');

$config->core_include("produit/gamme", "produit/attribut", "outils/form", "outils/mysql");
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
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$gamme = new Gamme($sql, $phrase, $id_langues);

$url_redirection = new UrlRedirection($sql);

$pager_gammes = new Pager($sql, array(20, 30, 50, 100, 200));
$filter_gammes = new Filter($pager_gammes, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'g.id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
		'type' => 'contain',
		'field' => 'g.ref',
	),
	'phrase' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
), array(), "filter_gammes");

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$gamme->load($id);
}

$form = new Form(array(
	'id' => "form-edit-gamme-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel", "add-image", "delete-image", "add-attribut", "delete-attribut"),
	'files' => array("new_image_file"),
));

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
			$gamme->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-image" :
			if ($file = $form->value('new_image_file')) {
				$dir = $config->get("medias_path")."www/medias/images/produits/";
				$gamme->add_image($data, $file, $dir);
			}
			$form->forget_value("new_image");
			break;
		case "delete-image" :
			$gamme->delete_image($data, $form->action_arg());
			break;
		case "add-attribut" :
			$gamme->add_attribut($data);
			$form->forget_value("new_attribut");
			break;
		case "delete-attribut" :
			$gamme->delete_attribut($data, $form->action_arg());
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $url_redirection->save_object($gamme, $data, array('phrase_url_key' => 'phrase_nom'));
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$gamme->load($id);
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['gamme'] = $gamme->values;
	$form->default_values['image'] = $gamme->images();
	$form->default_values['phrases'] = $phrase->get($gamme->phrases());
	$form->default_values['attributs'] = $gamme->attributs();
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

$buttons['new'] = $page->l($dico->t('NouvelleGamme'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'attributs' => $dico->t('Attributs'),
		'images' => $dico->t('Images'),
		'produits' => $dico->t('Produits'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
}
if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$form->input(array('name' => "gamme[phrase_nom]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_nom]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->input(array('name' => "gamme[ref]", 'label' => $dico->t('Reference') ))}
{$form->input(array('name' => "gamme[phrase_url_key]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_url_key]", 'label' => $dico->t('UrlKey'), 'items' => $displayed_lang))}
{$form->input(array('name' => "gamme[phrase_description_courte]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description_courte]", 'label' => $dico->t('DescriptionCourte'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->input(array('name' => "gamme[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->fieldset_end()}
HTML;
}
if ($action == "edit") {
	$attributs = $gamme->attributs();
	$attributs_ids = array_keys($attributs);
	$attributs_options = array();
	foreach ($gamme->all_attributs("unite") as $attribut_id => $label) {
		if (!in_array($attribut_id, $attributs_ids)) {
			$attributs_options[$attribut_id] = $label;
		}
	}
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUnAttribut'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-new-attribut"))}
{$form->select(array('name' => "new_attribut", 'options' => $attributs_options, 'template' => "#{field}"))}
{$form->input(array('type' => "submit", 'name' => "add-attribut", 'value' => $dico->t('Ajouter') ))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
HTML;
	$attribut = new Attribut($sql, $phrase, $id_langues);
	foreach ($attributs_ids as $attribut_id) {
		$main .= $page->inc("snippets/attribut");
		$main .= <<<HTML
{$form->input(array('type' => "submit", 'name' => "delete-attribut[$attribut_id]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}		
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images".$hidden['images'], 'id' => "produit-section-images-new"))}
{$form->input(array('type' => "file", 'name' => "new_image_file", 'label' => $dico->t('SelectFichier')))}
{$form->input(array('name' => "new_image[phrase_legende]", 'label' => $dico->t('TexteAlternatif'), 'items' => $displayed_lang))}
{$form->input(array('name' => "new_image[classement]", 'type' => "hidden", 'forced_value' => $gamme->new_classement()))}
{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter') ))}
<p class="message">{$dico->t('AttentionSuppressionImage')}</p>
<p><a href="mailto:{$config->get("photomail_email")}?Subject=gamme={$gamme->values['ref']}">{$dico->t('AjouterImagePhotomail')}</a></p>
{$form->fieldset_end()}
HTML;

	$images = $gamme->images();
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
		<input class="nom_hd" name="{$gamme->image_hd($image['id'])}" {$style_hd} value="{$gamme->image_hd($image['id'])}.{$image['hd_extension']}" readonly="readonly" />
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
	), array(), "filter_gamme_produits");
	
	$gamme->liste_produits($filter);
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
{$page->inc("snippets/filter-simple")}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "gamme[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer')));
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleGamme');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerGamme')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfGammes');
		$filter = $filter_gammes;
		$pager = $pager_gammes;
		$gamme->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();


