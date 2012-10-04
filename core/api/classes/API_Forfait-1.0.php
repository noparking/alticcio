<?php

class API_Forfait {
	
	private $api;
	private $sql;
	private $language;

	function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;
		$this->language = $api->get('language');
	}

	function table() {
$q = <<<SQL
SELECT prix_min, forfait FROM dt_frais_port
WHERE id_pays = 77
ORDER BY prix_min ASC
SQL;
		$res = $this->sql->query($q);
		$forfaits = array();
		while ($row = $this->sql->fetch($res)) {
			$forfaits[(int)$row['prix_min']] = (int)$row['forfait'];
		}

		return $forfaits;
	}
}
