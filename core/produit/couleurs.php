<?php
class Couleurs {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function get_liste($lang) {
		$q = "SELECT c.id, c.hexadecimal, p.phrase  
				FROM dt_couleurs AS c
				INNER JOIN dt_phrases AS p
				ON p.id = c.phrase_couleurs
				INNER JOIN dt_langues AS l
				ON l.id = p.id_langues
				AND p.id_langues = $lang
				ORDER BY p.phrase ";
		$res = $this->sql->query($q);
		$colors = array('...');
		while($row = $this->sql->fetch($res)) {
			$colors[$row['id']] = $row['phrase'];
		}
		return $colors;
	}
}
?>