<?php

class Sogenactif {

	public function __construct($config, $api_key = 0) {
		$paiement = $config->get('paiement');
		$this->api_key = sprintf("%04d", $api_key);
		$this->config = isset($paiement['sogenactif']) ? $paiement['sogenactif'] : array();
	}

	public function form($data) {
		$reference = $data['reference'].$this->api_key;
		$transaction_id = $data['reference'] % 1000000;
		$html = "";
		$parm = " merchant_id=".$this->config['merchant_id'];
		$parm .= " merchant_country=fr";
		$parm .= " amount=".floor($data['montant'] * 100);
		$parm .= " language=".$data['lgue'];
		$parm .= " customer_email=".$data['mail'];
		$parm .= " caddie=".$reference;
		$parm .= " currency_code=978";
		$parm .= " normal_return_url=".$data['normal_return_url'];
		$parm .= " cancel_return_url=".$data['cancel_return_url'];
		$parm .= " automatic_response_url=".$data['automatic_response_url'];
		$parm .= " pathfile=".$this->config['pathfile'];
		$parm .= " transaction_id=".$transaction_id; 

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

	public function check($post) {
		if(isset($post['DATA'])) {
			$pathfile = "pathfile=".$this->config['pathfile'];
			$message = "message=".$_POST['DATA'];
			$result = exec(trim($this->config['response']." ".$message." ".$pathfile));

			$tableau = explode ("!", $result);

			$data = array();
			$data['code'] = $tableau[1];
			$data['error'] = $tableau[2];
			$data['merchant_id'] = $tableau[3];
			$data['merchant_country'] = $tableau[4];
			$data['amount'] = $tableau[5];
			$data['transaction_id'] = $tableau[6];
			$data['payment_means'] = $tableau[7];
			$data['transmission_date']= $tableau[8];
			$data['payment_time'] = $tableau[9];
			$data['payment_date'] = $tableau[10];
			$data['response_code'] = $tableau[11];
			$data['payment_certificate'] = $tableau[12];
			$data['authorisation_id'] = $tableau[13];
			$data['currency_code'] = $tableau[14];
			$data['card_number'] = $tableau[15];
			$data['cvv_flag'] = $tableau[16];
			$data['cvv_response_code'] = $tableau[17];
			$data['bank_response_code'] = $tableau[18];
			$data['complementary_code'] = $tableau[19];
			$data['complementary_info'] = $tableau[20];
			$data['return_context'] = $tableau[21];
			$data['caddie'] = $tableau[22];
			$data['receipt_complement'] = $tableau[23];
			$data['merchant_language'] = $tableau[24];
			$data['language'] = $tableau[25];
			$data['customer_id'] = $tableau[26];
			$data['order_id'] = $tableau[27];
			$data['customer_email'] = $tableau[28];
			$data['customer_ip_address'] = $tableau[29];
			$data['capture_day'] = $tableau[30];
			$data['capture_mode'] = $tableau[31];
			$data['data'] = $tableau[32];

			if (($data['code'] == "") && ($data['error'] == "")) {
				echo "erreur appel response\n";
				print ("executable response non trouve $path_bin\n");
				
				return false;
			}
			else if ($data['code'] != 0){
				echo " API call error.\n";
				echo "Error message :  $error\n";

				return false;
			}
			else {
				return $data;
			}
		}
	}
}

