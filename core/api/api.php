<?php

$dirs = array("core", "classes", "hooks");

$dir_root = dirname(__FILE__);

foreach ($dirs as $dir) {
	$dir = "$dir_root/$dir";
	$api_files = scandir($dir);
	sort($api_files);

	$files = array();

	foreach ($api_files as $file_name) {
		if (is_file("$dir/$file_name") and preg_match("/\.php$/", $file_name)) {
			if (preg_match("/[0-9\.]+/", $file_name, $matches)) {
				$version = trim($matches[0], ".");
				if (!isset($_GET['v']) or $version <= $_GET['v']) {
					$key = preg_replace("/[0-9\.]+/", "", $file_name);
					$files[$key] = $file_name;
				}
			}
			else {
				$files[$file_name] = $file_name;
			}
		}
	}

	foreach ($files as $file_name) {
		include "$dir/$file_name";
	}
}
