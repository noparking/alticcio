<?php

abstract class AbstractContent {
	
	protected function validate_html($html) {
		$doc = new DOMDocument();
		$htmls = array(
			$html,
			utf8_encode($html),
			utf8_decode($html),
		);
		foreach ($htmls as $html) {
			if (@$doc->loadXML("<html>".$html."</html>")) {
				return true;
			}
		}

		return false;

	}

	public function get_id_langues($code_langue) {
		$q = <<<SQL
SELECT id FROM dt_langues WHERE code_langue = '$code_langue'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['id'];
	}

	public function duplicate($data) {
		if (!function_exists("abstract_content_duplicate_callback")) {
			function abstract_content_duplicate_callback (&$value, $field) {
				if (strpos($field, "phrase_") === 0) {
					$value = 0;
				}
			}
		}
		unset($data['id']);
		array_walk_recursive($data, "abstract_content_duplicate_callback");
		$old_id = $this->id;
		$new_id = $this->save($data);
		$this->id = $old_id;

		return $new_id;
	}
}
