<?php

class Form {
	public $id;

	public $name;
	public $label;
	public $value;
	
	private $fields = array();
	private $fields_falses = array();
	private $defaults = array();
	private $values = array();
	private $checks = array();
	private $attributes = array();

	function __construct($id) {
		$this->id = $id;
		if (isset($_SESSION['form'][$this->id])) {
			$this->values = $_SESSION['form'][$this->id];
		}
	}

	function set($array) {
		$this->values = $_SESSION['form'][$this->id] = array_replace_recursive($this->values, $array);
	}

	function reset() {
		$this->values = $_SESSION['form'][$this->id] = array();
	}

	function get() {
		return array_replace_recursive($this->fields_falses, $this->defaults, $this->values);
	}
	
	function val($name) {
		return $this->get_in_array($name, $this->get());
	}

	function key($name) {
		return key($this->val($name));
	}

	function fields($fields) {
		foreach ($fields as $field => $data) {
			$this->fields[$field] = $data;
			$this->set_in_array($field, false, $this->fields_falses);
		}
	}

	function defaults($defaults) {
		return $this->defaults = array_replace_recursive($this->defaults, $defaults);
	}

	function control($name) {
		if ($name) {
			$this->name = $name;
			$this->label = isset($this->fields[$name]) ? (is_array($this->fields[$name]) ? $this->fields[$name][0] : $this->fields[$name]) : "";
			$this->value = $this->val($name);
			$this->checked = $this->value ? "checked" : "";
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

	function select($option) {
		if ($option) {
			$this->option = $option;
			$value = $this->val($this->name);
			if (is_array($value)) {
				$this->selected = in_array($option, $value) ? "selected" : "";
			}
			else {
				$this->selected = $option == $value ? "selected" : "";
			}
		}

		return "";
	}
	
	function selected($option = null) {
		$this->select($option);

		return $this->selected;
	}

	function option($option = null) {
		$this->select($option);

		return $this->option;
	}

	function checks($checks) {
		return $this->checks = array_replace_recursive($this->checks, $checks);
	}

	function attr($key, $value = null) {
		if ($value !== null) {
			$this->attributes[$this->name][$key] = $value;
		}

		return isset($this->attributes[$this->name][$key]) ? $this->attributes[$this->name][$key] : "";
	}

	function validate($data = null) {
		if ($data === null) {
			$data = $this->get();
		}
		$report = array(
			'ok' => true,
			'ok_checks' => array(),
			'ko_checks' => array(),
			'ok_fields' => array(),
			'ko_fields' => array(),
		);
		foreach (array_keys($this->checks) as $check) {
			$report['checks'][$check]['ok'] = true;
			$report['checks'][$check]['fields'] = array();
			$report['checks'][$check]['ok_fields'] = array();
			$report['checks'][$check]['ko_fields'] = array();
		}
		foreach ($this->fields as $name => $params) {
			$this->control($name);
			$report['fields'][$name]['ok'] = true;
			$report['fields'][$name]['checks'] = array();
			$report['fields'][$name]['ok_checks'] = array();
			$report['fields'][$name]['ko_checks'] = array();
			if (is_array($params)) {
				$report['fields'][$name]['label'] = $params[0];
				foreach (array_slice($params, 1) as $check) {
					$func = $this->checks[$check];
					if ($func($this->get_in_array($name, $data), $this)) {
						$report['fields'][$name]['checks'][$check] = true;
						$report['checks'][$check]['fields'][$name] = true;
						$report['fields'][$name]['ok_checks'][] = $check;
						$report['checks'][$check]['ok_fields'][] = $name;
					}
					else {
						$report['fields'][$name]['checks'][$check] = false;
						$report['checks'][$check]['fields'][$name] = false;
						$report['fields'][$name]['ko_checks'][] = $check;
						$report['checks'][$check]['ko_fields'][] = $name;
						$report['fields'][$name]['ok'] = false;
						$report['checks'][$check]['ok'] = false;
						$report['ok'] = false;
					}
				}
			}
			else {
				$report['fields'][$name]['label'] = $params;
			}
		}
		foreach ($report['checks'] as $check => $check_data) {
			if ($check_data['ok']) {
				$report['ok_checks'][] = $check;
			}
			else {
				$report['ko_checks'][] = $check;
			}
		}
		foreach ($report['fields'] as $field => $field_data) {
			if ($field_data['ok']) {
				$report['ok_fields'][] = $field;
			}
			else {
				$report['ko_fields'][] = $field;
			}
		}

		return $report;
	}

	protected function set_in_array($name, $value, &$array) {
		if (preg_match("/([^\[]*)\[([^\]]*)\](.*)/", $name, $matches)) {
			$this->set_in_array($matches[2].$matches[3], $value, $array[$matches[1]]);
		}
		else {
			$array[$name] = $value;
		}
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
