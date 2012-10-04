<?php

session_start();

include "../../extranet/user.php";
include "../../outils/form.php";
include "../../outils/mysql.php";

$sql = new Mysql(array('database' => "doublet_exemple"));
$user = new User($sql);

$template = <<<TEMPLATE
<div>#{label} : #{field} #{description} #{errors}</div>
TEMPLATE;

$form_creation = new Form(array(
	'id' => "form-creation",
	'class' => "form-creation",
	'required' => array('login', 'email', 'password'),
));

$form_suppression = new Form(array(
	'id' => "form-suppression",
	'class' => "form-suppression",
));

$form_connexion = new Form(array(
	'id' => "form-login",
	'class' => "form-login",
	'actions' => array("connexion", "deconnexion"),
));

$form_creation->template = $template;
$form_suppression->template = $template;
$form_connexion->template = $template;

if ($form_creation->is_submitted() and $form_creation->validate()) {
	switch ($user->create($form_creation->values())) {
		case User::CREATED : $message = "L'utilisateur a été créé"; break;
		case User::ALLREADYEXISTS : $message = "Cet utilisateur existe déjà"; break;
	}
}

if ($form_suppression->is_submitted() and $form_suppression->validate()) {
	$user->delete($form_suppression->value('user'));
	$message = "L'utilisateur a été supprimé";
}

if ($form_connexion->is_submitted()) {
	if ($form_connexion->action() == "deconnexion") {
		$user->logout();
	}
	else {
		switch ($user->login($form_connexion->values())) {
			case User::UNKNOWN : $message = "L'utilisateur n'existe pas"; break;
			case User::UNAUTHORIZED : $message = "Cet utilisateur est bloqué"; break;
			case User::WRONGPASSWORD : $message = "Le mot de passe n'est pas bon"; break;
		}
	}
}

header('Content-Type: text/html; charset=utf-8'); 
?>

<html>
<head>
<style>
.invalid {
	border-color: red;
}
p.message {
	background-color: #ddd;
}
p.user {
	background-color: #fdd;
}
</style>
</head>
<body>
<?php
echo "<p class=\"user\">";
if (isset($_SESSION['extranet']['user'])) {
	print_r($user->data());
	$bouton_deconnexion = $form_connexion->input(array('name' => "deconnexion", 'type' => "submit", 'value' => "Déconnexion", 'template' => "#{field}"));
}
echo "</p>";
echo "<p class=\"message\">$message</p>";
// Affiche le formulaire
echo <<<FORM
{$form_connexion->form_start()}
{$form_connexion->fieldset_start("Connexion")}
{$form_connexion->input(array('name' => "login", 'label' => "Login"))}
{$form_connexion->input(array('name' => "password", 'type' => "password", 'label' => "Password"))}
{$form_connexion->input(array('name' => "connexion", 'type' => "submit", 'value' => "Connexion", 'template' => "#{field}"))}
$bouton_deconnexion
{$form_connexion->fieldset_end()}
{$form_connexion->form_end()}

{$form_creation->form_start()}
{$form_creation->fieldset_start("Creation")}
{$form_creation->input(array('name' => "login", 'label' => "Login"))}
{$form_creation->input(array('name' => "password", 'type' => "password", 'label' => "Password"))}
{$form_creation->input(array('name' => "email", 'label' => "Email"))}
{$form_creation->input(array('name' => "acces", 'type' => "checkbox", 'label' => "Accès", 'value' => "1"))}
{$form_creation->input(array('name' => "creer", 'type' => "submit", 'value' => "Creer", 'template' => "#{field}"))}
{$form_creation->fieldset_end()}
{$form_creation->form_end()}

{$form_suppression->form_start()}
{$form_suppression->fieldset_start("Suppression")}
{$form_suppression->select(array('name' => "user", 'label' => "Supprimer l'utilisateur", 'options' => $user->get_list()))}
{$form_suppression->input(array('name' => "supprimer", 'type' => "submit", 'value' => "Supprimer", 'template' => "#{field}"))}
{$form_suppression->fieldset_end()}
{$form_suppression->form_end()}
FORM;
?>
</body>
</html>