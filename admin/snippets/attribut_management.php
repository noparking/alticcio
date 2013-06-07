<?php
global $sql, $page, $dico, $form, $config, $phrase, $id_langues, $pager,
	   $filter, $object, $pager_attributs_management, $filter_attributs_management,
	   $attribut_management_selected, $attribut_management_filter_pager_name;

$config->core_include("produit/attribut_management");
$attribut_management = new AttributManagement($sql, $object, $phrase, $id_langues);

$groupes_options = $groupes = $attribut_management->groupes();
array_unshift($groupes_options, "");

$pager = $pager_attributs_management = new Pager($sql, array(10, 30, 50, 100, 200), "pager_".$attribut_management_filter_pager_name);
$filter = $filter_attributs_management = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
	),
	'name' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
	'groupe' => array(
		'title' => $dico->t('Groupe'),
		'type' => 'select',
		'options' => $groupes,
		'field' => 'ma.groupe',
		'form' => array(
			'name' => "attributs_management[%id%][groupe]",
			'method' => 'select',
			'options' => $groupes_options,
			'template' => '#{field}',
		),
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'ma.classement',
		'form' => array(
			'name' => "attributs_management[%id%][classement]",
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
			'class' => "input-text-numeric",
		),
	),
), array(), "filter_".$attribut_management_filter_pager_name, true);

if (isset($attribut_management_selected)) {
	$filter->select($attribut_management_selected);
}
$attribut_management->all_attributs($filter);
echo $page->inc("snippets/filter-form");

foreach ($filter->selected() as $selected_attribut) {
	echo $form->hidden(array('name' => "attributs_management[$selected_attribut][classement]", 'forced_default' => true));
	echo $form->hidden(array('name' => "attributs_management[$selected_attribut][groupe]", 'forced_default' => true));
}
