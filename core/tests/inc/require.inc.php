<?php

require_once dirname(__FILE__)."/../simpletest/autorun.php";
require_once dirname(__FILE__)."/simpletest_table_tester.php";

include dirname(__FILE__)."/../../../admin/includes/config.inc.php";

$config['db_database'] = "alticcio_test";

include dirname(__FILE__)."/../../outils/config.php";

$config = new Config();

$config->set("base_path_alticcio", dirname(__FILE__)."/../../../admin");

$config->core_include("outils/mysql");
