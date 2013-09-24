<?php

$menu->current('main/customers/commandes');

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");
$page->javascript[] = $config->media("produit.js");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$page->css[] = $config->media("produit.css");
$page->css[] = $config->core_media("jquery-ui.custom.css");

$form_start = $form->form_start();

$form->template = $page->inc("snippets/produits-form-template");

if ($action == "create" or $action == "edit") {
	$buttons[] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));
}

$buttons[] = $page->l($dico->t('NouvelleCommande'), $url->make("current", array('action' => "create", 'id' => "")));

if ($action == "create" or $action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons[] = $form->input(array('type' => "submit", 'name' => "reset", 'value' => $dico->t('Reinitialiser') ));
}

if ($action == "edit") {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "delete", 'class' => "delete", 'value' => $dico->t('Supprimer') ));
}

if ($action == "create" or $action == "edit") {
	$sections = array(
		'commande' => $dico->t('Commande'),
		'client' => $dico->t('Client'),
		'livraison' => $dico->t('AdresseLivraison'),
		'facturation' => $dico->t('AdresseFacturation'),
	);

	if ($action == "edit") {
		$sections['produits'] = $dico->t('Produits');
	}

	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");

	$main = <<<HTML
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}

{$form->fieldset_start(array('legend' => $dico->t('Commande'), 'class' => "produit-section produit-section-commande".$hidden['commande'], 'id' => "produit-section-commande"))}
{$form->input(array('name' => "commande[shop]", 'label' => "Shop"))}
{$form->input(array('name' => "commande[id_api_keys]", 'label' => "id_api_keys"))}
{$form->select(array('name' => "commande[etat]", 'label' => "Etat", 'options' => $etats))}
{$form->text(array('name' => "commande[montant]", 'label' => "Montant"))}
{$form->text(array('name' => "commande[frais_de_port]", 'label' => "Frais de port"))}
{$form->input(array('name' => "commande[tva]", 'label' => "TVA"))}
{$form->date(array('name' => "commande[date_commande]", 'label' => "Date de la commande"))}
{$form->select(array('name' => "commande[paiement]", 'label' => "Paiement", 'options' => $paiements))}
{$form->select(array('name' => "commande[paiement_statut]", 'label' => "Statut  du paiement", 'options' => $paiements_statuts))}
{$form->textarea(array('name' => "commande[commentaire]", 'label' => "Commentaire"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('Client'), 'class' => "produit-section produit-section-client".$hidden['client'], 'id' => "produit-section-commande"))}
{$form->input(array('name' => "commande[nom]", 'label' => "Nom"))}
{$form->input(array('name' => "commande[prenom]", 'label' => "Prénom"))}
{$form->select(array('name' => "commande[profil]", 'label' => "Profil", 'options' => $dico->d("profils_clients")))}
{$form->input(array('name' => "commande[societe]", 'label' => "Société"))}
{$form->input(array('name' => "commande[num_client]", 'label' => "Numéro Client"))}
{$form->input(array('name' => "commande[siret]", 'label' => "SIRET"))}
{$form->input(array('name' => "commande[email]", 'label' => "Email"))}
{$form->input(array('name' => "commande[telephone]", 'label' => "Téléphone"))}
{$form->input(array('name' => "commande[fax]", 'label' => "Fax"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('AdresseLivraison'), 'class' => "produit-section produit-section-livraison".$hidden['livraison'], 'id' => "produit-section-livraison"))}
{$form->input(array('name' => "commande[livraison_societe]", 'label' => "Société"))}
{$form->input(array('name' => "commande[livraison_societe2]", 'label' => "Société (complement)"))}
{$form->input(array('name' => "commande[livraison_adresse]", 'label' => "Adresse"))}
{$form->input(array('name' => "commande[livraison_adresse2]", 'label' => "Adresse (complément)"))}
{$form->input(array('name' => "commande[livraison_adresse3]", 'label' => "Adresse (complément 2)"))}
{$form->input(array('name' => "commande[livraison_cp]", 'label' => "Code postal"))}
{$form->input(array('name' => "commande[livraison_ville]", 'label' => "Ville"))}
{$form->input(array('name' => "commande[livraison_cedex]", 'label' => "Cedex"))}
{$form->select(array('name' => "commande[livraison_pays]", 'label' => "Pays", 'options' => $liste_pays))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('AdresseFacturation'), 'class' => "produit-section produit-section-facturation".$hidden['facturation'], 'id' => "produit-section-facturation"))}
{$form->input(array('name' => "commande[facturation_societe]", 'label' => "Société"))}
{$form->input(array('name' => "commande[facturation_societe2]", 'label' => "Société (complement)"))}
{$form->input(array('name' => "commande[facturation_adresse]", 'label' => "Adresse"))}
{$form->input(array('name' => "commande[facturation_adresse2]", 'label' => "Adresse (complément)"))}
{$form->input(array('name' => "commande[facturation_adresse3]", 'label' => "Adresse (complément 2)"))}
{$form->input(array('name' => "commande[facturation_cp]", 'label' => "Code postal"))}
{$form->input(array('name' => "commande[facturation_ville]", 'label' => "Ville"))}
{$form->input(array('name' => "commande[facturation_cedex]", 'label' => "Cedex"))}
{$form->select(array('name' => "commande[facturation_pays]", 'label' => "Pays", 'options' => $liste_pays))}
{$form->fieldset_end()}
HTML;
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "commande[id]"))}

{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
HTML;

	foreach ($commande->produits() as $key => $produit) {
		$id_produits = $produit['id_produits'];
		$id_sku = $produit['id_sku'];
		$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produit')." #".$id_produits." ".$dico->t("SKU")." #".$id_sku, 'class' => "produit-section produit-section-produits".$hidden['produits']))}
{$form->html($dico->t('Produit')." : ".$page->l($id_produits, $url2->make("produits", array('type' => "produits", 'id' => $id_produits, 'action' => "edit"))))}
{$form->html($dico->t('SKU')." : ".$page->l($id_sku, $url2->make("produits", array('type' => "sku", 'id' => $id_sku, 'action' => "edit"))))}
{$form->input(array('type' => "hidden", 'name' => "produits[$key][id_produits]", 'value' => $id_produits))}
{$form->input(array('type' => "hidden", 'name' => "produits[$key][id_sku]", 'value' => $id_sku))}
{$form->input(array('name' => "produits[$key][ref]", 'label' => "Référence"))}
{$form->input(array('name' => "produits[$key][nom]", 'label' => "Nom"))}
{$form->input(array('name' => "produits[$key][prix_unitaire]", 'label' => "Prix unitaire"))}
{$form->input(array('name' => "produits[$key][quantite]", 'label' => "Quantite"))}
{$form->select(array('name' => "produits[$key][echantillon]", 'label' => "Echantillon", 'options' => array("non", "oui")))}
{$form->textarea(array('name' => "produits[$key][personnalisation_texte]", 'label' => "Texte perso"))}
{$form->input(array('name' => "produits[$key][personnalisation_fichier]", 'label' => "Fichier perso"))}
{$form->input(array('name' => "produits[$key][personnalisation_nom_fichier]", 'label' => "Nom fichier perso"))}
{$form->input(array('type' => "submit", 'name' => "delete-produit[$key]", 'class' => "delete", 'value' => $dico->t('Supprimer') ))}
{$form->fieldset_end()}
HTML;
	}


	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('NouveauProduit'), 'class' => "produit-section produit-section-produits".$hidden['produits']))}
{$form->input(array('name' => "produits[new][id_produits]", 'label' => "Id Produit"))}
{$form->input(array('name' => "produits[new][id_sku]", 'label' => "Id SKU"))}
{$form->input(array('name' => "produits[new][ref]", 'label' => "Référence"))}
{$form->input(array('name' => "produits[new][nom]", 'label' => "Nom"))}
{$form->input(array('name' => "produits[new][prix_unitaire]", 'label' => "Prix unitaire"))}
{$form->input(array('name' => "produits[new][quantite]", 'label' => "Quantite"))}
{$form->textarea(array('name' => "produits[new][personnalisation_texte]", 'label' => "Texte perso"))}
{$form->input(array('name' => "produits[new][personnalisation_fichier]", 'label' => "Fichier perso"))}
{$form->input(array('name' => "produits[new][personnalisation_nom_fichier]", 'label' => "Nom fichier perso"))}
{$form->fieldset_end()}
{$form->fieldset_end()}
HTML;
}

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
		$commande->liste($filter);
		$main = $page->inc("snippets/filter");
		break;
}

$form_end = $form->form_end();
