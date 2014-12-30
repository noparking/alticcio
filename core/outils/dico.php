<?php

class Dico {
	
	//public $template_untranslated = '<span class="untranslated-term">#{term}</span>';
	//public $template_default = '<span class="default-term">#{term}</span>';
	//public $template_normal = '#{term}';
	public $template_untranslated = '#{term}';
	public $template_default = '#{term}';
	public $template_normal = '#{term}';
	
	private $language;
	private $default_languages;
	
	private $terms = array();
	private $default_terms = array();
	private $reverted_terms = array();
	
	private $data = array();
	private $default_data = array();
	
	public function __construct($language, $default_languages = array("fr_FR")) {
		$this->language = $language;
		if (!is_array($default_languages)) {
			$default_languages = array($default_languages);
		}
		$this->default_languages = $default_languages;
	}
	
	public function add($dir) {	
		foreach (array_reverse($this->default_languages) as $default_language) {
			$file = "$dir/{$default_language}.php";
			if (file_exists($file)) {
				unset($t, $d);
				require $file;
				if (isset($t)) {
					$this->add_default_terms($t);
					$this->add_reverted_terms($t);
				}
				if (isset($d)) {
					$this->add_default_data($d);
				}
				unset($t, $d);
			}
		}
		
		$file = "$dir/{$this->language}.php";
		if (file_exists($file)) {
			unset($t, $d);
			require $file;
			if (isset($t)) {
				$this->add_terms($t);
			}
			if (isset($d)) {
				$this->add_data($d);
			}
			unset($t, $d);
		}
	}
	
	public function addfile($file) {
		unset($t, $d);
		require $file;
		if (isset($t)) {
			$this->add_terms($t);
		}
		if (isset($d)) {
			$this->add_data($d);
		}
		unset($t, $d);
	}
	
	public function d($key) {
		if (isset($this->data[$key])) {
			return $this->data[$key];
		}
		if (isset($this->default_data[$key])) {
			return $this->default_data[$key];
		}
		return null;
	}
	
	public function t($key, $replacements = array()) {
		
		if (isset($this->terms[$key])) {
			$term = $this->get_term($this->terms[$key], $this->template_normal);
		}
		else if (isset($this->reverted_terms[$key]) and isset($this->terms[$this->reverted_terms[$key]])) {
			$term = $this->get_term($this->terms[$this->reverted_terms[$key]], $this->template_normal);
		}
		else if (isset($this->default_terms[$key])) {
			$term = $this->get_term($this->default_terms[$key], $this->template_default);
		}
		else if (isset($this->reverted_terms[$key]) and isset($this->default_terms[$this->reverted_terms[$key]])) {
			$term = $this->get_term($this->default_terms[$this->reverted_terms[$key]], $this->template_default);
		}
		else {
			$term = $this->get_term($key, $this->template_untranslated);
		}
		foreach ($replacements as $search => $replace) {
			$term = str_replace($search, $replace, $term);
		}
		return $term;
	}

	public function translate($text) {
		return preg_replace_callback("/\{dico:([^\}]+)\}/", array($this, "translate_callback"), $text);
	}

	private function translate_callback($matches) {
		return $this->t($matches[1]);
	}

	public function prix($prix, $suffix = "", $alt = null) {
		if ($prix == 0 and $alt !== null) {
			return $alt;
		}
		$data = $this->d('prix');
		$montant = number_format($prix * $data['facteur'], $data['decimals'], $data['dec_point'], $data['thousands_sep']);
		return str_replace(" ", "&nbsp;", str_replace("{devise}", $data['devise'], str_replace("{montant}", $montant, $data['format']))).$suffix;
	}
	
	public function export($keys, $func = 't') {
		$export = array();
		foreach ($keys as $key) {
			$export[$key] = $this->$func($key);
		}
		return $export;
	}
	
	private function get_term($term, $template) {
		return str_replace("#{term}", $term, $template);
	}
	
	private function add_terms($terms) {
		if (is_array($terms)) {
			$this->terms = $this->merge_values($this->terms, $terms);
		}
	}
	
	private function add_reverted_terms($terms) {
		if (is_array($terms)) {
			$this->reverted_terms = $this->merge_values($this->reverted_terms, array_flip($terms));
		}
	}
	
	private function add_default_terms($terms) {
		if (is_array($terms)) {
			$this->default_terms = $this->merge_values($this->default_terms, $terms);
		}
	}
	
	private function add_data($data) {
		if (is_array($data)) {
			$this->data = $this->merge_values($this->data, $data);
		}
	}
	
	private function add_default_data($data) {
		if (is_array($data)) {
			$this->default_data = $this->merge_values($this->default_data, $data);
		}
	}

	private function merge_values($values, $new_values) {
		foreach ($new_values as $key => $value) {
			if (isset($values[$key])) {
				if (is_array($value) and is_array($values[$key])) {
					$values[$key] = $this->merge_values($values[$key], $value);
				}
				else {
					$values[$key] = $value;
				}
			}
			else {
				$values[$key] = $value;
			}
		}
		return $values;
	}
}

?>
