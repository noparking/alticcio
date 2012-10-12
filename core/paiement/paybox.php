<?php
class Paybox {
	public $site = "";
	public $type = "";
	public $rang = "";
	public $reference = "";
	public $version = "00104";
	public $cle = "";
	public $identifiant = "";
	public $devise = "";
	public $activite = "024";
	public $archivage = "aaaaaa";
	public $differe = "000";
	public $autorisation = "";
	public $pays = "FRA";
	public $num_trans = "0000526489";
	public $num_appel = "0000436527";
	public $code_retour;
	
	
	function __construct(Config $config) {
		$this->site = $config->get("paybox_site");
		$this->cle = $config->get("paybox_cle");
		$this->rang = $config->get("paybox_rang");
		$this->reference = $config->get("paybox_reference");
		$this->activite = $config->get("paybox_activite");
		$this->differe = $config->get("paybox_differe");
		$this->activite = $config->get("paybox_activite");
		$this->devise = $config->get("paybox_devise");
		$this->identifiant = "";//$config->get("paybox_identifiant");
	}
	
	function describe_error($code_erreur) {
		$dico = $GLOBALS['dico'];
		switch ($code) {
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
    			return $dico->t("ErrorOccured");
			
		}
	}
	
	function get_form(Form $form) {
		$dico = $GLOBALS['dico'];
		$form->template = <<<HTML
<p>
	#{label}
	#{field}
HTML;
		$mois = array(
			'01' => $dico->t("Janvier"),
			'02' => $dico->t("Février"),
			'03' => $dico->t("Mars"),
			'04' => $dico->t("Avril"),
			'05' => $dico->t("Mai"),
			'06' => $dico->t("Juin"),
			'07' => $dico->t("Juillet"),
			'08' => $dico->t("Aout"),
			'09' => $dico->t("Septembre"),
			'10' => $dico->t("Octobre"),
			'11' => $dico->t("Novembre"),
			'12' => $dico->t("Decembre"),
		);
		$annee = array();
		$current_year = (int) date("Y", time());
		for ($year = $current_year ; $year < $current_year + 10 ; $year++) {
			$y = $year - ((int) ($year / 100) * 100);
			$annee[$y] = $year;
		}
		$str = <<<HTML
{$form->input(array('name' => "paiement[carte]", 'type' => "text", 'label' => $dico->t("NumeroCarte")))}
{$form->input(array('name' => "paiement[cvv]", 'type' => "text", 'label' => $dico->t("NumeroCVV")))}
{$form->select(array('name' => "paiement[expiration][mois]", 'label' => $dico->t("DateExpiration"), 'options' => $mois))}
HTML;
		$form->template = <<<HTML
#{field}
</p>
HTML;
		$str .= <<<HTML
{$form->select(array('name' => "paiement[expiration][annee]", 'options' => $annee))}
HTML;
		$form->template = <<<HTML
<p>
	#{label}
	#{field}
</p>
HTML;
		$str .= <<<HTML
{$form->input(array('type' => "submit", 'name' => "submit", 'value' => "Payer"))}<br />
HTML;
		return $str;
	}
	
	function post_request($url, $data, $optional_headers = null) {
		$params = array(
			'http' => array(
				'method' => 'POST',
				'content' => $data,
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
			)
		);
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = fopen($url, "rb", false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $url");
		}
		$response = stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url");
		}
		return $response;
	}
	
	function debit_account($porteur, $dateval, $cvv, $timestamp = null) {
		$this->type = "00002";
		$this->montant = str_pad((int)(100 * $this->montant), 10, 0, STR_PAD_LEFT);
		$vars['porteur'] = $porteur;
		$vars['dateval'] = $dateval;
		$vars['cvv'] = $cvv;
		$this->code_retour = $this->send_question($vars, $timestamp);
	}
	
	function build_question($vars, $timestamp = null) {
		if ($timestamp == null) {
			$timestamp = time();
		}
	
		return "VERSION=".$this->version.
		"&DATEQ=".date("dmYHis", $timestamp).
		"&TYPE=".$this->type.
		"&MONTANT=".$this->montant.
		"&NUMQUESTION=0000000001".
		"&SITE=".$this->site.
		"&RANG=".$this->rang.
		"&REFERENCE=".$this->reference.
		"&CLE=".$this->cle.
		"&IDENTIFIANT=".$this->identifiant.
		"&DEVISE=".$this->devise.
		"&PORTEUR=".$vars['porteur'].
		"&DATEVAL=".$vars['dateval'].
		"&CVV=".$vars['cvv'].
		"&ACTIVITE=".$this->activite.
		"&ARCHIVAGE=".$this->archivage.
		"&DIFFERE=".$this->differe.
		"&AUTORISATION=".$this->autorisation.
		"&NUMAPPEL=".$this->num_appel.
		"&NUMTRANS=".$this->num_trans.
		"&PAYS=".$this->pays.
		"&TYPECARTE";
	}
	
	function send_question($vars, $timestamp = null) {
		$question = $this->build_question($vars, $timestamp);
		var_dump($question);
		$value = $this->post_request(
			$GLOBALS['config']->get("paybox_url"),
			$question
		);
	
		return $value;
	}
	
}