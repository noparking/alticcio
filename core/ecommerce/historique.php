<?php

class Historique {
	
	var $historique;

	public function __construct($historique) {
		$this->historique = $historique;
		if (!isset(	$_SESSION['historiques'])) {
			$_SESSION['historiques'] = array();
		}
		if (!isset(	$_SESSION['historiques'][$historique])) {
			$_SESSION['historiques'][$historique] = array();
		}
	}

	public function store($item) {
		$_SESSION['historiques'][$this->historique] = array_diff($_SESSION['historiques'][$this->historique], array($item));
		$_SESSION['historiques'][$this->historique][] = $item;
	}

	public function get($number) {
		return array_reverse(array_slice($_SESSION['historiques'][$this->historique], -$number, $number));
	}
}
