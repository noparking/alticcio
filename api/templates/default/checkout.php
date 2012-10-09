<?php

$data = array_merge($data, $form->values());

$logo = "";

$cart = "";
foreach ($products as $id => $product) {
	foreach ($product as $key => $value) {
		$cart .= $form->input(array('type' => "hidden", 'name' => "produits[$id][$key]", 'forced_value' => $value));
	}
}

$form->template = <<<HTML
<div class="ligne-form">#{label} #{field} </div>
HTML;

$errors_messages = '<div class="error"><ul>';
foreach ($errors as $error) {
	$errors_messages .= "<li>$error</li>";
}
$errors_messages .= '</ul></div>';

$main = "";
if (isset($_GET['logo'])) {
	$main .= <<<HTML
<div class="checkout-logo"><img src="{$_GET['logo']}" alt="Logo"></div>
HTML;
}

switch ($step) {
	case "informations" :
		$main .= <<<HTML
<div class="checkout-step">
	<p><strong>Etape 1 : Informations</strong> > Etape 2 : Récapitulatif > Etape 3 : Paiement</p>	
</div>
$errors_messages
{$form->form_start()}
<div class="form-step-action clearfix">
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action next-step", 'name' => "recapitulatif", 'value' => "Confirmer et passer à l'étape 2 : Récapitulatif de la commande >"))}
</div>

<div class="infos clearfix">
{$form->fieldset_start(array('legend' => "Informations personnelles", 'class' => "infos-perso"))}
{$form->input(array('name' => "commande[nom]", 'label' => "Nom", 'type' => "text"))}
{$form->input(array('name' => "commande[prenom]", 'label' => "Prénom", 'type' => "text"))}
{$form->input(array('name' => "commande[societe]", 'label' => ucfirst($voca['societe']), 'type' => "text"))}
{$form->input(array('name' => "commande[siret]", 'label' => "N° de SIRET", 'type' => "text"))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => "Contact", 'class' => "contact-perso"))}
{$form->input(array('name' => "commande[telephone]", 'label' => "Téléphone", 'type' => "text"))}
{$form->input(array('name' => "commande[fax]", 'label' => "Fax", 'type' => "text"))}
{$form->input(array('name' => "commande[email]", 'label' => "Email", 'type' => "text"))}
{$form->fieldset_end()}
</div>
<div class="adresse clearfix">
{$form->fieldset_start(array('legend' => "Adresse de livraison", 'class' => "adresse-livraison"))}
{$form->input(array('name' => "livraison[societe]", 'label' => "Raison sociale", 'type' => "text"))}
{$form->input(array('name' => "livraison[societe2]", 'label' => ucfirst($voca['livraison_societe2']), 'type' => "text"))}
{$form->input(array('name' => "livraison[adresse]", 'label' => "N° et type de voie", 'type' => "text"))}
{$form->input(array('name' => "livraison[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "text"))}
{$form->input(array('name' => "livraison[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "text"))}
{$form->input(array('name' => "livraison[cp]", 'label' => "Code postal", 'type' => "text"))}
{$form->input(array('name' => "livraison[ville]", 'label' => "Ville", 'type' => "text"))}
{$form->input(array('name' => "livraison[cedex]", 'label' => "Cedex", 'type' => "text"))}
{$form->select(array('name' => "livraison[pays]", 'label' => "Pays", 'options' => $liste_pays))}
<br />
{$form->input(array('id' => "same-address", 'name' => "same_address", 'class' => "same_address", 'label' => "Même adresse pour la facturation", 'type' => "checkbox"))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => "Adresse de facturation", 'id' => "fieldset-facturation", 'class' => "adresse-facturation"))}
{$form->input(array('name' => "facturation[societe]", 'label' => "Raison sociale", 'type' => "text"))}
{$form->input(array('name' => "facturation[societe2]", 'label' => ucfirst($voca['facturation_societe2']), 'type' => "text"))}
{$form->input(array('name' => "facturation[adresse]", 'label' => "N° et type de voie", 'type' => "text"))}
{$form->input(array('name' => "facturation[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "text"))}
{$form->input(array('name' => "facturation[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "text"))}
{$form->input(array('name' => "facturation[cp]", 'label' => "Code postal", 'type' => "text"))}
{$form->input(array('name' => "facturation[ville]", 'label' => "Ville", 'type' => "text"))}
{$form->input(array('name' => "facturation[cedex]", 'label' => "Cedex", 'type' => "text"))}
{$form->select(array('name' => "facturation[pays]", 'label' => "Pays", 'options' => $liste_pays))}
{$form->fieldset_end()}
</div>
<div class="form-step-action clearfix">
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action next-step", 'name' => "recapitulatif", 'value' => "Confirmer et passer à l'étape 2 : Récapitulatif de la commande >"))}
</div>
{$cart}
{$form->form_end()}
HTML;
		break;

	case "recapitulatif" :

		$products_html = "<thead><tr><th>Produit</th><th>Référence</th><th>Prix unitaire</th><th>Quantité</th><th>Prix</th></tr></thead><tbody>";
		$total = 0;
		foreach ($products as $item_id => $item) {
			$prix_unitaire = number_format($item['prix_unitaire'], 2, ',', '');
			$prix = number_format($item['prix_unitaire'] * $item['quantite'], 2, ',', '');
			$products_html .= "<tr><td><p>{$item['nom']}</p>";
			if ($personnalisations[$item_id]['texte_perso']) {
				$texte_a_inserer = nl2br($personnalisations[$item_id]['texte_perso']);
				$products_html .= "<p><span class='label-perso'>Texte à insérer : </span>{$texte_a_inserer}</p>";
			}
			$products_html .= "</td><td>{$item['ref']}</td>";
			$products_html .= "<td>{$prix_unitaire}&nbsp;€</td><td>{$item['quantite']}</td><td>{$prix}&nbsp;€</td></tr>";
			$total += $prix;
		}
		$sous_total = number_format($total, 2, ',', '');
		$total = number_format($total + $frais_de_port + $tva, 2, ',', '');
		$frais_de_port = number_format($frais_de_port, 2, ',', '');
		$tva = number_format($tva, 2, ',', '');
		$products_html .= "</tbody><tfoot><tr><td /><td /><td /><td>Sous total</td><td>$sous_total&nbsp;€</td></tr>";
		$products_html .= "<tr><td /><td /><td /><td>Frais de port</td><td>$frais_de_port&nbsp;€</td></tr>";
		if ($tva) {
			$products_html .= "<tr><td /><td /><td /><td>TVA</td><td>$tva&nbsp;€</td></tr>";
		}
		$products_html .= "<tr class='total'><td /><td /><td /><td>Total</td><td>$total&nbsp;€</td></tr></tfoot>";
		$main .= <<<HTML
<div class="checkout-step">
	<p>Etape 1 : Informations > <strong>Etape 2 : Récapitulatif</strong> > Etape 3 : Paiement</p>	
</div>
$errors_messages
{$form->form_start()}
<div class="form-step-action clearfix">
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action previous-step", 'name' => "modification", 'value' => "< Retour : Modifier les informations"))}
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action next-step", 'name' => "paiement", 'value' => "Valider et payer (étape 3) >"))}
</div>

<table class="checkout-products">
{$products_html}
</table>

	<div class="form-checkout form-checkout-recap clearfix">
		<fieldset class="infos-perso">
			<legend>Informations personnelles</legend>
			<p>{$data['commande']['nom']}<br /> {$data['commande']['prenom']}<br /> {$data['commande']['societe']}<br />SIRET : {$data['commande']['siret']}</p>
		</fieldset>
		
		<fieldset class="infos-perso">
			<legend>Contact</legend>
			<p>{$data['commande']['email']} <br />Tel: {$data['commande']['telephone']}<br />Fax: {$data['commande']['fax']}</p>
		</fieldset>
		
		<fieldset class="adresse-livraison">
			<legend>Adresse de livraison</legend>
				<p>{$data['livraison']['adresse']}<br />{$data['livraison']['adresse2']}<br />{$data['livraison']['adresse3']}<br />{$data['livraison']['cp']}<br />{$data['livraison']['ville']}<br />{$data['livraison']['cedex']}<br />{$liste_pays[$data['livraison']['pays']]}</p>
		</fieldset>
		
		<fieldset class="adresse-facturation" id="fieldset-facturation">
			<legend>Adresse de facturation</legend>
				<p>{$data['facturation']['adresse']}<br />{$data['facturation']['adresse2']}<br />{$data['facturation']['adresse3']}<br />{$data['facturation']['cp']}<br />{$data['facturation']['ville']}<br />{$data['facturation']['cedex']}<br />{$liste_pays[$data['facturation']['pays']]}</p>
		</fieldset>
	</div>
	<div class="bloc-commentaire">
		{$form->textarea(array('name' => "commande[commentaire]", 'label' => "Vous souhaitez nous informer d'un point en particulier concernant votre commande, laissez votre commentaire :"))}
	</div>
	<div class="bloc-cgv">
		{$form->input(array('name' => "cgv", 'type' => "checkbox", 'label' => "J'ai lu et j'accepte les", 'unchecked' => true, 'template' => "#{field} #{label}"))}
		<a href="http://www.doublet.fr/cgv_doublet" target="_blank">conditions générales de vente</a>
	</div>
	<div class="clear"></div>

{$form->input(array('name' => "commande[id_api_keys]", 'type' => "hidden", 'value' => $api->key_id()))}
{$form->input(array('name' => "commande[nom]", 'label' => "Nom", 'type' => "hidden"))}
{$form->input(array('name' => "commande[prenom]", 'label' => "Prénom", 'type' => "hidden"))}
{$form->input(array('name' => "commande[societe]", 'label' => "Société", 'type' => "hidden"))}
{$form->input(array('name' => "commande[siret]", 'label' => "N° de SIRET", 'type' => "hidden"))}
{$form->input(array('name' => "commande[telephone]", 'label' => "Téléphone", 'type' => "hidden"))}
{$form->input(array('name' => "commande[fax]", 'label' => "Fax", 'type' => "hidden"))}
{$form->input(array('name' => "commande[email]", 'label' => "Email", 'type' => "hidden"))}

{$form->input(array('name' => "livraison[societe]", 'label' => "Raison sociale", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse]", 'label' => "N° et type de voie", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[cp]", 'label' => "Code postal", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[ville]", 'label' => "Ville", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[cedex]", 'label' => "Cedex", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[pays]", 'label' => "Pays", 'type' => "hidden"))}

{$form->input(array('name' => "facturation[societe]", 'label' => "Raison sociale", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse]", 'label' => "N° et type de voie", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[cp]", 'label' => "Code postal", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[ville]", 'label' => "Ville", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[cedex]", 'label' => "Cedex", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[pays]", 'label' => "Pays", 'type' => "hidden"))}
<div class="form-step-action clearfix">
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action previous-step", 'name' => "modification", 'value' => "< Retour : Modifier les informations"))}
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action next-step", 'name' => "paiement", 'value' => "Valider et payer (étape 3) >"))}
</div>
{$cart}
{$form->form_end()}
HTML;
		break;

	case "paiement" :
		$moyens_paiement = array(
			'cheque' => "Chèque",
			'mandat' => "Mandat administratif",
			'cb' => "Carte bleue",
			'paypal' => "Paypal",
			'facture' => "À réception de la facture",
		);
		$moyen_paiement_disponibles = explode(",",$api_data['moyens_paiement']);
		$moyens_paiement = array_intersect_key($moyens_paiement, array_flip($moyen_paiement_disponibles));
		$main .= <<<HTML
<div class="checkout-step">
	<p>Etape 1 : Informations > Etape 2 : Récapitulatif > <strong>Etape 3 : Paiement</strong></p>	
</div>
$errors_messages
{$form->form_start()}
{$form->select(array('name' => "commande[paiement]", 'label' => "Choisissez un moyen de paiement : ", 'options' => $moyens_paiement))}

{$form->input(array('name' => "commande[id_api_keys]", 'type' => "hidden", 'value' => $api->key_id()))}
{$form->input(array('name' => "commande[nom]", 'label' => "Nom", 'type' => "hidden"))}
{$form->input(array('name' => "commande[prenom]", 'label' => "Prénom", 'type' => "hidden"))}
{$form->input(array('name' => "commande[societe]", 'label' => "Société", 'type' => "hidden"))}
{$form->input(array('name' => "commande[siret]", 'label' => "N° de SIRET", 'type' => "hidden"))}
{$form->input(array('name' => "commande[telephone]", 'label' => "Téléphone", 'type' => "hidden"))}
{$form->input(array('name' => "commande[fax]", 'label' => "Fax", 'type' => "hidden"))}
{$form->input(array('name' => "commande[email]", 'label' => "Email", 'type' => "hidden"))}
{$form->input(array('name' => "commande[commentaire]", 'label' => "Commentaire", 'type' => "hidden"))}

{$form->input(array('name' => "livraison[societe]", 'label' => "Raison sociale", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse]", 'label' => "N° et type de voie", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[cp]", 'label' => "Code postal", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[ville]", 'label' => "Ville", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[cedex]", 'label' => "Cedex", 'type' => "hidden"))}
{$form->input(array('name' => "livraison[pays]", 'label' => "Pays", 'type' => "hidden"))}

{$form->input(array('name' => "facturation[societe]", 'label' => "Raison sociale", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse]", 'label' => "N° et type de voie", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse2]", 'label' => "Z.I, Z.A, résidence, immeuble, étage", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[adresse3]", 'label' => "Boite postale, lieu-dit, ...", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[cp]", 'label' => "Code postal", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[ville]", 'label' => "Ville", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[cedex]", 'label' => "Cedex", 'type' => "hidden"))}
{$form->input(array('name' => "facturation[pays]", 'label' => "Pays", 'type' => "hidden"))}
<div class="form-step-action clearfix">
{$form->input(array('type' => "submit", 'class' => "doublet-cart-action next-step", 'name' => "validation", 'value' => "Continuer >"))}
</div>
{$cart}
{$form->form_end()}
HTML;
		break;

	case "paiement_cic" :
		$data_cic = array(
			'montant' => $commande->montant(),
			'reference' => $id_commande,
			'lgue' => "FR",
			'mail' => $data['commande']['email'],
			'url_retour' => "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
			'url_retour_ok' => "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
			'url_retour_err' => "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
		);
		$main .= <<<HTML
<p>Merci pour votre achat.</p>
<p>Le paiement s'effectue sur un site sécurisé.</p>
{$cic->form($data_cic, "_self", false)}
HTML;
		break;

	case "paiement_paypal" :
		$data_paypal = array(

		);
		$main .= <<<HTML
<p>Merci pour votre achat.</p>
<p>Le paiement s'effectue sur le site sécurisé Paypal.</p>
{$paypal->form($commande, "_blank", true)}
HTML;
		break;

	case "remerciements" :
		$main .= <<<HTML
<div class="checkout-step">
	<p>Etape 1 : Informations > Etape 2 : Récapitulatif > Etape 3 : Paiement</p>	
</div>
$errors_messages
<div class="checkout-validation">
	<p><img src="{$config->media('checkout-ok.png')}" /></p>
	<p>Merci pour votre commande.</p>
	<p>{$data['infos']['paiement']}</p>
	<p>{$voca['message_commande']}</p>
	<p>L'équipe commerciale.</p>
	<button onclick="javascript:window.close()">Fermer cette fenêtre</button>
</div>
HTML;
		break;
}


