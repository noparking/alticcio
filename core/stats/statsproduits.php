<?php

require_once "abstract_stats.php";

class StatsProduits extends AbstractStats {
	
	private $sql;

	function __construct($sql) {
		$this->sql = $sql;
	}

	function visites($id_langues, $filter = null) {

		if ($filter === null) {
			$filter = $this->sql;
		}

		$q = <<<SQL
SELECT s.item AS id, s.`from`, ph.phrase AS nom, COUNT(s.id) AS hits, MIN(s.date_requete) AS first_hit, MAX(s.date_requete) AS last_hit
FROM dt_statistiques AS s
INNER JOIN dt_produits AS p ON s.item = p.id
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = p.phrase_nom AND ph.id_langues = $id_langues
WHERE `type` = 'produit'
GROUP BY item
SQL;
		$res = $filter->query($q);
		$visites = array();

		while ($row = $filter->fetch($res)) {
			$visites[] = $row;
		}

		return $visites;
	}

	function get_froms() {
		$q = "SELECT DISTINCT `from` FROM dt_statistiques WHERE `type` = 'produit'";
		$res = $this->sql->query($q);
		$froms = array();
		while ($row = $this->sql->fetch($res)) {
			$froms[$row['from']] = $row['from'];
		}
			
		return $froms;
	}

	public function visites_produit($item, $start, $stop, $increment = "1 month") {
		$date_start = $start;
		$date_stop = strtotime("+$increment", $date_start) - 1;
		$i = 0;
		$stats = array();
		while ($date_stop <= $stop) {
			$q = <<<SQL
SELECT COUNT(*) AS nb
FROM dt_statistiques
WHERE item = {$item} AND date_requete >= {$date_start} AND date_requete <= {$date_stop}
SQL;
			$res = $this->sql->query($q);
			while ($row = $this->sql->fetch($res)) {
				$stats[$i] = $row['nb'];
			}
			$date_start = $date_stop + 1;
			$date_stop =  strtotime("+$increment", $date_start) - 1;
			$i++;
		}

		return $stats;
	}

	public function nom_produit($id_produits, $id_langues) {
		$q = <<<SQL
SELECT ph.phrase
FROM dt_produits AS p
INNER JOIN dt_phrases AS ph ON p.phrase_nom = ph.id AND ph.id_langues =	{$id_langues}
WHERE p.id = {$id_produits}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['phrase'];
	}
}
