<?php
class Paybox_System {
	public $site;
	public $identifiant;
	public $rang;
	public $devise;
	public $retour;
	public $server;
	public $hmac_key;
	public $paybox = null;
	public $backup1 = null;
	public $backup2 = null;
	public $annule;
	public $effectue;
	public $refuse;
	public $repondre_a;
	public $pubKey;
	public $total;
	public $cmd;
	public $porteur;
	public $hash = "SHA512";
	public $hmac;
	public $time;
	public $data;
	
	function __construct(Config $config) {
		$this->site = $config->get("paybox_site");
		$this->rang = $config->get("paybox_rang");
		$this->devise = $config->get("paybox_devise");
		$this->identifiant = $config->get("paybox_identifiant");
		$this->retour = $config->get("paybox_retour");
		$this->server = $config->get("paybox_server");
		$this->hmac_key = $config->get("paybox_hmac_key");
		$this->paybox = $config->get("paybox_paybox");
		$this->backup1 = $config->get("paybox_backup1");
		$this->backup2 = $config->get("paybox_backup2");
		$this->annule = $config->get("paybox_payment_annule");
		$this->effectue = $config->get("paybox_payment_effectue");
		$this->refuse = $config->get("paybox_payment_refuse");
		$this->repondre_a = $config->get("paybox_payment_validate");
		$this->pubKey = $config->get("paybox_public_key");
	}
	
	function generateHmac($message) {
		$key = pack("H*", $this->hmac_key);
		$this->hmac = strtoupper(hash_hmac("sha512", $message, $key));
	}
	
	function getOnlineServer() {
		return $this->server[0];
	}
	
	function setMontant($montant) {
		$this->total = (int)(100 * $montant);
	}
	
	function setReference($ref) {
		$this->cmd = $ref;
	}
	
	function buildForm($typepaiement = null, $typecarte = null) {
		$this->time = date("c");
		$values = array(
			'PBX_SITE' => $this->site,
			'PBX_RANG' => $this->rang,
			'PBX_IDENTIFIANT' => $this->identifiant,
			'PBX_TOTAL' => $this->total,
			'PBX_DEVISE' => $this->devise,
			'PBX_CMD' => $this->cmd,
			'PBX_PORTEUR' => $this->porteur,
			'PBX_RETOUR' => $this->retour,
			'PBX_EFFECTUE' => $this->effectue,
			'PBX_ANNULE' => $this->annule,
			'PBX_REFUSE' => $this->refuse,
			'PBX_HASH' => $this->hash,
			'PBX_TIME' => $this->time,
		);
		if ($typepaiement && $typecarte) {
			$values['PBX_TYPEPAIEMENT'] = $typepaiement;
			$values['PBX_TYPECARTE'] = $typecarte;
		}
		if ($this->repondre_a && !empty($this->repondre_a)) {
			$values['PBX_REPONDRE_A'] = $this->repondre_a;
		}
		$server = $this->getOnlineServer();
		$message = $this->createMessage($values);
		$this->generateHmac($message);
		$values['PBX_HMAC'] = $this->hmac;
		$form = <<<HTML
<form method="post" action="{$server}" id="payboxSystemForm">
HTML;
		foreach ($values as $key => $value) {
			$form .= <<<HTML
<input type="hidden" name="{$key}" value="{$value}" />
HTML;
		}
		$form .= <<<HTML
</form>	
HTML;
		return $form;
	}
	
	function createMessage($values) {
		$str = "";
		foreach ($values as $key => $value) {
			if ($key != "PBX_HMAC") {
				if ($str != "") {
					$str .= "&";
				}
				$str .= "$key=$value";
			}
		}
		return $str;
	}
	
	function array_urlencode($var) {
		$array = array();
		foreach ($var as $key =>$value) {
			$array[$key] = urlencode($value);
		}
		return $array;
	}
	
	function describeError() {
		$array = array();
		parse_str($this->data, $array);
		$dico = $GLOBALS['dico'];
		switch ($array['error']) {
			case "00016":
				return $dico->t("AlreadyRegistered");
			case "00004":
			case "00014":
			case "00097":
				return $dico->t("CarteInvalide");
			case "00007":
			case "00008":
				return $dico->t("DateExpirationInvalide");
			case "00020":
				return $dico->t("CVVIncorrect");
			default:
				return $dico->t("ErrorOccured")." - {$array['error']}";	
		}
	}
	
	function traitementRetour() {
		$fsize =  filesize($this->pubKey);
		$fp = fopen($this->pubKey, 'r');
		$filedata = fread($fp, $fsize);
		fclose($fp);
		$key = openssl_pkey_get_public($filedata);
		$first = strpos($_SERVER['REQUEST_URI'], "?");
		$queryStr = substr($_SERVER['REQUEST_URI'], $first + 1);
		$pos = strrpos($queryStr, "&");
		$data = substr($queryStr, 0, $pos);
		$pos = strpos($queryStr, "=", $pos) + 1;
		$sig = substr($queryStr, $pos);
		$sig = base64_decode(urldecode($sig));
		$t = openssl_verify($data, $sig, $key);
		$this->data = $data;
		return ($t == 1);
	}
	
	function noError() {
		$array = array();
		parse_str($this->data, $array);
		return ($array['error'] == "00000" and isset($array['autorisation']));
	}
	
	function getReference() {
		$array = array();
		parse_str($this->data, $array);
		return $array['reference'];
	}
	
	function validatePayment() {
		if ($this->noError() == true) {
			$ref = $this->getReference();
			if (preg_match("#^([0-3]+)aberlaas([0-9]+)#i", $ref, $matches)) {
				$commande = new Commande($GLOBALS['sql']);
				$commande->load($matches[2]);
				if ($commande->values['shop'] == (int) $matches[1]) {
					$commande->update_paiement("valide", 'cb');
				}
			}
		}
	}
}