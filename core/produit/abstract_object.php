<?php
// TODO : utiliser save_data à la place de save_image
abstract class AbstractObject {
	
	public $sql;
	public $phrase;
	public $langue;
	public $type;
	public $table;
	public $images_table;
	public $phrase_fields = array();
	public $values;

	public function __construct($sql, $phrase = null, $langue = 1) {
		$this->sql = $sql;
		$this->phrase = $phrase;
		$this->langue = $langue;
	}

	public function load($id) {
		$id = (int)$id;
		$q = "SELECT * FROM {$this->table} WHERE id = $id";
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			foreach ($row as $key => $value) {
				$this->values[$key] = $value;
			}
			$this->id = $id;
			return true;
		}
		else {
			$this->id = null;
			return false;
		}
	}

	public function attr($value) {
		if (isset($this->values[$value])) {
			return $this->values[$value];
		}
		return isset($this->$value) ? $this->$value : null;
	}

	public function duplicate($data) {
		function abstract_object_duplicate_callback (&$value, $field) {
			if (strpos($field, "phrase_") === 0) {
				$value = 0;
			}
		}
		array_walk_recursive($data, "abstract_object_duplicate_callback");
		return $this->save($data);
	}

	public function save($data) {
		if (isset($data[$this->type]['id'])) {
			$id = $data[$this->type]['id'];

			$values = array();
			foreach ($data[$this->type] as $field => $value) {
				if (strpos($field, "phrase_") === 0) {
					foreach ($data['phrases'][$field] as $lang => $phrase) { 
						$value = $this->phrase->save($lang, $phrase, $value);
					}
				}
				if ($field != 'id') {
					$values[] = "$field = '$value'";
				}
			}
			if (count($values)) {
				$q = "UPDATE {$this->table} SET ".implode(",", $values);
				$q .= " WHERE id=".$id;
				$this->sql->query($q);
			}
			$this->save_images($data);
		}
		else {
			$fields = array();
			$values = array();
			foreach ($data[$this->type] as $field => $value) {
				if (strpos($field, "phrase_") === 0) {
					foreach ($data['phrases'][$field] as $lang => $phrase) { 
						$value = $this->phrase->save($lang, $phrase, $value);
					}
				}
				$fields[] = $field;
				$values[] = "'$value'";
			}
			if (count($fields)) {
				$q = "INSERT INTO {$this->table} (".implode(",", $fields).") VALUES (".implode(",", $values).")";
				$this->sql->query($q);
			}
			$id = $this->sql->insert_id();
		}
		if (isset($data['site_tiers'])) {
			$site = $data['site_tiers']['site'];
			$entity_id = $data['site_tiers']['entity_id'];
			$entity_table = isset($data['site_tiers']['entity_table']) ? $data['site_tiers']['entity_table'] : "";
			$q = <<<SQL
DELETE FROM dt_sites_tiers
WHERE dt_table = '{$this->table}' AND dt_id = $id AND site = '{$site}'
SQL;
			$this->sql->query($q);

			$q = <<<SQL
INSERT INTO dt_sites_tiers (dt_table, dt_id, site, entity_id, entity_table)
VALUES ('{$this->table}', {$id}, '{$site}', {$entity_id}, '{$entity_table}')
SQL;
			$this->sql->query($q);
		}

		$this->id = $id;

		return $id;
	}

	public function delete($data) {
		$images = $this->images();
		foreach ($images as $image) {
			$this->delete_image($data, $image['id']);
		}

		$q = "DELETE FROM {$this->table} WHERE id = {$this->id}";
		$this->sql->query($q);
		
		foreach ($this->phrase_fields as $field) {
			$q = <<<SQL
DELETE FROM dt_phrases WHERE id = {$this->attr($field)}
SQL;
			$this->sql->query($q);
		}

		$q = <<<SQL
DELETE FROM dt_sites_tiers
WHERE dt_table = '{$this->table}' AND dt_id = {$this->id}
SQL;
		$this->sql->query($q);
	}

	public function phrases() {
		$ids = array();
		foreach ($this->phrase_fields as $attr) {
			if ($value = $this->attr($attr)) {
				$ids[$attr] = $value;
			}
		}
		$images = $this->images();
		foreach ($images as $image) {
			$ids['image'][$image['id']]['phrase_legende'] = $image['phrase_legende'];
		}

		return $ids;
	}

	public function get_phrases() {
		return $this->phrase->get($this->phrases());
	}

	public function save_images($data) {
		if (isset($data['image'])) {
			foreach ($data['image'] as $image_id => $image_data) {
				$set = array();
				foreach ($image_data as $field => $value) {
					if (substr($field, 0, 7) == "phrase_") {
						$id_phrase = $value;
						foreach ($data['phrases']['image'][$image_id][$field] as $lang => $phrase) {
							$id_phrase = $this->phrase->save($lang, $phrase, $id_phrase);
							$set[] = "$field = $id_phrase";
						}
					}
					else {
						$set[] = "$field = '$value'";
					}

				}
				if (count($set)) {
					$q = "UPDATE {$this->images_table} SET ".implode(',', $set)." WHERE id = $image_id";
					$this->sql->query($q);
				}
			}
		}
	}

	public function save_data($data, $key, $table) {
		if (isset($data[$key])) {
			foreach ($data[$key] as $key_id => $key_data) {
				$set = array();
				foreach ($key_data as $field => $value) {
					if (substr($field, 0, 7) == "phrase_") {
						$id_phrase = $value;
						foreach ($data['phrases'][$key][$key_id][$field] as $lang => $phrase) {
							$id_phrase = $this->phrase->save($lang, $phrase, $id_phrase);
							$set[] = "$field = $id_phrase";
						}
					}
					else {
						$set[] = "$field = '$value'";
					}
				}
				$q = "UPDATE `{$table}` SET ".implode(',', $set)." WHERE id = $key_id";
				$this->sql->query($q);
			}
		}
	}

	public function id_field() {
		return str_replace("dt_", "id_", $this->table);
	}

	public function add_image($data, $file, $dir) {
		if (is_array($file)) {
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			$file_name = md5_file($file['tmp_name']).$ext;
			move_uploaded_file($file['tmp_name'], $dir.$file_name);
		}
		else if (file_exists($file)) {
			preg_match("/(\.[^\.]*)$/", $file, $matches);
			$ext = $matches[1];
			$file_name = md5_file($file).$ext;
			copy($file, $dir.$file_name);
		}

		$id_field = $this->id_field();
		$q = <<<SQL
UPDATE {$this->images_table} SET classement = classement + 1
WHERE {$id_field} = {$data[$this->type]['id']}
AND classement >= {$data['new_image']['classement']}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
INSERT INTO {$this->images_table} ({$id_field}, ref, classement)
VALUES ('{$data[$this->type]['id']}', '{$file_name}', '{$data['new_image']['classement']}');
SQL;
		$this->sql->query($q);

		$id = $this->sql->insert_id();
		$data_image = array();
		$data_image['image'][$id]['phrase_legende'] = 0;
		$data_image['phrases']['image'][$id]['phrase_legende'] = $data['new_image']['phrase_legende'];
		$this->save_images($data_image);
	}

	public function delete_image($data, $id) {
		$id_field = $this->id_field();
		$q = <<<SQL
UPDATE {$this->images_table} SET classement = classement - 1
WHERE {$id_field} = {$data[$this->type]['id']}
AND classement > {$data['image'][$id]['classement']}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM {$this->images_table} WHERE id = {$id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_phrases WHERE id = {$data['image'][$id]['phrase_legende']}
SQL;
		$this->sql->query($q);
	}

	public function images() {
		$images = array();

		if (isset($this->images_table) and $this->images_table and isset($this->id) and $this->id) {
			$id_field = $this->id_field();
			$q = <<<SQL
SELECT * FROM {$this->images_table} WHERE {$id_field} = {$this->id} ORDER BY classement
SQL;
			$res = $this->sql->query($q);
			
			while ($row = $this->sql->fetch($res)) {
				$images[$row['id']] = $row;
			}
		}

		return $images; 
	}

	public function vignette() {
		foreach ($this->images() as $image) {
			if ($image['vignette']) {
				return $image['ref'];
			}
		}
		return false;
	}

	public function new_classement() {
		if (!isset($this->id) or !$this->id) {
			return 1;
		}

		$id_field = $this->id_field();
		$q = <<<SQL
SELECT MAX(classement) max_classement FROM {$this->images_table} 
WHERE {$id_field} = {$this->id}
SQL;
		$res = $this->sql->query($q);
		
		$row = $this->sql->fetch($res);

		return $row["max_classement"] + 1;
	}

	public function get_id_langues($code_langue) {
		$q = <<<SQL
SELECT id FROM dt_langues WHERE code_langue = '$code_langue'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['id'];
	}
}
