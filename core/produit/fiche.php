<?php

require_once "abstract_object.php";

class Fiche extends AbstractObject {

	public $type = "fiche";
	public $table = "dt_fiches_modeles";
	public $phrase_fields = array('phrase_nom');
	public $produit;
	public $sku;
	public $phrases;
	public $phrase;
	public $langue;

	public function __construct ($sql, $phrase = null, $langue = null, $produit = null, $sku = null, $attribut = null, $phrases = null) {
		parent::__construct($sql, $phrase, $langue);
		$this->produit = $produit;
		$this->sku = $sku;
		$this->attribut = $attribut;
		$this->phrases = $phrases;
	}

	public function liste($id_langues, &$filter = null) {
		$q = <<<SQL
SELECT f.id, p.phrase FROM dt_fiches_modeles AS f
LEFT OUTER JOIN dt_phrases AS p ON p.id = f.phrase_nom AND p.id_langues = $id_langues
SQL;
		if ($filter === null) {
			$filter = $this->sql;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function modeles($id_langues) {
		$modeles = array();
		foreach ($this->liste($id_langues) as $element) {
			$modeles[$element['id']] = $element['phrase'];
		}

		return $modeles;
	}

	public function html($fiche_id) {
		$fiche_id = (int)$fiche_id;
		$q = <<<SQL
SELECT html, css FROM dt_fiches_modeles WHERE id = $fiche_id
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		$html = $row['html'];

		$lines = array();
		foreach (explode("\n", $html)  as $line) {
			$lines[] = preg_replace_callback('/^(.*)\$([a-z_]+)\[n\](.*)$/', array($this, 'html_replace_line'), $line);
		}
		foreach ($lines as $i => $line) {
			if (preg_match('/\[IF *\$?([a-z_]+)(\[([\d\w]+)\])?\] */', $line, $matches)) {
				if (!$this->html_replace($matches)) {
					unset($lines[$i]);
				}
				else {
					$lines[$i] = str_replace($matches[0], "", $line);
				}
			}
		}
		$html = implode("\n", $lines);

		$html = preg_replace_callback('/\$([a-z_]+)(\[([\d\w]+)\])?/', array($this, 'html_replace'), $html);
		
		return array($html, $row['css']);
	}

	private function html_vars() {
		global $config;
		global $dico;
		$images = array();
		foreach ($this->produit->images() as $img) {
			$images[] = $config->core_media("produits/".$img['ref']);
		}

		$liste_attributs = $this->produit->fiche_perso_attributs($this->attribut, $this->langue);
		$choix = array("", $dico->t("Oui"), $dico->t("Non"));
		$attributs = array();
		$attributs_pictos = array();
		$all_attributs = array();
		foreach ($liste_attributs as $attribut) {
			$valeur = $attribut['valeur'];
			if ($attribut['type'] == "choice") {
				$valeur = $choix[$attribut['valeur']];
			}
			if ($valeur and $valeur != "...") {
				if (is_array($valeur)) {
					$valeurs_unites = array();
					foreach ($valeur as $v) {
						$valeurs_unites[] = trim("{$v} {$attribut['unite']}");
					}
					$attribut_str = "{$attribut['nom']} : ".implode(", ", $valeurs_unites);
				}
				else {
					$attribut_str = "{$attribut['nom']} : {$valeur} {$attribut['unite']}";
				}
				if ($attribut['fiche_technique']) {
					$attributs[] = $attribut_str;
				}
				if ($attribut['pictos_vente']) {
					$attributs_pictos[] = $attribut_str;
				}
				$all_attributs[] = $attribut_str;
			}
		}

		foreach (array('variantes', 'accessoires', 'composants') as $method) {
			$skus = $this->produit->$method();
			$$method = "";
			if (count($skus)) {
				$$method .= "<table class='$method'>";
				$$method .= "<tr><th>Nom</th><th>Ref</th><th>Prix HT</th></tr>";
				foreach ($skus as $id => $item) {
					$this->sku->load($id);
					$phrases = $this->phrase->get($this->sku->phrases());
					$nom = isset($phrases['phrase_ultralog'][$this->langue]) ? $phrases['phrase_ultralog'][$this->langue] : "";
					$$method .= "<tr>";
					$$method .= "<td>{$nom}</td>";
					$$method .= "<td>{$this->sku->values['ref_ultralog']}</td>";
					$prix = $this->sku->prix();
					$prix_degressifs = $this->sku->prix_degressifs();
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
					$$method .= "<td>$prix</td>";
					$$method .= "</tr>";
				}
				$$method .= "</table>";
			}
		}

		return array(
			'images' => $images,
			'attributs' => $attributs,
			'attributs_pictos' => $attributs_pictos,
			'all_attributs' => $all_attributs,
			'variantes' => $variantes,
			'accessoires' => $accessoires,
			'composants' => $composants,
			'dico' => $dico,
		);
	}

	private function html_replace($matches) {
		$p = $this->produit->values;
		$ph = $this->phrases;
		$lg = $this->langue;
		$index = isset($matches[3]) ? $matches[3] : null;
		if (isset($p[$matches[1]])) {
			if (is_array($p[$matches[1]]) and isset($p[$matches[1]][$index])) {
				return $p[$matches[1]][$index];
			}
			else {
				return $p[$matches[1]];
			}
		}
		if (isset($p["phrase_".$matches[1]])) {
			if (isset($ph["phrase_".$matches[1]][$lg])) {
				return $ph["phrase_".$matches[1]][$lg];
			}
			else {
				return "";
			}
		}

		$vars = $this->html_vars();

		if (isset($vars[$matches[1]])) {
			$var = $vars[$matches[1]];
			if (method_exists($var, 't')) {
				return $var->t($index);
			}
			else if (is_array($var)) {
				if ($index === null and count($var)) {
					return "&lt;array&gt;";
				}
				else if (isset($var[$index])) {
					return $var[$index];
				}
				else {
					return "";
				}
			}
			else {
				return $var;
			}
		}

		return "&lt;undefined&gt;";
	}

	private function html_replace_line($matches) {
		$vars = $this->html_vars();
		$lines = array();
		if (is_array($vars[$matches[2]])) {
			foreach ($vars[$matches[2]] as $element) {
				$lines[] = $matches[1].$element.$matches[3];
			}
			return implode("\n", $lines);
		}
		return "";
	}

	public function xml() {
		$output = "";

		$lang = $this->langue;
		$p = $this->produit->values;
		$ph = $this->phrases;
		$short_description = strip_tags($ph['phrase_description_courte'][$lang]);
		$long_description = strip_tags($ph['phrase_description'][$lang]);
		$output .= "<nom>{$ph['phrase_nom'][$lang]}</nom>";
		$output .= "<ref>{$p['ref']}</ref>";
		$output .= "<description_courte>$short_description</description_courte>";
		$output .= "<description>$long_description</description>";

		$output .= "<images>";
		foreach ($this->produit->images() as $image) {
			$output .= "<image>";
			$output .= "<file>";
			$output .= $image['ref'];
			$output .= "</file>";
			$output .= "<alt>";
			$output .= $ph['image'][$image['id']]['phrase_legende'][$lang];
			$output .= "</alt>";
			$output .= "</image>";
		}
		$output .= "</images>";

		$attributs = $this->produit->fiche_perso_attributs($this->attribut, $this->langue);
		$output .= "<attributs>";
		foreach ($attributs as $attribut) {
			$valeur = ($attribut['type'] == "choice") ? $choix[$attribut['valeur']] : $attribut['valeur'];
			$output .= "<attribut>";
			$output .= "<nom>{$attribut['nom']}</nom>";
			$output .= "<valeur>{$valeur}</valeur>";
			$output .= "<unite>{$attribut['unite']}</unite>";
			$output .= "</attribut>";
		}
		$output .= "</attributs>";

		foreach (array('variantes', 'accessoires', 'composants') as $method) {
			$skus = $this->produit->$method();
			$output .= "<$method>";
			foreach ($skus as $id => $item) {
				$output .= "<".rtrim($method, 's').">";
				$this->sku->load($id);
				$phrases = $this->phrase->get($this->sku->phrases());
				$output .= "<nom>{$phrases['phrase_ultralog'][$this->langue]}</nom>";
				$output .= "<ref>{$this->sku->values['ref_ultralog']}</ref>";
				$output .= "<prix>";
				$prix = $this->sku->prix();
				$prix_degressifs = $this->sku->prix_degressifs();
				$output .= "<prix_degressif quantite=\"1\">{$prix['montant_ht']}</prix_degressif>";
				foreach ($prix_degressifs as $p) {
					$output .= "<prix_degressif quantite=\"{$p['quantite']}\">{$prix['montant_ht']}</prix_degressif>";
				}
				$output .= "</prix>";
				$output .= "</".rtrim($method, 's').">";
			}
			$output .= "</$method>";
		}
		
		return $output;
	}
}
