<?php

class Paypal {
	
	private $serveurs = array(
		'test' => "https://www.sandbox.paypal.com/cgi-bin/webscr",
		'prod' => "https://www.paypal.com/cgi-bin/webscr",
	);

	private $config;
	private $shop;
	private $serveur;
	private $notify_url;

	public function __construct($config, $api_key = 0) {
		$paiement =  $config->get('paiement');
		$this->shop = $config->get('shop') ? $config->get('shop') : "000";
		$this->api_key = sprintf("%04d", $api_key);
		$this->config = isset($paiement['paypal']) ? $paiement['paypal'] : array();
		$env = isset($this->config['environnement']) ? $this->config['environnement'] : "test";
		$this->serveur = isset($this->serveurs[$env]) ? $this->serveurs[$env] : $this->serveurs['test'];
	}
	
	public function form($data, $target = "_self", $debug = false) {
		$amount = number_format($data['amount'], 2, ".", "");
		$currency_code = isset($data['currency_code']) ? $data['currency_code'] : "EUR";
		$invoice = $data['invoice'].$this->shop.$this->api_key;
		$name = (isset($data['name']) ? $data['name'] : "").$invoice;
		
		$form_class = $debug ? "lastform" : "lastform autosubmit";
		$bouton = $debug ? '<input type="submit" name="bouton" value="OK" />' : "";
		$form = <<<HTML
<form method="post" class="{$form_class}" name="PaypalFormulaire" target="{$target}" action="{$this->serveur}">
{$this->input("cmd", "_cart", $debug)}
{$this->input("upload", "1", $debug)}
{$this->input("business", $this->config['business'], $debug)}
{$this->input("currency_code", $currency_code, $debug)}
{$this->input("charset", "utf-8", $debug)}
{$this->input("notify_url", $this->notify_url, $debug)}
{$this->input("item_name_1", $name, $debug)}
{$this->input("amount_1", $amount, $debug)}
{$this->input("invoice", $invoice, $debug)}
{$this->input("return", $data['return'], $debug)}
{$this->input("cancel_return", $data['cancel_return'], $debug)}
{$this->input("notify_url", $data['notify_url'], $debug)}
HTML;
		return $form.<<<HTML
{$bouton}
</form>
HTML;
	}

	private function input($name, $value, $debug) {
		$type = $debug ? "text" : "hidden";
		$label = $debug ? $name : "";
		return <<<HTML
{$label}<input type="{$type}" name="{$name}" value="{$value}" />
HTML;
	}

	public function check($data) {
		$postfields = array("cmd=_notify-validate");
		foreach ($data as $key => $value) {
			$postfields[] = "$key=$value";
		}
 		$postfields = implode("&", $postfields);
 
		$curl = curl_init();
 
		curl_setopt($curl, CURLOPT_URL, $this->serveur);
		curl_setopt($curl,CURLOPT_POST, count($data) + 1);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		
		curl_close($curl);

		return ($result == "VERIFIED");
	}
}
