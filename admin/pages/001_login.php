<?php

$titre = $dico->t("Connexion");

$page->template("login");

$page->css[] = $config->media("login.css");

$config->core_include("extranet/user", "outils/mysql", "outils/form");

$sql = new Mysql($config->db());
$user = new User($sql);

if ($url->get('action') == "logout") {
	$user->logout();
	$url->redirect("login");
}

$form = new Form(array(
	'id' => "login",
	'class' => "login",
));

$erreur = "";
if ($form->is_submitted()) {
	switch ($user->login($form->values())) {
		case User::UNAUTHORIZED :
			$erreur = $dico->t("CompteBloque");
			break;
		case User::UNKNOWN :
		case User::WRONGPASSWORD :
			$erreur = $dico->t("ErreurConnexion");
			break;
	}
}

if ($user->is_logged()) {
	$user_data = $user->data();
	$langues = $user->list_langues();
	$params = array();
	if ($user_data['id_langues']) {
		list($langue, $pays) = explode("_", $langues[$user_data['id_langues']]);
		$params['langue'] = $langue;
		$params['pays'] = $pays;
	}
	if ($url->get('page_id') == 1) {
		$url->redirect("accueil", $params);
	}
	else {
		$url->redirect("current", $params);
	}
}

$contenu = "";
if(!empty($erreur)) {
	$contenu .= <<<HTML
<div class="message_error">
	$erreur
</div>
HTML;
}

$contenu .= <<<HTML
{$form->form_start()}
{$form->fieldset_start($dico->t("Connexion"))}
<p>{$form->input(array('name' => "login", 'label' => $dico->t("VotreLogin")))}</p>
<p>{$form->input(array('name' => "password", 'type' => "password", 'label' => $dico->t("VotreMotDePasse")))}</p>
<p>{$form->input(array('name' => "connexion", 'type' => "submit", 'value' => $dico->t("Entrer")))}</p>
{$form->fieldset_end()}
{$form->form_end()}
HTML;
