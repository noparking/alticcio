<?php

class Livraison {
	private $sql;
	private $id_langues;

	public function __construct($sql, $id_langues){
		$this->sql = $sql;
		$this->id_langues = $id_langues;
	} 
	
	public function liste_pays(){
		$q = <<<SQL
SELECT DISTINCT(fp.id_pays), ph.phrase FROM dt_frais_port AS fp 
INNER JOIN dt_pays AS p ON fp.id_pays = p.id
INNER JOIN dt_phrases AS ph ON p.phrase_nom = ph.id AND ph.id_langues = {$this->id_langues} 
WHERE fp.id_langues = {$this->id_langues}
SQL;
	
		$liste_pays = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)){
			$liste_pays[$row['id_pays']] = $row['phrase'];
		}
		
		return $liste_pays;
	}
	
	public function forfaits($id_boutiques = 0){
		$q = <<<SQL
SELECT fp.id_pays, fp.methode, fp.prix_min, fp.forfait, ph.phrase FROM dt_frais_port AS fp
INNER JOIN dt_phrases AS ph
ON fp.phrase_info = ph.id
WHERE fp.id_langues = {$this->id_langues} AND fp.id_boutiques = $id_boutiques 
ORDER BY fp.prix_min ASC
SQL;
		$forfaits = array();
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)){
			if (!isset($forfaits[$row['id_pays']])){
				$forfaits[$row['id_pays']] = array(
					'methode' => $row['methode'],
					'info' => $row['phrase'],
					'tarifs' => array(),
				);
			}
			$forfaits[$row['id_pays']]['tarifs'][$row['prix_min']] = array(
				'prix_min' => $row['prix_min'],
				'prix_max' => 0,
				'forfait' => $row['forfait'],
			);
		}
		
		foreach ($forfaits as $id_pays => $forfaits_pays) {
			$j = null;
			foreach ($forfaits_pays['tarifs'] as $i => $tarif)  {
				if ($j !== null){
					$forfaits[$id_pays]['tarifs'][$j]['prix_max'] = $tarif['prix_min'] - 1;
				}
				$j = $i;
			}
		}
		
		return $forfaits;
	}

	public function forfait ($montant, $id_pays, $id_boutiques = 0) {
		$q = <<<SQL
SELECT forfait FROM dt_frais_port WHERE prix_min <= $montant 
AND id_langues = {$this->id_langues}
AND id_pays = $id_pays
AND id_boutiques = $id_boutiques
ORDER BY prix_min DESC LIMIT 1
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['forfait'];
	}
	
	function check($data, $id_boutiques = 0){
		$id_pays = $data['commande']['livraison_pays'];
		$q = <<<SQL
SELECT methode from dt_frais_port
WHERE id_langues = {$this->id_langues} AND id_pays = {$id_pays} AND id_boutiques = $id_boutiques
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		if (!$row) {
			return false;
		} else {
			switch ($row['methode']) {
				case 'HorsDomTom':
					$debut_cp = substr($data['commande']['livraison_cp'], 0, 2);
					return !in_array(strtoupper($debut_cp), array('97','98','2A','2B', '20'));
					break;
				default :
					return true;
			}
		}
	}
}
