<?php

class API_Boutique {
	
	private $api;
	private $sql;
	private $key_id;

	function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;
		
		$this->key_id = $this->api->key_id();
	}

	function id_catalogues() {
# On suppose ici qu'une clé API n'est liée qu'à une seule boutique
		$q = <<<SQL
SELECT id_catalogues FROM dt_boutiques AS b
WHERE b.id_api_keys = {$this->key_id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['id_catalogues'];
	}

	function settings() {
		$settings = array();
# On suppose ici qu'une clé API n'est liée qu'à une seule boutique
		$q = <<<SQL
SELECT data_key, data_value FROM dt_boutiques_data AS bd
INNER JOIN dt_boutiques AS b ON b.id = bd.id_boutiques AND b.id_api_keys = {$this->key_id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$json = json_decode($row['data_value']);
			$settings[$row['data_key']] = $json ? $json : $row['data_value'];
		}

		return $settings;
	}

}

