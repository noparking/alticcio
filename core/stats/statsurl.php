<?php
/*
 * Classe PHP qui génére des statistiques sur les contacts
 */


class StatsUrl {
	
	public function __construct($sql = null, $dico) {
		$this->sql = $sql;
		$this->dico = $dico;
	}
		
	public function lister_resultats() {
		$q = "SELECT DATE_FORMAT(FROM_UNIXTIME(s.date_requete), '%Y') AS annee, DATE_FORMAT(FROM_UNIXTIME(s.date_requete), '%m') AS mois, COUNT(*) AS total
				FROM shorturl_log as s
				GROUP BY annee, mois
				ORDER BY annee, mois ";
		$rs = $this->sql->query($q);
		$tab = array();
		while ($row = $this->sql->fetch($rs)) {
			$tab[] = $row;
		};
		return $tab;
	}

	public function afficher_tableau($valeurs) {
		$i = 0;
		$prev_annee = 0;
		$mois = array(	"01" => $this->dico->t('MoisJanvier'),
				    "02" => $this->dico->t('MoisFevrier'),
				    "03" => $this->dico->t('MoisMars'),
				    "04" => $this->dico->t('MoisAvril'),
				    "05" => $this->dico->t('MoisMai'),
				    "06" => $this->dico->t('MoisJuin'),
				    "07" => $this->dico->t('MoisJuillet'),
				    "08" => $this->dico->t('MoisAout'),
				    "09" => $this->dico->t('MoisSeptembre'),
				    "10" => $this->dico->t('MoisOctobre'),
				    "11" => $this->dico->t('MoisNovembre'),
				    "12" => $this->dico->t('MoisDecembre') );
		
		// on recense les années
		$annees = array();
		$annees[] = "";
		foreach($valeurs as $k => $v) {
			if ($prev_annee != $v['annee']) {
				$annees[] = $v['annee'];
			}
			$prev_annee = $v['annee'];
		}
		
		// On génére le tableau HTML
		$html = '<table>';
		foreach($annees as $a) {
			$html .= '<tr>';
			$html .= '<td>'.$a.'</td>';
			if (empty($a)) {
				foreach($mois as $m => $month) {
					$html .= '<td>'.$month.'</td>';
				}
			}
			else {
				foreach($mois as $m => $month) {
					$total = 0;
					foreach($valeurs as $k => $v) {
						if ($v['annee'] == $a AND $v['mois'] == $m) {
							$total = round($v['total'],2);
						}
					}
					if ($total > 0) {
						$html .= '<td>'.$total.'</td>';
					}
					else {
						$html .= '<td>0</td>';
					}
				}
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
		return $html;
	}
}
?>