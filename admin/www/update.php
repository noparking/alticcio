<?php

header('Content-type: text/html; charset=UTF-8');

include dirname(__FILE__)."/../includes/config.php";

$config->core_include("outils/mysql", "outils/update");
$sql = new Mysql($config->db());
$update = new Update($sql);

include dirname(__FILE__)."/../includes/update.inc.php";

$update->execute();

echo "Mise à jour à la version {$update->version}";

