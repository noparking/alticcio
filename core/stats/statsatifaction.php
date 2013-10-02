<?php
class StatSatisfaction {
    
    public function __construct($sql) {
        $this->sql = $sql;
    }
    
    public function moyenne($note, $nbre, $sur) {
        $result = "";
        if ($note > 0) {
            $moyenne = $note/$nbre;
            $result = '<strong>'.round($moyenne,1).'</strong>/'.$sur;
        }
        return $result;
    }
    
    public function pourcentage_satisfait($nb_satisfait, $nb_total) {
        $result = "";
        if ($nb_total > 0) {
            $result = round((($nb_satisfait*100)/$nb_total),1).'%';
        }
        return $result;
    }
    
    public function check_satisfaction() {
        // une fonction qui contrôle que le taux de satisfaction a bien été calculé pour chaque réponse
        $q = "SELECT * FROM dt_sondage_satisfaction WHERE satisfait = 0";
        $rs = $this->sql->query($q);
        while($row = $this->sql->fetch($rs)) {
            if ($row['q1'] >= 3 AND $row['q2'] >= 3 AND $row['q3'] >= 3 AND $row['q4'] >= 3 AND $row['q5'] >= 3 AND $row['q6'] >= 3 AND $row['q7'] >= 3) {
                $q1 = "UPDATE dt_sondage_satisfaction SET satisfait = 1 WHERE id = ".$row['id'];
                $rs1 = $this->sql->query($q1);
            }
        }
    }
    
    public function resultats($langue_form="fr_FR", $annee="2010", $mois=0) {
        if ($mois > 0 AND $mois <= 12) {
            $date_debut = mktime(0,0,0,$mois,1,$annee);
            $date_fin = mktime(0,0,0,$mois,31,$annee);
        }
        else {
            $date_debut = mktime(0,0,0,1,1,$annee);
            $date_fin = mktime(0,0,0,1,1,($annee+1));
        }
	   $langue_form = $langue_form;
        $q = "SELECT DATE_FORMAT(FROM_UNIXTIME(s.date_reponse), '%Y') AS annee,
				DATE_FORMAT(FROM_UNIXTIME(s.date_reponse), '%m') AS mois, 
				COUNT(*) AS total, 
				SUM(satisfait) AS satisfait, 
				SUM(q1) AS q1,
				SUM(q2) AS q2,
				SUM(q3) AS q3,
				SUM(q4) AS q4,
				SUM(q5) AS q5,
				SUM(q6) AS q6,
				SUM(q7) AS q7,
				SUM(scoring) AS scoring
			FROM dt_sondage_satisfaction as s
			WHERE date_reponse > ".$date_debut." AND date_reponse < ".$date_fin."
			AND langue = '".$langue_form."' 
			GROUP BY annee, mois
			ORDER BY annee, mois";
        $rs = $this->sql->query($q);
        $stats = array();
        while($row = $this->sql->fetch($rs)) {
            $stats[] = array(   "mois"=>$row['mois'],
                                "total"=>$row['total'],
                                "tx_satisfait"=>$this->pourcentage_satisfait($row['satisfait'], $row['total']),
                                "moy_q1"=>$this->moyenne($row['q1'], $row['total'], 4),
                                "moy_q2"=>$this->moyenne($row['q2'], $row['total'], 4),
                                "moy_q3"=>$this->moyenne($row['q3'], $row['total'], 4),
                                "moy_q4"=>$this->moyenne($row['q4'], $row['total'], 4),
                                "moy_q5"=>$this->moyenne($row['q5'], $row['total'], 4),
                                "moy_q6"=>$this->moyenne($row['q6'], $row['total'], 4),
                                "moy_q7"=>$this->moyenne($row['q7'], $row['total'], 4),
                                "moy_scoring"=>$this->moyenne($row['scoring'], $row['total'], 28),
                            );
        }
        return $stats;
    }
    
    public function commentaires($langue_form="fr_FR", $annee="2010", $mois=0) {
        if ($mois > 0 AND $mois <= 12) {
            $date_debut = mktime(0,0,0,$mois,1,$annee);
            $date_fin = mktime(0,0,0,$mois,31,$annee);
        }
        else {
            $date_debut = mktime(0,0,0,1,1,$annee);
            $date_fin = mktime(0,0,0,1,1,($annee+1));
        }
	   $langue_form = $langue_form;
        $q = "SELECT commentaires, date_reponse, num_cde
			FROM dt_sondage_satisfaction
			WHERE commentaires != '' AND date_reponse > ".$date_debut."
				AND date_reponse < ".$date_fin." AND langue = '".$langue_form."'
			ORDER BY date_reponse DESC ";
        $rs = $this->sql->query($q);
        $commentaires = array();
        while($row = $this->sql->fetch($rs)) {
            $commentaires[] = array(    "cde"=>$row['num_cde'],
                                        "date"=>date('d M Y', $row['date_reponse']),
                                        "texte"=>$row['commentaires'],
                                );
        }
        return $commentaires;
    }
}
?>