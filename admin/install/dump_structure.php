<?php

$file = dirname(__FILE__)."/install.sql";
`mysqldump -uroot -p -d $argv[1] | sed 's/ AUTO_INCREMENT=[0-9]*\b//' > $file`;
