<?php

$html_page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Fiche technique</title>
	<meta name="robots" content="index,follow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	{$page->css()}
	{$page->my_css()}
</head>
<body>
	<div id="fiche-top">
		{$top}
	</div>
	<div id="fiche-html">
		{$fiche_html}
	</div>
	
	{$page->my_javascript()}
	{$page->javascript()}
</body>
</html>
HTML;
