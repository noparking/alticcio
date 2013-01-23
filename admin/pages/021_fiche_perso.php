<?php

$menu->current('main/products/products');

$config->core_include("produit/produit", "produit/attribut", "produit/sku", "outils/form", "outils/mysql", "outils/phrase", "outils/langue");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$produit = new Produit($sql, $id_langues);
$attribut = new Attribut($sql, $id_langues);
$sku = new Sku($sql, $phrase, $id_langues);

$code_langue = $config->get("langue");

if ($id = $url3->get('id')) {
	$produit->load($id);
	$phrases = $phrase->get($produit->phrases());
	$images = $produit->images();
}

$form = new Form(array(
	'id' => "form-fiche-$id",
	'actions' => array("reset"),
));

$user_data = $user->data();
$user_id = $user_data['id'];

if ($form->is_submitted()) {
	if ($form->action() == "reset") {
		$produit->reset_fiche_perso($user_id);
	}
	else {
		$produit->save_fiche_perso($form->values(), $user_id);
	}
}

$fiche = $produit->fiche_perso($user_id, array(
	'main' => array(
		'nom', 'ref', 'description_courte', 'description',
		'img0', 'img1', 'img2', 'img3', 'img4', 'img5', 'img6', 'img7', 'img8', 'img9',
		'attributs', 'variantes', 'accessoires', 'composants',
	),
	'hidden' => array(),
));

if (preg_match("/.html$/", $url3->get('file'))) {
	$page->css[] = $config->media("fichehtml.css");
	$page->template("fichehtml");
	$main = fiche_display_zone("main", "html");
	$link_back = $page->l($dico->t('Retour'), $url2->make("FicheTechnique", array("type"=>"", "action"=>"", "id" => $id)));
}
else if (preg_match("/.xml$/", $url3->get('file'))) {
	$page->template("fichexml");
	$xml = fiche_display_zone("main", "xml");
}
else {
	$page->javascript[] = $config->core_media("jquery.min.js");
	$page->javascript[] = $config->core_media("jquery-ui.sortable.min.js");
	$page->javascript[] = $config->media("fiche-produit.js");

	$titre_page = $dico->t('FichePersoProduit')." # ID : ".$id;

	$form_start = $form->form_start();

	$main = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Afficher'), 'class' => "fiche-produit-zone", 'id' => "fiche-produit-zone-main"))}
HTML;
	$main .= fiche_display_zone("main", "form");
	$main .= <<<HTML
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('PasAfficher'), 'class' => "fiche-produit-zone", 'id' => "fiche-produit-zone-hidden"))}
HTML;
	$main .= fiche_display_zone("hidden", "form");
	$main .= <<<HTML
{$form->fieldset_end()}
HTML;

	$file = "fiche";

	$buttons = array();
	$buttons['back'] = $page->l($dico->t('Retour'), $url2->make("Produits", array("type" => "produits", "action"=>"edit", "id" => $id)));
	$buttons['save'] = $form->input(array("type" => "submit", "name" => "action[save]", "value" => $dico->t('Sauvegarder') ));
	$buttons['reset'] = $form->input(array("type" => "submit", "name" => "action[reset]", "value" => $dico->t('Reinitialiser') ));
	$buttons['html'] = $page->l("HTML", $url3->make("current", array("file" => $file.".html")));
	$buttons['pdf'] = $page->l("PDF", $url3->make("current", array("file" => $file.".pdf")));
	$buttons['xml'] = $page->l("XML", $url3->make("current", array("file" => $file.".xml")));

	$form_end = $form->form_end();
}

function fiche_display_zone($zone, $display) {
	global $fiche;

	$html = "";
	if (isset($fiche[$zone])) {
		foreach ($fiche[$zone] as $i => $element) {
			$html .= fiche_display_element($element, $display);
		}
	}
	return $html;
}

function fiche_display_element($element, $display) {
	global $url2, $phrases, $config, $produit, $images, $code_langue, $page, $dico;

	$value = "";
	$phrase = "";
	switch ($element['element']) {
		case 'nom' :
			$phrase = 'phrase_nom';
			break;
		case 'ref' :
			$value = $produit->values['ref'];
			break;
		case 'description_courte' :
			$phrase = 'phrase_description_courte';
			break;
		case 'description' :
			$phrase = 'phrase_description';
			break;
		case 'attributs' :
			$value = fiche_attributs($element, $display);
			break;
		case 'variantes' :
		case 'accessoires' :
		case 'composants' :
			$value = fiche_relations($element, $display);
			break;
	}
	if (isset($phrases[$phrase][$code_langue])) {
		$value = $phrases[$phrase][$code_langue];
	}
	if (preg_match("/^img([0-9]+)$/", $element['element'], $matches)) {
		$html = "";
		$i = 0;
		foreach ($images as $image) {
			if ($matches[1] == $i) {
				$value = "http://".$_SERVER['HTTP_HOST'].$config->media("produits/".$image['ref']);
				break;
			}
			$i++;
		}
	}

	$output = isset($element[$display]) ? $element[$display] : (isset($element['html']) ? $element['html'] : "");

	if (!$output) {
		$output = $value;
	}
	else {
		$output = str_replace("[value]", $value, $output);
	}

	switch ($display) {
		case 'html' :
			if (!$output) {
				return "";
			}
			return <<<HTML
<div class="fiche-produit-element" id="fiche-produit-element-{$element['element']}">
	{$output}
</div>
HTML;
		case 'xml' :
			if (!$output) {
				return "";
			}
			if (!isset($element['xml']) or !$element['xml']) {
				$output = "<{$element['element']}>$output</{$element['element']}>";
			}
			return "\t$output\n";
		case 'form' :
			$element_id = isset($element['id']) ? $element['id'] : "";
			if ($element_id) {
				$output = $page->l("[".$element['element']."]", $url2->make("FicheElement", array("action" => "edit", "id" => $element_id))) . " $output";
			}
			return <<<HTML
<div class="fiche-produit-element" id="fiche-produit-element-{$element['element']}">
	{$output}
	<input type="hidden" name="fiche[{$element['element']}][id]" value="{$element_id}" />
	<input type="hidden" name="fiche[{$element['element']}][zone]" value="{$element['zone']}" />
	<input type="hidden" name="fiche[{$element['element']}][classement]" value="{$element['classement']}" />
</div>
HTML;
	}
}

function fiche_attributs($element, $display) {
	global $produit, $attribut, $code_langue, $dico;
	$attributs = $produit->fiche_perso_attributs($attribut, $code_langue);
	$choix = array("N/A", $dico->t("Oui"), $dico->t("Non"));

	switch ($display) {
		case 'xml' :
			$output = "<attributs>";
			foreach ($attributs as $attribut) {
				$valeur = ($attribut['type'] == "choice") ? $choix[$attribut['valeur']] : $attribut['valeur'];
				$output .= "<attribut>";
				$output .= "<nom>{$attribut['nom']}</nom>";
				$output .= "<valeur>{$valeur}</valeur>";
				$output .= "<unite>{$attribut['unite']}</unite>";
				$output .= "</attribut>";
			}
			$output .= "</attributs>";
			return $output;
		case 'form' :
		case 'html' :
			$output = "<ul>";
			foreach ($attributs as $attribut) {
				$valeur = ($attribut['type'] == "choice") ? $choix[$attribut['valeur']] : $attribut['valeur'];
				$output .= "<li>{$attribut['nom']} : {$valeur} {$attribut['unite']}</li>";
			}
			$output .= "</ul>";
			return $output; 
	}
}

function fiche_relations($element, $display) {
	global $produit, $sku, $phrase, $code_langue;
	$method = $element['element'];
	
	switch ($display) {
		case 'xml' :
			$output = "";
			return $output;
		case 'form' :
		case 'html' :
			$output = "";
			$skus = $produit->$method();
			if (count($skus)) {
				$output .= "<table>";
				$output .= "<tr><th>Nom</th><th>Ref</th><th>Prix HT</th></tr>";
				foreach ($skus as $id => $item) {
					$sku->load($id);
					$phrases = $phrase->get($sku->phrases());
					$output .= "<tr>";
					$output .= "<td>{$phrases['phrase_ultralog'][$code_langue]}</td>";
					$output .= "<td>{$sku->values['ref_ultralog']}</td>";
					$prix = $sku->prix();
					$prix_degressifs = $sku->prix_degressifs();
					if (count($prix_degressifs)) {
						$qties = array("<th>1</th>");
						$values = array("<td>{$prix['montant_ht']}</td>");
						foreach ($prix_degressifs as $p) {
							$qties[] = "<th>&gt; {$p['quantite']}</th>";
							$values[] = "<td>{$p['montant_ht']}</td>";
						}
						$prix = "<table>";
						$prix .= "<tr>".implode("", $qties)."</tr>";
						$prix .= "<tr>".implode("", $values)."</tr>";
						$prix .= "</table>";
					}
					else {
						$prix = $prix['montant_ht'];
					}
					$output .= "<td>$prix</td>";
					$output .= "</tr>";
				}
				$output .= "</table>";
			}
			return $output;
	}
}
