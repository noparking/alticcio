<?php

class Form {
	public $fields;
	//public $checks;
	public $defaults = array();
	public $values = array();

	public $name;
	public $label;
	public $value;
	public $checked;

	private $form_id;

	function __construct($form_id) {
		$this->form_id = $form_id;
		if (isset($_SESSION['form'][$this->form_id])) {
			$this->values = $_SESSION['form'][$this->form_id];
		}
	}

	function set($array) {
		$this->values = $_SESSION['form'][$this->form_id] = array_replace_recursive($this->values, $array);
	}

	function reset() {
		$this->values = $_SESSION['form'][$this->form_id] = array();
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
			$this->label = isset($this->fields[$name]) ? (is_array($this->fields[$name]) ? $this->fields[$name][0] : $this->fields[$name]) : "";
			$this->value = $this->get_value($name);
			$this->checked = $this->get_value($name) ? "checked" : "";
		}

		return "";
	}

	function name($name = null) {
		$this->control($name);

		return $this->name;
	}

	function label($name = null) {
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

	protected function get_value($name) {
		$value = $this->get_in_array($name, $this->values);
		
		if ($value === null) {
			$value = $this->get_in_array($name, $this->defaults);
		}

		return (string)$value;
	}

	protected function get_in_array($name, $array) {
		if (preg_match("/([^\[]*)\[([^\]]*)\](.*)/", $name, $matches)) {
			if (isset($array[$matches[1]])) {
				return $this->get_in_array($matches[2].$matches[3], $array[$matches[1]]);
			}
		}
		else if (isset($array[$name])) {
			return $array[$name];
		}

		return null;
	}
}
