<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "stats/statscommandes");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/commandes');


/*
 * On initialise la connexion MYSQL
 * On initialise la classe StatsContacts 
 */
$sql = new Mysql($config->db());
$stats = new StatsCommandes($sql, $dico);

$titre_page = $dico->t("StatsCommandes");


$nbre_commandes_mois = $stats->nombre_commandes_par_mois();
$tableau_nbre_commandes_mois = $stats->afficher_tableau($nbre_commandes_mois);

$main = <<<HTML
$tableau_nbre_commandes_mois
HTML;

$right = "";


?>
