<?php

class API_Sku {

	private $sql;

	function __construct($api) {
		$this->sql = $api->sql;
	}

	public function updated_since($date) {
		$date = (int)$date;
		$q = <<<SQL
SELECT * FROM dt_sku WHERE date_modification >= $date
SQL;
		$res = $this->sql->query($q);
		$skus = array();
		while ($row = $this->sql->fetch($res)) {
			$skus[] = $row;
		}

		return $skus;
	}

	public function get_id_by_ref($ref) {
		$q = <<<SQL
SELECT id FROM dt_sku WHERE ref_ultralog = '$ref'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return isset($row['id']) ? $row['id'] : false;
	}

	public function vary_from($id_sku) {
		$q = <<<SQL
SELECT id_produits FROM dt_sku_variantes WHERE id_sku = $id_sku
SQL;
		$res = $this->sql->query($q);
		$ids = array();
		while ($row = $this->sql->fetch($res)) {
			$ids[] = $row['id_produits'];
		}

		return $ids;
	}
}
