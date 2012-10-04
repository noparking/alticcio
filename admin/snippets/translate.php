<?php
global $config, $phrase, $traduction, $form, $displayed_lang, $not_all_traductions;

$langues = $phrase->langues(array('key' => "code_langue", 'value' => "code_langue"));
$langue = $config->get('langue');
$displayed_lang = array($langue => " (".$langues[$langue].")");
if ($lang = $traduction) {
	if ($lang == 'all') {
		foreach ($langues as $key => $value) {
			$displayed_lang[$key] = " ($value)"; 
		}
	} elseif ($lang != $langue) {
		$displayed_lang[$lang] = " (".$langues[$lang].")";
	}
}
else {
	$displayed_lang[$langue] = " (".$langues[$langue].")";
}

if (isset($not_all_traductions) and $not_all_traductions) {
	$traductions = $langues;
}
else {
	$traductions = array_merge(array("all" => "Tout"), $langues);
}
$traduction = isset($traduction) ? $traduction : $langue;

echo <<<HTML
<div id="bloc_trad">
{$form->select(array('name' => "lang", 'label' => "Traduire en", 'options' => $traductions, 'value' => $traduction, 'template' => "#{label} : #{field}"))}
{$form->input(array('type' => "submit", 'name' => "translate", 'value' => "Traduire"))}
</div>
HTML;
