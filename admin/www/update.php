<?php

header('Content-type: text/html; charset=UTF-8');

include dirname(__FILE__)."/../includes/config.php";

include dirname(__FILE__)."/../includes/update.inc.php";

$config->core_include("outils/mysql", "outils/update");
$sql = new Mysql($config->db());
$update = new Update($sql);

$update->execute();

echo "Mise à jour à la version {$update->version}";

