<?php

$init_dir = dirname(__FILE__)."/init";

$database = $argv[1];
$table = $argv[2];

class Mysql extends Mysqli {
	
	public function __construct() {
		parent::__construct("localhost", "root", "", "doublet_api_test");
		$this->set_charset("utf8");
	}

	public function fetch($result) {
		return $result->fetch_assoc();
	}
}

$sql = new Mysql();

$res = $sql->query("SELECT * FROM $table");

$data = array();
while ($row = $sql->fetch($res)) {
	$record = array();
	foreach ($row as $field => $value) {
		if (strpos($field, "phrase_") === 0) {
			$res2 = $sql->query("SELECT phrase, id_langues FROM dt_phrases WHERE id = $value");
			$phrases = array();
			while ($row2 = $sql->fetch($res2)) {
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
