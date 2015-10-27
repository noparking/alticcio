<?php

// Renommer en objet Site ? 
class Http {
	public $base_url = "";
	public $post = array();
	public $post_session = "";

	function __construct() {
		$this->post = $_POST;
	}

	function view($view) {
		ob_start();
		include dirname(__FILE__)."/../view/$view";
		return ob_get_clean();
	}

	function link($url) {
		return $this->base_url.$url;
	}

	function print_r() {
		$args = func_get_args();
		$value = call_user_func_array(array($this, "get"), $args);
		
		return print_r($value, true);
	}

	function str() {
		$args = func_get_args();
		$value = call_user_func_array(array($this, "get"), $args);
		
		return (string)$value;
	}

	  // Renommer pour ne pas confondre avec les méthode http get post, etc ?
	function get() {
		$args = func_get_args();
		$var = array_shift($args);
		foreach ($args as $arg) {
			if (isset($var[$arg])) {
				$var = $var[$arg];
			}
			else {
				return null;
			}
		}

		return $var;
	}

	function redirect($url, $code = null) {
		header("Location:	{$this->link($url)}", true, $code);
		exit;
	}

	function post_session_start($name = "") {
		$this->post_session = $name;
		if (isset($_SESSION['_POST'][$name])) {
			$this->post += $_SESSION['_POST'][$name];
		}
		// $this->post_session_save();
	}

	function post_session_save() {
		$_SESSION['_POST'][$this->post_session] = $this->post;
	}

	function post() {
		$args = array($this->post) + func_get_args();

		return call_user_func_array(array($this, "get"), $args);
	}

	function post_unset() {
		$this->post = array();
	}

	function value() {
		$args = func_get_args();
		$default_value = count($args) ? array_pop($args) : "";
		$value = call_user_func_array(array($this, "post"), $args);
		
		return $value === null ? $default_value : $value;
	}

	function checked() {

	}

# TODO à tester
# Gérer les checkbox
# Gérer les fichiers
}
