<?php

require_once "abstract_object.php";

class Attribut extends AbstractObject {

	public $type = "attribut";
	public $table = "dt_attributs";
	public $phrase_fields = array('phrase_nom');

	public function load($id) {
		parent::load($id);
		$q = <<<SQL
SELECT code FROM dt_types_attributs
WHERE id = {$this->attr('id_types_attributs')}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$this->type_attribut = $row['code'];

		$q = <<<SQL
SELECT unite FROM dt_unites_mesure
WHERE id = {$this->attr('id_unites_mesure')}
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			$this->unite = $row['unite'];
		}
		else {
			$this->unite = "";
		}
	}

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT a.id, p.phrase, t.code FROM dt_attributs AS a
LEFT OUTER JOIN dt_types_attributs AS t ON t.id = a.id_types_attributs 
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
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

	public function types($code_as_key = false) {
		$q = <<<SQL
SELECT id, code FROM dt_types_attributs
SQL;
		$res = $this->sql->query($q);

		$types = array();
		while($row = $this->sql->fetch($res)) {
			$types[$code_as_key ? $row['code'] : $row['id']] = $row['code'];
		}

		return $types;
	}

	public function unites($unite_as_key = false) {
		$q = <<<SQL
SELECT id, unite FROM dt_unites_mesure
SQL;
		$res = $this->sql->query($q);

		$unites = array();
		while($row = $this->sql->fetch($res)) {
			$unites[$unite_as_key ? $row['unite'] : $row['id']] = $row['unite'];
		}

		return $unites;
	}

	public function add_option($data) {
		$q = <<<SQL
INSERT INTO dt_options_attributs (id_attributs) VALUES ({$data['attribut']['id']})
SQL;
		$this->sql->query($q);

		$id = $this->sql->insert_id();
		$data_options = array();
		$data_options['options'][$id] = $data['new_option'];
		$data_options['options'][$id]['phrase_option'] = 0;
		$data_options['phrases']['options'][$id]['phrase_option'] = $data['new_option']['phrase_option'];
		$this->save_data($data_options, "options", "dt_options_attributs");
	}

	public function delete_option($data, $id) {
		$q = <<<SQL
DELETE FROM dt_options_attributs WHERE id = {$id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_phrases WHERE id = {$data['options'][$id]['phrase_option']}
SQL;
		$this->sql->query($q);
	}

	public function options() {
		$options = array();
		$q = <<<SQL
SELECT * FROM dt_options_attributs WHERE id_attributs = {$this->id} ORDER BY classement
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$options[$row['id']] = $row;
		}

		return $options; 
	}

	public function reference() {
		$q = <<<SQL
SELECT * FROM dt_attributs_references WHERE id_attributs = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		$row = $this->sql->fetch($res);

		return $row === false ? array() : $row;
	}

	public function reference_options($id_langues = 1) {
		$options = array(0 => "...");
		$reference = $this->reference();
		$table_name = $reference['table_name'];
		$field_label = $reference['field_label'];
		$field_value = $reference['field_value'];
		if (substr($reference['field_label'], 0, 6) == "phrase"){
			$q = <<<SQL
SELECT p.phrase AS label, t.{$field_value} AS value
FROM {$table_name} AS t
INNER JOIN dt_phrases AS p ON t.{$field_label} = p.id
WHERE id_langues = $id_langues
ORDER BY label ASC
SQL;
		}
		else {
			$q = <<<SQL
SELECT {$field_label} AS label, {$field_value} AS value
FROM {$table_name}
ORDER BY label ASC
SQL;
		}
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$options[$row['value']] = $row['label'];
		}

		return $options; 
	}
	
	public function valeurs() {
		$q = <<<SQL
SELECT * FROM dt_attributs_valeurs WHERE id_attributs = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		$row = $this->sql->fetch($res);

		return $row === false ? array('phrase_valeur' => 0, 'valeur_numerique' => 0) : $row;
	}

	public function phrases() {
		$ids = parent::phrases();
		$options = $this->options();
		foreach ($options as $option) {
			$ids['options'][$option['id']]['phrase_option'] = $option['phrase_option'];
		}
		$valeurs = $this->valeurs();
		$ids['valeurs']['phrase_valeur'] = $valeurs['phrase_valeur'];

		return $ids;
	}

	public function save($data) {
		$id = parent::save($data);
		if (isset($data['attribut']['id'])) {
			$this->save_data($data, "options", "dt_options_attributs");
		}
		if (isset($data['reference'])) {
			$q = "DELETE FROM dt_attributs_references WHERE id_attributs = $id";
			$this->sql->query($q);
			$q = "INSERT INTO dt_attributs_references (id_attributs, table_name, field_label, field_value) VALUES ($id, '{$data['reference']['table_name']}', '{$data['reference']['field_label']}', '{$data['reference']['field_value']}')";
			$this->sql->query($q);
		}
		if (isset($data['valeurs'])) {
			$valeurs = $data['valeurs'];
			foreach ($data['phrases']['valeurs']['phrase_valeur'] as $lang => $phrase) {
				$id_phrase = $this->phrase->save($lang, $phrase, $valeurs['phrase_valeur']);
			}
			$q = "DELETE FROM dt_attributs_valeurs WHERE id_attributs = $id";
			$this->sql->query($q);
			$q = "INSERT INTO dt_attributs_valeurs (id_attributs, type_valeur, valeur_numerique, phrase_valeur) VALUES ($id, '{$valeurs['type_valeur']}', {$valeurs['valeur_numerique']}, {$id_phrase})";
			$this->sql->query($q);
			$q = "UPDATE dt_produits_attributs SET valeur_numerique={$valeurs['valeur_numerique']}, phrase_valeur='{$id_phrase}' WHERE id_attributs = $id";
			$this->sql->query($q);
			$q = "UPDATE dt_sku_attributs SET valeur_numerique={$valeurs['valeur_numerique']}, phrase_valeur='{$id_phrase}' WHERE id_attributs = $id";
			$this->sql->query($q);
			$q = "UPDATE dt_matieres_attributs SET valeur_numerique={$valeurs['valeur_numerique']}, phrase_valeur='{$id_phrase}' WHERE id_attributs = $id";
			$this->sql->query($q);
		}

		return $id;
	}

	public function delete($data) {
		$options = $this->options();
		foreach ($options as $option) {
			$this->delete_option($data, $option['id']);
		}
		parent::delete($data);

		$q = <<<SQL
DELETE FROM dt_attributs_valeurs WHERE id_attributs = {$this->id}
SQL;
		$this->sql->query($q);
		
		$q = <<<SQL
DELETE FROM dt_sku_attributs WHERE id_attributs = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_produits_attributs WHERE id_attributs = {$this->id}
SQL;
		$this->sql->query($q);
	}
}
