<?php
/*
 * Configuration
 */
$config->core_include("outils/mysql", "outils/langue", "outils/form", "outils/phrase");
$config->core_include("produit/fiche", "produit/produit", "produit/attribut", "produit/sku");
$dirname = dirname(__FILE__).'/../traductions/';
//$main_lg = 'fr_FR';

$page->css[] = $config->media("schema.css");
$menu->current('main/products/export');

// Initialisation des classes
$sql = new Mysql($config->db());
$phrase = new Phrase($sql);
$produit = new Produit($sql, $phrase, $config->get("langue"));
$attribut = new Attribut($sql, $phrase, $config->get("langue"));
$sku = new Sku($sql, $phrase, $config->get("langue"));
$langue = $config->get("langue");
$form = new Form(array(
	'id' => "form-exports",
	'class' => "form-exports",
	'required' => array(),
));


// Vérification de l'envoi du formulaire
if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$form_produits = $datas['id_produits'];
	$form_applications = $datas['id_applications'];	
	
	// génération du XML
	$q = "SELECT id FROM dt_produits WHERE ";
	if ($form_produits != 0) {
		$q .= "id IN (".$form_produits.") ";
	}
	else if ($form_applications != 0) {
		if ($form_produits != 0) {
			$q .= " AND ";
		}
		$q .= "id_applications IN (".$form_applications.") ";
	}
	$q .= " AND actif = 1 ";
	//echo $q;
	$rs = $sql->query($q);
	
	$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$xml .= "<items>";
	while($row = $sql->fetch($rs)) {
		$produit->load($row['id']);
		$phrases = $phrase->get($produit->phrases());
		$images = $produit->images();
		$fiche = new Fiche($sql, $phrase, $langue, $produit, $sku, $attribut, $phrases);
		$xml .= "<item>".$fiche->xml()."</item>";
	}
	$xml .= "</items>";
	
	$xml = str_replace('<file>','<file>http://www.doublet.pro/medias/images/produits/',$xml);
	$xml = str_replace('<BR>','',$xml);
	$xml = str_replace('<strong>','',$xml);
	$xml = str_replace('</strong>','',$xml);
}
else {
	$form_produits = 0;
	$form_applications = 0;
	$xml = "";
}




/*
 * Affichage
 */
$titre_page = $dico->t('ExporterProduits');
$main = <<<HTML
<div>
	<textarea class="area_export">$xml</textarea>
</div>
HTML;

$right = <<<RIGHT
{$form->form_start()}
{$form->fieldset_start($dico->t("RechercheAvancee"))}
<p>{$form->input(array('name' => "id_produits", 'label' => $dico->t('EnterIdProduits'), 'value' => $form_produits ))}</p>
<p>{$form->input(array('name' => "id_applications", 'label' => $dico->t('EnterIdApplications'), 'value' => $form_applications ))}</p>
<p>{$form->input(array('type'=>'submit', 'name'=>'search', 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
{$form->form_end()}
RIGHT;

?>