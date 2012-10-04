<?php

class API_Sku {
	public function updated_since($date) {
		$date = (int)$date;
		$q = <<<SQL
SELECT * FROM dt_sku WHERE date_modification >= $date
SQL;
		$res = mysql_query($q);
		$skus = array();
		while ($row = mysql_fetch_assoc($res)) {
			$skus[] = $row;
		}

		return $skus;
	}

	public function get_id_by_ref($ref) {
		$q = <<<SQL
SELECT id FROM dt_sku WHERE ref_ultralog = '$ref'
SQL;
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);

		return isset($row['id']) ? $row['id'] : false;
	}

	public function vary_from($id_sku) {
		$q = <<<SQL
SELECT id_produits FROM dt_sku_variantes WHERE id_sku = $id_sku
SQL;
		$res = mysql_query($q);
		$ids = array();
		while ($row = mysql_fetch_assoc($res)) {
			$ids[] = $row['id_produits'];
		}

		return $ids;
	}
}
