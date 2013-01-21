<?php
/*
 * Configuration
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form");

$menu->current('main/params/users');

$sql = new Mysql($config->db());
$user = new User($sql);

$user_langues = $user->list_langues();

/*
 * Récupération des données
 */
$user_data = $user->data();

/*
 * Initialisation du formulaire
 */
$form = new Form(array(
	'id' => "form-edit",
	'class' => "form-edit",
	'required' => array('email'),
));

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

if ($form->is_submitted() and $form->validate()) {
	switch ($user->update($user_data['id'], $form->values())) {
		case User::UPDATED :
			$message = $dico->t("VosDonneesSauvegardees");
			$user->reset();
			break;
	}
}
else {
	$form->reset();
}


/* 
 * Affichage des messages
 */
if (isset($message)) {
	$message = '<div class="message">'.$message.'</div>';
}
else {
	$message = "";
}


/*
 * Valeurs renvoyées en HTML
 */
$profil_user = $user->profil($user_data['id_groupes_users']);
$titre_page = $dico->t('MonCompte');
$right = "";
$main = <<<HTML
$message
{$form->form_start()}
{$form->fieldset_start($dico->t("ModifierMonCompte"))}
<div class="ligne_form">{$form->html($dico->t("Login")." : ".$user_data['login'])}</div>
<div class="ligne_form">{$form->html($dico->t("Profil")." : ".$profil_user)}</div>
{$form->input(array('name' => "email", 'label' => $dico->t('Email'), 'value' => $user_data['email']))}
{$form->input(array('name' => "password", 'type' => $dico->t("Password"), 'label' => $dico->t("Password"), 'description' => $dico->t("VidePasDeChangements")))}
{$form->select(array('name' => 'id_langues', 'label' => $dico->t("Langue"), 'options' => $user_langues, 'value' => $user_data['id_langues']))}
{$form->input(array('type' => "hidden", 'name' => 'acces', 'forced_value' => 1))}
{$form->input(array('name' => "update", 'type' => "submit", 'value' => $dico->t("Sauvegarder"), 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}
HTML;

?>
