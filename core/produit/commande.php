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
			$id_sku_quantite = array();
			foreach ($data['produits'] as $produit) {
				$montant += $produit['prix_unitaire'] * $produit['quantite'];
				$id_sku_quantite[$produit['id_sku']] = $produit['quantite'];
			}
			$data['commande']['montant'] = $montant;
			if (!isset($data['commande']['frais_de_port'])) {
				$id_boutiques = isset($data['commande']['id_boutiques']) ? $data['commande']['id_boutiques'] : 0;
				$data['commande']['frais_de_port'] = $this->frais_de_port($montant, $this->langue, $data['commande']['livraison_pays'], $id_boutiques);
			}
			$ecotaxe = $this->ecotaxe($id_sku_quantite, $data['commande']['livraison_pays']);
			$data['commande']['ecotaxe'] = $ecotaxe;
			if (!isset($data['commande']['tva']) and isset($data['tva'])) {
				$tva = (float)$data['tva'] * ($montant + $data['commande']['frais_de_port'] + $ecotaxe) / 100;
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
					$produit['ecotaxe'] = $this->ecotaxe(array($produit['id_sku'] => 1), $data['commande']['livraison_pays']);
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
					$produit['ecotaxe'] = $this->ecotaxe(array($produit['id_sku'] => 1), $data['commande']['livraison_pays']);
					foreach ($produit as $cle => $valeur) {
						$fields[] = $cle;
						$values[] = $this->sql->quote_string("dt_commandes_produits", $cle, $valeur);
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
		$cmd_values['id_commandes'] = $cmd_values['id'];
		unset($cmd_values['id']);
		$revision = $this->get_last_revision_id($id_commande) + 1;
		$cmd_values['revision'] = $revision;
		$cmd_values['id_users'] = 0;
		$cmd_values['date_revision'] = time();
		if (isset($_SESSION['extranet']['user']['id'])) {
			$cmd_values['id_users'] = $_SESSION['extranet']['user']['id'];
		}
		foreach ($cmd_values as $cle => $valeur) {
			$fields[] = $cle;
			$values[] = "'".addslashes($valeur)."'";
		}
		$fields = implode(",", $fields);
		$values = implode(",", $values);
		$q = <<<SQL
INSERT INTO dt_commandes_revisions ($fields) VALUES($values)
SQL;
		$this->sql->query($q);
		$produits = $commande->produits();
		foreach ($produits as $id => $produit) {
			$fields = array();
			$values = array();
			$produit['id_commandes_produits'] = $id;
			unset($produit['id']);
			$produit['revision'] = $revision;
			foreach ($produit as $cle => $valeur) {
				$fields[] = $cle;
				$values[] = "'".addslashes($valeur)."'";
			}
			$fields = implode(",", $fields);
			$values = implode(",", $values);
			$q = <<<SQL
INSERT INTO dt_commandes_produits_revisions ({$fields}) VALUES ({$values})
SQL;
			$this->sql->query($q);
		}
	}
	
	public function get_last_revision_id($id_commande) {
		$q = <<<SQL
SELECT MAX(revision) AS `max` FROM dt_commandes_revisions WHERE id_commandes = {$id_commande}
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
DELETE FROM dt_commandes_revisions WHERE id_commandes = $id_commande;
SQL;
		$this->sql->query($q);
		$q = <<<SQL
DELETE FROM dt_commandes_produits_revisions WHERE id_commandes = $id_commande;
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

	public function frais_de_port($montant = null, $id_langues = 1, $id_pays = 77, $id_boutiques = 0) {
		if ($montant === null) {
			$montant = $this->montant();
		}
		$q = <<<SQL
SELECT forfait FROM dt_frais_port WHERE prix_min <= $montant 
AND id_langues = $id_langues
AND id_pays = $id_pays
AND id_boutiques = $id_boutiques
ORDER BY prix_min DESC LIMIT 1
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['forfait'];
	}

	public function ecotaxe($id_sku_quantite, $id_pays, $id_catalogues = 0) {
		if (!$id_sku_quantite) {
			return 0;
		}
		$liste_id_sku = implode(",", array_keys($id_sku_quantite));
		$q = <<<SQL
SELECT id_sku, montant FROM dt_ecotaxes
WHERE id_sku IN ($liste_id_sku) AND id_pays = $id_pays AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$ecotaxe = 0;
		while ($row = $this->sql->fetch($res)) {
			$ecotaxe += $row['montant'] * $id_sku_quantite[$row['id_sku']];
		}
		
		return $ecotaxe;
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

	public function infos() {
		return unserialize($this->values['infos']);
	}

	public function update_infos($infos) {
		$this->update(array('commande' => array('infos' => serialize($infos)));
	}
	
	public function changer_etat($etat, $id = null) {
		$old_id = false;
		$undef = false;
		if (isset($this->id) and $id !=  null) {
			$old_id = $this->id;
			$this->id = $id;
		} else if ($id != null) {
			$this->id = $id;
			$undef = true;
		}
		$this->update(array(
			'commande' => array(
				'etat' => $etat,
			),	
		));
		if ($old_id !== false) {
			$this->id = $old_id;
		}
		if ($undef == true) {
			unset($this->id);
		}
	}
	
	public function compare_revisions($start = 0, $end = 0) {
		$revisions = array();
		$where = "AND revision > $start";
		if ($end > $start) {
			$where .= " AND revision < $end";
		}
		$q = <<<SQL
SELECT dt_commandes_revisions.*, dt_users.login AS user FROM dt_commandes_revisions
LEFT JOIN dt_users ON dt_users.id = dt_commandes_revisions.id_users
WHERE id_commandes = $this->id $where
ORDER BY revision ASC;
SQL;
		$res = $this->sql->query($q);
		while ($data = $this->sql->fetch($res)) {
			$revision = $data['revision'];
			unset($data['revision']);
			unset($data['id']);
			$revisions[$revision]['user'] = $data['user'] ? $data['user'] : "non identifié";
			unset($data['user']);
			unset($data['id_users']);
			$revisions[$revision]['date_revision'] = $data['date_revision'];
			unset($data['date_revision']);
			if ($revision == 1) {
				$revisions[$revision] += $data;
			} else {
				foreach ($data as $cle => $valeur) {
					for ($i = 1 ; $i < $revision and !isset($revisions[$revision - $i][$cle]) ; $i++);
					if ($revisions[$revision - $i][$cle] != $valeur) {
						$revisions[$revision][$cle] = $valeur;
					}
				}
			}
		}
		return $revisions;
	}

	function ref() {
		$ref = $this->values['id'].sprintf("%03d", $this->values['shop']).sprintf("%04d", $this->values["id_api_keys"]);
		return $ref;
	}
}
