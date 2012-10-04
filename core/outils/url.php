<?php

abstract class Url {
	
	private $base = "";
	private $elements;
	
	public function set_base($base) {
		$this->base = $base;
	}
	
	public function elements() {
		$this->elements = func_get_args();
	}
	
	public function get($element) {
		$base = trim($this->base, "/");
		$url = str_replace($base, "", str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']));
		$url = trim($url, "/");
		$elements = explode("/", $url, count($this->elements));
		if (is_int($element)) {
			return $elements[$element];
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
		foreach ($this->elements as $element) {
			if ($elements[$element]) {
				$elements_list[] = $elements[$element];
			}
		}
		return $this->base.implode("/", $elements_list);
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
	
}
