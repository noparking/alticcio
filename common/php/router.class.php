<?php

#TODO tests à corriger

class Router {

	public $routes = array();
	public $data = array();
	public $prefixes = array();
	public $vars = array();

	function __construct($routes, $data = array()) {
		$this->routes = $routes;
		$this->data = $data;
	}

	function route() {
		foreach ($this->routes as $route) {
			$go = true;
			$this->vars = array();
			foreach ($route as $key => $pattern) {
				if (isset($this->data[$key])) {
					if (isset($this->prefixes[$key])) {
						$pattern = $this->prefixes[$key].$pattern;
					}
					if (!$this->match($this->data[$key], $pattern)) {
						$go = false;
						break;
					}
				}
			}
			if ($go) {
				return $route;
			}
		}
	}

	function match($value, $pattern) {
		$pattern = str_replace(".", "\.", $pattern);
		$pattern = str_replace("*", ".*", $pattern);
		preg_match_all("!\[([^\]]+)\]!", $pattern, $matches);
		$vars = array();
		foreach ($matches[1] as $var) {
			$explode = explode("=", $var, 2);
			$var_name = $explode[0];
			$var_value = isset($explode[1]) ? $explode[1] : "[^/]+";
			$pattern = str_replace("[$var]", "($var_value)", $pattern);
			$vars[] = $var_name;
		}
		if (preg_match("!^$pattern$!", $value, $matches)) {
			foreach ($vars as $index => $var) {
				$this->vars[$var] = $matches[$index + 1];
			}
			return true;
		}

		return false;
	}
}
