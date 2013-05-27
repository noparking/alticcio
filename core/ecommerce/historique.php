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
		$key = md5(serialize($item));
		unset($_SESSION['historiques'][$this->historique][$key]);
		$_SESSION['historiques'][$this->historique][$key] = $item;
	}

	public function get($number) {
		return array_reverse(array_slice($_SESSION['historiques'][$this->historique], -$number, $number));
	}
}
