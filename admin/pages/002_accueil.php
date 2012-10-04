<?php
$titre = "Accueil";
$menu->current('main/home');

$titre_page = $dico->t('Bienvenue');

$main = <<<HTML

HTML;

$right = <<<RIGHT
<ul>
	<li>{$page->l($dico->t("ReportingSatisfaction"), $url->make("ReportingSatisfaction"))}</li>
</ul>
RIGHT;

?>
