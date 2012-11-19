<?php

require_once "abstract_object.php";

class Commande extends AbstractObject {

	public $type = "commande";
	public $table = "dt_commandes";
	public $phrase_fields = array();
	
	public function token2id($token) {
		$q = <<<SQL
SELECT id FROM dt_commandes WHERE token = '$token'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['id'];
		}
		return false;
	}

	public function update($data) {
		$set = array();
		foreach ($data['commande'] as $field => $value) {
			$set[] = "$field='$value'";
		}
		$set = implode(',', $set);
		$q = <<<SQL
UPDATE dt_commandes SET $set
WHERE id = {$this->id}
SQL;
		$this->sql->query($q);
		
		$this->ajouter_revision($this->id);
	}

	public function save($data) {
		if (!isset($data['commande']['date_commande'])) {
			$data['commande']['date_commande'] = $_SERVER['REQUEST_TIME'];
		}
		if (isset($data['produits'])) {
			$montant = 0;
			foreach ($data['produits'] as $produit) {
				$montant +=  $produit['prix_unitaire'] * $produit['quantite'];
			}
			$frais_de_port = $this->frais_de_port($montant, $this->langue, $data['commande']['livraison_pays']);
			$data['commande']['montant'] = $montant;
			$data['commande']['frais_de_port'] = $frais_de_port;
			if (!isset($data['commande']['tva']) and isset($data['tva'])) {
				$tva = (float)$data['tva'] * ($montant + $frais_de_port) / 100;
				$data['commande']['tva'] = round($tva, 2);
			}
		}


		$id = parent::save($data);
		
		if (isset($data['produits'])) {
			$keys = array_keys($this->produits());
			foreach ($data['produits'] as $id_commandes_produits => $produit) {
				if (in_array($id_commandes_produits, $keys)) {
					$values = array();
					$produit['id_commandes'] = $id;
					foreach ($produit as $cle => $valeur) {
						$values[] = "{$cle}='{$valeur}'";
					}
					$values = implode(",", $values);
					$q = <<<SQL
UPDATE dt_commandes_produits SET $values WHERE id = {$id_commandes_produits}
SQL;
				}
				else {
					$fields = array();
					$values = array();
					$produit['id_commandes'] = $id;
					foreach ($produit as $cle => $valeur) {
						$fields[] = $cle;
						$values[] = "'{$valeur}'";
					}
					$fields = implode(",", $fields);
					$values = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_commandes_produits ({$fields}) VALUES ({$values})
SQL;
				}
				$this->sql->query($q);
			}
		}
		
		$this->ajouter_revision($id);

		return $id;
	}
	
	public function ajouter_revision($id_commande) {
		$commande = new Commande($this->sql);
		$commande->load($id_commande);
		$fields = array();
		$values = array();
		$cmd_values = $commande->values;
		$cmd_values['commande_id'] = $cmd_values['id'];
		unset($cmd_values['id']);
		$revision = $this->get_last_revision_id($id_commande) + 1;
		$cmd_values['revision'] = $revision;
		foreach ($cmd_values as $cle => $valeur) {
			$fields[] = $cle;
			$values[] = "'$valeur'";
		}
		$fields = implode(",", $fields);
		$values = implode(",", $values);
		$q = <<<SQL
INSERT INTO dt_commandes_revision ($fields) VALUES($values)
SQL;
		$this->sql->query($q);
		$produits = $commande->produits();
		foreach ($produits as $id => $produit) {
			$fields = array();
			$values = array();
			$produit['commandes_produits_id'] = $id;
			unset($produit['id']);
			$produit['revision'] = $revision;
			foreach ($produit as $cle => $valeur) {
				$fields[] = $cle;
				$values[] = "'{$valeur}'";
			}
			$fields = implode(",", $fields);
			$values = implode(",", $values);
			$q = <<<SQL
INSERT INTO dt_commandes_produits_revision ({$fields}) VALUES ({$values})
SQL;
			$this->sql->query($q);
		}
	}
	
	public function get_last_revision_id($id_commande) {
		$q = <<<SQL
SELECT MAX(revision) AS `max` FROM dt_commandes_revision WHERE commande_id = {$id_commande}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return (int) $row['max'];
	}

	public function delete($data) {
		$q = <<<SQL
DELETE FROM dt_commandes_produits WHERE id_commandes = {$data['commande']['id']}
SQL;
		$this->sql->query($q);
		$this->delete_revisions($data['commande']['id']);
		return parent::delete($data);
	}
	
	public function delete_revisions($id_commande) {
		$q = <<<SQL
DELETE FROM dt_commandes_revision WHERE commande_id = $id_commande;
DELETE FROM dt_commandes_produits_revision WHERE id_commandes = $id_commande;
SQL;
		$this->sql->query($q);
	}

	public function delete_produit($id) {
		$q = <<<SQL
DELETE FROM dt_commandes_produits WHERE id = {$id}
SQL;
		$this->sql->query($q);
	}

	public function montant() {
		$q = <<<SQL
SELECT montant + frais_de_port + tva AS montant FROM dt_commandes WHERE id = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['montant'];
	}

	public function frais_de_port($montant = null, $id_langues = 1, $id_pays = 77) {
		if ($montant === null) {
			$montant = $this->montant();
		}
		$q = <<<SQL
SELECT forfait FROM dt_frais_port WHERE prix_min <= $montant 
AND id_langues = $id_langues
AND id_pays = $id_pays 
ORDER BY prix_min DESC LIMIT 1
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['forfait'];
	}

	public function produits() {
		$q = <<<SQL
SELECT * FROM dt_commandes_produits WHERE id_commandes = {$this->id}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[$row['id']] = $row;
		}
		
		return $produits;
	}

	public function update_paiement($statut, $paiement = null) {
		$this->update(array('commande' => array('paiement_statut' => $statut)));
	}

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT c.id, c.nom, c.shop, c.id_api_keys, c.etat, c.montant,
COUNT(cp.id) AS nb_produits, paiement, paiement_statut, c.date_commande
FROM dt_commandes AS c
LEFT OUTER JOIN dt_commandes_produits AS cp ON cp.id_commandes = c.id
SQL;
		if ($filter === null) {
			$filter = $this->sql;
			$q .= <<<SQL
GROUP BY c.id
SQL;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}
	
	public function changer_etat($etat) {
		$this->save(array(
			'commande' => array(
				'id' => $this->id,
				'etat' => $etat,
			),	
		));
	}
}