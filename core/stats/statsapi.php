<?php

require_once "abstract_stats.php";

class StatsApi extends AbstractStats {
	
	private $sql;

	private $annee;
	private $id_keys;
	private $date_start;
	private $date_stop;
	
	public function __construct($sql, $params = array()) {
		$this->sql = $sql;
		foreach ($params as $cle => $valeur) {
			$this->$cle = $valeur;
		}
		$this->date_start = mktime(0, 0, 0, 1, 1, $this->annee);
		$this->date_stop = mktime(23, 59, 59, 12, 31, $this->annee);
	}

	public function keys_by_role($role) {
		$keys = array();
		$q = <<<SQL
SELECT k.id, k.name FROM api_keys AS k
INNER JOIN api_keys_roles AS kr ON kr.id_key = k.id 
INNER JOIN api_roles AS r ON r.id = kr.id_role
WHERE r.name = '$role'
ORDER BY k.name;
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$keys[$row['id']] = $row['name'];
		}

		return $keys;
	}

	private function init_dates(&$date_start, &$date_stop) {
		if ($date_start === null) {
			$date_start = $this->date_start;
		}
		if ($date_stop === null) {
			$date_stop = $this->date_stop;
		}
	}

	public function visites_totales($date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT COUNT(*) AS total FROM api_tracker
WHERE id_keys = {$this->id_keys} AND action = 'visit'
AND `date` BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);

		return ($row = $this->sql->fetch($res)) ? $row['total'] : 0;
	}

	public function commandes_totales($date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT COUNT(*) AS total FROM dt_commandes
WHERE id_api_keys = {$this->id_keys}
AND date_commande BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);
		
		return ($row = $this->sql->fetch($res)) ? $row['total'] : 0;
	}

	public function clients_totaux($date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT COUNT(DISTINCT(email)) AS total FROM dt_commandes
WHERE id_api_keys = {$this->id_keys}
AND date_commande BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);
		
		return ($row = $this->sql->fetch($res)) ? $row['total'] : 0;
	}

	public function ca_total($date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT SUM(cp.prix_unitaire * cp.quantite) AS total FROM dt_commandes AS c
INNER JOIN dt_commandes_produits AS cp ON cp.id_commandes = c.id
WHERE c.id_api_keys = {$this->id_keys}
AND c.date_commande BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);
	
		return ($row = $this->sql->fetch($res)) ? (int)$row['total'] : 0;
	}

	public function panier_moyen_total($date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$ca = $this->ca_total($date_start, $date_stop);
		$commandes = $this->commandes_totales($date_start, $date_stop);
		if ($commandes == 0) {
			return 0;
		}
		return round($ca / $commandes, 2);
	}

	public function produit_vu_total($id_produits, $date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT COUNT(*) AS total FROM api_tracker
WHERE id_keys = {$this->id_keys} AND action = 'product' AND item = $id_produits
AND `date` BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);
	
		return ($row = $this->sql->fetch($res)) ? (int)$row['total'] : 0;
	}

	public function produit_commande_total($id_produits, $date_start = null, $date_stop = null) {
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT COUNT(DISTINCT(c.id)) AS total FROM dt_commandes AS c
INNER JOIN dt_commandes_produits AS cp ON cp.id_commandes = c.id
WHERE c.id_api_keys = {$this->id_keys} AND cp.id_produits = $id_produits
AND date_commande BETWEEN {$date_start} AND {$date_stop}
SQL;
		$res = $this->sql->query($q);
		
		return ($row = $this->sql->fetch($res)) ? $row['total'] : 0;
	}

	private function stats_par_mois($methode, $id = null) {
		$date_start = $this->date_start;
		$date_stop = strtotime("+1 month", $date_start) - 1;
		$i = 0;
		$stats = array();
		while ($date_stop <= $this->date_stop) {
			$stats[$i] = $id === null ? $this->$methode($date_start, $date_stop) : $this->$methode($id, $date_start, $date_stop);
			$date_start = $date_stop + 1;
			$date_stop =  strtotime("+1 month", $date_start) - 1;
			$i++;
		}
		return $stats;
	}

	public function visites() {
		return $this->stats_par_mois("visites_totales");
	}

	public function commandes() {
		return $this->stats_par_mois("commandes_totales");
	}

	public function clients() {
		return $this->stats_par_mois("clients_totaux");
	}
	
	public function ca() {
		return $this->stats_par_mois("ca_total");
	}

	public function panier_moyen() {
		return $this->stats_par_mois("panier_moyen_total");
	}

	public function produits($catalogue) {
		$q = <<<SQL
SELECT ph.phrase AS nom, p.ref, p.id FROM dt_produits AS p
INNER JOIN dt_phrases AS ph ON ph.id = p.phrase_nom
INNER JOIN dt_langues AS l ON l.id = ph.id_langues
INNER JOIN dt_catalogues_categories_produits AS ccp ON ccp.id_produits = p.id
INNER JOIN dt_catalogues_categories AS cp ON cp.id = ccp.id_catalogues_categories
INNER JOIN dt_catalogues AS c ON c.id = cp.id_catalogues
WHERE c.nom LIKE '$catalogue' AND l.id = {$this->id_langue}
SQL;
		$res = $this->sql->query($q);
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[$row['id']] = "{$row['nom']}";
		}

		return $produits;
	}

	public function produit_vu($id_produits) {
		return $this->stats_par_mois("produit_vu_total", $id_produits);
	}

	public function produit_commande($id_produits) {
		return $this->stats_par_mois("produit_commande_total", $id_produits);
	}

	public function clients_details(&$filter = null) {
		$date_start = null;
		$date_stop = null;
		$this->init_dates($date_start, $date_stop);
		$q = <<<SQL
SELECT CONCAT(c.nom, " ", c.prenom, " (", c.societe, ")") AS nom_complet, c.email,
COUNT(DISTINCT(c.id)) AS commandes, SUM(cp.prix_unitaire * cp.quantite) AS montant
FROM dt_commandes AS c
INNER JOIN dt_commandes_produits AS cp ON cp.id_commandes = c.id
WHERE c.id_api_keys = {$this->id_keys}
AND date_commande BETWEEN {$date_start} AND {$date_stop}
SQL;
		if ($filter === null) {
			$filter = $this->sql;
			$q .= " GROUP BY c.email ORDER BY montant DESC"; 
		}
		$res = $filter->query($q);

		$clients = array();
		while ($row = $filter->fetch($res)) {
			$clients[] = $row;
		}
		
		return $clients;

	}
}
