<?php

class Paypal {
	
	private $serveurs = array(
		'test' => "https://www.sandbox.paypal.com/cgi-bin/webscr",
		'prod' => "https://www.paypal.com/cgi-bin/webscr",
	);

	private $config;
	private $serveur;
	private $notify_url;

	public function __construct($config) {
		$paiement =  $config->get('paiement');
		$this->config = isset($paiement['paypal']) ? $paiement['paypal'] : array();
		$env = isset($this->config['environnement']) ? $this->config['environnement'] : "test";
		$this->serveur = isset($this->serveurs[$env]) ? $this->serveurs[$env] : $this->serveurs['test'];
		$this->notify_url = "http://".$_SERVER['SERVER_NAME'].$config->get('base_url')."backend/paypal.php";
	}
	
	public function form($commande, $target = "_self", $debug = false) {
		$form_class = $debug ? "lastform" : "lastform autosubmit";
		$bouton = $debug ? '<input type="submit" name="bouton" value="OK" />' : "";
		$form = <<<HTML
<form method="post" class="{$form_class}" name="PaypalFormulaire" target="{$target}" action="{$this->serveur}">
{$this->input("cmd", "_cart", $debug)}
{$this->input("upload", "1", $debug)}
{$this->input("business", $this->config['business'], $debug)}
{$this->input("currency_code", "EUR", $debug)}
{$this->input("charset", "utf-8", $debug)}
{$this->input("notify_url", $this->notify_url, $debug)}
HTML;
		$i = 1;
		foreach ($commande->produits() as $produit) {
			$form .= <<<HTML
{$this->input("item_name_$i", $produit['nom'], $debug)}
{$this->input("amount_$i", $produit['prix_unitaire'], $debug)}
{$this->input("quantity_$i", $produit['quantite'], $debug)}
HTML;
			$i++;
		}
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
}
