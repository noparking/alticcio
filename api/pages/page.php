<?php

$page = basename(str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
if (file_exists(include_path("pages/$page"))) {
	session_start();
	header("Content-Type: text/html; charset=UTF-8");
	date_default_timezone_set('Europe/Paris');
	if (isset($data['error']) and $data['error'] <= 102) {
		echo $data['message'];
	}
	else {
		$directory = "default";
		foreach (array($include_path."../../api", $include_path) as $path) {
			foreach (scandir("$path/templates") as $dir) {
				if ((int)$dir == $api->key_id()) {
					$directory = $dir;
					break;
				}
			}
		}
		include include_path("pages/$page");
		include include_path("pages/header.php");
		include include_path("templates/$directory/$page");
		include include_path("templates/$directory/body.php");
		include include_path("pages/footer.php");
	}
	exit;
}
