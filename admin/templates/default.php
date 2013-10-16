<?php

if (!isset($logo_src)) {
	$logo_src = $config->media('logo-alticcio.jpg');
}
if (!isset($logo_alt)) {
	$logo_alt ="logo alticcio";
}

array_unshift($page->css, $config->media("default.css"));

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->media("menu.js");
$page->javascript[] = $config->media("main.js");
$page->jsdico[] = array('SauvegardeEnCours', 'ConfirmerSuppression');

if (!isset($code_google)) {
	$code_google = '<!-- Google -->';
}
$header_stats = "";

if (!isset($right)) {
	$right = "";
}
if (!isset($left)) {
	$left = "";
}
if (!isset($form_start)) {
	$form_start = "";
}
if (!isset($form_end)) {
	$form_end = "";
}

$bloc_buttons = '';
if (isset($buttons) and is_array($buttons)) {
	$config->core_include("outils/buttons_manager");
	$buttons_manager = new ButtonsManager($buttons);
	$buttons_manager->groupe['default'] = array(
		'back' => "",
		'list' => "",
		'items' => "",
	);
	$buttons_manager->groupe['operations'] = array(
		'new' => "",
		'save' => "",
		'duplicate' => "",			
		'reset' => "",
		'delete' => "",				
	);

	// autres boutons
	$default_buttons = $buttons_manager->groupe("default");
	if (count($default_buttons)) {
		$bloc_buttons .= '<div id="buttons"><ul class="buttons_actions">';
		foreach ($default_buttons as $key => $button) {
			$bloc_buttons .= '<li class="button-'.$key.'">'.$button.'</li>';
		}
		$bloc_buttons .= '</ul></div>';
	}

	// boutons d'opérations
	$operations_buttons = $buttons_manager->groupe("operations");
	if (count($operations_buttons)) {
		$bloc_buttons .= '<div id="buttons"><ul class="buttons_actions">';
		foreach ($operations_buttons as $key => $button) {
			$bloc_buttons .= '<li class="button-'.$key.'">'.$button.'</li>';
		}
		$bloc_buttons .= '</ul></div>';
	}
}

$bloc_right = "";
if ($right) {
	$bloc_right = '<div id="right">'.$right.'</div>';
}
$bloc_left = "";
if ($left) {
	$bloc_left = '<div id="left">'.$left.'</div>';
}

$html_page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Alticcio Admin</title>
	<meta name="robots" content="noindex,nofollow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	{$page->css()}
	{$page->my_css()}
	<!--[if IE]>
	<link href="{$config->media("ie.css")}" rel="stylesheet" type="text/css" media="all" />
	<![endif]-->
	{$page->jsvars()}
</head>
<body>
	{$html_debug}
	<div id="container">
		<!-- choix des langues -->
		{$menu->get('top')}
	
		<!-- header : logo et stats -->
		<div id="header">
			<h1><img src="{$logo_src}" alt="{$logo_alt}" /></h1>
		</div>
		
		<!-- Navigation principale -->
		{$menu->get('main')}
		
		<!-- Contenu -->	
		<div id="content">
			{$form_start}
			<h2>{$titre_page}</h2>
			{$bloc_buttons}
			{$bloc_left}
			<div id="main">
				{$main}
			</div>
			{$bloc_right}
			{$form_end}
			<div class="spacer"></div>
		</div>
	</div>
	{$page->my_javascript()}
	{$page->javascript()}
	{$page->my_javascript("post")}
	{$code_google}
</body>
</html>
HTML;
