<?php

$menu->current('main/content/diaporamas');

$titre = "Diaporamas";
$config->core_include("outils/form", "outils/mysql", "outils/langue", "outils/phrase");
$config->core_include("contenu/diaporama");
$config->core_include("outils/filter", "outils/pager", "outils/url_redirection");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $config->core_media("jquery.colorbox-min.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->core_media("colorbox.css");
$page->css[] = $config->media("dteditor.css");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());
$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));
$phrase = new Phrase($sql);

$diaporama = new Diaporama($sql, $phrase, $id_langues);
$diaporama->dir = $config->get("medias_path")."www/medias/images/diaporamas/";

$url_redirection = new UrlRedirection($sql);

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'd.id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
		'type' => 'contain',
	),
	'titre' => array(
		'title' => $dico->t('Titre'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
	'section' => array(
		'title' => $dico->t('Section'),
		'type' => 'select',
		'field' => 'd.section',
		'options' => $diaporama->sections(),
	),
	'actif' => array(
		'title' => $dico->t('Active'),
		'type' => 'select',
		'field' => 'p.actif',
		'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
	),
), array(), "filter_diaporama");

$titre_page = "Diaporamas";

$id = 0;

$action = $url->get('action');
if ($id = $url->get('id')) {
	$diaporama->load($id);
}

$form = new Form(array(
	'id' => "form-edit-diaporama-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel", "add-image",	"delete-image"),
	'files' => array("vignette_file", "new_image_file"),
));

$section = "presentation";
if ($form->value('section')) {
	$section = $form->value('section');
}
$traduction = $form->value("lang");

$messages = array();

if ($form->is_submitted()) {
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
			$diaporama->delete($data);
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-image" :
			if ($file = $form->value('new_image_file')) {
				$diaporama->add_image($data, $file, $diaporama->dir);
			}
			$form->forget_value("new_image");
			break;
		case "delete-image" :
			$diaporama->delete_image($data, $form->action_arg());
			break;
		case "save":
			if ($form->validate()) {
				$id_saved = $url_redirection->save_object($diaporama, $data, array('phrase_url_key' => 'phrase_titre'));
				if ($id_saved === false) {
					$messages[] = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
				}
				else if ($id_saved == -1) {
					$messages[] = '<p class="message">Il existe déjà un diaporama ayant cette référence</p>';
				}
				else if ($id_saved > 0) {
					$form->reset();
				}
			}
			break;
	}
}
else {
	$form->reset();
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['diaporama'] = $diaporama->values;
	$images = $diaporama->images();
	$form->default_values['image'] = $images;
	$form->default_values['phrases'] = $phrase->get($diaporama->phrases());
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

// variable $displayed_lang définie dans ce snippet
$main = $page->inc("snippets/translate");

$main .= $page->inc("snippets/messages");

$hidden = array('presentation' => "");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}
$buttons['new'] = $page->l($dico->t("Nouveau"), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'images' => $dico->t('Images'),
	);

	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");

	$main .= <<<HTML
{$form->input(array('name' => "diaporama[id]", 'type' => "hidden"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;
}

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));

	$vignette = "";
	if ($diaporama->values['vignette']) {
		$vignette = <<<HTML
<img src="{$config->core_media("diaporamas/".$diaporama->values['vignette'])}" alt="Vignette" />
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "diaporama-section-presentation"))}
{$form->input(array('name' => "diaporama[ref]", 'label' => $dico->t('Reference')))}
{$form->input(array('name' => "diaporama[phrase_titre]", 'type' => "hidden"))}
{$vignette}
{$form->input(array('type' => "file", 'name' => "vignette_file", 'label' => $dico->t('Vignette')))}
{$form->select(array('name' => "diaporama[actif]", 'label' => $dico->t('Actif'), 'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive') )))}
{$form->input(array('name' => "phrases[phrase_titre]", 'label' => $dico->t('Titre'), 'items' => $displayed_lang))}
{$form->input(array('name' => "diaporama[phrase_url_key]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_url_key]", 'label' => $dico->t('UrlKey'), 'items' => $displayed_lang))}
{$form->input(array('name' => "diaporama[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->select(array('name' => "diaporama[section]", 'label' => $dico->t('Section'), 'options' => $diaporama->sections()))}
{$form->input(array('name' => "diaporama[classement]", 'label' => $dico->t('Classement')))}
{$form->fieldset_end()}
HTML;
}

if ($action == "edit") {
	$buttons['delete'] = $form->input(array('type' => "submit", 'class' => "delete", 'name' => "delete", 'value' => $dico->t('Supprimer') ));

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images".$hidden['images'], 'id' => "produit-section-images-new"))}
{$form->input(array('type' => "file", 'name' => "new_image_file", 'label' => $dico->t('SelectFichier') ))}
{$form->input(array('name' => "new_image[phrase_legende]", 'label' => $dico->t('TexteAlternatif'), 'items' => $displayed_lang))}
{$form->input(array('name' => "new_image[classement]", 'type' => "hidden", 'forced_value' => 0))}
{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter') ))}
<p class="message">{$dico->t('AttentionSuppressionImage')}</p>
{$form->fieldset_end()}
HTML;

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
			$images_rows[$order] = <<<HTML
<tr>
	<td class="drag-handle"></td>
	<td><img class="produit-image" src="{$config->core_media("diaporamas/".$image['ref'])}" /></td>
	<td>
		{$form->input(array('name' => "image[".$image['id']."][phrase_legende]", 'type' => "hidden"))}
		{$form->input(array('name' => "phrases[image][".$image['id']."][phrase_legende]", 'items' => $displayed_lang))}
	</td>
	<td>{$form->input(array('name' => "image[".$image['id']."][affichage]", 'type' => "checkbox", 'checked' => $image['affichage']))}</td>
	<td>
		{$form->input(array('name' => "image[".$image['id']."][classement]", 'type' => "hidden", 'forced_value' => $order))}
		{$form->input(array('type' => "submit", 'name' => "delete-image[".$image['id']."]", 'class' => "delete", 'value' => "Supprimer"))}
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
}

switch($action) {
	case "create" :
		$titre_page = "Créer un nouveau diaporama";
		break;
	case "edit" :
		$titre_page = "Editer le diaporama # ID : ".$id;
		break;
	default :
		$titre_page = "Liste des diaporamas";
		$diaporama->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
