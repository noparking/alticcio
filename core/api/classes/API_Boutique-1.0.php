<?php

class API_Boutique {
	
	private $api;
	private $sql;

	function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;
	}

	function settings() {
		$settings = array();
		$key_id = $this->api->key_id();
# On suppose ici qu'une clé API n'est liée qu'à une seule boutique
		$q = <<<SQL
SELECT data_key, data_value FROM dt_boutiques_data AS bd
INNER JOIN dt_boutiques AS b ON b.id = bd.id_boutiques AND b.id_api_keys = {$key_id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$json = json_decode($row['data_value']);
			$settings[$row['data_key']] = $json ? $json : $row['data_value'];
		}

		return $settings;
	}
}
