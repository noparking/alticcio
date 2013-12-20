<?php

class API_Produit {

	private $sql;
	private $language;
	private $id_langues;
	private $phrases;

	function __construct($api) {
		$this->sql = $api->sql;
		$this->language = $api->info('language');
		$q = "SELECT id FROM dt_langues WHERE code_langue = '{$this->language}'";
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);
		$this->id_langues = $row['id'];
	}

	// Informations globales
	public function infos($id_produits) {
		$lang = $this->language;
		$phrase = new Phrase($this->sql);
		$produit = new Produit($this->sql, $phrase, $lang);

		$produit->load($id_produits);
		$this->phrases = $produit->phrases_dynamiques();

		$infos = array(
			'id' => $id_produits,
			'thumbnail' => $produit->vignette(),
			'name' => $this->get_phrase('nom'),
			'short_description' => $this->get_phrase('description_courte'),
			'benefits' => $this->get_phrase('avantages_produit'),
			'price' => $produit->prix_mini(),
		);

		return $infos;
	}

	// Informations détaillées
	public function fiche($id_produits) {
		$lang = $this->language;
		$phrase = new Phrase($this->sql);
		$produit = new Produit($this->sql, $phrase, $lang);

		if (!$produit->load($id_produits)) {
			return false;
		}
		$this->phrases = $produit->phrases_dynamiques();

		$variantes = array_keys($produit->variantes());
		$accessoires = array_keys($produit->accessoires());
		$composants = array_keys($produit->composants());
		$complementaires = array_keys($produit->complementaires());
		$similaires = array_keys($produit->similaires());

		$infos_skus = array();
		$infos_produits = array();

		$skus = implode(",", array_merge($variantes, $accessoires, $composants));
		if ($skus) {
			$q = <<<SQL
SELECT s.id, s.ref_ultralog, p.phrase, px.montant_ht FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p ON s.phrase_ultralog = p.id AND p.id_langues = {$this->id_langues}
LEFT OUTER JOIN dt_prix AS px ON px.id_sku = s.id
WHERE s.id IN ($skus) AND s.actif = 1
SQL;
			$res = mysql_query($q);
			while ($row = mysql_fetch_assoc($res)) {
				$infos_skus[$row['id']] = array(
					'id' => $row['id'],
					'ref' => $row['ref_ultralog'],
					'name' => $row['phrase'],
					'price' => $row['montant_ht'],
				);
			}
		}

		$produits = implode(",", array_merge($complementaires, $similaires));
		if ($produits) {
			$images_produits = array();
			$q = <<<SQL
SELECT id_produits, ref, vignette FROM dt_images_produits
WHERE id_produits IN ($produits)
ORDER BY classement ASC
SQL;
			$res = mysql_query($q);
			while ($row = mysql_fetch_assoc($res)) {
				$images_produits[$row['id_produits']]['images'][] = $row['ref'];
				if ($row['vignette']) {
					$images_produits[$row['id_produits']]['thumbnail'] = $row['ref'];
				}
			}

			$q = <<<SQL
SELECT pr.id, pr.ref, ph.phrase, pr.id_types_produits FROM dt_produits AS pr
LEFT OUTER JOIN dt_phrases AS ph ON pr.phrase_nom = ph.id AND ph.id_langues = {$this->id_langues}
WHERE pr.id IN ($produits) AND pr.actif = 1
SQL;
			$res = mysql_query($q);
			while ($row = mysql_fetch_assoc($res)) {
				$infos_produits[$row['id']] = array(
					'id' => $row['id'],
					'ref' => $row['ref'],
					'name' => $row['phrase'],
					'thumbnail' => isset($images_produits[$row['id']]['thumbnail']) ? $images_produits[$row['id']]['thumbnail'] : "",
					'images' => isset($images_produits[$row['id']]['images']) ? $images_produits[$row['id']]['images'] : array(),
				);
			}
		}

		$images = array();
		foreach ($produit->images() as $image) {
			if ($image['affichage']) {
				$images[] = $image['ref'];
			}
		}

		$attributs = array();
		foreach ($produit->attributs_data() as $attribut_data) {
			$attribut = $attribut_data[0]; // On ne gère pas les valeurs multiples
			$id_attributs = $attribut['id_attributs'];
			$name = isset($this->phrases['attributs'][$id_attributs][$this->language]) ? $this->phrases['attributs'][$id_attributs][$this->language] : "";
			switch ($attribut['type_valeur']) {
				case "phrase_valeur":
					if (is_array($attribut['phrase_valeur'])) {
						$value = array();
						foreach ($this->phrases['valeurs_attributs'][$id_attributs][0] as $v) {
							$value[] = $v[$this->language];
						}
					}
					else {
						$value = isset($this->phrases['valeurs_attributs'][$id_attributs][0][$this->language]) ? $this->phrases['valeurs_attributs'][$id_attributs][0][$this->language] : "";
					}
					break;
				default:
					$value = $attribut[$attribut['type_valeur']];
			}
			$attributs[] = array(
				'name' => $name,
				'value' => $value,
				'unit' => $attribut['unite'],
				'type' => $attribut['id_types_attributs'],
				'flags' => array(
					'fiche_technique' => $attribut['fiche_technique'],
					'pictos_vente' => $attribut['pictos_vente'],
					'top' => $attribut['top'],
					'comparatif' => $attribut['comparatif'],
					'filtre' => $attribut['filtre'],
				),
			);
		}

		$fiche = array(
			'id' => $id_produits,
			'name' => $this->get_phrase('nom'),
			'thumbnail' => $produit->vignette(),
			'images' => $images,
			'attributs' => $attributs,
			'description' => $this->get_phrase('description'),
			'variants' => $this->get_infos($variantes, $infos_skus),
			'accessories' => $this->get_infos($accessoires, $infos_skus),
			'components' => $this->get_infos($composants, $infos_skus),
			'complementary' => $this->get_infos($complementaires, $infos_produits),
			'similar' => $this->get_infos($similaires, $infos_produits),
			'filtered_variants' => $produit->attributs_filtre($this->id_langues),
			'customizable' => ($produit->values['id_types_produits'] == 2),
			'customization' => $produit->personnalisation(),
		);

		return $fiche;
	}

	// infos sur un sku (une variante)
	function sku($id_sku) {
		$q = <<<SQL
SELECT s.id, s.ref_ultralog, p.phrase, px.montant_ht FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p ON s.phrase_ultralog = p.id AND p.id_langues = {$this->id_langues}
LEFT OUTER JOIN dt_prix AS px ON px.id_sku = s.id
WHERE s.id = $id_sku
SQL;
		$res = mysql_query($q);
		if ($row = mysql_fetch_assoc($res)) {
			return array(
				'id' => $row['id'],
				'ref' => $row['ref_ultralog'],
				'name' => $row['phrase'],
				'price' => $row['montant_ht'],
			);
		}
		else {
			return false;
		}
	}

	public function get_phrase ($key) {
		return isset($this->phrases['phrase_'.$key][$this->language]) ? $this->phrases['phrase_'.$key][$this->language] : "";
	}

	public function get_infos($ids, $correspondances) {
		$infos = array();
		foreach ($ids as $id) {
			if (isset($correspondances[$id])) {
				$infos[] = $correspondances[$id];
			}
		}

		return $infos;
	}

	public function texte_perso($id_produit) {
		$q = <<<SQL
SELECT libelle FROM dt_personnalisations_produits
WHERE `type` = 'texte' AND id_produits = $id_produit
SQL;
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);

		return $row['libelle'];
	}
}
