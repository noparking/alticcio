<?php

$menu->current('main/products/compare');

$config->core_include("produit/application", "produit/produit", "produit/attribut", "produit/sku");
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");

$sql = new Mysql($config->db());

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));
$code_langue = $config->get("langue");

$phrase = new Phrase($sql);

$application = new Application($sql, $phrase, $id_langues);

$produit = new Produit($sql, $phrase, $id_langues);

$sku = new Sku($sql, $phrase, $id_langues);

$attribut = new Attribut($sql, $phrase, $id_langues);

$form = new Form(array(
	'id' => "form-compare-products",
));

$comparatif = "";
$comparatif_csv = array();

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
		$comparatif_csv[] = array(
			"ID",
			"Nom",
			"Référence",
			"Type",
			"Statut",
			"Gamme",
			"Offre",
			"Filière de recyclage",
			"Intitulé de l'url",
			"Description courte",
			"Description",
			"Conseil d'entretien",
			"Mode d'emploi",
			"Les + produit",
			"Attributs",
			"Déclinaisons",
			"Accessoires",
			"Produits complémentaires",
			"Produits similaires",
			"Meta title",
			"Meta keywords",
			"Meta description",
		);
		$types = $produit->types();
		$gammes = $produit->gammes();
		$offres = array(
			0 => "...",
			1 => $dico->t('GammeEssentiel'),
			2 => $dico->t('GammePro'),
			3 => $dico->t('GammeExpert'),
		);
		$recyclages = $produit->recyclage($id_langues);
		foreach ($produits as $id_produits) {
			$produit->load($id_produits);
			$phrases = $phrase->get($produit->phrases());
			$data = $produit->values;
			$statut = $data['actif'] ? "actif" : "inactif";
			$nom_produit = isset($phrases['phrase_nom']) ? $phrases['phrase_nom'][$config->get("langue")] : "";
			$url_key = isset($phrases['phrase_url_key']) ? $phrases['phrase_url_key'][$config->get("langue")] : "";
			$description_courte = isset($phrases['phrase_description_courte']) ? $phrases['phrase_description_courte'][$config->get("langue")] : "";
			$description = isset($phrases['phrase_description']) ? $phrases['phrase_description'][$config->get("langue")] : "";
			$entretien = isset($phrases['phrase_entretien']) ? $phrases['phrase_entretien'][$config->get("langue")] : "";
			$mode_emploi = isset($phrases['phrase_mode_emploi']) ? $phrases['phrase_mode_emploi'][$config->get("langue")] : "";
			$avantages_produit = isset($phrases['phrase_avantages_produit']) ? $phrases['phrase_avantages_produit'][$config->get("langue")] : "";
			$meta_title = isset($phrases['phrase_meta_title']) ? $phrases['phrase_meta_title'][$config->get("langue")] : "";
			$meta_keywords = isset($phrases['phrase_meta_keywords']) ? $phrases['phrase_meta_keywords'][$config->get("langue")] : "";
			$meta_description = isset($phrases['phrase_meta_description']) ? $phrases['phrase_meta_description'][$config->get("langue")] : "";
			$attributs_data = $produit->attributs_data();
			$attributs_application = $application->attributs();
			$attributs = "<ul>";
			foreach  ($attributs_application as $attribut_id) {
				if (isset($attributs_data[$attribut_id])) {
					$attribut_data = $attributs_data[$attribut_id][0]; // On ne gère pas les valeurs multiples
					$nom_attribut = $phrases['attributs'][$attribut_data['id_attributs']][$code_langue];
					$unite = $attribut_data['unite'] ? $attribut_data['unite'] : "";
					if ($attribut_data['phrase_valeur']) {
						if (is_array($attribut_data['phrase_valeur'])) {
							$valeurs_unites = array();
							foreach ($phrases['valeurs_attributs'][$attribut_data['id_attributs']][0] as $v) {
								$valeurs_unites[] = trim("{$v[$code_langue]} {$unite}");
							}
							$valeur = implode(", ", $valeurs_unites);
						}
						else {
							$valeur = $phrases['valeurs_attributs'][$attribut_data['id_attributs']][0][$code_langue];
							$valeur .= " $unite";
						}
					}
					else {
						if (is_array($attribut_data['valeur_numerique'])) {
							$valeurs_unites = array();
							foreach ($attribut_data['valeur_numerique'] as $v) {
								$v = format_valeur_numerique($v, $attribut_data['id_types_attributs']);
								$valeurs_unites[] = trim("{$v} {$unite}");
							}
							$valeur = implode(", ", $valeurs_unites);
						}
						else {
							$valeur = format_valeur_numerique($attribut_data['valeur_numerique'], $attribut_data['id_types_attributs']);
							$valeur .= " $unite";
						}
					}
					$value = trim($valeur);
					$attributs .= "<li>$nom_attribut : $value</li>";
				}
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
<td>{$page->l($nom_produit, $url2->make("produits", array('type' => "produits", 'action' => 'edit', 'id' => $data['id'])))}</td>
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
			$comparatif_csv[] = array(
				$data['id'],
				$nom_produit,
				$data['ref'],
				$types[$data['id_types_produits']],
				$statut,
				$gammes[$data['id_gammes']],
				$offres[$data['offre']],
				$recyclages[$data['id_recyclage']],
				$url_key,
				$description_courte,
				$description,
				$entretien,
				$mode_emploi,
				$avantages_produit,
				$attributs,
				$declinaisons,
				$accessoires,
				$complementaires,
				$similaires,
				$meta_title,
				$meta_keywords,
				$meta_description,
			);
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
		$comparatif_csv_line = array("ID", "Nom");
		$attributs_application = $application->attributs();
		foreach  ($attributs_application as $attribut_id) {
			$attribut->load($attribut_id);
			list($label) = $phrase->get(array($attribut->values['phrase_nom']));
			$comparatif .= <<<HTML
<th>{$label[$config->get('langue')]}</th>
HTML;
			$comparatif_csv_line[] = $label[$config->get('langue')];
		}
		$comparatif_csv[] = $comparatif_csv_line;
		$comparatif .= "</tr>";
		foreach ($produits as $id_produits) {
			$produit->load($id_produits);
			$phrases = $phrase->get($produit->phrases());
			$data = $produit->values;
			$nom_produit = isset($phrases['phrase_nom']) ? $phrases['phrase_nom'][$config->get("langue")] : "";
			$attributs_data = $produit->attributs_data();
			$attributs = "";
			$comparatif_csv_line = array($data['id'], $nom_produit);
			foreach  ($attributs_application as $attribut_id) {
				if (isset($attributs_data[$attribut_id])) {
					$attribut_data = $attributs_data[$attribut_id][0]; // On ne gère pas les valeurs multiples
					$nom_attribut = $phrases['attributs'][$attribut_data['id_attributs']][$code_langue];
					$unite = $attribut_data['unite'] ? $attribut_data['unite'] : "";
					if ($attribut_data['phrase_valeur']) {
						if (is_array($attribut_data['phrase_valeur'])) {
							$valeurs_unites = array();
							foreach ($phrases['valeurs_attributs'][$attribut_data['id_attributs']][0] as $v) {
								$valeurs_unites[] = trim("{$v[$code_langue]} {$unite}");
							}
							$valeur = implode(", ", $valeurs_unites);
						}
						else {
							$valeur = $phrases['valeurs_attributs'][$attribut_data['id_attributs']][0][$code_langue];
							$valeur .= " $unite";
						}
					}
					else {
						if (is_array($attribut_data['valeur_numerique'])) {
							$valeurs_unites = array();
							foreach ($attribut_data['valeur_numerique'] as $v) {
								$v = format_valeur_numerique($v, $attribut_data['id_types_attributs']);
								$valeurs_unites[] = trim("{$v} {$unite}");
							}
							$valeur = implode(", ", $valeurs_unites);
						}
						else {
							$valeur = format_valeur_numerique($attribut_data['valeur_numerique'], $attribut_data['id_types_attributs']);
							$valeur .= " $unite";
						}
					}
					$value = trim($valeur);
					$attributs .= "<td>{$value}</td>";
					$comparatif_csv_line[] = $value;
				}
				else {
					$attributs .= "<td></td>";
					$comparatif_csv_line[] = "";
				}
			}
			$comparatif .= <<<HTML
<tr>
<td>{$data['id']}</td>
<td>{$nom_produit}</td>
$attributs
</tr>
HTML;
			$comparatif_csv[] = $comparatif_csv_line;
		}
		$comparatif .= "</table>";
		break;
	}

	if (substr($form->action(), -3) == "csv") {
		$file = str_replace("-", ".", $form->action());
		header("Content-Type: text/csv");
		header("Content-disposition: filename=$file");
		$output = fopen('php://output', 'w');
		foreach ($comparatif_csv as $comparatif_csv_line) {
			fputcsv($output, $comparatif_csv_line);
		}
		fclose($output);
		exit;
	}
}

$titre_page = "Comparatif";

$form_start = $form->form_start();

$buttons['fiche'] = $form->input(array('type' => "submit", 'name' => "fiche", 'value' => $dico->t('Fiche') ));
$buttons['fichecsv'] = $form->input(array('type' => "submit", 'name' => "fiche-csv", 'value' => $dico->t('Fiche')." CSV" ));
$buttons['attributs'] = $form->input(array('type' => "submit", 'name' => "attributs", 'value' => $dico->t('Attributs') ));
$buttons['attributscsv'] = $form->input(array('type' => "submit", 'name' => "attributs-csv", 'value' => $dico->t('Attributs')." CSV" ));

$main = <<<HTML
{$form->select(array('name' => "application", 'label' => $dico->t('Application'), 'options' => $application->select()))}
{$form->input(array('name' => "produits", 'label' => 'ID produits'))}
{$form->select(array('name' => "actif", 'label' => '', 'options' => array("Tous", "Actifs", "Inactifs")))}
HTML;
$main .= $comparatif;

$form_end = $form->form_end();

function format_valeur_numerique($v, $id_types_attributs) {
	global $config;

	switch ($id_types_attributs) {
		case 1 : 
			$options = array(0 => "N/A", 1 => "Oui", 2 => "Non");
			$v = $options[$v];
			break;
		case 2 :
			if ($v == 0) {
				$v = "N/A";
			}
			else {
				$stars = "";
				for ($i = 0; $i < $v; $i++) {
					$stars .= "*";
				}
				$v = $stars;
			}
			break;
	}
	return $v;
}
