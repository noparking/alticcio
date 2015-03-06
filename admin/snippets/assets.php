<?php
global $sql, $page, $dico, $form, $config, $phrase, $id_langues, $pager, $filter,
	   $url, $pager_assets, $filter_assets, $object, $assets;

$pager = $pager_assets = new Pager($sql, array(10, 30, 50, 100, 200), "pager_{$object->type}_assets");
$filter = $filter_assets = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'a.id',
		'link' => array(
			'href' => $url->make("assets", array('action' => "edit", 'id' => "{value}")),
			'target' => "_blank",
		),
	),
	'titre' => array(
		'title' => $dico->t('Titre'),
		'type' => 'contain',
		'field' => "a.titre",
	),
	'id_types_assets' => array(
		'title' => $dico->t('Type'),
		'type' => 'select',
		'field' => 'a.id_types_assets',
		'options' => $object->types_assets(),
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'al.classement',
		'form' => array(
			'name' => "assets[%id%][classement]",
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
			'class' => "input-text-numeric",
		),
	),
), array(), "filter_{$object->type}_assets", true);


if (isset($assets_selected)) {
	$filter->select($assets_selected);
}
$object->all_assets($filter);
echo $page->inc("snippets/filter-form");

