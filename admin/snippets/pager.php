<?php
global $config, $page, $dico, $pager;

$page->javascript[] = $config->media("pager.js");

$page_s = $pager->pages() > 1 ? $dico->t("pages") : $dico->t("page");

echo <<<HTML
{$dico->t("Page")}
{$pager->previous("&lt;")}
{$pager->pageinput()}
{$pager->next("&gt;")}
{$dico->t("PageSur")} {$pager->pages()} {$page_s}
|
{$dico->t("Voir")}
{$pager->numberselect()}
{$dico->t("ParPage")}
|
{$dico->t("Total")} : {$pager->total()}
HTML;
