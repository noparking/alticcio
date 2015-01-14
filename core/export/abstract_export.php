<?php

abstract class AbstractExport {

	public $sql;
	public $sql_export;
	public $excluded = array();

	public function __construct($sql, $sql_export) {
		$this->sql = $sql;
		$this->sql_export = $sql_export;
	}

	function insert_values($fields, $values) {
		$fields_list = array();
		foreach ($fields as $i => $field) {
			if (!in_array($i, $this->excluded)) {
				$fields_list[] = $field;
			}
		}
		$fields_list = implode(",", $fields_list);

		$values_list = array();
		foreach ($values as $value) {
			$value_list = array();
			foreach ($value as $i => $data) {
				if (!in_array($i, $this->excluded)) {
					$value_list[] = addslashes($data);
				}
			}
			$values_list[] = "('".implode("','", $value_list)."')";
		}
		$values_list = implode(",", $values_list);
		if ($values_list) {
			$q = <<<SQL
INSERT INTO {$this->export_table} ($fields_list) VALUES $values_list
SQL;
			$this->sql_export->query($q);
		}
	}

	function langues() {
		$q = <<<SQL
SELECT id, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);
		$langues = array();
		while ($row = $this->sql->fetch($res)) {
			$langues[$row['id']] = $row['code_langue'];
		}

		return $langues;
	}

	function attributs($attributs_data, $phrases, $code_langue, $type = null, $nb = null) {
		$separator = "\n";
		$attributs = array();
		foreach ($attributs_data as $attr) {
			if (isset($attr[0])) {
				$attr = $attr[0]; // On ne gère pas les valeurs multiples
			}
			if ($type === null or $attr[$type]) {
				if ($attr['phrase_valeur']) {
					if (is_array($attr['phrase_valeur'])) {
						$valeurs_unites = array();
						foreach ($phrases['valeurs_attributs'][$attr['id_attributs']][0] as $v) {
							if (!isset($v[$code_langue])) {
								$v[$code_langue] = "";
							}
							$valeurs_unites[] = trim("{$v[$code_langue]} {$attr['unite']}");
						}
						$valeur = implode(", ", $valeurs_unites);
					}
					else {
						if (isset($phrases['valeurs_attributs'][$attr['id_attributs']][0][$code_langue])) {
							$valeur = $phrases['valeurs_attributs'][$attr['id_attributs']][0][$code_langue];
						}
						else {
							$valeur = "";
						}
						$valeur .= " {$attr['unite']}";
					}
				}
				else {
					$choices = array(
						0 => "N/A",		
						1 => "Oui",
						2 => "Non",
					);
					if (is_array($attr['valeur_numerique'])) {
						$valeurs_unites = array();
						foreach ($attr['valeur_numerique'] as $v) {
							switch ($attr['id_types_attributs']) {
								case 1:
									$v = $choices[$v];
									break;
								case 2:
									$v .= "/5";
									break;
							}
							$valeurs_unites[] = trim("{$v} {$attr['unite']}");
						}
						$valeur = implode(", ", $valeurs_unites);
					}
					else {
						$valeur = $attr['valeur_numerique'];
						switch ($attr['id_types_attributs']) {
							case 1:
								$valeur = $choices[$valeur];
								break;
							case 2:
								$valeur .= "/5";
								break;
						}
						$valeur .= " {$attr['unite']}";
					}
				}
				if (isset($phrases['attributs'][$attr['id_attributs']][$code_langue])) {
					$attributs[] = $phrases['attributs'][$attr['id_attributs']][$code_langue]." :\t".trim($valeur);
				}
				else {
					$attributs[] = " :\t".trim($valeur);
				}
			}
		}
		return implode($separator, $nb === null ? $attributs : array_slice($attributs, 0, $nb));
	}

	function images($object, $max_images) {
		$images = array();
		foreach ($object->images() as $img) {
			if ($img['affichage']) {
				$images[] = $img['ref'];
			}
		}
		$ret = array();
		for ($i = 0; $i < $max_images; $i++) {
			$ret[] = isset($images[$i]) ? $images[$i] : "";
		}

		return $ret;
	}

	function phrase($field, $phrases, $code_langue) {
		return isset($phrases[$field][$code_langue]) ? strip_tags($phrases[$field][$code_langue]) : "";
	}
}
