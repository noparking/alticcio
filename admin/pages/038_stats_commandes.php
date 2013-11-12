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
$stats = new StatsCommandes($sql, $dico, $congig->get("main_shop"));

$titre_page = $dico->t("StatsCommandes");

// Nombre de commandes total
$nbre_commandes_mois = $stats->nombre_commandes_par_mois();
$tableau_nbre_commandes_mois = $stats->afficher_tableau($nbre_commandes_mois);

// CA total
$ventes_totales_mois = $stats->chiffre_affaires_par_annee_mois();
$tableau_ventes_mois = $stats->afficher_tableau($ventes_totales_mois, "montant");

// Panier moyen
$panier_moyen_mois = $stats->panier_moyen_par_annee_mois();
$tableau_panier_mois = $stats->afficher_tableau($panier_moyen_mois, "montant");

$main = <<<HTML
<h3>Nbre de commandes</h3>
$tableau_nbre_commandes_mois
<h3>Ventes</h3>
$tableau_ventes_mois
<h3>Panier moyen</h3>
$tableau_panier_mois
HTML;

$right = "";


?>
