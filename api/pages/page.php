<?php

$page = basename(str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
if (file_exists(dirname(__FILE__)."/$page")) {
	session_start();
	header("Content-Type: text/html; charset=UTF-8");
	date_default_timezone_set('Europe/Paris');
	if (isset($data['error']) and $data['error'] <= 102) {
		echo $data['message'];
	}
	else {
		$directory = "default";
		foreach (scandir(dirname(__FILE__)."/../templates") as $dir) {
			if ((int)$dir == $api->key_id()) {
				$directory = $dir;
				break;
			}
		}
		include dirname(__FILE__)."/$page";
		include dirname(__FILE__)."/header.php";
		include dirname(__FILE__)."/../templates/$directory/$page";
		include dirname(__FILE__)."/../templates/$directory/body.php";
		include dirname(__FILE__)."/footer.php";
	}
	exit;
}
