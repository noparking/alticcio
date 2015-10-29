<?php

require dirname(__FILE__)."/routing.class.php";

class Http {
	public $root_dir;
	public $base_url;
	public $media_url;
	public $config = array();
	public $vars = array();
	public $post = array();
	public $post_session = "";

// TODO : gérer un default_control et un global_control(?), pareil pour les view
// But : avoir des controler et des vue commun à plusieurs sites
	function __construct($root_dir = null) {
		$this->root_dir = $root_dir ? $root_dir : dirname(__FILE__)."/..";
		$this->post = $_POST;
	}

	public function boot($callback = null) {
		$this->load_config();
		if ($callback) {
			$this->control_vars = $callback();
		}
# TODO faire un routing pour les langues comme pour la config
		$this->load_control();
	}

	public function reboot() {
		$this->load_control();
	}
	
	public function load_config() {
		$config_dir = $this->root_dir."/config/";
		$data = $_SERVER;
		include $config_dir."routing.php";
		$routing = new Routing($routes, $data);
		$config_subdir = $routing->target();
		$this->load_config_dir($config_dir."default");
		$this->load_config_dir($config_dir.$config_subdir);
		$this->load_config_dir($config_dir."global");
		$this->base_url = isset($this->config['settings']['base_url']) ? $this->config['settings']['base_url'] : "/";
		$this->media_url = isset($this->config['settings']['media_url']) ? $this->config['settings']['media_url'] : "/media";
	}

	public function load_config_dir($config_dir) {
		if (is_dir($config_dir)) {
			foreach (scandir($config_dir) as $file) {
				$config_file = $config_dir."/".$file;
				if (pathinfo($config_file, PATHINFO_EXTENSION) == "php") {
					include $config_file;
					$var = basename($config_file, ".php");
					if (isset($$var)) {
						$this->config = array_replace_recursive($this->config, array($var => $$var));
					}
				}
			}
		}
	}

	public function load_control() {
		$base_url = $this->config('settings', 'base_url');
		$control_dir = $this->root_dir."/control/";
		$data = $_SERVER;
		include $control_dir."routing.php";

		// TODO refactoriser client et serveur alias avec une méthode commune
		if (isset($client_alias)) {
			$routes_alias = array();
			foreach ($client_alias as $request_uri => $target) {
				$routes_alias[] = array(
					'REQUEST_URI' => $request_uri,
					'target' => $target,
				);
			}
			$routing = new Routing($routes_alias, $data);
			$routing->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $routing->target()) {
				$this->redirect($alias , 301);
			}
		}

		if (isset($server_alias)) {
			$routes_alias = array();
			foreach ($server_alias as $request_uri => $target) {
				$routes_alias[] = array(
					'REQUEST_URI' => $request_uri,
					'target' => $target,
				);
			}
			$routing = new Routing($routes_alias, $data);
			$routing->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $routing->target()) {
				$data['REQUEST_URI'] = $this->link($alias);
			}
		}

		$routing = new Routing($routes, $data);
		$routing->prefixes['REQUEST_URI'] = $base_url;

		$file = $routing->target();
		$url = $routing->vars;
	
		foreach (array('config', 'control_vars') as $var) {
			if (isset($this->$var) and is_array($this->$var)) {
				foreach ($this->$var as $key => $value) {
					$$key = $value;
				}
			}
		}

		include $control_dir.$file;
	}

	function view($view, $var = null) {
		if (is_array($var)) {
			foreach ($var as $subvar => $value) {
				$$subvar = $value;
			}
		}
		ob_start();
		include $this->root_dir."/view/$view";
		return ob_get_clean();
	}

	function view_each($view, $array) {
		$display = "";
		foreach ($array as $vars) {
			$display .= $this->view($view, $vars);
		}
		
		return $display;
	}

	function vars($key, $view) {
		$display = "";
		if (isset($this->vars[$key])) {
			$method = is_array($this->vars[$key]) ? "view_each" : "view";
			$display .= $this->$method($view, $this->vars[$key]);
		}

		return $display;
	}

	function get_in_array() {
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

	function config() {
		$args = array_merge(array($this->config), func_get_args());
		
		return call_user_func_array(array($this, "get_in_array"), $args);
	}

// TODO function url

	function link($url) {
		return $this->base_url.$url;
	}

	function media($url) {
		return $this->media_url.$url;
	}

// Ci-dessous : utile ???
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
	//CF get_in_array
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

	function redirect($url = "", $code = null) {
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
		$args = array_merge(array($this->post), func_get_args());

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
