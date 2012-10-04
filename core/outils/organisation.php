<?php

class Organisation {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function get_liste($langue) {
		$q = "SELECT o.id, ph.phrase FROM dt_organisations_internationales AS o";
		$q .= " LEFT OUTER JOIN dt_phrases as ph ON ph.id = o.phrase_organisations";
		$q .= " LEFT OUTER JOIN dt_langues as lg ON lg.id = ph.id_langues";
		$q .= " WHERE lg.code_langue = '$langue'";
		$q .= " ORDER BY ph.phrase";
	
		$result = $this->sql->query($q);
		$liste = array('...');
		while ($row = $this->sql->fetch($result)) {
			$liste[$row['id']] = $row['phrase'];
		}
		return $liste;
	}
	
}

?>