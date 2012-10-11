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
}
