<?php

abstract class Url {
	
	private $base = "";
	private $elements = array();
	private $elements_with_trailing_slashes = array();
	
	public function set_base($base) {
		$this->base = $base;
	}
	
	public function elements() {
		$elements = func_get_args();
		foreach ($elements as $element) {
			if (substr($element, -1) == "/") {
				$element = substr($element, 0, -1);
				$this->elements_with_trailing_slashes[] = $element;
			}
			$this->elements[] = $element;
		}
	}
	
	public function get($element) {
		$base = trim($this->base, "/");
		$url = str_replace($base, "", str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']));
		$url = trim($url, "/");
		$url = str_replace("'", "", $url);
		$url = str_replace('"', '', $url);
		$elements = explode("/", $url, count($this->elements));
		if (is_int($element)) {
			return isset($elements[$element]) ? $elements[$element] : null;
		} else {
			$elements_flipped = array_flip($this->elements);
			if (isset($elements_flipped[$element]) and isset($elements[$elements_flipped[$element]])) {
				return $elements[$elements_flipped[$element]];
			} else {
				return "";
			}
		}
	}
	
	abstract protected function build();
	
	public function make() {
		$args = func_get_args();
		$elements = call_user_func_array(array($this, 'build'), $args);
		$elements_list = array();
		$trailing_slash = "";
		foreach ($this->elements as $element) {
			if ($elements[$element]) {
				$trailing_slash = "";
				if (in_array($element, $this->elements_with_trailing_slashes)) {
					$trailing_slash = "/";
				}
				$elements_list[] = $elements[$element];
			}
		}
		return $this->base.implode("/", $elements_list).$trailing_slash;
	}
	
	public function redirect() {
		$args = func_get_args();
		$url = "";
		if (isset($args[0])) {
			$url = $args[0];
			if (strpos($args[0], "http://") !== 0) {
				$url = call_user_func_array(array($this, 'make'), $args);
			}
		}
		$query_string = $_SERVER['QUERY_STRING'] ? "?".$_SERVER['QUERY_STRING'] : "";
		header("Location: ".$url.$query_string);
		exit;
	}

	public function always_redirect() {
		header('HTTP/1.1 301 Moved Permanently', false, 301);
		$args = func_get_args();
		call_user_func_array(array($this, 'redirect'), $args);
	}

	public function canonical_redirect() {
		$args = func_get_args();
		$url = call_user_func_array(array($this, 'make'), $args);
		if ($url != $_SERVER['REQUEST_URI']) {
			call_user_func_array(array($this, 'always_redirect'), $args);
		}
	}

	public function current() {
		return "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	}
}
