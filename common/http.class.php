<?php

require dirname(__FILE__)."/router.class.php";

class Http {
	public $root_dirs;
	public $base_url;
	public $media_url;
	public $config = array();
	public $vars = array();
	public $control_vars = array();
	public $url_vars = array();

	public $post = array();
	public $post_session = "";

	function __construct($root_dirs = null) {
		$this->root_dirs = $root_dirs ? (is_array($root_dirs) ? $root_dirs : array($root_dirs)) : array(dirname(__FILE__)."/..");
		$this->post = $_POST;
	}

	function path($file) {
		foreach ($this->root_dirs as $root_dir) {
			if (file_exists($root_dir.$file)) {
				return $root_dir.$file;
			}
		}

		return $file;
	}

	function load($callback = null) {
		$this->load_config();
# TODO faire un router pour les langues comme pour la config
		$this->load_control();
		$this->execute();
	}

	function reload() {
		$this->load_control();
		$this->execute();
	}

	function load_config() {
		$data = $_SERVER;
		require $this->path("/config/routes.php");
		$router = new Router($routes, $data);
		$config_subdir = $router->target();
		$this->load_config_dir($this->path("/config/default"));
		$this->load_config_dir($this->path("/config/".$config_subdir));
		$this->load_config_dir($this->path("/config/global"));
		$this->base_url = isset($this->config['settings']['base_url']) ? $this->config['settings']['base_url'] : "/";
		$this->base_url = rtrim($this->base_url, "/")."/";
		$this->media_url = isset($this->config['settings']['media_url']) ? $this->config['settings']['media_url'] : "/media";
		$this->media_url = rtrim($this->media_url, "/")."/";
	}

	function load_config_dir($config_dir) {
		if (is_dir($config_dir)) {
			foreach (scandir($config_dir) as $file) {
				$config_file = $config_dir."/".$file;
				if (pathinfo($config_file, PATHINFO_EXTENSION) == "php") {
					require $config_file;
					$var = basename($config_file, ".php");
					if (isset($$var)) {
						$this->config = array_replace_recursive($this->config, array($var => $$var));
					}
				}
			}
		}
	}

	function load_control() {
		$base_url = $this->config('settings', 'base_url');
		$data = $_SERVER;
		require $this->path("/control/routes.php");

		// TODO refactoriser client et serveur alias avec une méthode commune
		if (isset($client_alias)) {
			$routes_alias = array();
			foreach ($client_alias as $request_uri => $target) {
				$routes_alias[] = array(
					'REQUEST_URI' => $request_uri,
					'target' => $target,
				);
			}
			$router = new Router($routes_alias, $data);
			$router->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $router->target()) {
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
			$router = new Router($routes_alias, $data);
			$router->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $router->target()) {
				$data['REQUEST_URI'] = $this->url($alias);
			}
		}

		$router = new Router($routes, $data);
		$router->prefixes['REQUEST_URI'] = $base_url;

		$this->main_control = $router->target();
		$this->url_vars = $router->vars;
	}

	function execute() {
		foreach (array('config', 'control_vars') as $var) {
			if (isset($this->$var) and is_array($this->$var)) {
				foreach ($this->$var as $key => $value) {
					$$key = $value;
				}
			}
		}

		$start = $this->control("start.php");
		if (file_exists($start)) {
			require $start;
		}

		require $this->control($this->main_control);

		$finish = $this->control("finish.php");
		if (file_exists($finish)) {
			require $finish;
		}
	}

	function control($control) {
		return $this->path("/control/$control");
	}

	function model($model) {
		return $this->path("/model/$model");
	}

	function view($view, $var = null) {
		if (is_array($var)) {
			foreach ($var as $subvar => $value) {
				$$subvar = $value;
			}
		}
		ob_start();
		require $this->path("/view/$view");
		
		return ob_get_clean();
	}

	function view_each($view, $array) {
		$display = "";
		foreach ($array as $vars) {
			$display .= $this->view($view, $vars);
		}
		
		return $display;
	}

	function view_vars($key, $view) {
		$display = "";
		if (isset($this->vars[$key])) {
			$display = $this->view($view, array($key => $this->vars[$key]));
		}
		
		return $display;
	}

	function view_each_vars($key, $view) {
		$display = "";
		if (isset($this->vars[$key])) {
			if (is_array($this->vars[$key])) {
				$array = array();
				foreach (array_unique($this->vars[$key]) as $var) {
					$array[] = array($key => $var);
				}
				$display .= $this->view_each($view, $array);
			}
			else {
				$display .= $this->view($view, array($key => $this->vars[$key]));
			}
		}

		return $display;
	}

#Todo parent_control() parent_view() parent_model();

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
	
	function vars() {
		$args = array_merge(array($this->vars), func_get_args());
		
		return call_user_func_array(array($this, "get_in_array"), $args);
	}

	function from_url($var) {
		return isset($this->url_vars[$var]) ? $this->url_vars[$var] : null;
	}

#avoir un url_add
# et un url_change
	function url($url = "") {
		return $this->base_url.$url;
	}

	function media($url) {
		return $this->media_url.$url;
	}
	
	function redirect($url = "", $code = null) {
		header("Location: {$this->url($url)}", true, $code);
		exit;
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
