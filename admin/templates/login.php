<?php
if (!isset($code_google)) {
	$code_google = '<!-- Google -->';
}
/*
 * HTML de la page
 */
$html_page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$titre}</title>
	<meta name="robots" content="noindex,nofollow" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	{$page->css()}
</head>
<body>
	<div id="container">
		{$menu->get('lang')}
		<div id="bloc_login">
			<div id="logo_login">
				<h1><img src="{$config->media('logo-alticcio.jpg')}" alt="logo alticcio" /></h1>
				<p>{$dico->t('IntroAdmin')}</p>
			</div>
			<div id="form_login">
				{$contenu}
				<ul id="liens_login">
					<li>{$page->l($dico->t('OubliPassword'),"#")}</li>
				</ul>
			</div>
			<div class="spacer"></div>
		</div>
	</div>
	{$page->my_javascript()}
	{$page->javascript()}
     {$code_google}
</body>
</html>
HTML;

?>
