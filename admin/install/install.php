<?php

$_SERVER['HTTP_HOST'] = "";
$_SERVER['REQUEST_URI'] = $argv[1];

$dump_file = dirname(__FILE__)."/install.sql";

if (!file_exists($dump_file)) {
	die("No SQL file for installation...");
}

include dirname(__FILE__)."/../includes/config.php";

$config->core_include("outils/mysql");

$q = file_get_contents($dump_file);

$sql->query($q);

