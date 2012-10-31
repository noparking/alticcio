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
	
	public $total;
	public $cmd;
	public $porteur;
	public $hash = "SHA512";
	public $hmac;
	public $time;
	
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
	
	function buildForm() {
		$server = $this->getOnlineServer();
		$message = $this->createMessage();
		$this->generateHmac($message);
		var_dump($message);
		$this->urlencode("time");
		$form = <<<HTML
<form method="post" action="{$server}">
	<input type="hidden" name="PBX_SITE" value="{$this->site}" />
	<input type="hidden" name="PBX_RANG" value="{$this->rang}" />
	<input type="hidden" name="PBX_IDENTIFIANT" value="{$this->identifiant}" />
	<input type="hidden" name="PBX_TOTAL" value="{$this->total}" />
	<input type="hidden" name="PBX_DEVISE" value="{$this->devise}" />
	<input type="hidden" name="PBX_CMD" value="{$this->cmd}" />
	<input type="hidden" name="PBX_PORTEUR" value="{$this->porteur}" />
	<input type="hidden" name="PBX_RETOUR" value="{$this->retour}" />
	<input type="hidden" name="PBX_HASH" value="{$this->hash}" />
	<input type="hidden" name="PBX_TIME" value="{$this->time}" />
	<input type="hidden" name="PBX_EFFECTUE" value="{$this->effectue}" />
	<input type="hidden" name="PBX_ANNULE" value="{$this->annule}" />
	<input type="hidden" name="PBX_REFUSE" value="{$this->refuse}" />
	<input type="hidden" name="PBX_REPONDRE_A" value="{$this->repondre_a}" />
HTML;
	if ($this->paybox) {
		$form .= <<<HTML
<input type="hidden" name="PBX_PAYBOX" value="{$this->paybox}" />
<input type="hidden" name="PBX_BACKUP1" value="{$this->backup1}" />
<input type="hidden" name="PBX_BACKUP2" value="{$this->backup2}" />
HTML;
	}
	$form .= <<<HTML
	<input type="hidden" name="PBX_HMAC" value="{$this->hmac}">
	<input type="submit" value="Envoyer">
</form>	
HTML;
		return $form;
	}
	
	function createMessage() {
		$this->time = date("c", time());
		$message = <<<URL
PBX_SITE={$this->site}
&PBX_RANG={$this->rang}
&PBX_IDENTIFIANT={$this->identifiant}
&PBX_TOTAL={$this->total}
&PBX_DEVISE={$this->devise}
&PBX_CMD={$this->cmd}
&PBX_PORTEUR={$this->porteur}
&PBX_RETOUR={$this->retour}
&PBX_HASH={$this->hash}
&PBX_TIME={$this->time}
&PBX_EFFECTUE={$this->effectue}
&PBX_ANNULE={$this->annule}
&PBX_REFUSE={$this->refuse}
&PBX_REPONDRE_A={$this->repondre_a}
URL;
		if ($this->paybox) {
			$message .= <<<URL
&PBX_PAYBOX={$this->paybox}
&PBX_BACKUP1={$this->backup1}
&PBX_BACKUP2={$this->backup2}
URL;
		}
		return $message;
	}
	
	function urlencode($var) {
		if (is_array($var)) {
			foreach ($var as $value) {
				$this->urlencode($value);
			}
		} else {
			$this->$var = urlencode($this->$var);
		}
	}
}