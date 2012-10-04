<?php
/*
 * Configuration
 */
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$menu->current('main/params/dash');

$titre_page = $dico->t('Dashboard');
$main = "";

/*
 * On contrôle s'il y a plusieurs SKU identiques en les regroupant dans un tableau.
 * Si oui, on affiche le nombre de doublons
 */
$q = "SELECT id, ref_ultralog FROM dt_sku ORDER BY ref_ultralog ";
$rs = $sql->query($q);
while($row = $sql->fetch($rs)) {
	
}
?>