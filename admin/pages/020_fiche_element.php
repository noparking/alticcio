<?php

$menu->current('main/products/products');

$config->core_include("produit/produit", "outils/form", "outils/mysql", "outils/phrase", "outils/langue");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$produit = new Produit($sql, $phrase, $id_langues);

if ($id = $url2->get('id')) {
	$fiche_element = $produit->fiche_perso_element($id);
}

$form = new Form(array(
	'id' => "form-fiche-$id",
	'actions' => array("save"),
));

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "save":
			$produit->save_fiche_perso_element($data, $id);
			$fiche_element = $produit->fiche_perso_element($id);
			$form->reset();
			break;
	}
}
else {
	$form->reset();
}

$form->default_values['fiche_element'] = $fiche_element;

$titre_page = $dico->t('FicheTechniqueElement')." # ID : ".$id;

$buttons['back'] = $page->l($dico->t('Retour'), $url3->make("FicheTechnique", array('id' => $url2->get('type'))));

$form_start = $form->form_start();

$buttons['save'] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
$main = <<<HTML
<p>Vous pouvez utiliser des balises HTML pour formater cet élément.</p>
<p>Utilisez [value] pour faire référence à la valeur de l'élément.</p>
{$form->textarea(array('name' => "fiche_element[html]", 'label' => $dico->t('HTML')))}
{$form->textarea(array('name' => "fiche_element[xml]", 'label' => $dico->t('XML')))}
HTML;

$form_end = $form->form_end();
