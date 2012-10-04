<?php
class Mesure {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function get_unites() {
		$q = "SELECT u.id, u.unite  
				FROM dt_unites_mesure AS u
				ORDER BY u.id ";
		$res = $this->sql->query($q);
		$unites = array('...');
		while($row = $this->sql->fetch($res)) {
			$unites[$row['id']] = $row['unite'];
		}
		return $unites;
	}
}
?>