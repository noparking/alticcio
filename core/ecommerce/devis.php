<?php

class DevisPose {
	
	public $sql;

	function __construct($sql) {
		$this->sql = $sql;
	}

	function save($data) {
		$values = array();
		$num_commande = (int)$data['num_commande'];
		$num_devis = (int)$data['num_devis'];
		$type_pose = $data['type_pose'];
		$date_creation = time();
		foreach ($data['data'] as $champ => $valeur) {
			$values[] = "($num_commande, $num_devis, '$type_pose', '$champ', '$valeur', $date_creation)";
		}
		$values = implode(",", $values);
		$q = <<<SQL
INSERT INTO dt_devis_pose (num_commande, num_devis, type_pose, champ, valeur, date_creation)
VALUES $values
SQL;
		$this->sql->query($q);
	}

	function load($num_commande, $num_devis, $type_pose) {
		$num_commande = (int)$num_commande;
		$num_devis = (int)$num_devis;
		$type_pose = addslashes($type_pose);
		$q = <<<SQL
SELECT date_creation, champ, valeur FROM dt_devis_pose WHERE num_commande = $num_commande AND num_devis = $num_devis AND type_pose = '$type_pose'
SQL;
		$res = $this->sql->query($q);
		$data = array(
			'num_commande' => $num_commande,
			'num_devis' => $num_devis,
			'type_pose' => $type_pose,
		);
		$found = false;
		while ($row = $this->sql->fetch($res)) {
			$data['data'][$row['champ']] = $row['valeur'];
			$data['date_creation'] = $row['date_creation'];
			$found = true;
		}

		return $found ? $data : false;
	}
}
