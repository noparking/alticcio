<?php

class AttributManagement {
	
	public $sql;
	public $phrase;
	public $langue;
	public $attributs_table;

	public function __construct($sql, $attributs_table, $phrase = null, $langue = 1) {
		$this->sql = $sql;
		$this->phrase = $phrase;
		$this->langue = $langue;
		$this->attributs_table = $attributs_table;
	}

	public function all_attributs($filter = null) {
		if ($filter === null) {
			$filter = $this->sql;
		}
		$application_id = isset($this->id) ? $this->id : 0;
		$q = <<<SQL
SELECT DISTINCT(a.id), p.phrase AS name, ma.groupe, ma.classement FROM dt_attributs AS a
LEFT OUTER JOIN dt_phrases AS p ON p.id = a.phrase_nom AND p.id_langues = {$this->langue}
LEFT OUTER JOIN {$this->attributs_table}_management AS ma ON ma.id_attributs = a.id
SQL;
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function groupes() {
		$q = <<<SQL
SELECT ga.id, p.phrase AS nom FROM dt_groupes_attributs AS ga
LEFT OUTER JOIN dt_phrases AS p ON p.id = ga.phrase_nom AND p.id_langues = {$this->langue}
SQL;
		$res = $this->sql->query($q);
		$groupes = array();
		while ($row = $this->sql->fetch($res)) {
			$groupes[$row['id']] = $row['nom'];
		}
		
		return $groupes;
	}
}
