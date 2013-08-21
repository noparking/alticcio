<?php

$init_dir = dirname(__FILE__)."/init";

$database = $argv[1];
$table = $argv[2];

mysql_connect("localhost", "root", "");
mysql_set_charset("utf8");
mysql_select_db($database);

$res = mysql_query("SELECT * FROM $table");

$data = array();
while ($row = mysql_fetch_assoc($res)) {
	$record = array();
	foreach ($row as $field => $value) {
		if (strpos($field, "phrase_") === 0) {
			$res2 = mysql_query("SELECT phrase, id_langues FROM dt_phrases WHERE id = $value");
			$phrases = array();
			while ($row2 = mysql_fetch_assoc($res2)) {
				if ($row2['phrase']) {
					$phrases[$row2['id_langues']] = $row2['phrase'];
				}
			}
			if (count($phrases)) {
				$record[$field] = $phrases;
			}
		}
		else {
			$record[$field] = $value;
		}
	}
	$data[] = $record;
}

if (count($data) {
	$table_content = var_export($data, true);

	$content = <<<PHP
<?php

\$$table = $table_content;
PHP;

	file_put_contents("$init_dir/$table.php", $content);
}
