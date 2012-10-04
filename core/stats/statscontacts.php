<?php
/*
 * Classe PHP qui génére des statistiques sur les contacts
 */

require_once dirname(__FILE__)."/../outils/message.php";

class StatsContacts {
	
	public $periode = "month";
	public $version = "DTFR";
	
	public function __construct($sql = null, $dico) {
		$this->sql = $sql;
		$this->dico = $dico;
	}
	
	
	private function determiner_date_debut() {
		if ($this->periode == "month") {
			$this->date_debut = date("Y")."-".date("m")."-01 00:00:00";
		}
		return $this->date_debut;
	}
	
	
	private function determiner_date_fin() {
		if ($this->periode == "month") {
			$this->date_debut = date("Y")."-".date("m")."-31 23:59:59";
		}
		return $this->date_debut;
	}
	
	private function retourner_time($date) {
		$diff_day_time = explode(" ",$date);
		$diff_day = explode("-",$diff_day_time[0]);
		if(!empty($diff_day_time[1])) {
			$diff_time = explode(":",$diff_day_time[1]);
		}
		else {
			$diff_time = explode(":","00:00:00");
		}
		return mktime($diff_time[0], $diff_time[1], $diff_time[2], $diff_day[1], $diff_day[2], $diff_day[0]);
	}
	
	
	public function nombre_devis() {
		$message = new Message($this->sql);
		return $message->count(
			$this->version,
			'devis',
			$this->retourner_time($this->determiner_date_debut()),
			$this->retourner_time($this->determiner_date_fin())
		);
	}
	
	public function nombre_demande_catalogue() {
		$message = new Message($this->sql);
		return $message->count(
			$this->version,
			'catalogue',
			$this->retourner_time($this->determiner_date_debut()),
			$this->retourner_time($this->determiner_date_fin())
		);
	}
	
	public function nombre_demande_info() {
		$message = new Message($this->sql);
		return $message->count(
			$this->version,
			'contact',
			$this->retourner_time($this->determiner_date_debut()),
			$this->retourner_time($this->determiner_date_fin())
		);
	}
	
	public function lister_totaux_mois($requete, $liste_mois = array()) {
		$rs = $this->sql->query($requete);
		$total_per_month = array();
		$previous_month = "";
		$annee = 0;
		while($row = mysql_fetch_array($rs)) {
			$mois_message = date("m", $row['date_envoi']);
			$annee_message = date("Y", $row['date_envoi']);
			if ($mois_message != $previous_month) {
				$total_per_month[$annee_message][$mois_message] = 1;
			}
			else {
				$total_per_month[$annee_message][$mois_message] = $total_per_month[$annee_message][$mois_message] + 1;
			}
			$previous_month = $mois_message;
		}
		return $total_per_month;
	}
	
	public function afficher_tableau_totaux($totaux, $liste_mois = array()) {
		$html = '<table summary="" name="" class="resultats_stats">';
		$html .= '<tr>';
		$html .= '<th></th>';
		foreach($liste_mois as $num => $mois) {
			$html .= '<th>'.$mois.'</th>';
		}
		$html .= '</tr>';
		$annee = 0;
		foreach($totaux as $year => $line) {
			if ($year != $annee) {
				if ($annee > 0) {
					$html .= '</tr>';
				}
				$html .= '<tr>';
				$html .= '<td class="left">'.$year.'</td>';
			}
			foreach($liste_mois as $num => $mois) {
				if (isset($totaux[$year][$num])) {
						$html .= '<td class="center">'.$totaux[$year][$num].'</td>';
				}
				else {
					$html .= '<td class="center">0</td>';
				}
				if ($num == "12") {
					$html .= '</tr>';
				}
			}
			$annee = $year;
		}
		$html .= '</table>';
		return $html;
	}
}
?>