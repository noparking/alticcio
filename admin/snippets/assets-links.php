<?php
global $sql, $page, $dico, $form, $config, $phrase, $id_langues, $pager, $filter,
	   $asset, $asset_links,
	   $pager_assets_gamme, $filter_assets_gamme,
	   $pager_assets_produit, $filter_assets_produit,
	   $pager_assets_sku, $filter_assets_sku;

$link_type = $vars['link_type'];

if ($link_type == "sku") {
	$field_ref = "ref_ultralog";
	$field_nom = "phrase_ultralog";
}
else {
	$field_ref = "ref";
	$field_nom = "phrase_nom";
}

$pager_name = "pager_assets_".$link_type;
$filter_name = "filter_assets_".$link_type;
$pager = $$pager_name = new Pager($sql, array(10, 30, 50, 100, 200), $pager_name);
$filter = $$filter_name = new Filter($pager, array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'al.link_id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
		'type' => 'contain',
		'field' => "j.$field_ref",
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'p.phrase',
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'al.classement',
		'form' => array(
			'name' => "asset_links[{$link_type}][%id%][classement]",
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
			'class' => "input-text-numeric",
		),
	),
), array(), $filter_name, true);

if (isset($asset_links[$link_type])) {
	$filter->select(array_keys($asset_links[$link_type]));
}
$method = "all_links_{$link_type}";
$asset->$method($filter);
echo $page->inc("snippets/filter-form");

