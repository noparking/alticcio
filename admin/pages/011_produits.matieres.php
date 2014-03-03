<?php

$menu->current('main/products/matieres');

$config->core_include("produit/matiere", "produit/attribut");
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->media("dteditor.css");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$matiere = new Matiere($sql, $phrase, $id_langues);

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'm.id',
	),
	'ref_matiere' => array(
		'title' => $dico->t('Reference'),
		'type' => 'contain',
		'field' => 'm.ref_matiere',
	),
	'phrase' => array(
		'title' => $dico->t('Type'),
		'type' => 'select',
		'field' => 'm.id_familles_matieres',
		'options' => $matiere->familles_matieres($id_langues),
	),
), array(), "filter_matiere");

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$matiere->load($id);
}

$form = new Form(array(
	'id' => "form-edit-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel", "add-image", "delete-image"),
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
			break;
		case "reset":
			$form->reset();
			$traduction = null;
			break;
		case "delete":
			$matiere->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-image" :
			if ($file = $form->value('new_image_file')) {
				$dir = $config->get("medias_path")."www/medias/images/produits/";
				$matiere->add_image($data, $file, $dir);
			}
			$form->forget_value("new_image");
			break;
		case "delete-image" :
			$matiere->delete_image($data, $form->action_arg());
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $matiere->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$matiere->load($id);
			}
			break;
	}
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

if ($action == 'edit') {
	$form->default_values['matiere'] = $matiere->values;
	$form->default_values['image'] = $matiere->images();
	$form->default_values['phrases'] = $phrase->get($matiere->phrases());
	$form->default_values['attributs'] = $matiere->attributs();
	$form->default_values['applications'] = $matiere->applications();
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
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "edit") {
	$buttons['fichematiere'] = $page->l($dico->t('FicheMatiere'), $url3->make("FicheMatiere", array('id' => $id)));
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "matiere[id]"))}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
HTML;
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'attributs' => $dico->t('Attributs'),
		'images' => $dico->t('Images'),
		'applications' => $dico->t('Applications'),
	);
	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Attributs'), 'class' => "produit-section produit-section-attributs".$hidden['attributs'], 'id' => "produit-section-attributs"))}
HTML;
	$attribut = new Attribut($sql, $phrase, $id_langues);
	foreach ($matiere->all_attributs() as $attribut_id) {
		$main .= $page->inc("snippets/attribut");
	}
	$main .= <<<HTML
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images".$hidden['images'], 'id' => "produit-section-images-new"))}
{$form->input(array('type' => "file", 'name' => "new_image_file", 'label' => $dico->t('SelectFichier') ))}
{$form->input(array('name' => "new_image[phrase_legende]", 'label' => $dico->t('TexteAlternatif'), 'items' => $displayed_lang))}
{$form->input(array('name' => "new_image[classement]", 'type' => "hidden", 'forced_value' => $matiere->new_classement()))}
{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter')))}
<p class="message">{$dico->t('AttentionSuppressionImage')}</p>
{$form->fieldset_end()}
HTML;

	$images = $matiere->images();
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
	<td></td>
</tr>
</thead>
<tbody>
HTML;
		$form_template = $form->template;
		$form->template = "#{field}";
		$images_rows = array();
		foreach ($images as $image) {
			$order = $form->value("images[{$image['id']}]") !== null ? $form->value("images[{$image['id']}][classement]") : $image['classement'];

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
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Applications'), 'class' => "produit-section produit-section-applications".$hidden['applications'], 'id' => "produit-section-applications"))}
{$dico->t('CochezApplications')}
<table id="applications">
<tbody>
HTML;
	$form_template = $form->template;
	$form->template = "#{field}";
	foreach ($matiere->applications() as $application) {
		$main .= <<<HTML
<tr>
	<td>{$form->input(array('name' => "applications[".$application['id']."][checked]", 'type' => "checkbox", 'checked' => $application['checked']))}</td>
	<td>{$application['name']}</td>
</tr>
HTML;
	}
	$form->template = $form_template;
	$main .= <<<HTML
</tbody>
</table>
{$form->fieldset_end()}
HTML;
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Presentation'), 'class' => "produit-section produit-section-presentation".$hidden['presentation'], 'id' => "produit-section-presentation"))}
{$form->input(array('name' => "matiere[ref_matiere]", 'label' => $dico->t('Reference') ))}
{$form->input(array('name' => "matiere[phrase_nom]", 'type' => "hidden"))}
{$form->input(array('name' => "phrases[phrase_nom]", 'label' => $dico->t('Nom'), 'items' => $displayed_lang))}
{$form->select(array('name' => "matiere[id_familles_matieres]", 'label' => $dico->t('FamillesMatieres'), 'options' => $matiere->familles_matieres($id_langues) ))}
{$form->select(array('name' => "matiere[id_ecolabels]", 'label' => $dico->t('EcoLabel'), 'options' => $matiere->ecolabels($id_langues) ))}
{$form->select(array('name' => "matiere[id_recyclage]", 'label' => $dico->t('FiliereRecyclage'), 'options' => $matiere->recyclages($id_langues) ))}
{$form->input(array('name' => "matiere[phrase_description_courte]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description_courte]", 'label' => $dico->t('DescriptionCourte'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->input(array('name' => "matiere[phrase_description]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_description]", 'label' => $dico->t('Description'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->input(array('name' => "matiere[phrase_entretien]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_entretien]", 'label' => $dico->t('ConseilsEntretien'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->input(array('name' => "matiere[phrase_marques_fournisseurs]", 'type' => "hidden"))}
{$form->textarea(array('name' => "phrases[phrase_marques_fournisseurs]", 'label' => $dico->t('MarquesFournisseurs'), 'items' => $displayed_lang, 'class' => "dteditor"))}
{$form->fieldset_end()}
HTML;
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleMatiere');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerMatiere')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfMatieres');
		$matiere->liste($id_langues, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

$buttons['newmatiere'] = $page->l($dico->t('NouvelleMatiere'), $url2->make("current", array('action' => "create", 'id' => "")));
