<?php

class Update {
	
	public $sql;
	public $maj = array();
	public $version = 0;
	public $errors = array();

	function __construct($sql) {
		$this->sql = $sql;
		$q = <<<SQL
SELECT valeur FROM dt_infos WHERE champ = 'version'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		if ($row === false) {
			$q = <<<SQL
INSERT INTO dt_infos (champ, valeur) VALUES ('version', '0')
SQL;
			$this->sql->query($q);
			$this->version = 0;
		}
		else {
			$this->version = $row['valeur'];
		}

		$functions = get_defined_functions();
		foreach ($functions['user'] as $function) {
			if (preg_match("/update_(\d+)/", $function, $matches)) {
				$this->maj[$matches[1]] = $function;
			}
		}
	}

	function execute($version_limite = null) {
		$nouvelle_version = $this->version;
		ksort($this->maj);
		foreach ($this->maj as $version => $function) {
			if ($version > $this->version and ($version_limite === null or $version <= $version_limite)) {
				$nouvelle_version = $version;
				try {
					$function($this);
				}
				catch (Exception $e) {
					$this->errors[$version] = $e->getMessage();
				}
			}
		}
		if (!isset($this->errors[$nouvelle_version])) {
			$q = <<<SQL
UPDATE dt_infos SET valeur = '$nouvelle_version' WHERE champ = 'version'
SQL;
			$this->sql->query($q);
			$this->version = $nouvelle_version;
		}
	}

	function last_version() {
		return max(array_keys($this->maj));
	}

	function versions() {
		$versions = array_keys($this->maj);
		sort($versions);
		array_unshift($versions, 0);

		return $versions;
	}
}
