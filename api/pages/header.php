<?php

$css = array();
foreach (array("style", basename($page, ".php")) as $name) {
	if (file_exists(dirname(__FILE__)."/../www/medias/css/$directory/$name.css")) {
		$css[] = "$name.css";
	}
	else if (file_exists(dirname(__FILE__)."/../www/medias/css/default/directory/$name.css")) {
		$css[] = "$name.css";
	}
}

?>

<html>
<head>
<?php
foreach ($css as $file) {
	echo <<<HTML
<link type="text/css" rel="stylesheet" media="all" href="{$config->media("$directory/$file")}" />
HTML;
}
?>
</head>
<body>

