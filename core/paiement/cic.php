<?php

class CIC {
	
	private $serveurs = array(
		'test' => "https://ssl.paiement.cic-banques.fr/test/paiement.cgi",
		'prod' => "https://ssl.paiement.cic-banques.fr/paiement.cgi",
	);

	private $config;
	private $shop;
	private $serveur;
	private $version = "3.0";

	public function __construct($config, $api_key = 0) {
		$paiement = $config->get('paiement');
		$this->shop = $config->get('shop') ? $config->get('shop') : "000";
		$this->api_key = sprintf("%04d", $api_key);
		$this->config = isset($paiement['cic']) ? $paiement['cic'] : array();
		$env = isset($this->config['environnement']) ? $this->config['environnement'] : "test";
		$this->serveur = isset($this->serveurs[$env]) ? $this->serveurs[$env] : $this->serveurs['test'];
	}

	public function form($data, $target = "_self", $debug = false) {
		$date = date("d/m/Y:H:i:s", $_SERVER['REQUEST_TIME']);
		$devise = isset($data['devise']) ? $data['devise'] : "EUR";
		$montant = number_format($data['montant'], 2, ".", "").$devise;
		$reference = $data['reference'].$this->shop.$this->api_key;
		$mac_string = <<<TEXT
{$this->config['tpe']}*{$date}*{$montant}*{$reference}**{$this->version}*{$data['lgue']}*{$this->config['societe']}*{$data['mail']}**********
TEXT;
		$mac = $this->hmac_sha1($this->usable_key($this->config['key']), $mac_string);
		$type = $debug ? "text" : "hidden";
		$form_class = $debug ? "lastform" : "lastform autosubmit";
		$bouton = $debug ? '<input type="submit" name="bouton" value="OK" />' : "";
		return <<<HTML
<form method="post" class="{$form_class}" name="CMCICFormulaire" target="{$target}" action="{$this->serveur}">
<input type="{$type}" name="version" value="{$this->version}" />
<input type="{$type}" name="TPE" value="{$this->config['tpe']}" />
<input type="{$type}" name="date" value="{$date}" />
<input type="{$type}" name="montant" value="{$montant}" />
<input type="{$type}" name="reference" value="{$reference}" />
<input type="{$type}" name="texte-libre" value="" />
<input type="{$type}" name="mail" value="{$data['mail']}" />
<input type="{$type}" name="lgue" value="{$data['lgue']}" />
<input type="{$type}" name="societe" value="{$this->config['societe']}" />
<input type="{$type}" name="MAC" value="{$mac}" />
<input type="{$type}" name="url_retour" value="{$data['url_retour']}" />
<input type="{$type}" name="url_retour_ok" value="{$data['url_retour_ok']}" />
<input type="{$type}" name="url_retour_err" value="{$data['url_retour_err']}" />
<input type="{$type}" name="options" value="" />
{$bouton}
</form>
HTML;
	}

	public function check($data) {
		$mac_string = <<<TEXT
{$data['TPE']}*{$data['date']}*{$data['montant']}*{$data['reference']}*{$data['texte-libre']}*{$this->version}*{$data['code-retour']}*{$data['cvx']}*{$data['vld']}*{$data['brand']}*{$data['status3ds']}*{$data['numauto']}*{$data['motifrefus']}*{$data['originecb']}*{$data['bincb']}*{$data['hpancb']}*{$data['ipclient']}*{$data['originetr']}*{$data['veres']}*{$data['pares']}*
TEXT;
		$mac = $this->hmac_sha1($this->usable_key($this->config['key']), $mac_string);

		return $mac == strtolower($data['MAC']);
	}

	public function validate($is_valid) {
		if ($is_valid) {
			return <<<TEXT
version=2
cdr=0

TEXT;
		}
		else {
			return <<<TEXT
version=2
cdr=1

TEXT;
		}
	}

	private function hmac_sha1($key, $data) {
		$length = 64; // block length for SHA1
		if (strlen($key) > $length) {
			$key = pack('H*', sha1($key));
		}
		$key  = str_pad($key, $length, chr(0x00));
		$ipad = str_pad('', $length, chr(0x36));
		$opad = str_pad('', $length, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;
		return sha1($k_opad.pack('H*', sha1($k_ipad.$data)));
	}

	private function usable_key($key) {
		if (strlen($key) != 20) {
			$suffix = ''.substr($key, 38, 2).'00';
			$key = substr($key, 0, 38);
			$cca0 = ord($suffix);
			if (($cca0 > 70) && ($cca0 < 97)) {
				$key .= chr($cca0 - 23).substr($suffix, 1, 1);
			}
			elseif (substr($suffix, 1, 1) == 'M') {
				$key .= substr($suffix, 0, 1).'0';
			}
			else {
				$key .= substr($suffix, 0, 2);
			}
			$key = pack('H*', $key);
		}
		return $key;
	}

}
