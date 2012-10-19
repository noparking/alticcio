<?php
/*
 * Classe PHP qui génére des statistiques sur les contacts
 */


class StatsUrl {
	
	public function __construct($sql = null, $dico, $annee) {
		$this->sql = $sql;
		$this->dico = $dico;
		$this->annee = $annee;
	}
	
	private function timestamp_debut() {
		return mktime(0, 0, 0, 1, 1, $this->annee);
	}
	
	private function timestamp_fin() {
		return mktime(0, 0, 0, 1, 1, ($this->annee + 1));
	}
	
	private function lister_resultats() {
		$debut = $this->timestamp_debut();
		$fin = $this->timestamp_fin();
		$q = 'SELECT DATE_FORMAT(FROM_UNIXTIME(s.date_requete), "%m") AS month, COUNT(*) AS total
				FROM shorturl_log as s
				WHERE s.date_requete > '.$debut.' AND s.date_requete < '.$fin.'
				GROUP BY month ';
		$rs = $this->sql->query($q);
		$row = $this->sql->fetch($rs);
		return $row;
	}

	public function tableau_resultats($months) {
		$resultats = $this->lister_resultats();
		$html = '<table id="" name="" summary="">';
		$html .= '<thead>';
		$html .= '<tr>';
		foreach($months as $key => $value) {
			$html .= '<td>'.$value.'</td>';
		}
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		$html .= '<tr>';
		foreach($resultats as $k => $v) {
			$html .= '<td>'.$value.'</td>';
		}
		$html .= '</tr>';
		$html .= '</tbody>';
		$html .= '</table>';
	}
}
?>