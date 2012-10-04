<?php

class API_Panier {

	private $sql;

	function __construct($api) {
		$this->sql = $api->sql;
	}

	function save($token, $product_id, $id_sku, $texte_perso) {
		$date_ajout = time();
		$personnalisation = addslashes(serialize(array(
			'texte' => $texte_perso,
		)));

		$q = <<<SQL
INSERT INTO dt_paniers (token, id_produits, id_sku, quantite, personnalisation, date_ajout)
VALUES ('$token', $product_id, $id_sku, 1, '$personnalisation', $date_ajout)
SQL;
		$this->sql->query($q);
		
		return $this->sql->insert_id();
	}

	function delete($id) {
		$q = "DELETE FROM dt_paniers WHERE id = $id";
		$this->sql->query($q);
	}
}
