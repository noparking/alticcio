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
$id_langue = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$gamme = new Gamme($sql, $phrase, $id_langue);

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
	'actions' => array("save", "delete", "cancel"),
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
	$buttons[] = $page->l($dico->t('Retour'), $url2->make("current", array('action' => "", 'id' => "")));
}

$buttons[] = $page->l($dico->t('NouvelleGamme'), $url2->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$sections = array(
		'presentation' => $dico->t('Presentation'),
		'attributs' => $dico->t('Attributs'),
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
	$attribut = new Attribut($sql, $phrase, $id_langue);
	foreach ($attributs_ids as $attribut_id) {
		$main .= $page->inc("snippets/attribut");
		$main .= <<<HTML
{$form->input(array('type' => "submit", 'name' => "delete-attribut[$attribut_id]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}		
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;

	$buttons[] = $form->input(array('type' => "submit", 'name' => "create", 'value' => $dico->t('Enregistrer')));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser')));
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
	$buttons[] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer')));
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
		$gamme->liste($id_langue, $filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();


