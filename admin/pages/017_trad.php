<?php
/*
 * Configuration
 */
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$menu->current('main/params/trad');

$titre_page = $dico->t('Traductions');
$main = "";


// on liste les fichiers de traductions
$dir = opendir($dirname); 
$fichiers_trad = array();
while($file = readdir($dir)) {
	if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
		$nm_file = explode(".",$file);
		$fichiers_trad[$nm_file[0]] = $file;
	}
}
closedir($dir);

// on récupère les traductions
$traductions = array();
foreach($fichiers_trad as $k => $lg) {
	include($dirname.$lg);
	$traductions[$k] = $t;
}

// on créé une liste de langues à contrôler
$check_lg = array();
foreach($fichiers_trad as $k => $values) {
	if ($k != $main_lg) {
		$check_lg[$k] = $values;
	}
}

// on liste les expressions manquantes par rapport à la langue principale
foreach($check_lg as $key => $values) {
	$main .= '<h4>'.$dico->t('TraductionsManquantes').' : '.$key.'</h4>';
	$main .= '<div class="liste_trad">';
	$i=0;
	foreach($traductions[$main_lg] as $cle => $trad) {
		if (!array_key_exists($cle,$traductions[$key])) {
			$main .= '$t[\''.$cle.'\'] = "'.$trad.'";<br/>';
			$i++;
		}
	}
	if ($i>0) {
		$main .= '<p><strong>'.$i.'</strong> '.$dico->t('ExpressionsTraduire').'</p>';
	}
	else {
		$main .= '<p>'.$dico->t('TraductionsCompletes').'</p>';
	}
	$main .= '</div>';
}

?>