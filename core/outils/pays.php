<?php

class Pays {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function liste($langue, $option='') {
		$q = <<<SQL
SELECT p.code_iso, p.id, ph.phrase FROM dt_pays AS p
LEFT OUTER JOIN dt_phrases as ph ON ph.id = p.phrase_nom
LEFT OUTER JOIN dt_langues as lg ON lg.id = ph.id_langues
WHERE lg.code_langue = '$langue'
ORDER BY ph.phrase
SQL;
		$res = $this->sql->query($q);
		
		$liste = array('...');
		while ($row = $this->sql->fetch($res)) {
			if (!empty($option)) {
				$liste[$row[$option]] = $row['phrase'];
			}
			else {
				$liste[$row['id']] = $row['phrase'];
			}
		}
		return $liste;
	}
	
	public function get_id($where, $value) {
		$q = "SELECT id FROM dt_pays WHERE $where = '$value'";	
		$result = $this->sql->query($q);
		$row = $this->sql->fetch($result);
		
		return $row['id'];
	}
	
	public function codes_ultralog() {
		$result = $this->sql->query("SELECT code_iso, code_ultralog FROM dt_pays");
		$liste = array();
		while ($row = $this->sql->fetch($result)) {
			$liste[$row['code_iso']] = $row['code_ultralog'];
		}
		return $liste;
	}
}

?>
