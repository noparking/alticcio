<?php

require dirname(__FILE__)."/router.class.php";
require dirname(__FILE__)."/dispatcher.class.php";

class Http {
	public $clean_path = true;
	public $trim_path = true;
	public $redirect_path = true;

	public $router;
	public $dispatcher;
	public $empty_file;
	public $base_url;
	public $media_url;
	public $config = array();
	public $vars = array();
	public $url_vars = array();
	public $control_vars = array();
	public $view_vars = array();
	public $show_vars = array();

	public $post = array();
	public $post_session = "";

#TODO faire une méthode autoload qui charge les models à travers path

	function __construct($root_dir) {
		$this->root_dir = $root_dir;
		$this->empty_file = dirname(__FILE__)."/empty.php";
		$this->dispatcher = new Dispatcher($root_dir);
		$this->post = $_POST;
	}

	function path($file) {
		if ($file[0] != "/") {
			$file = "/".$file;
		}
		$files = $this->dispatcher->paths($file);
		return isset($files[0]) ? $files[0] : $this->root_dir.$file;
	}
	
	function reverse_paths($file) {
		$files = $this->dispatcher->paths($file);
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
						$this->config = array_replace_recursive($this->config, array($var => $$var));
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

#TODO : gérer la query string
#au lieu d'un pattern (string), la route pourrait avoir un array (idée : mettre les trucs de _GET pour la route)
		$this->router = new Router();
		$data = $_SERVER;
#TODO : utiliser 'path' plutôt que 'REQUEST_URI' trafiqué
# Supprimer le principe des préfixes
		$path = preg_replace("!^{$this->base_url}!", "", $data['REQUEST_URI']);
		$path = preg_replace("!\?{$data['QUERY_STRING']}$!", "", $path);
		$data['row_path'] = $path;
		if ($this->clean_path) {
			$path = preg_replace("!/+!", "/", $path);
		}
		if ($this->trim_path) {
			$path = trim($path, "/");
		}
#TODO : redirect si path != row_path et si option redirect_path
		$data['path'] = $path;
		$data['GET'] = $_GET;
		require $this->path("/control/routes.php");
		$this->router->routes = $this->routes = $routes;
		$this->router->data = $data;
		$this->router->route();
		$this->url_vars = isset($this->router->vars['path']) ? $this->router->vars['path'] : array();
		$this->router->associate_vars("path", "control");
		$route = $this->router->apply();
		$this->main_control = $route['control'];
	}

	function execute() {
# TODO à quoi serve finalement les control_vars ?
# Cet export de variables est-il justifié ?
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

#TODO plusieurs façon d'urtiliser les variables :
# array('toto' => "TOTO")  substitution
# array('$toto' => "TOTO") variable
# ou alors, second tableau pour les substitutions
# ou alors, substitution si la vue ne se termine pas par .php
# si .php => second tableau éventuel pour substitutions
# si pas .php => second tableau ou premier si pas de second pour substitutions
# TODO avoir un _view() sans fléxibilité sur les paramètres (usage interne) 
# view sera la version "smart" qui appelra _view après analyse des paramètres
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
# (ou plutôt, delegate)

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

# TODO méthode url_vars('titi', 'toto') qui renvoie un tableau
# but : pouvoir faire : list($toto, $titi) = $this->url_vars('toto', 'titi');

# TODO remplacer from_url() par url_var() ?
	function from_url($var) {
		return $this->get_in_array($this->url_vars, $var);
	}

#TODO ajouter un array en second paramètre pour prendre des valeurs par défaut de variable si elle n'existe pas ou si on veut les changer
# Ajouter des noms de routes (en clé des tableau)
# Supprimer le système de préfixes ? Dans ce cas, on retranche la base URL. On peut ajouter une clé url qui serait REQUEST_URI - base_url
# Modifier la méthode url pour avoir :
# url("/mon/url/{id}")  {id} est pris dans les variables d'url courantes
# url("/mon/url/{id}", array('id' => 42))  {id} est pris dans le second paramètre, sinon dans les variables d'url courantes
# url(nom_url) url de la route de clé nom_url. Les variables éventuielles sont remplacée par les valeurs des variables url courantes
# url(nom_url, array('id' => 42)) Les valeurs des variables sont prises d'abord dans le second paramètre
# url() c'est l'url courante telle quelle (NON, c'est la racine. url($this) est l'url courante
# url(array('id' => 42)) url courante avec cahngement de variables
# url("+/qsd") on ajoute à l'url courante
# url("-/qsd") on enlève le dernier élément et on ajoute à la suite
# url("--/qsd") on enlève les deux derniers éléments et on ajoute à la suite
# url("---/qsd") on enlève les trois derniers éléments et on ajoute à la suite
# Pourvoir passer un second tableau pour les paramètres get ?
# Gerer les url absolues : url("http://example.com");
# Faire un objet spécifique ?

# url($this, array(variables_url)) # url courante
# url("", array(variables_url)) # url racine
#TODO Quid de la query string ?
# en passant éventuellement un second tableau en paramètre ; en se basant sur http_build_query ?

	function url($url = "", $url_vars = array(), $get_vars = array()) {
		if (isset($url->route['path'])) {
			$url = $url->route['path'];
		}
		$url_vars = array_merge($this->url_vars, $url_vars);

		$first_char = isset($url[0]) ? $url[0] : "";
		switch ($first_char) {
			case "+" :
				break;
			case "-" :
				break;
			default :
				if (isset($this->routes[$url])) {
					#route noméee
				}
				else {

				}
		}

		foreach ($url_vars as $key => $value) {
			$url = str_replace("{".$key."}", $value, $url);
		}

		return $this->base_url.$url;
	}

	function media($url) {
		return $this->media_url.$url;
	}
	
	function redirect($param = "", $code = null) {
		$url = is_array($param) ? $this->url($this, $param) : $this->url($param);
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
