<?php

$menu->current('main/products/commandes');

$config->core_include("produit/commande");
$config->core_include("outils/form");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->jsvars[] = array(
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");

$sql = new Mysql($config->db());

$commande = new Commande($sql);

$action = $url->get('action');
if ($id = $url->get('id')) {
	$commande->load($id);
}

$form = new Form(array(
	'id' => "form-edit-commande-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"cancel",
	),
	'permissions' => $user->perms(),
	'permissions_object' => "commande",
));

switch($action) {
	case "create" :
		$titre_page = $dico->t('CreerNouvelleCommande');
		break;
	case "edit" :
		$titre_page = $dico->t('EditerCommande')." # ID : ".$id;
		break;
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ListeOfCommandes');
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'title' => 'ID',
				'type' => 'between',
				'order' => 'DESC',
				'field' => 'c.id',
			),
			'nom' => array(
				'title' => $dico->t('Nom'),
				'type' => 'contain',
			),
		), array(), "filter_commandes");
		$commande->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}
