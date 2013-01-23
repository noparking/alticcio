<?php

$menu->current('main/products/matieres');

$config->core_include("produit/fiche_matiere", "produit/matiere", "produit/attribut", "outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$config->core_include("outils/exterieurs/html2pdf/html2pdf.class");

$sql = new Mysql($config->db());

$phrase = new Phrase($sql);

$langue = $config->get("langue");
$lang = new Langue($sql);
$id_langues = $lang->id($langue);

$matiere = new Matiere($sql, $phrase, $id_langues);
$attribut = new Attribut($sql, $phrase, $id_langues);

if ($id = $url5->get('id')) {
	$matiere->load($id);
	$phrases = $phrase->get($matiere->phrases());
	$images = $matiere->images();
}

$fiche = new FicheMatiere($sql, $phrase, $langue, $id_langues, $matiere, $attribut, $phrases);

$form = new Form(array(
	'id' => "form-fiche-matiere-$id",
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

$modeles = $fiche->modeles($langue);
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
{$form->select(array('name' => "fiche", 'options' => $fiche->modeles($langue), 'forced_value' => $fiche_id))}
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

