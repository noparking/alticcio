<?php

$config->core_include("outils/update", "outils/form");

$titre_page = $dico->t("Mise à jour");

$form = new Form(array(
	'id' => "form-edit-update",
	'class' => "form-edit",
	'actions' => array("update"),
));

include dirname(__FILE__)."/../includes/update.inc.php";

$update = new Update($sql);

$message = "";
if ($form->is_submitted()) {
	$data = $form->escaped_values();
	$update->version = $data['version_actuelle'];
	$update->execute($data['nouvelle_version']);
	$svn_infos = $update->svn_up($config->get('svn'));

	$message = <<<HTML
<p class="message">Mis à jour à la version {$update->version}</p>
HTML;
	if ($svn_infos) {
		$message .= <<<HTML
<pre><p class="message">{$svn_infos}</p></pre>
HTML;
	}

	if (count($update->errors)) {
		foreach ($update->errors as $version => $error) {
		$message .= <<<HTML
<p class="message_error"><strong>Update $version:</strong> $error</p>
HTML;
		}
	}
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

