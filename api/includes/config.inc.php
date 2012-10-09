<?php

$dirs_names = array();
$rep_sites = dirname(__FILE__).'/../../../api/sites/';
$dir = opendir($rep_sites);
while ($dirname = readdir($dir)) {
	if (!in_array($dirname, array('.', '..', 'default')) && is_dir($rep_sites.$dirname)) {
		$dirs_names[] = $dirname;
	}
}
closedir($dir);
$pos_starts = array();
$pos_ends = array();
foreach ($dirs_names as $name) {
	$uri = str_replace("/", ".", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	if (($pos_start = strpos($uri, $name)) !== false) {
		$pos_end = $pos_start + strlen($name);
		$pos_starts[$name] = $pos_start;
		$pos_ends[$name] = $pos_end;
	}
}
$selected_name = "";
if (count($pos_starts) > 0) {
	$max_pos_end = max($pos_ends);
	foreach ($pos_ends as $name => $pos_end) {
		if ($pos_end == $max_pos_end) {
			if ($selected_name == "" or $pos_starts[$name] < $pos_starts[$selected_name]) {
				$selected_name = $name;
			}
		}
	}
}

$default_config = array();
if (file_exists($rep_sites.'default/config.php')) {
	include $rep_sites.'default/config.php';
	$default_config = $config;
}
if (file_exists($rep_sites.$selected_name.'/config.php')) {
	include $rep_sites.$selected_name.'/config.php';
	$config += $default_config;
}
if (file_exists($rep_sites.'global/config.php')) {
	include $rep_sites.'global/config.php';
}
