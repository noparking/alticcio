<?php

$config->core_include("outils/mysql", "outils/filter", "outils/pager");
$page->css[] = $config->media("filter_demo.css");

$menu->current("main/params/demofilter");

$sql = new Mysql($config->db());
$lang = $config->get('langue');

$q = <<<SQL
SELECT p.id, ph.phrase AS nom, p.code_iso, p.code_ultralog, p.id_continents, p.niveau_priorite, p.num_serie FROM dt_pays AS p
INNER JOIN dt_phrases AS ph ON p.phrase_nom = ph.id INNER JOIN dt_langues AS l ON ph.id_langues = l.id AND l.code_langue = '$lang'
SQL;

$pager = new Pager($sql, array(20, 30, 50, 100, 200));
$filter = new Filter($pager, array(
	'id' => array(
		'field' => 'p.id',
		'title' => 'ID',
		'type' => 'between',
		'order' => 'ASC',
	),
	'nom' => array(
		'field' => 'ph.phrase',
		'title' => $dico->t('Nom'),
		'type' => 'contain',
	),
	'code_iso' => array(
		'title' => 'Code ISO',
	),
	'code_ultralog' => array(
		'title' => 'Code Ultralog',
	),
	'id_continents' => array(
		'title' => 'ID Continent',
	),
	'niveau_priorite' => array(
		'title' => 'Priorité',
		'type' => 'select',
		'options' => array(1 => 1, 2 => 2, 3 => 3),
	),
	'num_serie' => array(
		'title' => 'Numéro série',
	),
));
$res = $filter->query($q);
$filter->fetchall($res);
$titre_page = "Demo filtre";
$num_partie = "demofilter";
$right = "";
$main = <<<HTML
<form action="" method="post">
{$page->inc("snippets/filter")}
</form>
HTML;

?>
