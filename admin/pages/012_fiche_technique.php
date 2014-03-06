<?php

$menu->current('main/products/products');

$config->core_include("produit/fiche", "produit/produit", "produit/attribut", "produit/sku", "outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$config->core_include("outils/exterieurs/html2pdf/html2pdf.class");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$produit = new Produit($sql, $phrase, $id_langues);
$attribut = new Attribut($sql, $phrase, $id_langues);
$sku = new Sku($sql, $phrase, $id_langues);

$langue = $config->get("langue");

if ($id = $url5->get('id')) {
	$produit->load($id);
	$phrases = $phrase->get($produit->phrases());
	$images = $produit->images();
}

$fiche = new Fiche($sql, $phrase, $langue, $produit, $sku, $attribut, $phrases);

$form = new Form(array(
	'id' => "form-fiche-$id",
	'actions' => array("select"),
));

if ($form->is_submitted()) {
	if ($form->action() == "select") {
		$fiche_id = $form->value('fiche');
	}
}
else {
	$form->reset();
}

$modeles = $fiche->modeles($id_langues);
if (!isset($fiche_id)) {
	$fiche_id = $url5->get('fiche_id');
}
if (!$fiche_id) {
	$fiche_id = key($modeles);
}

if (preg_match("/.xml$/", $url5->get('file'))) {
	$page->template("fichexml");
	$xml = $fiche->xml();
}
else {
	$file = "fiche";
	$page->css[] = $config->media("fichehtml.css");
	$page->template("fichehtml");
	list($main, $fiche_css) = $fiche->html($fiche_id);
	$page->my_css[] = $fiche_css;
	$top = <<<HTML
{$form->form_start()}
<ul>
<li>{$page->l($dico->t('Retour'), $url2->make("Produits", array("type" => "produits", "action"=>"edit", "id" => $id)))}</li>
<li><a href="#" onclick="window.print();">Imprimer</a></li>
<li>{$page->l("PDF", $url5->make("current", array("file" => "$file.pdf", "fiche_id" => $fiche_id)))}</li>
<li>{$page->l("XML", $url5->make("current", array("file" => "$file.xml", "fiche_id" => $fiche_id)))}</li>
</ul>
{$form->select(array('name' => "fiche", 'options' => $fiche->modeles($id_langues), 'forced_value' => $fiche_id))}
{$form->input(array('type' => "submit", "name" => "action[select]", "value" => $dico->t('Ok') ))}
{$form->form_end()}
HTML;

	list($fiche_html, $fiche_css) = $fiche->html($fiche_id);
	$page->my_css[] = $fiche_css;

	if (preg_match("/.pdf$/", $url5->get('file'))) {
		$html2pdf = new HTML2PDF('P', 'A4', 'fr');
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($page->my_css().$fiche_html);
		$html2pdf->Output();
	}
}

