<?php

class Phrase {

	private $sql;
	private $langues = array();
	private $max_id;

	public function __construct($sql) {
		$this->sql = $sql;

		$q = "SELECT id, code_langue FROM dt_langues";
		$res = $this->sql->query($q);

		while ($row = $sql->fetch($res)) {
			$this->langues[$row['code_langue']] = $row;
		}

		$q = "SELECT MAX(id) as max_id FROM dt_phrases";
		$res = $this->sql->query($q);

		$row = $sql->fetch($res);
		$this->max_id = $row['max_id'];
	}

	public function langues($params = array('key' => 'id', 'value' => 'code_langue')) {
		$langues = array();
		foreach ($this->langues as $langue) {
			$langues[$langue[$params['key']]] = $langue[$params['value']];
		}
		asort($langues);
		
		return $langues;
	}

	public function save($lang, $phrase, $id = 0) {
		$id = (int)$id;
		if (isset($this->langues[$lang])) {
			$id_langues = $this->langues[$lang]['id'];
			$time = time();

			if ($phrase) {
				$q = <<<SQL
SELECT id FROM dt_phrases WHERE id = $id AND id_langues = $id_langues
SQL;
				$res = $this->sql->query($q);
				if ($this->sql->fetch($res)) {
					$q = <<<SQL
UPDATE dt_phrases SET phrase = '{$phrase}', date_update = {$time}
WHERE id = {$id} AND id_langues = {$id_langues}
SQL;
				}
				else {
					if (!$id) {
						$id = $this->next_id();
					}
					$q = <<<SQL
INSERT INTO dt_phrases (id, phrase, id_langues, date_creation, date_update)
VALUES ({$id}, '{$phrase}', {$id_langues}, {$time}, {$time})
SQL;
				}
				$this->sql->query($q);
			}
			else {
				$q = <<<SQL
DELETE FROM dt_phrases WHERE id = {$id} AND id_langues = {$id_langues}
SQL;
				$this->sql->query($q);
				$q = <<<SQL
SELECT id FROM dt_phrases WHERE id = {$id}
SQL;
				$res = $this->sql->query($q);
				if (!$this->sql->fetch($res)) {
					$id = 0;
				}
			}
		}

		return $id;
	}

	public function next_id() {
		$this->max_id++;
		return $this->max_id;
	}

	public function get($ids, $default_languages = array()) {
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		$tab_ids = $this->get_ids($ids);

		$data = array();
		if (count($tab_ids)) {
			$list_ids = implode(",", $tab_ids);
			$q = <<<SQL
SELECT p.id, p.phrase, l.code_langue FROM dt_phrases AS p
INNER JOIN dt_langues AS l ON p.id_langues = l.id
WHERE p.id IN ($list_ids)
SQL;
			$res = $this->sql->query($q);

			while ($row = $this->sql->fetch($res)) {
				$data[$row['id']][$row['code_langue']] = $row['phrase'];
			}
		}

		$codes_langues = array_keys($this->langues);
		foreach ($default_languages as $default_language) {
			foreach (array_keys($data) as $id) {
				foreach ($codes_langues as $code_langue) {
					if (!isset($data[$id][$code_langue])) {
						$data[$id][$code_langue] = "";
					}
				}
				foreach ($data[$id] as $code_langue => $phrase) {
					if (!$phrase and $data[$id][$default_language]) {
						$data[$id][$code_langue] = $data[$id][$default_language];
					}
				}
			}
		}

		$phrases = $ids;
		array_walk_recursive($phrases, array($this, "get_phrases"), $data);

		return $phrases;
	}

	private function get_ids($ids) {
		$tab_ids = array();
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$tab_ids = array_merge($tab_ids, $this->get_ids($id));
			}
		}
		else if ($ids) {
			$tab_ids[$ids] = $ids;
		}
		return $tab_ids;
	}

	private function get_phrases(&$item, $key, $data) {
		if (isset($data[$item])) {
			$item = $data[$item];
		}
		else {
			$item = array();
		}
	}
}
