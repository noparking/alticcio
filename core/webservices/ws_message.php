<?php

class WSMessage {
	
	var $message;
	var $key;
	var $sql;
	
	static $types_voies = array(
		28 => array("cours ","cour ","COURS ","COUR ","Cours ","Cour "),
		29 => array("grande rue ","grde rue ","grd rue ","Grande Rue ","GRANDE RUE ","GRDE RUE ","Grde Rue ","grand rue ","Grand Rue "),
		1 => array("rue ","ru ","Rue ","RUE ","ure "),
		2 => array("chemin ","ch ","ch. ","chmin ","CHEMIN ","Chemin "),
		3 => array("route ","rte ","rt. ","rt ","rte. ","Route ","Rte ","Rte. ","Rt ","Rt. ","ROUTE "),
		4 => array("avenue ","av ","ave ","av. ","ave. ","avn ","AVENUE ","Avenue ","AVE ","AV "),
		5 => array("street ","str ","STREET ","Str ","Str. "),
		6 => array("allée ", "allee ", "alle ", "alee ", "all ","Allée","Allee "),
		7 => array("boulevard ","bd ","bld ","blvd ","bd. ","bvd. ","bld. ","blvd. ","Boulevard ","Bv ","BV ","bv ","BD ","Bd "),
		11 => array("impasse ","imp ","impase ","imp. ","Impasse "),
		14 => array("passage ","pass ", "psg ", "pas ", "pas. ","Passage "),
		15 => array("place ","pl ","pl. ","plce ","ple ","Place ","PLACE ","places ","Places "),
		17 => array("rond-point ","rond-pt ","rond pt ","rd pt ","rond point ","rpt ", "rpt. ", "Rond-Point "),
		18 => array("square ","sq ","sqre ","sq. ","Square ","SQUARE "),
		24 => array("sentier ","Sentier ","SENTIER "),
		25 => array("faubourg ","fbg ","fbrg ","fb. ","fbg. ","Faubourg ","FAUBOURG "),
		26 => array("quai ","q. ","Quai ","QUAI "),
		27 => array("chaussée ","chauss ","chaus ", "chaussee ","Chaussee ","Chaussée ","CHAUSSEE "),
		28 => array("Esplanade ","esplanade ")
	);
	
	static $profils = array(
		'99' => "Particulier",
		'6' => "Association",
		'11' => "Club",
		'2' => "Mairie",
		'4' => "Administration",
		'1' => "Entreprise",
	);
	
	static $objets = array(
		'contact' => 1,
		'catalogue' => 2,
		'devis' => 3,
	);
	
	function __construct($sql) {
		$this->sql = $sql;
		$this->message = new Message($sql);
	}
	
	function get_messages_data($from = null, $to = null, $filter = array()) {
		$messages_list = $this->message->get($from, $to);
		$messages = array();
		foreach ($messages_list as $m) {
			$message = array(
				'objet' => self::$objets[$m['type']],
				'id' => $m['id'],
				'nom' => $m['nom'],
				'prenom' => $m['prenom'],
				'adresse' => array(
					'adresse1' => $m['adresse'],
					'adresse2' => $m['adresse2'],
					'adresse3' => $m['adresse3'],
					'num_voie' => self::get_num_voie($m['adresse']),
					'type_voie' => self::get_type_voie($m['adresse']),
					'nom_voie' => self::get_nom_voie($m['adresse']),
					'lieu_dit' => self::get_lieu_dit($m['adresse3']),
					'bp' => self::get_bp($m['adresse3']),
				),
				'codepostal' => $m['cp'],
				'ville' => self::get_ville($m['ville']),
				'cedex' => self::get_cedex($m['ville']),
				'num_cedex' => self::get_num_cedex($m['ville']),
				'pays' => array(
					'iso' => $m['pays'],
					'ultralog' => $this->get_code_pays_ultralog($m['pays']),
				),
				'telephone' => $m['tel'],
				'mobile' => $m['mob'],
				'email' => $m['email'],
				'siret' => ($m['siret'] != "-") ? $m['siret'] : "",
				'numeroclient' => $m['num_client'],
				'organisme' => $m['organisme'],
				'categorieclient' => array(
					'id' => $m['profil'],
					'nom' => isset(self::$profils[$m['profil']]) ? self::$profils[$m['profil']] : "",
				),
				'fonction' => $m['fonction'],
				'contact' => array(
					'email' => $m['accepter_email'],
					'telephone' => $m['accepter_tel'],
					'catalogue' => $m['type'] == "catalogue" ? 1 : $m['accepter_catalogue'],
				),
				'texte' => $m['message'],
				'piece_jointe' => self::get_file_path($m['fichier']),
				'date' => date("Y-m-d H:i:s", $m['date_envoi']),
				'timestamp' => $m['date_envoi'],
			);
			if ($toto = self::filter($message, $filter)) {
				$messages[] = $message;
			}
		}

		return $messages;
	}

	function get_messages($from = null, $to = null, $filter = array()) {
		$messages = $this->get_messages_data($from, $to, $filter);
		if (count($messages)) {
			$messages = array('message' => $messages);
		}
		$data = array('messages' => $messages);
		
		$xml = new XmlBuilder($data);
		return $xml->getDocument();
	}
	
	private static function filter($message, $filter) {
		foreach ($filter as $element => $value) {
			if (isset($message[$element])) {
				if (is_array($value)) {
					if (!self::filter($message[$element], $value)) {
						return false;
					}
				}
				else {
					if ($message[$element] != $value) {
						return false;
					}
				}
			}
		}
		return true;
	}
	
	function get_code_pays_ultralog($alpha2) {
		static $codes_pays = null;
		if ($codes_pays === null) {
			require_once dirname(__FILE__)."/../outils/pays.php";
			$pays = new Pays($this->sql);
			$codes_pays = $pays->codes_ultralog();
		}
		return isset($codes_pays[$alpha2]) ? sprintf("%04d", $codes_pays[$alpha2]) : "";
	}
	
	static function get_file_path($fichier) {
		$fichier = basename($fichier);
		if (!$fichier or !strpos($fichier, ".")) {
			return null;
		}
		else {
			global $config;
			return $config->get('upload_url').$fichier;
		}
	}
	
	static function get_num_voie($adresse) {
		preg_match("/(\d+( *(bis|ter))?)/i", $adresse, $matches);
		return isset($matches[1]) ? $matches[1] : null;
	}

	static function get_type_voie($adresse) {
		foreach (self::$types_voies as $type => $labels) {
			foreach ($labels as $label) {
				if (strpos($adresse, $label) !== false) {
					return $type;
				}
			}
		}
	}
	
	static function get_nom_voie($adresse) {
		$num = self::get_num_voie($adresse);
		$type = self::get_type_voie($adresse);
		if (isset(self::$types_voies[$type])) {
			preg_match("/$num *(".implode("|", self::$types_voies[$type]).") *(.*)/i", $adresse, $matches);
			return isset($matches[2]) ? trim($matches[2]) : "";
		}
		else {
			return "";
		}
	}

	static function is_bp($adresse) {
		return preg_match("/\d/", $adresse);
	}
	
	static function get_lieu_dit($adresse) {
		return self::is_bp($adresse) ? "" : $adresse;
	}
	
	static function get_bp($adresse) {
		return self::is_bp($adresse) ? $adresse : "";
	}
	
	static function get_ville($ville) {
		return preg_match("/(.*)\s*cedex\s*([\d\s]*)/i", $ville, $matches) ? trim($matches[1]) : $ville;
	}
	
	static function get_cedex($ville) {
		return preg_match("/cedex/i", $ville, $matches) ? 1 : 0;
	}
	
	static function get_num_cedex($ville) {
		return preg_match("/cedex\s*([\d\s]+)/i", $ville, $matches) ? $matches[1] : "";
	}
}

?>
