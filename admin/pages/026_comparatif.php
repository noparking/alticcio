<?php

$menu->current('main/products/compare');

$config->core_include("produit/application", "produit/produit", "produit/attribut", "produit/sku");
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));

$phrase = new Phrase($sql);

$application = new Application($sql, $phrase, $id_langue);

$produit = new Produit($sql, $phrase, $config->get("langue"));

$sku = new Sku($sql, $phrase, $config->get("langue"));

$attribut = new Attribut($sql, $phrase, $id_langue);

$form = new Form(array(
	'id' => "form-compare-products",
));

$comparatif = "";
$comparatif_csv = "";

if ($form->is_submitted()) {
	$values = $form->escaped_values();
	switch ($values['actif']) {
		case 0 : $actif = null; break;
		case 1 : $actif = 1; break;
		case 2 : $actif = 0; break;
	} 
	$liste_produit = trim(preg_replace("/[^0-9]+/", " ", $values['produits']));
	$produits = $liste_produit ? explode(" ", $liste_produit) : array();
	$produits = $application->produits($values['application'], $produits, $actif);
	$application->load($values['application']);

	switch ($form->action()) {
		case 'fiche' :
		case 'fiche-csv' :
		$comparatif .= <<<HTML
<table>
<tr>
<th>ID</th>
<th>Nom</th>
<th>Référence</th>
<th>Type</th>
<th>Statut</th>
<th>Gamme</th>
<th>Offre</th>
<th>Filière de recyclage</th>
<th>Intitulé de l'url</th>
<th><div style="width: 200px">Description courte</div></th>
<th><div style="width: 600px">Description</div></th>
<th><div style="width: 200px">Conseil d'entretien</div></th>
<th><div style="width: 200px">Mode d'emploi</div></th>
<th><div style="width: 200px">Les + produit</div></th>
<th><div style="width: 300px">Attributs</div></th>
<th><div style="width: 500px">Déclinaisons</div></th>
<th><div style="width: 500px">Accessoires</div></th>
<th><div style="width: 500px">Produits complémentaires</div></th>
<th><div style="width: 500px">Produits similaires</div></th>
<th><div style="width: 200px">Meta title</div></th>
<th><div style="width: 200px">Meta keywords</div></th>
<th><div style="width: 300px">Meta description</div></th>
</tr>
HTML;
		$comparatif_csv = <<<CSV
ID;Nom;Référence;Type;Statut;Gamme;Offre;Filière de recyclage;Intitulé de l'url;Description courte;Description;Conseil d'entretien;Mode d'emploi;Les + produit;Attributs;Déclinaisons;Accessoires;Produits complémentaires;Produits similaires;Meta title;Meta keywords;Meta description
CSV;
		$types = $produit->types();
		$gammes = $produit->gammes();
		$offres = array(
			0 => "...",
			1 => $dico->t('GammeEssentiel'),
			2 => $dico->t('GammePro'),
			3 => $dico->t('GammeExpert'),
		);
		$recyclages = $produit->recyclage($id_langue);
		foreach ($produits as $id_produits) {
			$produit->load($id_produits);
			$phrases = $phrase->get($produit->phrases());
			$data = $produit->values;
			$statut = $data['actif'] ? "actif" : "inactif";
			$nom = isset($phrases['phrase_nom']) ? $phrases['phrase_nom'][$config->get("langue")] : "";
			$url_key = isset($phrases['phrase_url_key']) ? $phrases['phrase_url_key'][$config->get("langue")] : "";
			$description_courte = isset($phrases['phrase_description_courte']) ? $phrases['phrase_description_courte'][$config->get("langue")] : "";
			$description = isset($phrases['phrase_description']) ? $phrases['phrase_description'][$config->get("langue")] : "";
			$entretien = isset($phrases['phrase_entretien']) ? $phrases['phrase_entretien'][$config->get("langue")] : "";
			$mode_emploi = isset($phrases['phrase_mode_emploi']) ? $phrases['phrase_mode_emploi'][$config->get("langue")] : "";
			$avantages_produit = isset($phrases['phrase_avantages_produit']) ? $phrases['phrase_avantages_produit'][$config->get("langue")] : "";
			$meta_title = isset($phrases['phrase_meta_title']) ? $phrases['phrase_meta_title'][$config->get("langue")] : "";
			$meta_keywords = isset($phrases['phrase_meta_keywords']) ? $phrases['phrase_meta_keywords'][$config->get("langue")] : "";
			$meta_description = isset($phrases['phrase_meta_description']) ? $phrases['phrase_meta_description'][$config->get("langue")] : "";
			$attributs_produit = $produit->attributs();
			$attributs_application = $application->attributs();
			$attributs = "<ul>";
			foreach  ($attributs_application as $attribut_id) {
				$attribut->load($attribut_id);
				list($label) = $phrase->get(array($attribut->values['phrase_nom']));
				switch ($attribut->type_attribut) {
					case 'choice' :
						switch ($attributs_produit[$attribut_id]) {
							case 0 : $value = "N/A"; break;
							case 1 : $value = "Oui"; break;
							case 2 : $value = "Non"; break;
						}
						break;
					case 'mark' :
						$value = $attributs_produit[$attribut_id] ? $attributs_produit[$attribut_id] : "N/A";
						break;
					case 'text' :
					case 'textarea' :
						$value = isset($phrases['valeurs_attributs'][$attribut_id]) ? $phrases['valeurs_attributs'][$attribut_id][$config->get('langue')] : "";
						break;
					case 'select' :
					case 'multiselect' :
						$options = array();
						$phrase_ids = array();
						foreach ($attribut->options() as $option) {
							$phrase_ids[$option['id']] = $option['phrase_option'];
						}
						$phrases_options = $phrase->get($phrase_ids);
						foreach ($attribut->options() as $option) {
							$options[$option['phrase_option']] = $phrases_options[$option['id']][$config->get('langue')];
						}
						$value = $options[$attributs_produit[$attribut_id]];
						break;
					case 'number' :
					default :
						$value = $attributs_produit[$attribut_id];
						break;
				}
				$attributs .= "<li>{$label[$config->get('langue')]} : $value</li>";
			}
			$attributs .= "</ul>";
			
			$declinaisons = "<ul>";
			foreach ($produit->variantes() as $declinaison) {
				$sku->load($declinaison['id_sku']);
				$phrases_sku = $phrase->get($sku->phrases());
				$declinaisons .= "<li>({$sku->values['ref_ultralog']}) {$phrases_sku['phrase_ultralog'][$config->get('langue')]}</li>";
			}
			$declinaisons .= "</ul>";

			$accessoires = "<ul>";
			foreach ($produit->accessoires() as $accessoire) {
				$sku->load($accessoire['id_sku']);
				$phrases_sku = $phrase->get($sku->phrases());
				$accessoires .= "<li>({$sku->values['ref_ultralog']}) {$phrases_sku['phrase_ultralog'][$config->get('langue')]}</li>";
			}
			$accessoires .= "</ul>";

			$complementaires = "<ul>";
			foreach ($produit->complementaires() as $complementaire) {
				$produit->load($complementaire['id_produits_compl']);
				$phrases_produit = $phrase->get($produit->phrases());
				$complementaires .= "<li>({$produit->values['ref']}) {$phrases_produit['phrase_nom'][$config->get('langue')]}</li>";
			}
			$complementaires .= "</ul>";

			$similaires = "<ul>";
			foreach ($produit->similaires() as $similaire) {
				$produit->load($similaire['id_produits_sim']);
				$phrases_produit = $phrase->get($produit->phrases());
				$similaires .= "<li>({$produit->values['ref']}) {$phrases_produit['phrase_nom'][$config->get('langue')]}</li>";
			}
			$similaires .= "</ul>";

			$comparatif .= <<<HTML
<tr>
<td>{$page->l($data['id'], $url2->make("produits", array('type' => "produits", 'action' => 'edit', 'id' => $data['id'])))}</td>
<td>{$page->l($nom, $url2->make("produits", array('type' => "produits", 'action' => 'edit', 'id' => $data['id'])))}</td>
<td>{$data['ref']}</td>
<td>{$types[$data['id_types_produits']]}</td>
<td>{$statut}</td>
<td>{$gammes[$data['id_gammes']]}</td>
<td>{$offres[$data['offre']]}</td>
<td>{$recyclages[$data['id_recyclage']]}</td>
<td>{$url_key}</td>
<td>{$description_courte}</td>
<td>{$description}</td>
<td>{$entretien}</td>
<td>{$mode_emploi}</td>
<td>{$avantages_produit}</td>
<td>{$attributs}</td>
<td>{$declinaisons}</td>
<td>{$accessoires}</td>
<td>{$complementaires}</td>
<td>{$similaires}</td>
<td>{$meta_title}</td>
<td>{$meta_keywords}</td>
<td>{$meta_description}</td>
</tr>
HTML;
			foreach (array("description_courte", "description") as $var) {
				$$var = str_replace(array(';', "\n", "\r"), array('', ' ', ' '), $$var);
			}
			$comparatif_csv .= "\n" . <<<CSV
{$data['id']};{$nom};{$data['ref']};{$types[$data['id_types_produits']]};{$statut};{$gammes[$data['id_gammes']]};{$offres[$data['offre']]};{$recyclages[$data['id_recyclage']]};{$url_key};{$description_courte};{$description};{$entretien};{$mode_emploi};{$avantages_produit};{$attributs};{$declinaisons};{$accessoires};{$complementaires};{$similaires};{$meta_title};{$meta_keywords};{$meta_description}
CSV;
		}
		$comparatif .= "</table>";
		break;
	
		case 'attributs' :
		case 'attributs-csv' :
		$comparatif .= <<<HTML
<table>
<tr>
<th>ID</th>
<th>Nom</th>
HTML;
		$comparatif_csv .= "ID;Nom";
		$attributs_application = $application->attributs();
		foreach  ($attributs_application as $attribut_id) {
			$attribut->load($attribut_id);
			list($label) = $phrase->get(array($attribut->values['phrase_nom']));
			$comparatif .= <<<HTML
<th>{$label[$config->get('langue')]}</th>
HTML;
			$comparatif_csv .= ";{$label[$config->get('langue')]}";
		}
		$comparatif .= "</tr>";
		foreach ($produits as $id_produits) {
			$produit->load($id_produits);
			$phrases = $phrase->get($produit->phrases());
			$data = $produit->values;
			$nom = isset($phrases['phrase_nom']) ? $phrases['phrase_nom'][$config->get("langue")] : "";
			$attributs_produit = $produit->attributs();
			$attributs = "";
			$comparatif_csv .= "\n{$data['id']};{$nom}";
			foreach  ($attributs_application as $attribut_id) {
				$attribut->load($attribut_id);
				switch ($attribut->type_attribut) {
					case 'choice' :
						switch ($attributs_produit[$attribut_id]) {
							case 0 : $value = "N/A"; break;
							case 1 : $value = "Oui"; break;
							case 2 : $value = "Non"; break;
						}
						break;
					case 'mark' :
						$value = $attributs_produit[$attribut_id] ? $attributs_produit[$attribut_id] : "N/A";
						break;
					case 'text' :
					case 'textarea' :
						$value = isset($phrases['valeurs_attributs'][$attribut_id]) ? $phrases['valeurs_attributs'][$attribut_id][$config->get('langue')] : "";
						break;
					case 'select' :
					case 'multiselect' :
						$options = array();
						$phrase_ids = array();
						foreach ($attribut->options() as $option) {
							$phrase_ids[$option['id']] = $option['phrase_option'];
						}
						$phrases_options = $phrase->get($phrase_ids);
						foreach ($attribut->options() as $option) {
							$options[$option['phrase_option']] = $phrases_options[$option['id']][$config->get('langue')];
						}
						$value = $options[$attributs_produit[$attribut_id]];
						break;
					case 'number' :
					default :
						$value = $attributs_produit[$attribut_id];
						break;
				}
				$attributs .= "<td>$value</td>";
				$comparatif_csv .= ";{$value}";
			}
			$comparatif .= <<<HTML
<tr>
<td>{$data['id']}</td>
<td>{$nom}</td>
$attributs
</tr>
HTML;
		}
		$comparatif .= "</table>";
		break;
	}

	if (substr($form->action(), -3) == "csv") {
		$file = str_replace("-", ".", $form->action());
		header("Content-Type: text/csv");
		header("Content-disposition: filename=$file");
		echo $comparatif_csv;
		exit;
	}
}

$titre_page = "Comparatif";

$form_start = $form->form_start();

$buttons[] = $form->input(array('type' => "submit", 'name' => "fiche", 'value' => $dico->t('Fiche') ));
$buttons[] = $form->input(array('type' => "submit", 'name' => "fiche-csv", 'value' => $dico->t('Fiche')." CSV" ));
$buttons[] = $form->input(array('type' => "submit", 'name' => "attributs", 'value' => $dico->t('Attributs') ));
$buttons[] = $form->input(array('type' => "submit", 'name' => "attributs-csv", 'value' => $dico->t('Attributs')." CSV" ));

$main = <<<HTML
{$form->select(array('name' => "application", 'label' => $dico->t('Application'), 'options' => $application->select()))}
{$form->input(array('name' => "produits", 'label' => 'ID produits'))}
{$form->select(array('name' => "actif", 'label' => '', 'options' => array("Tous", "Actifs", "Inactifs")))}
HTML;
$main .= $comparatif;

$form_end = $form->form_end();

