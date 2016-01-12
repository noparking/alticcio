<?php

require __DIR__."/router.class.php";
require __DIR__."/dispatcher.class.php";

class Http {
	public $clean_path = true;
	public $rtrim_path = true;
	public $redirect_path = true;

	public $router;
	public $dispatcher;
	public $base_url;
	public $media_url;
	public $config = [];
	public $vars = [];
	public $url_vars = [];
	public $view_vars = [];
	public $show_vars = [];

	public $post = [];
	public $post_session = "";

	public $current_input = null;

#TODO faire une méthode autoload qui charge les models à travers path

	function __construct($root_dir) {
		$this->root_dir = $root_dir;
		$this->dispatcher = new Dispatcher($root_dir);
		$this->dispatcher->dirs[] = __DIR__;
		$this->post = $_POST;
	}

	function path($file) {
		if ($file[0] != "/") {
			$file = "/".$file;
		}
		$files = $this->dispatcher->paths($file);

		return isset($files[0]) ? $files[0] : $this->root_dir.$file;
	}

	function delegate($file) {
		return $this->dispatcher->delegate($file);
	}

	function load() {
		$this->load_config();
# TODO faire un router pour les langues comme pour la config
		$this->load_control();
		$this->execute();
	}
	
	function testload() {
		$this->load_config();
		$this->load_control();
		header('Content-Type: text/plain; charset=utf-8');
		die(print_r($this));
	}

	function reload() {
		$this->load_control();
		$this->execute();
	}

	function load_config() {
		foreach (array_reverse($this->dispatcher->paths("/config")) as $config_dir) {
			$this->load_config_dir($config_dir);
		}
		$this->base_url = isset($this->config['settings']['base_url']) ? $this->config['settings']['base_url'] : "";
		$this->media_url = isset($this->config['settings']['media_url']) ? $this->config['settings']['media_url'] : $this->base_url."/medias";
	}

	function load_config_dir($config_dir) {
		if (is_dir($config_dir)) {
			foreach (scandir($config_dir) as $file) {
				$config_file = $config_dir."/".$file;
				if (pathinfo($config_file, PATHINFO_EXTENSION) == "php") {
					require $config_file;
					$var = basename($config_file, ".php");
					if (isset($$var)) {
						$this->config = array_replace_recursive($this->config, [$var => $$var]);
					}
				}
			}
		}
	}

	function load_control() {
		// TODO refactoriser client et serveur alias avec une méthode commune
		// a revoir avec la mthode route au lieu de target
		/*
		if (isset($client_alias)) {
			$routes_alias = [];
			foreach ($client_alias as $request_uri => $target) {
				$routes_alias[] = [
					'REQUEST_URI' => $request_uri,
					'target' => $target,
				];
			}
			$router = new Router($routes_alias, $data);
			$router->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $router->target()) {
				$this->redirect($alias , 301);
			}
		}

		if (isset($server_alias)) {
			$routes_alias = [];
			foreach ($server_alias as $request_uri => $target) {
				$routes_alias[] = [
					'REQUEST_URI' => $request_uri,
					'target' => $target,
				];
			}
			$router = new Router($routes_alias, $data);
			$router->prefixes['REQUEST_URI'] = $base_url;
			if ($alias = $router->target()) {
				$data['REQUEST_URI'] = $this->url($alias);
			}
		}
		*/

#TODO au lieu d'un pattern (string), la route pourrait avoir un array (idée : mettre les trucs de _GET pour la route)
		$this->router = new Router();
		$data = $_SERVER;
		$path = preg_replace("!^{$this->base_url}!", "", $data['REQUEST_URI']);
		$path = preg_replace("!\?{$data['QUERY_STRING']}$!", "", $path);
		$data['row_path'] = $path;
		if ($this->clean_path) {
			$path = preg_replace("!/+!", "/", $path);
		}
		if ($this->rtrim_path and strlen($path) > 1) {
			$path = rtrim($path, "/");
		}
		if ($path != $data['row_path'] and $this->redirect_path) {
			$this->redirect($path, 301);
		}
		$data['path'] = $path;
		$data['GET'] = $_GET;
		require $this->path("/control/routes.php");
		$this->router->routes = $this->routes = $routes;
		$this->router->data = $data;
		$this->route = $this->router->route();
		$this->url_vars = isset($this->router->vars['path']) ? $this->router->vars['path'] : [];
		$this->router->associate_vars("path", "control");
		$route = $this->router->apply();
		$this->main_control = $route['control'];
	}

	function execute() {
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

	function get_class($class) {
		$class_in_config = $this->config("classes", $class);

		return $class_in_config ? $this->get_class($class_in_config) : $class;
	}

	function in_views($vars) {
		$this->view_vars= array_replace_recursive($this->view_vars, $vars);
	}

#TODO plusieurs façon d'urtiliser les variables :
# ['toto' => "TOTO"]  substitution
# ['$toto' => "TOTO"] variable
# ou alors, second tableau pour les substitutions
# ou alors, substitution si la vue ne se termine pas par .php
# si .php => second tableau éventuel pour substitutions
# si pas .php => second tableau ou premier si pas de second pour substitutions
# TODO avoir un _view() sans fléxibilité sur les paramètres (usage interne) 
# view sera la version "smart" qui appelra _view après analyse des paramètres
	function view($view, $vars = []) {
		$show_vars = $this->show_vars;
		$this->show_vars = array_replace_recursive($this->view_vars, $vars);
		foreach ($this->show_vars as $subvar => $value) {
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
			$display = $this->view($view, [$key => $this->vars[$key]]);
		}
		
		return $display;
	}

	function view_each_vars($key, $view) {
		$display = "";
		if (isset($this->vars[$key])) {
			if (is_array($this->vars[$key])) {
				$array = [];
				foreach (array_unique($this->vars[$key]) as $var) {
					$array[] = [$key => $var];
				}
				$display .= $this->view_each($view, $array);
			}
			else {
				$display .= $this->view($view, [$key => $this->vars[$key]]);
			}
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

	function show($var) {
		$args = array_merge([$this->show_vars], func_get_args());

		return call_user_func_array([$this, "get_in_array"], $args);
	}

	function on($condition, $ok, $ko = "") {
		if (is_array($ok)) {
			$ret = "";
			foreach ($ok as $key => $value) {
				if ($key == $condition) {
					$ret .= $value;
				}
				else if (is_array($ko) and isset($ko[$key])) {
					$ret .= $ko[$key];
				}
			}

			return $ret;
		}
		else {
			return $condition ? $ok : $ko;
		}
	}

	function config() {
		$args = array_merge([$this->config], func_get_args());
		
		return call_user_func_array([$this, "get_in_array"], $args);
	}
	
	function vars() {
		$args = array_merge([$this->vars], func_get_args());
		
		return call_user_func_array([$this, "get_in_array"], $args);
	}

	function url($path = null, $url_vars = [], $get_vars = [], $default_get_vars = []) {
		$add_get = false;
		if (is_array($path)) {
			$default_get_vars = $get_vars;
			$get_vars = $url_vars;
			$url_vars = $path;
			$path = $this->route['path'];
			$add_get = true;
		}
		else if ($path === null) {
			$path = $this->route['path'];
			$add_get = true;
		}
		else if ($path === "") {
			$path = $this->route['path'];
		}
		else if (isset($this->routes[$path]['path'])) {
			$path = $this->routes[$path]['path'];
		}
		$first_char = isset($path[0]) ? $path[0] : "";
		switch ($first_char) {
			case "/" :
				$url = $this->base_url.$path;
				break;
			case "+" :
				$url = $this->base_url.(isset($this->route['path']) ? $this->route['path'] : "").substr($path, 1);
				break;
			default :
				$url = $path;
		}
		$url_vars = array_merge($this->url_vars, $url_vars);
		foreach ($url_vars as $key => $value) {
			$url = str_replace("{".$key."}", $value, $url);
		}
//TODO substitution des partie optionnelles et des *
		$get_vars += $default_get_vars;
		if ($add_get) {
			$get_vars += $_GET;
		}
		if (count($get_vars)) {
			$url .= "?".http_build_query($get_vars);
		}

		return $url;
	}

	function url_var($var) {
		return $this->get_in_array($this->url_vars, $var);
	}

	function url_vars() {
		$vars = [];
		foreach (func_get_args() as $var) {
			$vars[] = $this->get_in_array($this->url_vars, $var);
		}

		return $vars;
	}

	function media($url) {
		return $this->media_url.$url;
	}
	
	function redirect($path = "", $code = null) {
		header("Location: {$this->url($path)}", true, $code);
		exit;
	}

# TODO à compléter
	function content_type($file) {
		preg_match("/\.([^\.]+)$/", $file, $matches);
		if (isset($matches[1])) {
			$type = strtolower($matches[1]);
		}
		switch ($type) {
			case 'css':
				return "text/css";
			case 'js':
				return "application/javascript";
			case 'pdf':
				return "application/pdf";
			default:
				return "image/$type";

		}
	}

#TODO méthode relatve au POST est aux formulaire
	function post() {
		# Récupère une valeur en post
		# post('toto', 'titi', 'tata') equivaut à post('toto[titi][tata]')
	}

	function post_value() {
		# Récupère une valeur en post d'après le nom
		# post_value('toto[titi][tata]')
	}

	function post_use($realm) {
		# utilise un espace de stockage (par défaut $this->url(""))
	}

	function post_reset() {
		#réinitialise l'espace à $_POST
	}

	function post_unset() {
		#réinitialise l'espace à $_POST
	}

	function current_input($name) {
		if ($name) {
			$this->current_input = $name;
		}
	}
# ou remplaceer current_input par :
# name doit toujours être appelé pour sélectionner un input
	function name($name) {
		return $this->current_input = $name;
	}
#	<input name="{$this->name("toto[titi]")}" value="{$this->value()}"
#	<input name="{$this->name("toto[titi]")}" value="{$this->value}"
#	<input name="{$this->text("toto[titi]")}" value="{$this->value}"
#	ou <input name="{$this->name("toto", "titi")}"  
# méthodes : input, text, radio, submit, textarea, file, hidden, (certaines méthodes peuvent être redondantes)
# variables positionnée après :
# name (ce que la méthode renvoi), checked, selected

# ou
# methode name()
# methode value() (pour les radios)
# attributs value, checked, selected
	function value($name = null) {
		$this->current_input($name);

		return $this->post_value($this->current_input);
	}


// Ci-dessous : utile ???
/*
	function print_r() {
		$args = func_get_args();
		$value = call_user_func_array([$this, "get_in_array"], $args);
		
		return print_r($value, true);
	}

	function str() {
		$args = func_get_args();
		$value = call_user_func_array([$this, "get_in_array"], $args);
		
		return (string)$value;
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
		$args = array_merge([$this->post], func_get_args());

		return call_user_func_array([$this, "get_in_array"], $args);
	}

	function post_unset() {
		$this->post = [];
	}

	function value() {
		$args = func_get_args();
		$default_value = count($args) ? array_pop($args) : "";
		$value = call_user_func_array([$this, "post"], $args);
		
		return $value === null ? $default_value : $value;
	}

	function checked() {

	}
*/

# TODO à tester
# Gérer les checkbox
# Gérer les fichiers
}
