<?php

$menu->current('main/products/catalogs');

$config->core_include("database/tools");
$config->core_include("produit/catalogue", "produit/catalogue_categorie", "outils/form", "outils/mysql");
$config->core_include("outils/filter", "outils/pager");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");

$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");
$page->javascript[] = $config->media("catalogue.js");

$sql = new Mysql($config->db());

$catalogue = new Catalogue($sql);

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'c.id',
		'group_by' => true,
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'c.nom',
	),
	'id_langues' => array(
		'title' => $dico->t('Langue'),
		'type' => 'select',
		'options' => $catalogue->langues(),
	),
	'type' => array(
		'title' => $dico->t('Type'),
		'type' => 'select',
		'field' => 'c.type',
		'options' => array(1 => $dico->t('Online'), 2 => $dico->t('Offline')),
	),
	'statut' => array(
		'title' => $dico->t('Statut'),
		'type' => 'select',
		'field' => 'c.statut',
		'options' => array($dico->t('Desactive'), $dico->t('Active') ),
	),
	'nb_produits' => array(
		'title' => 'Nb Produits',
		'type' => 'between',
		'order' => 'DESC',
		'group' => true,
	),
), array(), "filter_catalogue");

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$catalogue->load($id);
}

$form = new Form(array(
	'id' => "form-edit-catalogue-$id",
	'class' => "form-edit",
	'actions' => array("save", "delete", "cancel", "add-categorie", "duplicate"),
));

if ($form->is_submitted()) {
	$data = $form->escape_values();
	switch ($form->action()) {
		case "reset":
			$form->reset();
			break;
		case "delete":
			$catalogue->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "add-categorie":
			$catalogue->add_categorie($data);
			$form->forget_value("categorie[nom]");
			$catalogue->load($id);
			break;
		case "duplicate":
			$id = $catalogue->duplicate();
			$url2->redirect("current", array('action' => "edit", 'id' => $id));
			break;
		case "export":
			$catalogue->export($data);
			break;
		default :
			if ($action == "edit" or $action == "create") {
				$id = $catalogue->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$catalogue->load($id);
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
	$form->default_values['catalogue'] = $catalogue->values;
	$form->default_values['export_data'] = $catalogue->values['export_data'];
}

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;

$main = $page->inc("snippets/messages");
$right = "";

if ($action == "edit") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "catalogue[id]"))}
HTML;
	$categories_options = options_select_tree(DBTools::tree($catalogue->categories()), $form, "categories");
	$url = $url2->make("produits", array('type' => "catalogues_categories", "action" => "edit"));
	$categories_links = print_link_tree(DBTools::tree($catalogue->categories()), $url, "categories");
	$right = <<<HTML
{$form->fieldset_start($dico->t('Categories') )}
{$categories_links}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("Categories spéciales") )}
{$form->select(array('name' => "catalogue[home]", 'label' => $dico->t("Categorie en page d'accueil"), 'options' => $categories_options))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t('NouvelleCategorie') )}
{$form->input(array('name' => "categorie[id_catalogues]", 'type' => "hidden", 'value' => "{$id}"))}
{$form->input(array('name' => "categorie[nom]", 'label' => $dico->t('Nom') ))}
{$form->select(array('name' => "categorie[id_parent]", 'label' => $dico->t('CategorieParent'), 'options' => $categories_options))}
{$form->input(array('name' => "add-categorie", 'type' => "submit", 'value' => $dico->t('Ajouter'), 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;
	$buttons['delete'] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section", 'id' => "produit-section-produits"))}
{$dico->t("Il y a %nb produits dans ce catalogue", array('%nb' => $catalogue->nb_produits()))}
{$form->fieldset_end()}
HTML;
}

if ($action == "create" or $action == "edit") {
	$auto = array(
		0 => "Manuellement",
		3600 => "Toutes les heures",
		86400 => "Tous les jours",
		604800 => "Toutes les semaines",
	);
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Informations'), 'class' => "produit-section", 'id' => "produit-section-informations"))}
{$form->input(array('name' => "catalogue[nom]", 'label' => $dico->t('Nom') ))}
{$form->select(array('name' => "catalogue[id_langues]", 'label' => $dico->t('Langue'), 'options' => $catalogue->langues()))}
{$form->select(array('name' => "catalogue[type]", 'label' => $dico->t('Type'), 'options' => array(1 => $dico->t('Online'), 2 => $dico->t('Offline')) ))}
{$form->select(array('name' => "catalogue[statut]", 'label' => $dico->t('Statut'), 'options' => array($dico->t('Desactive'), $dico->t('Active') )))}
{$form->select(array('name' => "catalogue[export_frequency]", 'options' => $auto, 'label' => "Exporter ce catalogue", 'description' => "Vous pouvez faire des exports manuels ou réexporter automatiquement ce catalogue à intervalles de temps réguliers"))}
{$form->fieldset_end()}
HTML;
	$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Export'), 'class' => "produit-section", 'id' => "produit-section-export"))}
HTML;
	$export_configuration = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Configuration')))}
{$form->textarea(array('name' => "export_data[cartouche_prix]", 'label' => "Cartouche prix", 'description' => "%prix et %unite seront subsitués dynamiquement"))}
{$form->textarea(array('name' => "export_data[note_prix]", 'label' => "Note prix", 'description' => "Cette note est ajoutée lorsque les frais de port ne sont pas compris."))}
{$form->textarea(array('name' => "export_data[note_personnalisation]", 'label' => "Note personnalisation", 'description' => "Cette note est ajoutée pour les produits personnalisables"))}
{$form->fieldset_end()}
HTML;
	if ($export = $catalogue->get_export()) {
		switch ($export['etat']) {
			case "built" :
				$mode = $export['auto'] ? "automatique" : "manuel";
				$date = date("d/m/Y H:i:s", $export['date_export']);
				$main .= <<<HTML
<p>Dernier export : {$date} ($mode)</p>
<div class="buttons">
	<ul class="buttons_actions">
		<li>{$page->l($dico->t('Télécharger'), $config->get("medias_url")."exports/catalogues/".$export['fichier'])}</li>
	</ul>
</div>
<br />
{$export_configuration}
<div class="buttons">
	<ul class="buttons_actions">
		<li>{$form->input(array('type' => "submit", 'name' => "export", 'value' => $dico->t('Exporter') ))}</li>
	</ul>
</div>
HTML;
				break;
			default :
				$main .= <<<HTML
<p class="message">L'export de ce catalogue est en cours. Cette opération peut prendre quelques minutes.</p>
HTML;
				break;
		}
	}
	else {
		$main .= <<<HTML
{$export_configuration}
{$form->input(array('type' => "submit", 'name' => "export", 'value' => $dico->t('Exporter') ))}
HTML;
	}
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;
	$buttons['duplicate'] = $form->input(array('type' => "submit", 'name' => "duplicate", 'value' => $dico->t('Dupliquer') ));
	$buttons['reset'] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Réinitialiser') ));
}

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouveauCatalogue');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerCatalogue')." # ID : ".$id;
		break;
	default :
		$titre_page = $dico->t('ListeOfCatalogues');
		$catalogue->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();

$buttons['new'] = $page->l($dico->t('NouveauCatalogue'), $url2->make("current", array('action' => "create", 'id' => "")));
