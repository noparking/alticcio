<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "stats/statsurl");
$page->css[] = $config->media("sondages.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/shorturl');


/*
 * On initialise la connexion MYSQL
 * On initialise la classe StatsContacts 
 */
$sql = new Mysql($config->db());
$stats = new StatsUrl($sql, $dico);

$titre_page = $dico->t("StatsURL");
$nbre_short_url = $stats->lister_resultats();
$tableau_short_url = $stats->afficher_tableau($nbre_short_url);

$main = <<<HTML
$tableau_short_url
HTML;

$right = "";


?>
