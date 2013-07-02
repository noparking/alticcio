<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form");
$page->template("simple");
$page->css[] = $config->media("reporting.css");

$sql = new Mysql($config->db());


/*
 * Mesure de la satisfaction :
 * Si la réponse d'un sondage comprend que des notes de 3 et/ou 4, on le considère comme satisfait
 */
$q = "SELECT * FROM dt_sondage_satisfaction WHERE satisfait = 0";
$rs = $sql->query($q);
while($row = $sql->fetch($rs)) {
	if ($row['q1'] >= 3 AND $row['q2'] >= 3 AND $row['q3'] >= 3 AND $row['q4'] >= 3 AND $row['q5'] >= 3 AND $row['q6'] >= 3 AND $row['q7'] >= 3) {
		$q1 = "UPDATE dt_sondage_satisfaction SET satisfait = 1 WHERE id = ".$row['id'];
		$rs1 = $sql->query($q1);
	}
}


/*
 * Tableau du nombre de réponses par mois/année
 */

 
/*
 * Tableau du % de satisfaits par mois/année
 */


/*
 * Podium des questions pour le dernier mois
 */









/*
 * Stats mensuelles
 */
$mois_en_cours = date('m');
$mois_precedent = "";

function moyenne($note, $nbre, $sur) {
	if ($note > 0) {
		$moyenne = $note/$nbre;
		if (!empty($sur)) {
			return '<strong>'.round($moyenne,1).'</strong>/'.$sur;
		}
		else {
			return round($moyenne,1);
		}
	}
	else {
		return "-";
	}
}

function evolution($note_now, $note_prev) {
	if ($note_now > $note_prev) {
		$color = "green";
	}
	else if ($note_now == $note_prev) {
		$color = "orange";
	}
	else if ($note_now < $note_prev) {
		$color = "red";
	}
	return $color;
}



/* 
 * Valeurs renvoyées dans le template
 */
/*
$titre_page = $dico->t("ReportingSatisfaction");



$main = <<<HTML
<div id="container">
	<header>
		<div id="boutons">
			<ul>
				<li>{$page->l($dico->t("Retour"), $url->make("Accueil"))}</li>
				<li><a href="javascript:window.print()" >{$dico->t("Imprimer")}</a></li>
			</ul>
		</div>
		<div id="logo">
			<img src="{$config->media('logo-doublet-home.jpg')}" alt="logo Doublet" />
		</div>
		<div id="titre">
			<h1>{$dico->t('EnqueteSatisfactionClients')}</h1>
			<h2>{$dico->t('SemaineDu')} $date_week1 {$dico->t('Au')} $date_week2</h2>
		</div>
	</header>
	<section>
	<div id="notation">
		<div id="details">
HTML;


$liste_notes = array( 	"q1" => $dico->t('QualiteAccueil'),
						"q2" => $dico->t('QualiteReponse'),
						"q3" => $dico->t('QualitePrix'),
						"q7" => $dico->t('QualiteEmballage'),
						"q4" => $dico->t('QualiteLivraison'),
						"q5" => $dico->t('QualitePose'),
						"q6" => $dico->t('QualiteProduit'),
					);
$note_globale_A = 0;
$note_globale_B = 0;
foreach($liste_notes as $key => $value) {
	$nb_reponses_A = calculer($d1, $d0, '');
	$nb_reponses_B = calculer($d2, $d1, '');
	$note_A = calculer($d1, $d0, $key);
	$note_B = calculer($d2, $d1, $key);
	$note_globale_A = $note_globale_A + $note_A;
	$note_globale_B = $note_globale_B + $note_B;
	$note = moyenne(calculer($d1, $d0, $key), $nb_reponses_A, 4);
	$bordure = evolution(moyenne(calculer($d1, $d0, $key), $nb_reponses_A, ''), moyenne(calculer($d2, $d1, $key), $nb_reponses_B, ''));
	$main .= <<<HTML
<dl class="$bordure" >
	<dt>$value<dt>
	<dd>$note</dd>
</dl>
HTML;
}

$note_G = moyenne($note_globale_A, $nb_reponses_A, 28);
$bordure_G = evolution(moyenne($note_globale_A, $nb_reponses_A, ''), moyenne($note_globale_B, $nb_reponses_B, ''));
$main .= <<<HTML
</div>
<div id="global">
	<dl class="$bordure_G">
		<dt>{$dico->t('NoteGlobale2')}<dt>
		<dd>$note_G</dd>
	</dl>
	</div>
</div>
</section>
HTML;

$var_textes = array("%nbA%", "%nbB%");
$var_chiffres = array($nb_reponses_A, $nb_reponses_B);
$note_bas_page = str_replace($var_textes, $var_chiffres, $dico->t('NoteBasPageSatisfaction'));
$main .= <<<HTML
<section>
	<div id="notebaspage">
		$note_bas_page
	</div>
	<div id="legend">
		<ul>
			<li>{$dico->t('ResultatsSemaineAvant')}</li>
			<li class="red" >{$dico->t('ResultatBaisse')}</li>
			<li class="orange" >{$dico->t('ResultatEgal')}</li>
			<li class="green" >{$dico->t('ResultatHausse')}</li>
		</ul>
	</div>
</section>
HTML;

$main .= <<<HTML
<section>
	<div id="commentaires">
		<h3>{$dico->t('Commentaires')}</h3>
HTML;

$q_comments = "SELECT date_reponse, num_cde, commentaires 
				FROM dt_sondage_satisfaction 
				WHERE date_reponse >= ".$d1." 
				AND date_reponse <= ".$d0." ";
$rs = $sql->query($q_comments);
$i=1;
while ($row = $sql->fetch($rs)) {
	if (!empty($row['commentaires'])) {
		$date_comment = date('d M Y',$row['date_reponse']);
		$main .= <<<HTML
<dl>
	<dt>Commande {$row['num_cde']} - $date_comment</dt>
	<dd>{$row['commentaires']}</dd>
</dl>
HTML;
		if ($i == 3) {
			$main .= '<div style="clear:both;"></div>';
			$i=1;
		}
		else {
			$i++;
		}
	}
	
}

$main .= <<<HTML
</div>
</section>
</div>
HTML;

$right = "";

*/
?>
