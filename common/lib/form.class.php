<?php

class Form {
	public $fields;
	public $checks;
	public $defaults;

	public $name;
	public $label;
	public $value;
	public $checked;

	function __construct() {

	}

	function fill() {

	}

	function reset() {

	}

	function get() {
# sans aucun paramètre, retourne tout le tableau des valeurs
# les checkbox doivent bien être à false
# on part de $this->fields pour complèter à null les valeurs non settée
	}

	function key() {
		#première clé de $this->get(avec les mêmes params)
	}

	function control($name) {
		if ($name) {
			$this->name = $name;
			$this->label = $this->fields[$name][0];
			$this->value = ""; #TODO
			$this->checked = ""; #TODO
		}

		return "";
	}

	function name($name = null) {
		$this->control($name);

		return $this->name;
	}

	function label($name = null {
		$this->control($name);

		return $this->label;
	}

	function value($name = null) {
		$this->control($name);
		
		return $this->value;
	}
	
	function checked($name = null) {
		$this->control($name);

		return $this->checked;
	}
	
	function selected($value) {
# TODO à revoir
		return in_array($this->flat_get($this->name), $value) ? "selected" : "";
	}

	function check() {
		$report = array('ok' => true);
		foreach (array_keys($this->checks) as $check) {
			$report['checks'][$check]['ok'] = true;
			$report['checks'][$check]['ok_fields'] = array();
			$report['checks'][$check]['ko_fields'] = array();
		}
		foreach ($this->fields as $name => $params) {
			$this->control($name);
			$report['fields'][$name]['ok'] = true;
			$report['fields'][$name]['ok_ckecks'] = array();
			$report['fields'][$name]['ko_checks'] = array();
			foreach (array_slice($params, 1) as $ckeck) {
				$func = $this->checks[$check];
				if ($func($this)) {
					$report['fields'][$name]['checks'][$ckeck] = true;
					$report['checks'][$check]['fields'][$name] = true;
					$report['fields'][$name]['ok_ckecks'][] = $check;
					$report['checks'][$check]['ok_fields'][] = $name;
				}
				else {
					$report['fields'][$name]['checks'][$ckeck] = false;
					$report['checks'][$check]['fields'][$name] = false;
					$report['fields'][$name]['ko_ckecks'][] = $check;
					$report['checks'][$check]['ko_fields'][] = $name;
					$report['fields'][$name]['ok'] = false;
					$report['checks'][$check]['ok'] = false;
					$report['ok'] = false;
				}
			}
		}

		return $report;
	}
}
