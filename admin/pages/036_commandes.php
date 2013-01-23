<?php

$config->core_include("produit/commande");
$config->core_include("outils/form", "outils/pays", "outils/langue");
$config->core_include("outils/filter", "outils/pager");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$commande = new Commande($sql);

$pays = new Pays($sql);
$liste_pays = $pays->liste($id_langues);

$action = $url->get('action');
if ($id = $url->get('id')) {
	$commande->load($id);
}

$etats = array(
	0 => "En cours",
	1 => "Expédiée",
	2 => "Annulée",
);

$paiements = array(
	'cheque' => "Chèque",
	'mandat' => "Mandat",
	'facture' => "Facture",
	'cb' => "CB",
	'paypal' => "Paypal",
	'devis' => "Devis",
);

$paiements_statuts = array(
	'attente' => "Attente",
	'valide' => "Validé",
	'refuse' => "Refusé",
	'annule' => "Annulé",
	'rembourse' => "Remboursé",
	'test' => "Test",
);
	
$form = new Form(array(
	'id' => "form-edit-commande-$id",
	'class' => "form-edit",
	'actions' => array(
		"save",
		"delete",
		"reset",
		"delete-produit",
	),
	'permissions' => $user->perms(),
	'permissions_object' => "commande",
));

$section = "commande";
if ($form->value('section')) {
	$section = $form->value('section');
}

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "save" :
			if (isset($data['produits']['new']) and !$data['produits']['new']['id_sku']) {
				unset($data['produits']['new']);
			}
			$id = $commande->save($data);
			$form->reset();
			if ($action != "edit") {
				$url->redirect("current", array('action' => "edit", 'id' => $id));
			}
			$commande->load($id);
			break;
		case "delete" :
			$commande->delete($data);
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
			break;
		case "reset" :
			$form->reset();
			break;
		case "delete-produit" :
			$commande->delete_produit($form->action_arg());
			$form->reset();
			break;
	}
}

if ($action == 'edit') {
	$form->default_values['commande'] = $commande->values;
	$form->default_values['produits'] = $commande->produits();
}

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'c.id',
		'group_by' => true,
	),
	'shop' => array(
		'title' => $dico->t('Shop'),
		'field' => 'c.shop',
	),
	'id_api_keys' => array(
		'title' => $dico->t('API'),
		'field' => 'c.id_api_keys',
	),
	'etat' => array(
		'title' => $dico->t('Etat'),
		'field' => 'c.etat',
		'type' => "select",
		'options' => $etats,
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'c.nom',
	),
	'montant' => array(
		'title' => $dico->t('Montant'),
		'type' => 'between',
		'field' => 'c.montant',
		'order' => 'DESC',
	),
	'nb_produits' => array(
		'title' => $dico->t('Produits'),
		'type' => 'between',
		'order' => 'DESC',
		'group' => true,
	),
	'paiement' => array(
		'title' => $dico->t('Paiement'),
		'field' => 'c.paiement',
		'type' => "select",
		'options' => $paiements,
	),
	'paiement_statut' => array(
		'title' => $dico->t('Statut'),
		'field' => 'c.paiement_statut',
		'type' => "select",
		'options' => $paiements_statuts,
	),
	'date_commande' => array(
		'title' => $dico->t('Date'),
		'type' => 'date_between',
		'field' => 'c.date_commande',
	),
), array(), "filter_commandes");
