<?php
/*
 * Classe PHP qui génére des statistiques sur les contacts
 */


class StatsCommandes {
	
	public function __construct($sql = null, $dico) {
		$this->sql = $sql;
		$this->dico = $dico;
	}
	
	public function nombre_commandes_par_mois() {
		$q = "SELECT DATE_FORMAT(FROM_UNIXTIME(c.date_commande), '%Y') AS year, DATE_FORMAT(FROM_UNIXTIME(c.date_commande), '%m') AS month, COUNT(*) AS total
				FROM dt_commandes as c
				WHERE (c.paiement != 'refuse'  OR c.paiement != 'annule') 
				GROUP BY year, month ";
		$rs = $this->sql->query($q);
		$tab = array();
		while ($row = $this->sql->fetch($rs)) {
			$tab[] = $row;
		};
		return $tab;
	}
	
	public function chiffre_affaires_par_mois() {
		$q = "SELECT DATE_FORMAT(FROM_UNIXTIME(c.date_commande), '%Y') AS annee, DATE_FORMAT(FROM_UNIXTIME(c.date_commande), '%m') AS mois, SUM(c.montant) AS total
				FROM dt_commandes as c
				WHERE (c.paiement != 'refuse'  OR c.paiement != 'annule') 
				GROUP BY annee, mois";
		$rs = $this->sql->query($q);
		$tab = array();
		while ($row = $this->sql->fetch($rs)) {
			$tab[] = $row;
		};
		return $tab;
	}
	
	public function afficher_tableau($valeurs) {
		$html = '<table>';
		foreach($valeurs as $k => $v) {
			
		}
		
		
		
		
		foreach($valeurs as $k => $v) {
			$details_date = explode("-",$v['month']);
			if ($prev_annee != $details_date[0]) {
				if ($n > 0) {
					$html .= '</tr>';
				}
				$html .= '<tr>';
				$html .= '<td>'.$details_date[0].' '.$v['month'].'</td>';
				foreach($mois as $m => $month) {
					$html .= '<td>';
					if (isset($details_date[1]) AND $m == $details_date[1]) {
						$html .= $v['total'];
					}
					$html .= '</td>';
				}
			}
			$prev_annee = $details_date[0];
			$n++;
		}
		$html .= '</tr>';
		$html .= '</table>';
		return $html;
	}
}
?>