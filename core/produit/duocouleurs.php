<?php
class Duocouleurs {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function get_liste($lang) {
		$q = "SELECT c.id, c.image_duo, p.phrase  
				FROM dt_duo_couleurs AS c
				INNER JOIN dt_phrases AS p
				ON p.id = c.phrase_nom
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