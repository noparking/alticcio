<?php
$menu->current('main/content/pages');

$titre = "Pages statiques";
$config->core_include("outils/form", "outils/mysql", "outils/url_redirection");
$config->core_include("contenu/static_page");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->core_media("form.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $config->core_media("jquery.colorbox-min.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->core_media("colorbox.css");
$page->css[] = $config->media("dteditor.css");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
);
$page->post_javascript[] = <<<JAVASCRIPT
$(".page-apercu").colorbox({inline:true, href:"#page-apercu"});
JAVASCRIPT;

$sql = new Mysql($config->db());
$url_redirection = new UrlRedirection($sql);
$static_page = new StaticPage($sql);
$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'p.id',
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
	),
	'code_langue' => array(
		'title' => $dico->t('Langue'),
		'type' => 'select',
		'field' => 'l.id',
		'options' => $static_page->langues(),
	),
	'actif' => array(
		'title' => $dico->t('Active'),
		'type' => 'select',
		'field' => 'p.actif',
		'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
	),
), array(), "filter_static_page");

$titre_page = "Pages statiques";

$id = 0;

$action = $url->get('action');
if ($id = $url->get('id')) {
	$static_page->load($id);
}

$form = new Form(array(
	'id' => "form-edit-page-$id",
	'class' => "form-edit-page",
	'actions' => array("save", "delete", "cancel"),
	'required' => array("page[nom]"),
	'check' => array(
		'page[contenu]' => array("validate_html"),
	),
));
$form->fields_error_messages = array(
	'page[contenu]' => array(
		'validate_html' => "Le contenu HTML de la page n'est pas valide.",
	),
);

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "delete":
			if ($url_redirection->delete_object($static_page, $data)) {
				$form->reset();
				$url->redirect("current", array('action' => "", 'id' => ""));
			}
			break;
		case "save":
			if ($form->validate()) {
				$id_saved = $url_redirection->save_object($static_page, $data, array('intitule_url' => ""));
				if ($id_saved === false) {
					$messages[] = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
				}
				else if ($id_saved == -1) {
					$messages[] = '<p class="message">Il existe déjà une page portant ce nom</p>';
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

if ($action == 'edit') {
	$form->default_values['page'] = $static_page->values;
}

$form->check();

foreach ($form->errors() as $error) { 
	$messages[] = <<<HTML
<p class="message">
	$error
</p>
HTML;
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$main = $page->inc("snippets/messages");

if ($action == "create" or $action == "edit") {
	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}
$buttons['new'] = $page->l($dico->t("Nouveau"), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$buttons['delete'] = $form->input(array('type' => "submit", 'class' => "delete", 'name' => "delete", 'value' => $dico->t('Supprimer') ));
	$main .= <<<HTML
{$form->input(array('name' => "page[id]", 'type' => "hidden"))}
HTML;
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->input(array('name' => "page[nom]", 'label' => $dico->t('Nom')))}
{$form->select(array('name' => "page[id_langues]", 'label' => $dico->t('Langue'), 'options' => $static_page->langues()))}
{$form->input(array('name' => "page[intitule_url]", 'label' => $dico->t('IntituleURL')))}
{$form->input(array('type' => "checkbox", 'name' => "page[actif]", 'label' => $dico->t('Actif')))}
{$form->textarea(array('name' => "page[contenu]", 'label' => $dico->t('Contenu'), 'class' => "dteditor"))}
{$form->select(array('name' => "page[design]", 'label' => $dico->t('Design'), 'options' => array('none' => "Aucun", 'header/footer' => "En-tête et pied de page")))}
{$form->input(array('name' => "page[meta_title]", 'label' => $dico->t('MetaTitle'), 'class' => ""))}
{$form->textarea(array('name' => "page[meta_description]", 'label' => $dico->t('MetaDescription')))}
{$form->textarea(array('name' => "page[meta_keywords]", 'label' => $dico->t('MetaKeywords')))}
HTML;
	$buttons['preview'] = '<a class="page-apercu" href="#">Aperçu</a>';
	$main .= <<<HTML
<div style="display:none">
	<div id="page-apercu">
		{$static_page->afficher($id, true)}
	</div>
</div>
HTML;
}

switch($action) {
	case "create" :
		$titre_page = "Créer une nouvelle page statique";
		break;
	case "edit" :
		$titre_page = "Editer la page statique # ID : ".$id;
		break;
	default :
		$titre_page = "Liste des pages statiques";
		$static_page->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
