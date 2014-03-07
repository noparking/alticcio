<?php

class UrlRedirection {

	private $sql;
	private $nb_sections;

	public $automatic = true;
	
	public function __construct($sql, $nb_sections = 1) {
		$this->sql = $sql;
		$this->nb_sections = $nb_sections;
	}

	public function load($code_url) {
		$q = "SELECT * FROM dt_url_redirections WHERE code_url = '$code_url'";
		$res = $this->sql->query($q);
		
		return $this->sql->fetch($res); 
	}

	public function is_free($code_url) {
		return $this->load($code_url) ? false : true;
	}
	
	public function save($code_url, $data) {
		if (!$code_url || !$this->is_free($code_url)) {
			return false;
		}
		$fields = array('code_url', 'niveau');
		$values = array("'$code_url'", 1);
		$fields_values = array();
		foreach ($data as $field => $value) {
			$fields[] = "`$field`";
			$values[] = "'$value'";
			$fields_values[] = "`$field` = '$value'";
		}
		$fields = implode(",", $fields);
		$values = implode(",", $values);
		$where = implode(" AND ", $fields_values);


		$q = <<<SQL
UPDATE dt_url_redirections SET niveau = 0 WHERE $where
SQL;
		$this->sql->query($q);
		
		$q = <<<SQL
INSERT INTO dt_url_redirections ($fields) VALUES ($values)
SQL;
		$this->sql->query($q);

		return true;
	}

	private function lowercase($name) {
		$name = strtolower($name);
		$name = str_replace(array("â", "à", "ä"), "a", $name);
		$name = str_replace(array("é", "è", "ê", "ë"), "e", $name);
		$name = str_replace(array("î", "ï"), "i", $name);
		$name = str_replace(array("ô", "ö"), "o", $name);
		$name = str_replace(array("û", "ü", "ù"), "u", $name);
		$name = str_replace(array("ŷ", "ÿ"), "y", $name);

		return $name;
	}

	public function normalize($name) {
		$name = $this->lowercase($name);
		$name = preg_replace("/[^a-z0-9]/", "-", $name);
		
		return $name;
	}

	public function create_by_name($name) {
		$normalized_name = $this->normalize($name);
		$new_name = $normalized_name;
		$i = 1;
		while (!$this->is_free($new_name)) {
			$new_name = $normalized_name."-".$i;
			$i++;
		}

		return $new_name;
	}

	public function check($code_url, $data) {
		$data['niveau'] = 1;
		$data2 = $this->load($code_url);
		foreach ($data2 as $key => $value) {
			if (isset($data[$key]) and $data[$key] != $value) {
				return false;
			}
		}
		return true;
	}

	public function primary($code_url) {
		$q = <<<SQL
SELECT u1.code_url FROM dt_url_redirections AS u1
INNER JOIN dt_url_redirections AS u2 ON u1.id_langues = u2.id_langues AND u1.`table` = u2.`table` AND u1.variable = u2.variable 
WHERE u1.niveau = 1 and u2.code_url = '$code_url'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['code_url'];
	}

	public function short_encode($num_section, $id) {
		return base_convert(($id - 1) * $this->nb_sections + $num_section, 10, 36);
	}
	
	public function short_decode($short) {
		$num = (int)base_convert($short, 36, 10);
		$num_section = (($num - 1) % $this->nb_sections) + 1;
		$id = ceil($num / $this->nb_sections);
		return array($num_section, $id);
	}

	public function long2short($code_url, $num_section = 1) {
		$code_url = addslashes($code_url);
		$q = <<<SQL
SELECT u1.id FROM dt_url_redirections AS u1
INNER JOIN dt_url_redirections AS u2 ON u1.id_langues = u2.id_langues AND u1.`table` = u2.`table` AND u1.variable = u2.variable 
WHERE u1.niveau = 1 and u2.code_url = '$code_url'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $this->short_encode($num_section, $row['id']);
		}
		else {
			return "";
		}
	}

	public function short2long($short) {
		list($num_section, $id) = $this->short_decode($short);
		$q = <<<SQL
SELECT u1.code_url, l.code_langue FROM dt_url_redirections AS u1
INNER JOIN dt_url_redirections AS u2 ON u1.id_langues = u2.id_langues AND u1.`table` = u2.`table` AND u1.variable = u2.variable 
INNER JOIN dt_langues AS l ON l.id = u1.id_langues
WHERE u1.niveau = 1 and u2.id = $id
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return array($num_section, $row['code_url'], $row['code_langue']);
		}
		else {
			return array(1, "", "");
		}
	}

	public function search($s, $site = "") {
		$terme = addslashes(trim($this->lowercase($s)));

		if ($site) {
			$date = time();
			$q = <<<SQL
INSERT INTO dt_recherches (site, terme, date_recherche)
VALUES ('$site', '$terme', '$date')
SQL;
			$this->sql->query($q);
		}

		$q = <<<SQL
SELECT ur.id_langues, ur.code_url, ur.table, ur.variable, l.code_langue FROM dt_url_redirections_contenu AS urc
INNER JOIN dt_url_redirections AS ur ON urc.id_url_redirections = ur.id AND ur.niveau = 1
INNER JOIN dt_langues AS l ON ur.id_langues = l.id
WHERE MATCH(urc.contenu) AGAINST ('$terme')
SQL;
		$res = $this->sql->query($q);
		$results = array();
		while ($row = $this->sql->fetch($res)) {
			$results[] = $row;
		}

		return $results;
	}

	public function search_count($s) {
		$s = addslashes(trim($this->lowercase($s)));
		$q = <<<SQL
SELECT COUNT(ur.id) AS nb FROM dt_url_redirections_contenu AS urc
INNER JOIN dt_url_redirections AS ur ON urc.id_url_redirections = ur.id AND ur.niveau = 1
INNER JOIN dt_langues AS l ON ur.id_langues = l.id
WHERE MATCH(urc.contenu) AGAINST ('$s')
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['nb'];
	}

	public function save_object($object, $data, $url_key_fields) {

		foreach ($url_key_fields as $key => $value) {
			if (strpos($key, "phrase_") === 0) {
				foreach ($data['phrases'][$key] as $lang => $phrase_url_key) {
					if ($phrase_url_key) {
						$phrase_url_key = $this->normalize($phrase_url_key);
						if (!$this->is_free($phrase_url_key)) {
							$url_redirection_data =	array(
								'table' => $object->table,
								'variable' => isset($data[$object->type]['id']) ? $data[$object->type]['id'] : 0,
								'id_langues' => $object->get_id_langues($lang),
							);
							if (!$this->check($phrase_url_key, $url_redirection_data)) {
								return false;
							}
						}
					}
				}
			}
			else {
				if ($data[$object->type][$key]) {
					$phrase_url_key = $this->normalize($data[$object->type][$key]);
					if (!$this->is_free($phrase_url_key)) {
						$url_redirection_data =	array(
							'table' => $object->table,
							'variable' => isset($data[$object->type]['id']) ? $data[$object->type]['id'] : 0,
							'id_langues' => $object->values['id_langues'],
						);
						if (!$this->check($phrase_url_key, $url_redirection_data)) {
							return false;
						}
					}
				}
			}
		}

		$id_saved = $object->save($data);
		if ($id_saved <= 0) {
			return $id_saved;
		}

		$object->load($id_saved);

		if ($this->automatic) {
			$save_again = false;
			$data2 = $data;
			foreach ($url_key_fields as $key => $value) {
				if (strpos($key, "phrase_") === 0) {
					foreach ($data['phrases'][$key] as $lang => $phrase_url_key) {
						if ($phrase_url_key) {
							$code_url = $this->normalize($phrase_url_key);
						}
						else if ($value) {
							$code_url = $this->create_by_name($data['phrases'][$value][$lang]);
						}
						else {
							$code_url = "";
						}
						if ($code_url) {
							$this->save($code_url, array(
								'table' => $object->table,
								'variable' => $id_saved,
								'id_langues' => $object->get_id_langues($lang),
							));
						}
						if ($phrase_url_key != $code_url) {
							$save_again = true;
							$data2['phrases'][$key][$lang] = $code_url;
							$data2[$object->type][$key] = $data[$object->type][$key];
						}
					}
				}
				else {
					if ($data[$object->type][$key]) {
						$code_url = $data[$object->type][$key];
					}
					else if ($value) {
						$code_url = $this->create_by_name($data[$object->type][$value]);
					}
					else {
						$code_url = "";
					}
					if ($code_url) {
						$this->save($code_url, array(
							'table' => $object->table,
							'variable' => $id_saved,
							'id_langues' => $object->values['id_langues'],
						));
					}
					if (!$data[$object->type][$key]) {
						$save_again = true;
						$data2[$object->type][$key] = $code_url;
					}
				}
			}

			if ($save_again) {
				$data2[$object->type]['id'] = $id_saved;
				$object->save($data2);
				$object->load($id_saved);
			}
		}
		
		return $id_saved;
	}

	public function delete_object($object, $data) {
		$q = <<<SQL
DELETE FROM dt_url_redirections WHERE `table` = '{$object->table}' AND variable = {$object->id}
SQL;
		$this->sql->query($q);

		return $object->delete($data);
	}
}
