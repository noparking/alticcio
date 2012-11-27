<?php
if (!class_exists("AbstractObject")) {
	require_once "abstract_object.php";
}

class Facture extends AbstractObject {
	public $type = "facture";
	public $table = "dt_factures";
	public $phrase_fields = array();
	
	const LOAD_ID = 1;
	const LOAD_NUMBER = 2;
	const LOAD_CMD_ID = 4;
	
	function load($var, $type = self::LOAD_ID) {
		if ($type == self::LOAD_ID) {
			return parent::load($var);
		} else if ($type == self::LOAD_NUMBER or $type == self::LOAD_CMD_ID) {
			if ($type == self::LOAD_NUMBER) {
				$var = mysql_real_escape_string($var);
				$q = "SELECT * FROM {$this->table} WHERE number = '{$var}'";
			} else {
				$var = (int) $var;
				$q = "SELECT * FROM {$this->table} WHERE id_commandes = {$var}";
			}
			$res = $this->sql->query($q);
			if ($row = $this->sql->fetch($res)) {
				foreach ($row as $key => $value) {
					$this->values[$key] = $value;
				}
				$this->id = $this->values['id'];
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}
	}
	
	function save($data) {
		$data['facture']['id_commandes'] = $data['facture']['id'];
		unset($data['facture']['id']);
		$facture = new Facture($this->sql);
		if ($facture->load($data['facture']['id_commandes'], self::LOAD_CMD_ID)) {
			$data['facture']['id'] = $facture->id;
		}
		$id = parent::save($data);
		$data['facture']['id'] = $id;
		if (!isset($data['facture']['number']) or empty($data['facture']['number'])) {
			$data['facture']['number'] = $id;
			parent::save($data);
		}
		if (isset($data['produits'])) {
			$keys = array_keys($this->produits());
			foreach ($data['produits'] as $id_factures_produits => $produit) {
				unset($produit['id_commandes']);
				if (in_array($id_factures_produits, $keys)) {
					$values = array();
					$produit['id_factures'] = $id;
					foreach ($produit as $cle => $valeur) {
						$values[] = "{$cle}='{$valeur}'";
					}
					$values = implode(",", $values);
					$q = <<<SQL
UPDATE dt_factures_produits SET $values WHERE id = {$id_factures_produits}
SQL;
				}
				else {
					$fields = array();
					$values = array();
					$produit['id_factures'] = $id;
					foreach ($produit as $cle => $valeur) {
						$fields[] = $cle;
						$values[] = "'{$valeur}'";
					}
					$fields = implode(",", $fields);
					$values = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_factures_produits ({$fields}) VALUES ({$values})
SQL;
				}
				$this->sql->query($q);
			}
		}
	}
	
	function delete() {
		
	}
	
	function update() {
		
	}
	
	function produits() {
		$q = <<<SQL
SELECT * FROM dt_factures_produits WHERE id_factures = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[$row['id']] = $row;
		}
		
		return $produits;
	}
	
	function numero_facture_possible() {
		$q = <<<SQL
SELECT MAX(id) AS max FROM dt_factures
SQL;
		$res = $this->sql->query($q);
		$data = $this->sql->fetch($res);
		return (int) $data['max'] + 1;
	}
}