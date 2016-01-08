<?php
class StatsEmailing {
	
	const LOGGED = 1;
	const CREATED = 2;
	const UPDATED = 3;
	const UNKNOWN = -1;
	const UNAUTHORIZED = -2;
	const WRONGPASSWORD = -3;
	const ALLREADYEXISTS = -4;
	
	public function __construct($sql = null, $dico) {
		$this->sql = $sql;
		$this->dico = $dico;
	}
	
	public function edit($data, $action_form) {
		if ($action_form == "create") {
	 		$data = $this->get_data($data);
			$q = "SELECT emailing FROM dt_stats_emailing WHERE emailing = '".$this->sql->real_escape_string($data['emailing'])."'";
			$result = $this->sql->query($q);
			if ($this->sql->fetch($result)) {
				return self::ALLREADYEXISTS;
			}
		}
		else {
			$data = $this->get_data($data);
		}
		$values = array();
		foreach ($data as $key => $value) {
			$value = $this->sql->real_escape_string($value);
			if (is_numeric($value)) {
				$values[] = "$key = $value";
			}
			else {
				$values[] = "$key = '$value'";
			}
		}
		
		if ($action_form == "create") {
			$q = "INSERT INTO dt_stats_emailing SET ".implode(", ", $values);
			$this->sql->query($q);
			return self::CREATED;
		}
		else {
			$q = "UPDATE dt_stats_emailing SET ".implode(", ", $values)." WHERE id = ".$data['id'];
			$this->sql->query($q);
			return self::UPDATED;
		}
	}
	
	public function load($params) {
		$where = array();
		foreach ($params as $key => $value) {
			$value = $this->sql->real_escape_string($value);
			if (is_numeric($value)) {
				$where[] = "$key = $value";
			}
			else {
				$where[] = "$key = '$value'";
			}
		}
		$q = "SELECT * FROM dt_stats_emailing WHERE ".implode(" AND ", $where);
		$result = $this->sql->query($q);
		return $this->sql->fetch($result);
	}
	
	private function get_data($params) {
		$data = array();
		foreach (array('id','date_envoi','emailing','id_filiales','nb_emails_db','nb_emails_send','nb_emails_opened','nb_emails_clics','commentaires','img_emailing','nb_desabonnements') as $field) {
			if (isset($params[$field])) {
				$data[$field] = $params[$field];
			}
		}
		$data['pourcentage_npai'] = $this->calcul_pourcentage_npai($data['nb_emails_db'], $data['nb_emails_send']);
		$data['pourcentage_ouverture'] = $this->calcul_pourcentage_ouverture($data['nb_emails_send'], $data['nb_emails_opened']);
		$data['pourcentage_clic'] = $this->calcul_pourcentage_clic($data['nb_emails_send'], $data['nb_emails_clics']);
		$data['pourcentage_reactivite'] = $this->calcul_pourcentage_reactivite($data['nb_emails_opened'], $data['nb_emails_clics']);
		$data['pourcentage_desabonnements'] = $this->calcul_pourcentage_desabonnement($data['nb_emails_send'], $data['nb_desabonnements']);
		return $data;
	}
	
	private function pourcentage($valeur, $total) {
		return ($valeur*100)/$total;
	}
	
	private function calcul_pourcentage_npai($nbre_email_db, $nbre_email_send) {
		$pourcent = ($nbre_email_send * 100)/$nbre_email_db;
		$npai = 100 - $pourcent;
		return round($npai, 2);
	}
	
	private function calcul_pourcentage_desabonnement($nbre_email_send, $nbre_desabonnements) {
		$pourcent = ($nbre_desabonnements * 100)/$nbre_email_send;
		return round($pourcent, 2);
	}
	
	private function calcul_pourcentage_ouverture($nbre_email_send, $nbre_email_open) {
		$pourcent = ($nbre_email_open * 100)/$nbre_email_send;
		return round($pourcent, 2);
	}
	
	private function calcul_pourcentage_clic($nbre_email_send, $nbre_email_clic) {
		$pourcent = ($nbre_email_clic * 100)/$nbre_email_send;
		return round($pourcent, 2);
	}
	
	private function calcul_pourcentage_reactivite($nbre_email_open, $nbre_email_clic) {
		$pourcent = ($nbre_email_clic * 100)/$nbre_email_open;
		return round($pourcent, 2);
	}
	
	public function get_list($params) {
		$q = "SELECT e.id, e.emailing, e.img_emailing, e.date_envoi, e.nb_desabonnements, e.nb_emails_send, e.pourcentage_npai, e.pourcentage_ouverture, e.pourcentage_clic, e.pourcentage_reactivite, e.pourcentage_desabonnements, f.code_version 
				FROM dt_stats_emailing AS e
				INNER JOIN dt_filiales AS f
				ON f.id = e.id_filiales ";
		if (!empty($params)) {
			$q .= $params;
		}
		$result = $this->sql->query($q);
		$liste = array();
		while($row = $this->sql->fetch($result)) {
			$liste[$row['id']] = $row;
		}
		return $liste;
	}	
}
?>
