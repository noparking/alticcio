<?php

include "../../outils/form.php";

$form = new Form(array(
	'id' => "myform",
	'class' => "formclass",
	'actions' => array("envoyer"),
));

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

if ($form->is_submitted()) {
	if (!$form->validate()) {
		echo "<p><b>Erreurs !</b></p>";
		foreach ($form->errors() as $error) {
			echo "<p>$error</p>";
		}
	}
}

// Affiche le formulaire
echo <<<FORM
{$form->form_start()}
{$form->fieldset_start("Formulaire")}
{$form->input(array('name' => "nom", 'label' => "Nom", 'description' => "Votre nom", 'value' => "Nom par défaut"))}
{$form->recaptcha(array('label' => "Captcha", 'description' => "Tappez les mots ci-dessus"))}
{$form->input(array('type' => "submit", 'name' => "envoyer", 'value' => "Envoyer", 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}
FORM;
?>
</body>
</html>