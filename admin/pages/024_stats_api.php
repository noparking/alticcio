<?php
$config->core_include("extranet/user", "outils/mysql", "outils/form", "outils/langue", "stats/statsapi");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/api');

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));

$form = new Form(array(
	'id' => "form-search-stats",
	'class' => "form-search-stats",
	'required' => array(),
	'actions' => array("general", "products", "clients"),
));

$main = "";

$titre_page = $dico->t("StatsApiBoutiques");

$action = isset($_SESSION["statsapi_action"]) ? $_SESSION["statsapi_action"] : "";
$params = array();

$params = $form->values();
if ($form->is_submitted()) {
	$action = $form->action();
	if (in_array($action, array("general", "products", "clients"))) {
		$_SESSION["statsapi_action"] = $action;
	}
	else {
		$action = isset($_SESSION["statsapi_action"]) ? $_SESSION["statsapi_action"] : "";
	}
}

$params['id_langue'] = $id_langue;

$stats = new StatsApi($sql, $params);
$boutiques = $stats->keys_by_role("boutique");

if (isset($params['id_keys'])) {
	$buttons[] = $form->input(array('type'=>'submit', 'name'=>'general', 'value'=> "Général"));
	$buttons[] = $form->input(array('type'=>'submit', 'name'=>'products', 'value'=> "Produits"));
	$buttons[] = $form->input(array('type'=>'submit', 'name'=>'clients', 'value'=> "Clients"));

	$produits = $stats->produits($boutiques[$params['id_keys']]);
	if (!isset($params['id_produits'])) {
		$params['id_produits'] = key($produits);
	}
}
else {
	$action = "";
}

$mois = array(
	"Janvier",
	"Février",
	"Mars",
	"Avril",
	"Mai",
	"Juin",
	"Juillet",
	"Août",
	"Septembre",
	"Octobre",
	"Novembre",
	"Décembre",
);
$liste_mois = implode("</th><th>", $mois);

$select_product = false;

if ($action == "general") {
	$titre_page = <<<HTML
Statistiques générales de {$boutiques[$params['id_keys']]} pour l'année {$params['annee']}
HTML;
	$stats_visites = $stats->visites();
	$liste_visites = implode("</td><td>", $stats_visites);
	$stats_commandes = $stats->commandes();
	$liste_commandes = implode("</td><td>", $stats_commandes);
	$stats_clients = $stats->clients();
	$liste_clients = implode("</td><td>", $stats_clients);
	$stats_ca = $stats->ca();
	$liste_ca = implode("</td><td>", $stats_ca);
	$stats_panier_moyen = $stats->panier_moyen();
	$liste_panier_moyen = implode("</td><td>", $stats_panier_moyen);
	$main .= <<<HTML
<h3>Tableau de bord</h3>
<table>
<tr><td>Visites</td><td>{$stats->visites_totales()}</td></tr>
<tr><td>Commandes</td><td>{$stats->commandes_totales()}</td></tr>
<tr><td>Clients</td><td>{$stats->clients_totaux()}</td></tr>
<tr><td>Chiffre d'affaire</td><td>{$stats->ca_total()}</td></tr>
<tr><td>Panier moyen</td><td>{$stats->panier_moyen_total()}</td></tr>
</table>
<h3>Visites</h3>
<img alt="Visites" src="{$stats->graphic($stats_visites, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_visites}</td></tr>
</table>
<h3>Commandes</h3>
<img alt="Commandes" src="{$stats->graphic($stats_commandes, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_commandes}</td></tr>
</table>
<h3>Clients</h3>
<img alt="Clients" src="{$stats->graphic($stats_clients, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_clients}</td></tr>
</table>
<h3>Chiffre d'affaire</h3>
<img alt="Chiffre d'affaire" src="{$stats->graphic($stats_ca, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_ca}</td></tr>
</table>
<h3>Panier moyen</h3>
<img alt="Panier moyen" src="{$stats->graphic($stats_panier_moyen, $mois)}" />
<table>
<tr><th>{$liste_mois}</th></tr>
<tr><td>{$liste_panier_moyen}</td></tr>
</table>
HTML;
}
else if ($action == "products") {
	$select_product = true;
	$titre_page = <<<HTML
Statistiques des produits de {$boutiques[$params['id_keys']]} pour l'année {$params['annee']}
HTML;
	$stats_produit_vu = $stats->produit_vu($params['id_produits']);
	$liste_produit_vu = implode("</td><td>", $stats_produit_vu);
	$stats_produit_commande = $stats->produit_commande($params['id_produits']);
	$liste_produit_commande = implode("</td><td>", $stats_produit_commande);
	$main .= <<<HTML
<h3>{$produits[$params['id_produits']]}</h3>
<table>
<tr><td>Vu</td><td>{$stats->produit_vu_total($params['id_produits'])}</td></tr>
<tr><td>Commandé</td><td>{$stats->produit_commande_total($params['id_produits'])}</td></tr>
</table>
<br />
<img alt="Produit vu et commandé" src="{$stats->graphic(array($stats_produit_vu, $stats_produit_commande), $mois, array('Vu', 'Commandé'))}" />
<table>
<tr><td/><th>{$liste_mois}</th></tr>
<tr><td>Vu</td><td>{$liste_produit_vu}</td></tr>
<tr><td>Commandé</td><td>{$liste_produit_commande}</td></tr>
</table>
HTML;
}
else if ($action == "clients") {
	$config->core_include("outils/filter", "outils/pager");
	$pager = new Pager($sql, array(20, 30, 50, 100, 200));
	$filter = new Filter($pager, array(
		'nom_complet' => array(
			'title' => 'Client',
			'type' => 'contain',
			'field' => 'CONCAT(c.nom, " ", c.prenom, " (", c.societe, ")")',
		),
		'email' => array(
			'title' => 'Email',
			'type' => 'contain',
			'group_by' => true,
		),
		'commandes' => array(
			'title' => 'Commandes',
			'type' => 'between',
			'group' => true,
		),
		'montant' => array(
			'title' => 'Montant',
			'type' => 'between',
			'order' => 'DESC',
			'group' => true,
		),
	), array(), "filter_clients");
	$titre_page = <<<HTML
Statistiques des clients de {$boutiques[$params['id_keys']]} pour l'année {$params['annee']}
HTML;
	$stats->clients_details($filter);
	$main = <<<HTML
<h3>Clients</h3>
{$page->inc("snippets/filter-simple")}
HTML;
}

$form_start = $form->form_start();

$action = $action ? $action : "general";
$right = <<<HTML
{$form->fieldset_start($dico->t("RechercheAvancee"))}
<p>{$form->select(array('name' => "id_keys", 'label' => $dico->t('SelectBoutique'), 'options' => $boutiques))}</p>
<p>{$form->select(array('name' => "annee", 'label' => $dico->t('SelectAnnee'), 'options' => $stats->annees(), 'value' => date("Y")))}</p>
HTML;
if ($select_product) {
	$right .= <<<HTML
<p>{$form->select(array('name' => "id_produits", 'label' => $dico->t('SelectProduit'), 'options' => $produits))}</p>
HTML;
}
$right .= <<<HTML
<p>{$form->input(array('type'=>'submit', 'name'=>$action, 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
HTML;

$form_end = $form->form_end();

