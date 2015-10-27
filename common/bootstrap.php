<?php

$dir = dirname(__FILE__);

require $dir."/routing.class.php";

# TODO Gérer les default et global
$config_dir = $dir."/../config/";
$data = $_SERVER;
include $config_dir."routing.php";
$routing = new Routing($routes, $data);
$config_subdir = $routing->target();
foreach (scandir($config_dir.$config_subdir) as $file) {
	$config_file = $config_dir.$config_subdir."/".$file;
	if (pathinfo($config_file, PATHINFO_EXTENSION) == "php") {
		include $config_file;
	}
}

# TODO faire un routing pour les langues comme pour la config

require $dir."/http.class.php";
$http = new Http();
$http->base_url = $settings['base_url'];

$control_dir = $dir."/../control/";
$data = $_SERVER;
include $control_dir."routing.php";

if (isset($client_alias)) {
	$routes_alias = array();
	foreach ($client_alias as $request_uri => $target) {
		$routes_alias[] = array(
			'REQUEST_URI' => $request_uri,
			'target' => $target,
		);
	}
	$routing = new Routing($routes_alias, $data);
	$routing->prefixes['REQUEST_URI'] = $settings['base_url'];
	if ($alias = $routing->target()) {
		$http->redirect($alias , 301);
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
	$routing->prefixes['REQUEST_URI'] = $settings['base_url'];
	if ($alias = $routing->target()) {
		$data['REQUEST_URI'] = $http->link($alias);
	}
}

$routing = new Routing($routes, $data);
$routing->prefixes['REQUEST_URI'] = $settings['base_url'];

$file = $routing->target();
$url = $routing->vars;
include $control_dir.$file;
