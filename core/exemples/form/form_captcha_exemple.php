<?php

include "../../outils/form.php";

$form = new Form(array(
	'id' => "myform",
	'class' => "formclass",
	'captcha' => array('mycaptcha'),
));
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
// Affiche le formulaire
echo <<<FORM
{$form->form_start()}
{$form->fieldset_start("Etape 1")}
{$form->captcha(array('type' => "hidden", 'name' => "champ-cache", 'value' => "truc"))}
{$form->input(array('type' => "submit", 'name' => "valider", 'value' => "Valider", 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->form_end()}
FORM;
?>
</body>
</html>