<?php

$config->core_include("outils/update", "outils/form");

$titre_page = $dico->t("Mise à jour");

$form = new Form(array(
	'id' => "form-edit-update",
	'class' => "form-edit",
	'actions' => array("update"),
));

$update = new Update($sql);

include dirname(__FILE__)."/../includes/update.inc.php";

$message = "";
if ($form->is_submitted()) {
	$data = $form->escaped_values();
	$update->version = $data['version_actuelle'];
	$update->execute($data['nouvelle_version']);

	$message = <<<HTML
<p class="message">Mise à jour à la version {$update->version}</p>
HTML;
}

$form_start = $form->form_start();

$buttons['update'] = $form->input(array('type' => "submit", 'name' => "update",	'value' => $dico->t('Mettre à jour') ));

$versions = array();
foreach ($update->versions() as $version) {
	$versions[$version] = "Version $version";
}

$main = <<<HTML
{$message}
{$form->select(array('name' => "version_actuelle", 'options' =>	$versions, 'label' => "Version actuelle :", 'forced_value' => $update->version))}
{$form->select(array('name' => "nouvelle_version", 'options' =>	$versions, 'label' => "Nouvelle version :", 'forced_value' => $update->last_version()))}
HTML;

$form_end = $form->form_end();

