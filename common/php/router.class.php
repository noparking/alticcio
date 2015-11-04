<?php

class Router {

	public $routes = array();
	public $data = array();
	public $prefixes = array();

	public $vars = array();
	public $stars = array();
	public $route = array();

	function route() {
		$this->route = array();
		$this->vars = array();
		$this->stars = array();
		foreach ($this->routes as $route) {
			$go = true;
			$patterns = array();
			$values = array();
			foreach ($route as $key => $pattern) {
				if (isset($this->data[$key])) {
					if (isset($this->prefixes[$key])) {
						$pattern = $this->prefixes[$key].$pattern;
					}
					$value = preg_replace("!/+!", "/", $this->data[$key]);
					$pattern = self::get_pattern($pattern);
					if (self::match($pattern, $value)) {
						$patterns[$key] = $pattern;
						$values[$key] = $value;
					}
					else {
						$patterns = array();
						$values = array();
						$go = false;
						break;
					}
				}
			}
			if ($go) {
				$this->route = $route;
				foreach (array_keys($route) as $key) {
					if (isset($this->data[$key])) {
						$this->vars[$key] = self::get_vars($patterns[$key], $values[$key]);
						$this->stars[$key] = self::get_stars($patterns[$key], $values[$key]);
					}
					else {
						$this->vars[$key] = array();
						$this->stars[$key] = array();
					}
				}
				break;
			}
		}
		
		return $this->route;
	}

	function apply($vars = array()) {
		$vars = array_replace_recursive($this->vars, $vars);
		$route = array();
		foreach (array_keys($this->route) as $key) {
			$route[$key] = $this->route[$key];
			if (strpos($route[$key], "{")) {
				foreach ($vars as $var => $var_value) {
					$route[$key] = str_replace("{".$var."}", $var_value, $route[$key]);
				}
			}
			$route[$key] =  vsprintf(str_replace("*", "%s", $route[$key]), $this->stars);
		}
		
		return $route;
	}
	
	static function match($pattern, $value) {
		$pattern = self::clean_pattern($pattern);

		return preg_match("!^$pattern$!", $value);
	}

	static function clean_pattern($pattern) {
		$pattern = preg_replace("/:VAR[^:]+:/", "", $pattern);
		$pattern = str_replace(":STAR:", "", $pattern);

		return $pattern;
	}

	static function get_pattern($pattern) {
		$regex = str_replace(".", "\.", $pattern);
		$regex = str_replace("*", "(:STAR:.*)", $regex);
		$regex = preg_replace("!\[([^\]]+)\]!", "($1)?", $regex);
		$regex = preg_replace("!\{([^\}=]+)=([^\}=]+)\}!", "(:VAR$1:$2)", $regex);
		$regex = preg_replace("!\{([^\}]+)\}!", ":VAR$1:[^/]+", $regex);

		return $regex;
	}

	static function get_positions($needle, $pattern) {

	}

	static function get_vars($pattern, $value) {
		$vars_positions = self::get_positions(":VAR", $pattern);
		$vars_names = array();
		preg_match_all("!:VAR([^:]+):!", $pattern, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $var_name) {
				$vars_names[] = $var_name;
			}
		}
		$pattern = preg_replace("/:VAR[^:]+:/", "", $pattern);
		$pattern = str_replace(":STAR:", "", $pattern);
		$vars = array();
		if (preg_match("!^$pattern$!", $value, $matches)) {
			foreach ($vars_positions as $pos) {
				$matches[$pos];
			}
		}

		return $vars;
	}



	// TODO : à supprimer
	function old_match() {
		$pattern = str_replace(".", "\.", $pattern);
		$pattern = str_replace("*", "({STAR}.*)", $pattern);
		$pattern = preg_replace("!\[([^\]]+)\]!", "($1)?", $pattern);
		preg_match_all("!\{([^\}]+)\}!", $pattern, $matches);
		$vars = array();
		foreach ($matches[1] as $var) {
			$explode = explode("=", $var, 2);
			$var_name = $explode[0];
			$var_value = isset($explode[1]) ? $explode[1] : "[^/]+";
			$pattern = str_replace("{".$var."}", "({VAR}$var_value)", $pattern);
			$vars[] = $var_name;
		}
		preg_match_all("!\([^\)]*\)!", $pattern, $matches);
		$vars_pos = array();
		$stars_pos = array();
		if (isset($matches[1])) {
			$i = 1;
			foreach ($matches[1] as $bracket) {
				if (strpos($bracket, "{VAR}") === 0) {
					$vars_pos[] = $i;
				}
				else if (strpos($bracket, "{STAR}") === 0) {
					$stars_pos[] = $i;
				}
				$i++;
			}
			$pattern = str_replace("{VAR}", "", $pattern);
			$pattern = str_replace("{STAR}", "", $pattern);
		}
		$value = preg_replace("!/+!", "/", $value);
		if (preg_match("!^$pattern$!", $value, $matches)) {
			foreach ($vars as $index => $var) {
				$this->vars[$var] = $matches[$vars_pos[$index]];
			}
			foreach ($stars_pos as $pos) {
				$this->stars[] = $matches[$pos];
			}
			return true;
		}

		return false;

	}
}
