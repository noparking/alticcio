<?php

include dirname(__FILE__)."/../../stats/statsapi.php";

class API_Stats {
	
	public $stats;
	private $sql;
	private $annee;

	public function __construct($api, $annee) {
		$this->sql = $api->sql;
		$this->annee = $annee;
		$params = array(
			'id_keys' => $api->key_id(),
			'annee' => $this->annee,
		);
		$this->stats = new StatsApi($this->sql, $params);
	}

	public function produit($id) {
		return array(
			'year' => $this->annee,
			'product' => $id,
			'viewed' => $this->stats->produit_vu($id),
			'ordered' => $this->stats->produit_commande($id),
			'total_viewed' => $this->stats->produit_vu_total($id),
			'total_ordered' => $this->stats->produit_commande_total($id),
		);
	}

	public function general() {
		return array(
			'year' => $this->annee,
			'visits' => $this->stats->visites(),
			'orders' => $this->stats->commandes(),
			'clients' => $this->stats->clients(),
			'turnover' => $this->stats->ca(),
			'average' => $this->stats->panier_moyen(),
			'total_visits' => $this->stats->visites_totales(),
			'total_orders' => $this->stats->commandes_totales(),
			'total_clients' => $this->stats->clients_totaux(),
			'total_turnover' => $this->stats->ca_total(),
			'total_average' => $this->stats->panier_moyen_total(),
		);
	}

	public function clients() {
		$details = $this->stats->clients_details();
		return array(
			'year' => $this->annee,
			'clients' => $details,
		);
	}

	public function produits() {
		$details = $this->stats->produits_details();
		return array(
			'year' => $this->annee,
			'products' => $details,
		);
	}
}
