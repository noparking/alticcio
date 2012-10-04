<?php

include dirname(__FILE__)."/../includes/config.php";

$dir = dirname(__FILE__)."/";

$file = str_replace($config->get("base_url"), "/", $_SERVER['REQUEST_URI']);

if (file_exists($dir.$file)) {
	$header = $config->header($file);
	$media = file_get_contents($dir.$file);
}
else {
	$header = "HTTP/1.0 404 Not Found";
	$media = "";
}

header($header);
echo $media;
