<?php

global $page, $attribut, $attribut_id, $phrase, $form, $config, $displayed_lang, $id_langues;

$page->css[] = $config->media("jquery-ui.min.css");
$page->css[] = $config->media("ui.multiselect.css");
$page->javascript[] = $config->media("jquery-ui.min.js");
$page->javascript[] = $config->media("ui.multiselect.js");
$page->post_javascript["multiselect"] = <<<JAVASCRIPT
$(document).ready(function() {
	$(".multiselect").multiselect();
});
JAVASCRIPT;

$attribut->load($attribut_id);
list($label) = $phrase->get(array($attribut->values['phrase_nom']));
$unit = $attribut->attr("unite");

echo $form->input(array('name' => "types_attributs[".$attribut_id."]", 'type' => "hidden", 'forced_value' => $attribut->type_attribut));

switch ($attribut->type_attribut) {
	case 'choice' :
		$options = array(0 => "N/A", 1 => "Oui", 2 => "Non");
		echo $form->radios(array('name' => "attributs[".$attribut_id."][0]", 'options' => $options, 'label' => $label[$config->get('langue')]));
		break;
	case 'mark' :
		$options = array(0 => "N/A", 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
		echo $form->radios(array('name' => "attributs[".$attribut_id."][0]", 'options' => $options, 'label' => $label[$config->get('langue')]));
		break;
	case 'text' :
		echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'type' => "hidden"));
		echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."][0]", 'label' => $label[$config->get('langue')], 'items' => $displayed_lang));
		break;
	case 'textarea' :
		echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'type' => "hidden"));
		echo $form->textarea(array('name' => "phrases[valeurs_attributs][".$attribut_id."][0]", 'label' => $label[$config->get('langue')], 'items' => $displayed_lang));
		break;
	case 'number' :
		echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'label' => $label[$config->get('langue')], 'unit' => $unit));
		break;
	case 'select' :
		$options = array();
		$phrase_ids = array();
		foreach ($attribut->options() as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($attribut->options() as $option) {
			$options[$option['phrase_option']] = isset($phrases_options[$option['id']][$config->get('langue')]) ? $phrases_options[$option['id']][$config->get('langue')] : "";
		}
		echo $form->select(array('name' => "attributs[".$attribut_id."][0]", 'options' => $options, 'label' => $label[$config->get('langue')], 'unit' => $unit));
		echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."][0]", 'type' => "hidden", 'forced_value' => 1));
		break;
	case 'multiselect' :
		$options = array();
		$phrase_ids = array();
		foreach ($attribut->options() as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($attribut->options() as $option) {
			$options[$option['phrase_option']] = $phrases_options[$option['id']][$config->get('langue')];
		}
		echo $form->select(array('name' => "attributs[".$attribut_id."][]", 'options' => $options, 'label' => $label[$config->get('langue')], 'multiple' => true));
		echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."][]", 'type' => "hidden", 'forced_value' => 1));
		break;
	case 'reference' :
		$options = $attribut->reference_options($id_langues);
		echo $form->select(array('name' => "attributs[".$attribut_id."][0]", 'options' => $options, 'label' => $label[$config->get('langue')], 'unit' => $unit));
		break;
	case 'readonly' :
		$valeurs = $attribut->valeurs();
		if ($valeurs['type_valeur'] == 'phrase_valeur') {
			echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'type' => "hidden", 'forced_value' => $valeurs['phrase_valeur']));
			echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."][0]", 'label' => $label[$config->get('langue')], 'readonly' => true, 'items' => $displayed_lang));
		}
		else {
			echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'label' => $label[$config->get('langue')], 'readonly' => true, 'forced_value' => $valeurs['valeur_numerique']));
		}
		break;
	case 'multitext' :
		$options = $attribut->options();
		$phrase_ids = array();
		foreach ($options as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($options as $option) {
			echo $form->input(array('name' => "attributs[".$attribut_id."][".$option['classement']."]", 'type' => "hidden"));
			echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."][".$option['classement']."]", 'label' => $label[$config->get('langue')]." (".$phrases_options[$option['id']][$config->get('langue')].")", 'items' => $displayed_lang));
		}
		break;
	case 'multifreevalue' :
	case 'multinumber' :
		$options = $attribut->options();
		$phrase_ids = array();
		$classements = array();
		foreach ($options as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
			$classements[$option['id']] = $option['classement'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($options as $option) {
			echo $form->input(array('name' => "attributs[".$attribut_id."][".$option['classement']."]", 'label' => $label[$config->get('langue')]." (".$phrases_options[$option['id']][$config->get('langue')].")"));
		}
		break;
	case 'freevalue' :
	default :
		echo $form->input(array('name' => "attributs[".$attribut_id."][0]", 'label' => $label[$config->get('langue')]));
}
