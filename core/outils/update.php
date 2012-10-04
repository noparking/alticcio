<?php

class Update {
	
	public $sql;
	public $maj = array();
	public $version = 0;

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
	}

	function execute($version_limite = null) {
		$nouvelle_version = $this->version;
		ksort($this->maj);
		foreach ($this->maj as $version => $closure) {
			if ($version > $this->version and ($version_limite === null or $version <= $version_limite)) {
				$nouvelle_version = $version;
				$closure($this);
			}
		}
		$q = <<<SQL
UPDATE dt_infos SET valeur = '$nouvelle_version' WHERE champ = 'version'
SQL;
		$this->sql->query($q);
		$this->version = $nouvelle_version;
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
