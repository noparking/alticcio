<?php
/*
 * Configuration
 */
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$menu->current('main/params/services');

$titre_page = $dico->t('QuelquesOutils');
$main = <<<HTML
<ul class="liens_schemas">
	<li>{$page->l($dico->t("TelechargerBatchs"), $url->make("Batch", array('action' => "")))}</li>
	<li>{$page->l($dico->t("FiltrerEmails"), $url->make("FiltreEmails", array('action' => "")))}</li>
	<li>{$page->l($dico->t("CalculResistanceVent"), $url->make("CalculVent", array('action' => "")))}</li> 
	<li>{$page->l($dico->t("CalculPoidsEvent"), $url->make("CalculFlags", array('action' => "")))}</li> 
	<li>{$page->l($dico->t("DemoCKEditor"), $url->make("CKEditorDemo"))}</li> 
	<li>{$page->l($dico->t("Demo Filtre"), $url->make("FilterDemo"))}</li> 
	<li>{$page->l($dico->t("Démo DTEditor"), $url->make("DTEditorDemo"))}</li> 
</ul>
HTML;
?>