<?php

$config->core_include("produit/produit", "produit/sku", "produit/commande", "produit/panier");
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue", "outils/pays");
$config->core_include("paiement/cic", "paiement/paypal");

$sql = new Mysql($config->db());

$phrase = new Phrase($sql);

$pays = new Pays($sql);
$liste_pays = $pays->liste(1, 'id');

$lang = $api->get("language");
$produit = new Produit($sql, $phrase, $lang);
$sku = new Sku($sql, $phrase, $lang);
$panier = new Panier($sql);

$voca = $api->vocabulary();
$api_data = $api->data();

$commande = new Commande($sql);

$form = new Form(array(
	'id' => "form-checkout",
	'class' => "form-checkout",
	'actions' => array("recapitulatif", "paiement", "modification"),
	'required' => explode(",", $api_data['champs_obligatoires']),
));
$form->required_mark = " (obligatoire)";

$products = array();
$personnalisations = array();
$step = isset($_GET['step']) ? $_GET['step'] : "informations";

$errors = array();

$total = 0;
foreach (explode(',', $_GET['cart']) as $item) {
	list($item_id, $qty) = explode('x', $item);
	$panier->load($item_id);
	$perso_data = unserialize($panier->values['personnalisation']);
	$id_sku = $panier->values['id_sku'];
	$id_produits = $panier->values['id_produits'];
	$nom = "";
	if ($produit->load($id_produits)) {
		$phrases = $phrase->get($produit->phrases());
		$nom = $phrases['phrase_nom'][$lang];
		$perso_label = $produit->personnalisation();
		$texte_perso = isset($perso_data['texte']) ? $perso_data['texte'] : "";
	}
	if ($sku->load($id_sku)) {
		$phrases = $phrase->get($sku->phrases());
		$prix = $sku->prix();
		$products[$item_id] = array(
			'nom' => $nom . "\n" . $phrases['phrase_ultralog'][$lang],
			'ref' => $sku->values['ref_ultralog'],
			'prix_unitaire' => $prix['montant_ht'],
			'quantite' => $qty,
			'id_produits' => $id_produits,
			'id_sku' => $id_sku,
			'personnalisation_texte' => base64_encode($texte_perso),
		);
		$total += $qty * $prix['montant_ht'];
		$personnalisations[$item_id] = array(
			'texte_perso' => $texte_perso,
			'label_texte_perso' => isset($perso_label['texte']['libelle']) ? $perso_label['texte']['libelle'] : "",
		);
	}
}
$frais_de_port = $commande->frais_de_port($total);
$tva = 0;
if (isset($api_data['tva'])) {
	$tva = (float)$api_data['tva'] * ($total + $frais_de_port) / 100;
}

$data = $form->escape_values();

$deja_enregistre = false;
$id_commande = 0;
$id_commande = $commande->token2id($_GET['token']);
if ($id_commande) {
	$deja_enregistre = true;
}

if ($form->is_submitted()) {
	if (!$form->validate()) {
		$errors[] = "Veuillez-remplir tous les champs obligatoires.";
	}
	else {
		if ($data['same_address']) {
			$data['facturation'] = $data['livraison'];
		}
		if ($api->get('name') == "handisport" and !strpos($data['commande']['email'], "@handisport.org")) {
			$errors[] = "Votre adresse email doit appartenir au domaine handisport.org pour pouvoir passer commande.";
		}
		switch ($form->action()) {
			case "recapitulatif" :
				$step = "recapitulatif";
				break;
			case "paiement" :
				if ($data['cgv']) {
					$step = "paiement";
				}
				else {
					$step = "recapitulatif";
					$errors[] = "Veuillez acceptez les conditions générales de vente"; 
				}
				break;
			case "validation" :
				foreach (array('livraison', 'facturation') as $type) {
					foreach ($data[$type] as $element => $value) {
						$data['commande'][$type."_".$element] = $value;
					}
				}
				$data['commande']['token'] = $_GET['token'];
				$data['commande']['shop'] = $config->get('shop');
				$data['commande']['tva'] = round($tva, 2);
				foreach ($products as $item_id => $product_data) {
					$data['produits'][$item_id]['personnalisation_texte'] = addslashes(base64_decode($product_data['personnalisation_texte']));
					$data['produits'][$item_id]['personnalisation_fichier'] = "";
					$data['produits'][$item_id]['personnalisation_nom_fichier'] = "";
				}
				if (!$id_commande) {
					$id_commande = $commande->save($data);
				}
				$commande->load($id_commande);

				$step = "remerciements";
				switch ($data['commande']['paiement']) {
					case 'cb' :
						$step = "paiement_cic";
						$cic = new CIC($config, $api->get('id'));
						break;
					case 'paypal' :
						$step = "paiement_paypal";
						$paypal = new Paypal($config);
						break;
				}
				break;
			case "modification" :
				$step = "informations";
				break;
		}
	}
}
else if ($step == "informations") {
	$form->reset();
}

$paiement_ok = true;
if ($step == "cb_result") {
	if ($_GET['retour'] == "ok") {
		$cb_message = <<<HTML
Votre paiement par carte bleu a bien été enregistré.
HTML;
	}
	else {
		$cb_message = <<<HTML
Votre paiement par carte bleu n'a pas été enregistré correctement.
HTML;
		$paiement_ok = false;
	}
	$step = "remerciements";
}

if ($step == "remerciements") {
	// Envoi de l'email de récap
	if ($paiement_ok) {
		$paiement_titre = "Merci pour votre commande.";
		$paiement_image = "checkout-ok.png";
	}
	else {
		$paiement_titre = "Désolé...";
		$paiement_image = "checkout-err.png";
	}
	$data['commande']['liste_produits'] = "";
	$total = 0;
	$ligne_produit = mail_get_template('email_product_line');
	foreach ($data['produits'] as $p) {
		$line = $ligne_produit;
		foreach ($p as $cle => $valeur) {
			if ($cle == 'personnalisation_texte' and $valeur) {
				$valeur = "<h4>Texte personnalisé</h4>".$valeur;  
			}
			$line = str_replace("%$cle%", stripslashes($valeur), $line);
		}
		$data['commande']['liste_produits'] .= $line;
		$total += $p['quantite'] * $p['prix_unitaire'];
	}
	$data['commande']['sous_total'] = number_format($total, 2, ".", "");
	$data['commande']['frais_de_port'] = number_format($frais_de_port, 2, ".", "");
	$data['commande']['tva'] = number_format($tva, 2, ".", "");
	$data['commande']['total'] = number_format($total + $frais_de_port + $tva, 2, ".", "");
	$data['infos'] = array(
		'date' => date("d/m/Y", $_SERVER['REQUEST_TIME']),
		'signature' => "L'équipe Doublet",
		'logo' => isset($_GET['logo']) ? '<img src="'.$_GET['logo'].'" alt="Logo" />' : "",
	);
	switch ($data['commande']['paiement']) {
		case 'cheque' :
			$data['infos']['paiement'] = <<<HTML
Vous avez choisi de payer par chèque. Merci de libeller votre chèque à l'ordre de Doublet et de l'envoyer à l'adresse suivante :
<br />Doublet SA
<br />67, rue de Lille 
<br />59710 Avelin
HTML;
			break;
		case 'mandat' :
			$data['infos']['paiement'] = <<<HTML
Vous avez choisi de payer par mandat administratif (administrations uniquement). Merci de l'envoyer à l'adresse suivante :
<br />Doublet SA
<br />67, rue de Lille 
<br />59710 Avelin
HTML;
			break;
		case 'facture' :
			$data['infos']['paiement'] = <<<HTML
Vous avez choisi de payer à la réception de votre facture. Notre service commercial va traiter votre commande dans les meilleurs délais.
HTML;
			break;
		case 'cb' :
			$data['infos']['paiement'] = <<<HTML
$cb_message
HTML;
			break;
		case 'paypal' :
			$data['infos']['paiement'] = <<<HTML
Vous avez choisi de payer par Paypal.
HTML;
			break;
	}

	if ($paiement_ok and !$deja_enregistre) {
		$data_message = $data;
		$data_message['voca'] = $voca;
		$data_message['livraison']['pays'] = $liste_pays[$data_message['livraison']['pays']];
		$data_message['facturation']['pays'] = $liste_pays[$data_message['facturation']['pays']];
		$message = mail_get_message($data_message);

		$subject = "Merci pour votre commande";

		$emails = explode("\n", $api->get('emails'));
		$headers = "From: ".$emails[0]."\r\n"; 
		$headers .= "Content-Type: text/html; charset=utf-8\r\n"; 
		$headers .= "Reply-to:".$emails[0]."\r\n";
		$headers .= "Bcc:".implode(",", $emails)."\r\n";

		mail($data['commande']['email'], $subject, $message, $headers);
	}
}

function mail_get_message($data) {
	$message = mail_get_template('email');
	foreach (array('infos', 'commande', 'livraison', 'facturation', 'voca') as $section) {
		foreach ($data[$section] as $var => $value) {
			$value = stripslashes($value);
			$message = str_replace("%".$section."_".$var."%", $value, $message);
			$message = str_replace("%".ucfirst($section."_".$var)."%", ucfirst($value), $message);
			$message = str_replace("%".strtoupper($section."_".$var)."%", strtoupper($value), $message);
		}
	}
	return $message;
}

function mail_get_template($name) {
	global $directory;
	return file_get_contents(dirname(__FILE__)."/../templates/$directory/$name.html");
}
