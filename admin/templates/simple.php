<?php

$html_page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="robots" content="index,follow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title></title>
	{$page->css()}
	{$page->jsvars()}
</head>
<body>
	<div id="main">
		{$main}
	</div>
	{$page->my_javascript()}
	{$page->javascript()}
</body>
</html>
HTML;

?>
