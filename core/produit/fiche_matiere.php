<?php

require_once "abstract_object.php";

class FicheMatiere extends AbstractObject {

	protected $type = "fiche";
	protected $table = "dt_fiches_matieres_modeles";
	protected $phrase_fields = array('phrase_nom');
	protected $phrases;
	protected $phrase;
	protected $langue;
	protected $id_langue;

	public function __construct ($sql, $phrase = null, $langue = null, $id_langue = null, $matiere = null, $attribut = null, $phrases = null) {
		parent::__construct($sql, $phrase, $langue);
		$this->matiere = $matiere;
		$this->attribut = $attribut;
		$this->phrases = $phrases;
		$this->id_langue = $id_langue;
	}

	public function liste($lang, &$filter = null) {
		$q = <<<SQL
SELECT f.id, p.phrase FROM {$this->table} AS f
LEFT OUTER JOIN dt_phrases AS p ON p.id = f.phrase_nom
LEFT OUTER JOIN dt_langues AS l ON l.id = p.id_langues
WHERE (l.code_langue = '$lang' OR f.phrase_nom = 0)
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

	public function modeles($lang) {
		$modeles = array();
		foreach ($this->liste($lang) as $element) {
			$modeles[$element['id']] = $element['phrase'];
		}

		return $modeles;
	}

	public function html($fiche_id) {
		$fiche_id = (int)$fiche_id;
		$q = <<<SQL
SELECT html, css FROM {$this->table} WHERE id = $fiche_id
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
		foreach ($this->matiere->images() as $img) {
			$images[] = "http://".$_SERVER['HTTP_HOST'].$config->media("produits/".$img['ref']);
		}

		$liste_attributs = $this->matiere->fiche_attributs($this->attribut, $this->langue);
		$choix = array("", $dico->t("Oui"), $dico->t("Non"));
		$attributs = array();
		foreach ($liste_attributs as $attribut) {
			$valeur = $attribut['valeur'];
			if ($attribut['type'] == "choice") {
				$valeur = $choix[$attribut['valeur']];
			}
			if ($valeur and $valeur != "...") {
				$attribut_str = "{$attribut['nom']} : {$valeur} {$attribut['unite']}";
				$attributs[] = $attribut_str;
			}
		}
		$applications = array();
		foreach ($this->matiere->applications() as $app) {
			if ($app['checked']) {
				$applications[] = $app['name'];
			}
		}
		return array(
			'images' => $images,
			'attributs' => $attributs,
			'applications' => $applications,
			'ecolabel' => $this->matiere->ecolabel($this->id_langue),
			'recyclage' => $this->matiere->recyclage($this->id_langue),
			'famille' => $this->matiere->famille_matiere($this->id_langue),
			'dico' => $dico,
		);
	}

	private function html_replace($matches) {
		$p = $this->matiere->values;
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
		$p = $this->matiere->values;
		$ph = $this->phrases;
		$output .= "<nom>{$ph['phrase_nom'][$lang]}</nom>";
		$output .= "<ref>{$p['ref']}</ref>";
		$output .= "<description_courte>{$ph['phrase_description_courte'][$lang]}</description_courte>";
		$output .= "<description>{$ph['phrase_description'][$lang]}</description>";

		$output .= "<images>";
		foreach ($this->matiere->images() as $image) {
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

		$attributs = $this->matiere->fiche_perso_attributs($this->attribut, $this->langue);
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

		return $output;
	}
}
