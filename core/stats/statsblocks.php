<?php
require_once dirname(__FILE__)."/../outils/message.php";

class StatsBlocks {
	
	public $version = "DTFR";
	
	public function __construct($sql = null, $dico) {
		$this->sql = $sql;
		$this->dico = $dico;
	}
	
	private function pourcentage($valeur, $total) {
		return ($valeur*100)/$total;
	}
	
	private function date_debut() {
		$this->debut = date("Y")."-".date("m")."-01 00:00:00";
		return $this->debut;
	}
	
	
	private function date_fin() {
		$this->fin = date("Y")."-".date("m")."-31 23:59:59";
		return $this->fin;
	}
	
	private function date_to_time($date) {
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
	
	public function header_stats() {
		$stats_msg = new Message($this->sql, "");
		$stats_devis = $stats_msg->count($this->version,'devis',$this->date_to_time($this->date_debut()), $this->date_to_time($this->date_fin()));
		$stats_contacts = $stats_msg->count($this->version,'contact',$this->date_to_time($this->date_debut()), $this->date_to_time($this->date_fin()));
		$stats_catalogues = $stats_msg->count($this->version,'catalogue',$this->date_to_time($this->date_debut()), $this->date_to_time($this->date_fin()));
		$html = '<div id="header_stats">';
		$html .= '<dl><dt>'.$stats_devis.'</dt><dd>'.$this->dico->t('DemandesDevis').'</dd></dl>';
		$html .= '<dl><dt>'.$stats_contacts.'</dt><dd>'.$this->dico->t('DemandesInfo').'</dd></dl>';
		$html .= '<dl><dt>'.$stats_catalogues.'</dt><dd>'.$this->dico->t('DemandesCatalogue').'</dd></dl>';
		$html .= '<dl><dt>&nbsp;</dt><dd>'.$this->dico->t('CeMois:').'</dd></dl>';
		$html .= '</div>';
		return $html;
	}
	
}
?>