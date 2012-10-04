<?php
global $config, $page;

$page->javascript[] = $config->core_media("jquery-ui.datepicker.min.js");
if ($config->get("langue") != "en_UK") {
	$lang = substr($config->get("langue"), 0, 2);
	$page->javascript[] = $config->core_media("ui.datepicker-".$lang.".js");
}
$page->javascript[] = $config->core_media("date-input.js");

$page->css[] = $config->core_media("jquery-ui.custom.css");
