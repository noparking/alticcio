<?php

require dirname(__FILE__)."/router.class.php";
require dirname(__FILE__)."/dispacher.class.php";

class Http {
	public $router;
	public $dispacher;
	public $empty_file;
	public $base_url;
	public $media_url;
	public $config = array();
	public $vars = array();
	public $control_vars = array();
	public $view_vars = array();
	public $show_vars = array();

	public $post = array();
	public $post_session = "";

	function __construct($root_dir) {
		$this->root_dir = $root_dir;
		$this->empty_file = dirname(__FILE__)."/empty.php";
		$this->dispacher = new Dispacher($root_dir);
		$this->post = $_POST;
	}

	function path($file) {
		$files = $this->dispacher->paths($file);
		return isset($files[0]) ? $files[0] : $this->root_dir.$file;
	}
	
	function reverse_paths($file) {
		$files = $this->dispacher->paths($file);
		return isset($files[0]) ? array_reverse($files) : array($this->root_dir.$file);
	}

#TODO : delegate pour les model, control et view
# delegate_control()
# delegate_view()

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
		foreach ($this->reverse_paths("/config") as $config_dir) {
			$this->load_config_dir($config_dir);
		}
		$this->base_url = isset($this->config['settings']['base_url']) ? $this->config['settings']['base_url'] : "/";
		$this->base_url = rtrim($this->base_url, "/")."/";
		$this->media_url = isset($this->config['settings']['media_url']) ? $this->config['settings']['media_url'] : $this->base_url."medias";
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

		// TODO refactoriser client et serveur alias avec une méthode commune
		// a revoir avec la mthode route au lieu de target
		/*
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
		*/

		$router = new Router();
		$data = $_SERVER;
		require $this->path("/control/routes.php");
		$router->routes = $routes;
		$router->data = $data;
		$router->prefixes['REQUEST_URI'] = $base_url;

		$route = $router->route();
		$this->main_control = $route['control'];
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

	function in_views($vars) {
		$this->view_vars= array_replace_recursive($this->view_vars, $vars);
	}

	function view($view, $vars = array()) {
		$show_vars = $this->show_vars;
		$this->show_vars = $vars;
		foreach (array_replace_recursive($this->view_vars, $vars) as $subvar => $value) {
			$$subvar = $value;
		}
		ob_start();
		require $this->path("/view/$view");
		$this->show_vars = $show_vars;
		
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

	function show($var) {
		$args = array_merge(array($this->show_vars), func_get_args());

		return call_user_func_array(array($this, "get_in_array"), $args);
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
		return isset($this->router->vars[$var]) ? $this->router->vars[$var] : null;
	}

	function url($url = "") {
		return $this->base_url.$url;
	}

	function url_add($something) {
#TODO Quid de la query string ?
		return $_SERVER['REQUEST_URI']."/".$something;
	}

	function url_change($vars) {
		$route = $this->router->apply($vars);

		return $route['REQUEST_URI'];
	}

	function media($url) {
		return $this->media_url.$url;
	}
	
	function redirect($param = "", $code = null) {
		$url = is_array($param) ? $this->url_change($param) : $this->url($param);
		header("Location: {$url}", true, $code);
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
