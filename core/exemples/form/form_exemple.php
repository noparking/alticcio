<?php

include "../../outils/form.php";

$form = new Form(array(
	'id' => "myform",
	'class' => "formclass",
	'required' => array('nom', 'message', 'choix', 'mdp', 'habitat'),
	'confirm' => array('email' => 'emailbis'),
	'validate' => array(
		'email' => array("validate_email"),
		'mdp' => array("validate_length", 6, 8),
	),
	'on_validation' => array('verifier_nom_different_de_mot_de_passe'),
	'steps' => 3,
	'actions' => array("suivant", "envoyer", "precedent"),
));

// fonction de validation spécifique
function verifier_nom_different_de_mot_de_passe($form) {
	$values = $form->values();
	if ($form->step() != 3 || $values['nom'] != $values['mdp']) {
		return true;
	}
	else {
		$form->invalid_field('nom', 'mdp');
		$form->error("Le mot de passe doit être différent du nom.", 'mdp');
		return false;
	}
}

// redéfinit le marqueur de champ obligatoire (par défaut "*") qui s'affiche dans le label
$form->required_mark = " (obligatoire)";

// redéfinit le template de tous les champs du formulaire (sinon, il existe un template par défaut)
$form->template = <<<TEMPLATE
<p>#{label} : #{field} #{description} #{errors}</p>
TEMPLATE;

// Autre template que l'on pourra passer en paramètre des champs
$autre_template = <<<TEMPLATE
<p><b>#{label} :</b></p>
<p><i>#{description}</i></p>
#{field}
TEMPLATE;

header('Content-Type: text/html; charset=utf-8'); 
?>

<html>
<head>
<style>
li.formclass-error {
	display: inline;
}
ul.formclass-error {
	list-style-type: none;
	display: inline;
}
.invalid, .formclass-error {
	color: red;
}
input.invalid {
	border-color: red;
}
</style>
</head>
<body>
<?php
var_dump($form->action());
//print_r($_POST);
// Traitement du formulaire
if ($form->is_submitted()) {
	if ($form->action() == "precedent") {
		if (!$form->previous()) {
			echo "Rien par là !";
		}
	}
	elseif (!$form->validate()) {
		echo "<p><b>Erreurs !</b></p>";
		foreach ($form->errors() as $error) {
			echo "<p>$error</p>";
		}
//		$form->reset_step();
	}
	else {
		if (!$form->next()) {
			echo "<p><b>Formulaire envoyé !</b></p>";
			print_r($form->values());
		}
	}
}
else {
	$form->reset();
}

// Affiche le formulaire
echo <<<FORM
{$form->form_start()}
{$form->fieldset_start("Etape 1")}
{$form->input(array('name' => "nom", 'label' => "Nom", 'description' => "Votre nom", 'value' => "Nom par défaut"))}
{$form->input(array('type' => "hidden", 'name' => "champ-cache", 'value' => "truc"))}
{$form->radios(array('name' => "habitat", 'label' => "Votre projet concerne", 'options' => array('maison' => "une maison", 'appartement' => "un appartment", 'bureau' => "un bureau", 'commerce' => "un commerce")))}
{$form->input(array('type' => "checkbox", 'name' => "a-cocher", 'label' => "case à cocher", 'value' => "1", 'checked' => true))}
{$form->input(array('type' => "submit", 'name' => "precedent", 'value' => "Précédent", 'template' => "#{field}"))}
{$form->input(array('type' => "submit", 'name' => "suivant", 'value' => "Suivant", 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}

{$form->form_start()}
{$form->fieldset_start("Etape 2")}
{$form->textarea(array('name' => "message", 'label' => "Message", 'description' => "Votre message", 'value' => "Message test", 'template' => $autre_template))}
{$form->select(array('name' => "choix", 'options' => array('' => "--", '1' => "choix 1", '2' => "choix 2", '3' => "choix 3"), 'value' => "2", 'label' => "Au choix", 'description' => "Faites un choix"))}
{$form->radios(array('name' => "option", 'options' => array('1' => "option 1", '2' => "option 2"), 'value' => "2", 'label' => "Autre choix", 'description' => "Choisissez une option", 'template' => $autre_template))}
{$form->input(array('type' => "submit", 'name' => "precedent", 'value' => "Précédent", 'template' => "#{field}"))}
{$form->input(array('type' => "submit", 'name' => "suivant", 'value' => "Suivant", 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}

{$form->form_start()}
{$form->fieldset_start("Etape 3")}
{$form->input(array('name' => "email", 'label' => "Email", 'description' => "Votre adresse email"))}
{$form->input(array('name' => "emailbis", 'label' => "Confirmation email", 'description' => "Retappez votre email"))}
{$form->input(array('type' => "password", 'name' => "mdp", 'label' => "Password", 'description' => "Entre 6 et 8 caractères, différent du nom"))}
{$form->input(array('type' => "submit", 'name' => "precedent", 'value' => "Précédent", 'template' => "#{field}", 'previous' => true))}
{$form->input(array('type' => "submit", 'name' => "envoyer", 'value' => "Envoyer", 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}
FORM;
?>
</body>
</html>