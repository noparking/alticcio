<?php

class Sogenactif {

	public function __construct($config, $api_key = 0) {
		$paiement = $config->get('paiement');
		$this->shop = $config->get('shop') ? $config->get('shop') : "000";
		$this->api_key = sprintf("%04d", $api_key);
		$this->config = isset($paiement['sogenactif']) ? $paiement['sogenactif'] : array();
	}

	public function form($data) {
		$reference = $data['reference'].$this->shop.$this->api_key;
		$html = "";
		$parm = " merchant_id=".$this->config['merchant_id'];
		$parm .= " merchant_country=fr";
		$parm .= " amount=".floor($data['montant'] * 100);
		$parm .= " language=fr";
		$parm .= " customer_email=".$data['mail'];
		$parm .= " caddie=".$reference;
		$parm .= " currency_code=978";
		$parm .= " normal_return_url=".$data['normal_return_url'];
		$parm .= " cancel_return_url=".$data['cancel_return_url'];
		$parm .= " automatic_response_url=".$data['automatic_response_url'];
		$parm .= " pathfile=".$this->config['pathfile'];

		$output = array();
		$return_var = 0;
		$result = exec(trim($this->config['request']." ".$parm." 2>&1"), $output, $return_var);
		
		$tableau = explode ("!", "$result");
		
		$code = $tableau[1];
		$error = $tableau[2];
		$message = $tableau[3];
		
		if ($return_var) {
			$html .= $result;
		}
		else if (($code == "") && ($error == "")) {
			$html .= "<br />erreur appel request<br />";
			$html .= "executable request non trouve $path_bin";
		}
		else if ($code != 0) {
			$html .= "<strong><h2>Erreur appel API de paiement.</h2></strong>";
			$html .= "<br /><br /><br />";
			$html .= " message erreur : $error <br>";
		}
		else {
			$html .= "<br /><br />";
			$html .= "$message <br />";
		}

		return $html;
	}
}

