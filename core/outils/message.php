<?php

class Message {
	
	private $sql;
	private $type;
	private $version;
	private $data;
	
	public function __construct($sql, $data = array()) {
		$this->sql = $sql;
		$this->type = isset($data['type']) ? $data['type'] : "contact";
		$this->version = isset($data['version']) ? $data['version'] : "";
		$this->data = is_array($data) ? $data : array();
	}
	
	public function get($from = null, $to = null) {
		$wheres = array();
		if (isset($from)) {
			$wheres[] = "date_envoi >= $from";
		}
		if (isset($to)) {
			$wheres[] = "date_envoi <= $to";
		}
		$where = "";
		if (count($wheres)) {
			$where = " WHERE ".implode(" AND ", $wheres);
		}
		$q = "SELECT * FROM dt_messages_devis AS md RIGHT OUTER JOIN dt_messages AS m ON m.id = md.id_messages".$where;
		$result = $this->sql->query($q);
		$messages = array();
		while ($row = $this->sql->fetch($result)) {
			$messages[] = $row;
		}
		return $messages;
	}
	
	
	public function count($version = null, $type = null, $date_debut = null, $date_fin = null) {
		$q = "SELECT COUNT(*) AS Total FROM dt_messages WHERE 1";
		if ($version) {
			$q .= " AND version = '$version'";
		}
		if ($type) {
			$q .= " AND type = '$type'";
		}
		if ($date_debut) {
			$q .= " AND date_envoi >= $date_debut";
		}
		if ($date_fin) {
			$q .= " AND date_envoi <= $date_fin";
		}
		$result = $this->sql->query($q);
		$row = $this->sql->fetch($result);
		
		return $row['Total'];
	}
	
	
	function save($data = null) {
		if ($data == null) {
			$data = $this->data;
		}
		$type = $this->type;
		$date_envoi = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		$version = $this->version;
		$profil = isset($data['profil']) ? $data['profil'] : "";
		$siret = isset($data['siret']) ? $data['siret'] : "";
		$organisme = isset($data['organisme']) ? $data['organisme'] : "";
		$civilite = isset($data['civilite']) ? $data['civilite'] : "M";
		$nom = isset($data['nom']) ? $data['nom'] : "";
		$prenom = isset($data['prenom']) ? $data['prenom'] : "";
		$fonction = isset($data['fonction']) ? $data['fonction'] : "";
		$num_client = isset($data['num_client']) ? $data['num_client'] : "";
		$adresse = isset($data['adresse']) ? $data['adresse'] : "";
		$adresse2 = isset($data['adresse2']) ? $data['adresse2'] : "";
		$adresse3 = isset($data['adresse3']) ? $data['adresse3'] : "";
		$cp = isset($data['cp']) ? $data['cp'] : "";
		$ville = isset($data['ville']) ? $data['ville'] : "";
		$pays = isset($data['pays']) ? $data['pays'] : "";
		$id_pays = isset($data['id_pays']) ? $data['id_pays'] : 0;
		$email = isset($data['email']) ? $data['email'] : "";
		$tel = isset($data['tel']) ? $data['tel'] : "";
		$mob = isset($data['mob']) ? $data['mob'] : "";
		$fax = isset($data['fax']) ? $data['fax'] : "";
		$siret = isset($data['siret']) ? $data['siret'] : "";
		$accepter_email = isset($data['accepter_email']) && $data['accepter_email'] ? '1' : '0';
		$accepter_tel = isset($data['accepter_tel']) && $data['accepter_tel'] ? '1' : '0';
		$accepter_catalogue = ($type == "catalogue" or $data['accepter_catalogue']) ? '1' : '0';
		$message = addslashes($data['message']); 
		$q = "INSERT INTO dt_messages (type, date_envoi, ip, version, profil, organisme,".
		" civilite, nom, prenom, fonction, num_client, adresse, adresse2, adresse3, cp, ville, pays, id_pays, email,".
		" tel, mob, fax, accepter_email, accepter_tel, accepter_catalogue, message, siret)".
		" VALUES ('$type', $date_envoi, '$ip', '$version', '$profil', '$organisme', '$civilite',".
		" '$nom', '$prenom', '$fonction', '$num_client', '$adresse', '$adresse2', '$adresse3', '$cp', '$ville', '$pays', '$id_pays',".
		" '$email', '$tel', '$mob', '$fax', $accepter_email, $accepter_tel, $accepter_catalogue, '$message', '$siret')";
		$this->sql->query($q);
		$id = $this->sql->insert_id();
		
		switch ($type) {
			case "devis" :
			case "pose" :
				$produit = isset($data['produit']) ? $data['produit'] : "";
				$id_produits = isset($data['id_produits']) ? (int)$data['id_produits'] : 0;				
				$delai = isset($data['delai']) ? $data['delai'] : "0";
				$sku = isset($data['sku']) ? $data['sku'] : "";
				$fichier = isset($data['fichier']) ? $data['fichier'] : "";
				$q = "INSERT INTO dt_messages_devis (id_messages, produit, id_produits, delai, sku, fichier)".
				" VALUES ($id, '$produit', $id_produits, '$delai', '$sku', '$fichier')";
				$this->sql->query($q);
				break;
		}
		
		return $id;
	}
	
}

?>
