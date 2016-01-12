<?php

class Stats {
	private $sql;

	function __construct($sql) {
		$this->sql = $sql;
	}

	function log($from, $type, $item, $ip = "") {
		$date = time();
		$ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
		$q = <<<SQL
INSERT INTO dt_statistiques (`from`, `type`, item, date_requete, ip)
VALUES ('$from', '$type', $item, $date, '$ip')
SQL;
		$this->sql->query($q);
	}
}
