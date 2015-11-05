<?php

#TODO : jeter une exception si pattern mal formé ?

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

	function associate_vars($src, $dest) {
		if (isset($this->vars[$src])) {
			$this->vars[$dest] = isset($this->vars[$dest]) ? array_merge($this->vars[$dest], $this->vars[$src]) : $this->vars[$src];
		}
	}

	function apply($vars = array()) {
		$route = array();
		foreach (array_keys($this->route) as $key) {
			$used_vars = $vars;
			if (isset($this->vars[$key])) {
				$used_vars = array_replace_recursive($this->vars[$key], $used_vars);
			}
			$route[$key] = $this->route[$key];
			if (strpos($route[$key], "{")) {
				foreach ($used_vars as $var => $var_value) {
					$route[$key] = preg_replace("!\{".$var."(=[^\}]+)?\}!", $var_value, $route[$key]);
				}
			}
			if (isset($this->stars[$key])) {
				$route[$key] =  vsprintf(str_replace("*", "%s", $route[$key]), $this->stars[$key]);
			}
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
		$regex = preg_replace("!\{([^\}]+)\}!", "(:VAR$1:[^/]+)", $regex);

		return $regex;
	}

	static function get_positions($needle, $pattern) {
		$last_pos = 0;
		$positions = array();
		$length = strlen($needle);
		$i = 1;
		while (($last_pos = strpos($pattern, "(", $last_pos)) !== false) {
			if (substr($pattern, $last_pos + 1, $length) == $needle) {
				$positions[] = $i;
				$last_pos = $last_pos + $length;
			}
			else {
				$last_pos++;
			}
			$i++;
		}

		return $positions;
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
		$pattern = self::clean_pattern($pattern);
		$vars = array();
		if (preg_match("!^$pattern$!", $value, $matches)) {
			$i = 0;
			foreach ($vars_positions as $pos) {
				$var_name = $vars_names[$i];
				$i++;
				$vars[$var_name] = isset($matches[$pos]) ? $matches[$pos] : null;
			}
		}

		return $vars;
	}

	static function get_stars($pattern, $value) {
		$stars_positions = self::get_positions(":STAR", $pattern);
		$pattern = self::clean_pattern($pattern);
		$stars = array();
		if (preg_match("!^$pattern$!", $value, $matches)) {
			foreach ($stars_positions as $pos) {
				$stars[] = $matches[$pos];
			}
		}

		return $stars;
	}
}
