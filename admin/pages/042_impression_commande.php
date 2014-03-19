<?php

$config->core_include("produit/commande");

$page->template("commande");

$commande = new Commande($sql);

if ($id = $url0->get('id')) {
	$id = $commande->load($id);
}

$paiements = array(
	'cb' => "Carte bancaire",
	'cheque' => "Chèque",
	'mandat' => "Mandat administratif",
	'paypal' => "Paypal",
	'facture' => "Paiement sur facture",
	'devis' => "Demande de devis",
);

$profils = $dico->d("profils_clients");

$adresse_livraison = <<<HTML
{$commande->values['prenom']} {$commande->values['nom']}
HTML;
$adresse_facturation = <<<HTML
{$commande->values['societe']} ({$profils[$commande->values['profil']]})
<br />{$commande->values['prenom']} {$commande->values['nom']}
HTML;
foreach (array('livraison', 'facturation') as $type) {
	$var_name = "adresse_$type";
	foreach (array('societe', 'adresse', 'adresse2', 'adresse3') as $element) {
		if ($commande->values[$type.'_'.$element]) {
			$$var_name .= <<<HTML
<br />{$commande->values[$type.'_'.$element]}
HTML;
		}
	}
	$$var_name .= <<<HTML
<br />{$commande->values[$type.'_cp']} {$commande->values[$type.'_ville']}
HTML;
	if ($commande->values[$type.'_cedex']) {
		$$var_name .= <<<HTML
<br />{$commande->values[$type.'_cedex']}
HTML;
	}
	if ($commande->values['email']) {
		$$var_name .= <<<HTML
<br />Email : {$commande->values['email']}
HTML;
	}
	if ($commande->values['telephone']) {
		$$var_name .= <<<HTML
<br />Tel : {$commande->values['telephone']}
HTML;
	}
	if ($commande->values['fax']) {
		$$var_name .= <<<HTML
<br />Fax : {$commande->values['fax']}
HTML;
	}
	if ($type == 'facturation') {
		if (isset($commande->values['siret']) and $commande->values['siret']) {
			$$var_name .= <<<HTML
<br />Siret : {$commande->values['siret']}
HTML;
		}
	}
}

$liste_articles = "";
foreach ($commande->produits() as $article) {
	if ($article['personnalisation_texte']) {
		$article['personnalisation_texte'] = "<br />".$article['personnalisation_texte'];
	}
	else {
		$article['personnalisation_texte'] = "";
	}
	if ($article['personnalisation_nom_fichier']) {
		$article['personnalisation_nom_fichier'] = "<br />".$article['personnalisation_nom_fichier'];
	}
	else {
		$article['personnalisation_nom_fichier'] = "";
	}
	$prix_unitaire = $dico->prix($article['prix_unitaire']);
	$ecotaxe = $dico->prix($article['ecotaxe']);
	$prix = $dico->prix(($article['prix_unitaire'] + $article['ecotaxe']) * $article['quantite']);
	$liste_articles .= <<<HTML
<tr>
	<td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=left>
		<strong>{$article['nom']}</strong>{$article['personnalisation_texte']}{$article['personnalisation_nom_fichier']}
	</td>
    <td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=left>
		{$article['ref']}
	</td>
    <td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=left>
		{$prix_unitaire}
    </td>
    <td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=left>
		{$ecotaxe}
    </td>
    <td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=middle>
		{$article['quantite']}
	</td>
    <td style="padding-right: 9px; padding-left: 9px; padding-bottom: 3px; padding-top: 3px" valign=top align=right>
		{$prix}
	</td>
HTML;
}

$data = array(
	'DoubletMail' => "doublet@doublet.fr",
	'DoubletTel' => "03 20 49 48 47",
	'ClientPrenom' => $commande->values['prenom'],
	'ClientNom' => $commande->values['nom'],
	'CommandeNum' => $commande->ref(),
	'CommandeDate' => date("d/m/Y", $commande->values['date_commande']),
	'AdresseFacturation' => $adresse_facturation,
	'ModePaiement' => $paiements[$commande->values['paiement']],
	'AdresseLivraison' => $adresse_livraison,
	'MessageClient' => $commande->values['commentaire'],
	'ListeArticles' => $liste_articles,
	'SousTotal' => $dico->prix($commande->values['montant'] + $commande->values['ecotaxe']),
	'FraisDePort' => $dico->prix($commande->values['frais_de_port']),
	'TVA' => $dico->prix($commande->values['tva']),
	'MontantGlobal' => $dico->prix($commande->values['montant'] + $commande->values['frais_de_port'] + $commande->values['tva'] + $commande->values['ecotaxe']),
	'Signature' => "Le service commercial Doublet",
);
