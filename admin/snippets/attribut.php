<?php

global $attribut, $attribut_id, $phrase, $form, $config, $displayed_lang, $id_langue;

$attribut->load($attribut_id);
list($label) = $phrase->get(array($attribut->values['phrase_nom']));
$unit = $attribut->attr("unite");
switch ($attribut->type_attribut) {
	case 'choice' :
		$options = array(0 => "N/A", 1 => "Oui", 2 => "Non");
		echo $form->radios(array('name' => "attributs[".$attribut_id."]", 'options' => $options, 'label' => $label[$config->get('langue')]));
		break;
	case 'mark' :
		$options = array(0 => "N/A", 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
		echo $form->radios(array('name' => "attributs[".$attribut_id."]", 'options' => $options, 'label' => $label[$config->get('langue')]));
		break;
	case 'text' :
		echo $form->input(array('name' => "attributs[".$attribut_id."]", 'type' => "hidden"));
		echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."]", 'label' => $label[$config->get('langue')], 'items' => $displayed_lang));
		break;
	case 'textarea' :
		echo $form->input(array('name' => "attributs[".$attribut_id."]", 'type' => "hidden"));
		echo $form->textarea(array('name' => "phrases[valeurs_attributs][".$attribut_id."]", 'label' => $label[$config->get('langue')], 'items' => $displayed_lang));
		break;
	case 'number' :
		echo $form->input(array('name' => "attributs[".$attribut_id."]", 'label' => $label[$config->get('langue')], 'unit' => $unit));
		break;
	case 'select' :
		$options = array();
		$phrase_ids = array();
		foreach ($attribut->options() as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($attribut->options() as $option) {
			$options[$option['phrase_option']] = $phrases_options[$option['id']][$config->get('langue')];
		}
		echo $form->select(array('name' => "attributs[".$attribut_id."]", 'options' => $options, 'label' => $label[$config->get('langue')], 'unit' => $unit));
		echo $form->input(array('name' => "phrases[valeurs_attributs][".$attribut_id."]", 'type' => "hidden", 'forced_value' => 1));
		break;
	case 'multiselect' :
		$options = array();
		$phrase_ids = array();
		foreach ($attribut->options() as $option) {
			$phrase_ids[$option['id']] = $option['phrase_option'];
		}
		$phrases_options = $phrase->get($phrase_ids);
		foreach ($attribut->options() as $option) {
			$options[$option['classement']] = $phrases_options[$option['id']][$config->get('langue')];
		}
		echo $form->select(array('name' => "attributs[".$attribut_id."]", 'options' => $options, 'label' => $label[$config->get('langue')]));
		break;
	case 'reference' :
		$options = $attribut->reference_options($id_langue);
		echo $form->select(array('name' => "attributs[".$attribut_id."]", 'options' => $options, 'label' => $label[$config->get('langue')], 'unit' => $unit));
		break;
	default :
		echo $form->input(array('name' => "attributs[".$attribut_id."]", 'label' => $label[$config->get('langue')]));
		break;
}
