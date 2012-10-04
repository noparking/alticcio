<?php
$menu->current('main/content/blocs');

$titre = "Blocs";
$config->core_include("outils/form", "outils/mysql");
$config->core_include("contenu/bloc");
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
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);
$page->post_javascript[] = <<<JAVASCRIPT
$(".bloc-apercu").colorbox({inline:true, href:"#bloc-apercu"});
JAVASCRIPT;

$sql = new Mysql($config->db());
$bloc = new Bloc($sql);
$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'b.id',
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
	),
	'code_langue' => array(
		'title' => $dico->t('Langue'),
		'type' => 'select',
		'field' => 'l.id',
		'options' => $bloc->langues(),
	),
	'actif' => array(
		'title' => $dico->t('Active'),
		'type' => 'select',
		'field' => 'b.actif',
		'options' => array(1 => $dico->t('Active'), 0 => $dico->t('Desactive')),
	),
), array(), "filter_bloc");


$titre_page = "Blocs";

$id = 0;

$action = $url->get('action');
if ($id = $url->get('id')) {
	$bloc->load($id);
}

$form = new Form(array(
	'id' => "form-edit-bloc-$id",
	'class' => "form-edit-bloc",
	'actions' => array("save", "delete", "cancel"),
	'required' => array("bloc[nom]"),
	'check' => array(
		'bloc[contenu]' => array("validate_html"),
	),
));
$form->fields_error_messages = array(
	'bloc[contenu]' => array(
		'validate_html' => "Le contenu HTML du bloc n'est pas valide.",
	),
);

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "delete":
			if ($dependances = $bloc->delete($data)) {
				$liste_blocs = "";
				foreach ($dependances as $i => $b) {
					$liste_blocs .= "<li>";
					$liste_blocs .= $page->l($b, $url->make("current", array('action' => "", 'id' => $i)));
					$liste_blocs .= "</li>";
				}
				$liste_blocs_referents = implode("</li><li>", $dependances);
				$messages[] = <<<HTML
Suppression impossible : ce bloc est référencé par les blocs suivants :
<ul>$liste_blocs</ul>
HTML;
			}
			else {
				$form->reset();
				$url->redirect("current", array('action' => "", 'id' => ""));
			}
			break;
		case "save":
			if ($form->validate()) {
				$id_saved = $bloc->save($data);
				if ($id_saved > 0) {
					$form->reset();
					if ($id_saved != $id) {
						$url->redirect("current", array('action' => "", 'id' => $id_saved));
					}
					$bloc->load($id);
				}
				else {
					switch ($id_saved) {
						case -1 : $message = "Il existe déjà un bloc portant ce nom."; break;
						case -2 : $message = "Ce bloc fait référence à un ou plusieurs blocs inexistants"; break;
						case -3 : $message = "Erreur de dépendance de blocs"; break;
						default : $message = "Unne erreur s'est produite"; break;
					}
					$messages[] = <<<HTML
<p class="message">
	$message
</p>
HTML;
				}
			}
			break;
	}
}
else {
	$form->reset();
}

if ($action == 'edit') {
	$form->default_values['bloc'] = $bloc->values;
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
	$buttons[] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}
$buttons[] = $page->l($dico->t("Nouveau"), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'class' => "delete", 'name' => "delete", 'value' => $dico->t('Supprimer') ));
	$main .= <<<HTML
{$form->input(array('name' => "bloc[id]", 'type' => "hidden"))}
HTML;
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->input(array('name' => "bloc[nom]", 'label' => $dico->t('Nom')))}
{$form->select(array('name' => "bloc[id_langues]", 'label' => $dico->t('Langue'), 'options' => $bloc->langues()))}
{$form->input(array('type' => "checkbox", 'name' => "bloc[actif]", 'label' => $dico->t('Actif')))}
{$form->textarea(array('name' => "bloc[contenu]", 'label' => $dico->t('Contenu'), 'class' => "dteditor"))}
HTML;
	$buttons[] = '<a class="bloc-apercu" href="#">Aperçu</a>';
	$main .= <<<HTML
<div style="display:none">
	<div id="bloc-apercu">
		{$bloc->afficher($id, true)}
	</div>
</div>
HTML;
}

switch($action) {
	case "create" :
		$titre_page = "Créer un nouveau bloc";
		break;
	case "edit" :
		$titre_page = "Editer le bloc # ID : ".$id;
		break;
	default :
		$titre_page = "Liste des blocs";
		$bloc->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
