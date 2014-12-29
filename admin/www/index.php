<?php

ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 200000);
ini_set('session.cookie_lifetime', 2000000);

session_start();
header('Content-type: text/html; charset=UTF-8');
date_default_timezone_set('Europe/Paris');

include dirname(__FILE__)."/../includes/config.php";
$config->core_include("outils/mysql", "outils/url", "outils/dico", "outils/page", "outils/menu", "extranet/user");

include include_path("includes/url.php");

$config->core_include("outils/debug");

if (function_exists('subsites')) {
	if ($subsite = subsites($url->get(0))) {
		include $subsite;
		exit;
	}
}

$page = new Page(dirname(__FILE__)."/../");
$page->template("default");

$sql = new Mysql($config->db());
$user = new User($sql);
$not_authentificated_pages = array(
	$page->get_page(1),
	$page->get_page(404),
);

$page_id = $url->get('page_id');
if (!$page_id) {
	$langue = $url->get('langue');
	$pays = $url->get('pays');
	$langue = $langue ? $langue : "fr";
	$pays = $pays ? $pays : "FR";
	$url->redirect("Accueil", array('langue' => $langue, 'pays' => $pays));
}

$page_file = $page->get_page($url->get('page_id'));

if (!$user->is_logged() and !in_array($page_file, $not_authentificated_pages)) {
	#$url->redirect("Login");
	$page_file = $page->get_page(1); # Page de login
}

$config->set('langue', $url->get('langue')."_".$url->get('pays'));
$config->set_if_not('default_langages', array("fr_FR"));

$dico = new Dico($config->get("langue"), $config->get("default_langages"));
$dico->add(dirname(__FILE__)."/../../core/traductions");
$dico->add(dirname(__FILE__)."/../traductions");
$dico->add(dirname(__FILE__)."/../../../admin/traductions");

$page->dico = $dico;

$menus_edited = array();
if (file_exists(dirname(__FILE__)."/../../../admin/includes/menu_edited.php")) {
	include dirname(__FILE__)."/../../../admin/includes/menu_edited.php";
}
include include_path("/includes/menu.php");
$menu_level = isset( $_SESSION['extranet']['user']['id_groupes_users']) ? $_SESSION['extranet']['user']['id_groupes_users'] : 0; 
$menu = new Menu($sql, $menus, $menu_level, $menus_edited);

if (!file_exists(dirname(__FILE__)."/../traductions/".$config->get('langue').".php")) {
	$page_file = $page->get_page("404");
}

if ($url->get(0) == "") {
	$url->redirect("accueil", array('langue' => "fr", 'pays' => "FR"));
}

if ($user->is_logged() and !$menu->can_access($_SERVER['REQUEST_URI'])) {
	$page_file = $page->get_page("403");
}

include include_path($page_file);

$format_file = $page->get_format($page_file);
$format_file_path = include_path($format_file);
if (file_exists($format_file_path)) {
	include $format_file_path;
}

$html_debug = debug::display();
include include_path($page->get_template());

echo $html_page;

function include_path($path) {
	if (file_exists(dirname(__FILE__)."/../../../admin/".$path)) {
		return dirname(__FILE__)."/../../../admin/".$path;
	}
	else {
		return dirname(__FILE__)."/../".$path;
	}
}
