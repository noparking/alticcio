<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form");
$page->css[] = $config->media("sondages.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/satisfaction');


/*
 * On initialise la connexion MYSQL
 */
$sql = new Mysql($config->db());

$months = array(	"01" => $dico->t('MoisJanvier'),
					"02" => $dico->t('MoisFevrier'),
					"03" => $dico->t('MoisMars'),
					"04" => $dico->t('MoisAvril'),
					"05" => $dico->t('MoisMai'),
					"06" => $dico->t('MoisJuin'),
					"07" => $dico->t('MoisJuillet'),
					"08" => $dico->t('MoisAout'),
					"09" => $dico->t('MoisSeptembre'),
					"10" => $dico->t('MoisOctobre'),
					"11" => $dico->t('MoisNovembre'),
					"12" => $dico->t('MoisDecembre') );

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
										
					
$form = new Form(array(
	'id' => "form-search-satisfaction",
	'class' => "form-search-satisfaction",
	'required' => array(),
));

function moyenne($note, $nbre, $sur) {
	if ($note > 0) {
		$moyenne = $note/$nbre;
		return '<strong>'.round($moyenne,1).'</strong>/'.$sur;
	}
	else {
		return "-";
	}
}
function pourcentage_satisfait($nb_satisfait, $nb_total) {
	if ($nb_total > 0) {
		return round((($nb_satisfait*100)%$nb_total),1).'%';
	}
	else {
		return "";
	}
}


$html_results = "";
if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$html_results .= '<table>';
	$html_results .= '<tr>';
	$html_results .= '<th>'.$dico->t('Mois').'</th>';
	$html_results .= '<th>'.$dico->t('NbreReponses').'</th>';
	$html_results .= '<th>'.$dico->t('PrctSatisfait').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionAccueil').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionReponses').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionPrix').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionEmballage').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionLivraison').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionPose').'</th>';
	$html_results .= '<th>'.$dico->t('QuestionProduit').'</th>';
	$html_results .= '<th>'.$dico->t('NoteGlobale').'</th>';
	$html_results .= '</tr>';
	$liste_comments = array();
	foreach($months as $key => $month) {
		$date_debut = mktime(0,0,0,$key,1,$datas['annee']);
		$date_fin = mktime(23,59,59,$key,31,$datas['annee']);
		$q = "SELECT * FROM dt_sondage_satisfaction WHERE date_reponse >= ".$date_debut." AND date_reponse <= ".$date_fin." AND langue = '".$datas['langue']."' ";
		$rs = $sql->query($q);
		$q1 = 0;
		$q2 = 0;
		$q3 = 0;
		$q4 = 0;
		$q5 = 0;
		$q6 = 0;
		$q7 = 0;
		$note = 0;
		$satisfait = 0;
		$i = 0;
		while ($row = $sql->fetch($rs)) {
			$q1 = $q1 + $row['q1'];
			$q2 = $q2 + $row['q2'];
			$q3 = $q3 + $row['q3'];
			$q4 = $q4 + $row['q4'];
			$q5 = $q5 + $row['q5'];
			$q6 = $q6 + $row['q6'];
			$q7 = $q7 + $row['q7'];
			$note = $note + $row['scoring'];
			$satisfait = $satisfait + $row['satisfait'];
			if (!empty($row['commentaires'])) {
				$date_comment = date('d M Y', $row['date_reponse']);
				$html_comments = <<<HTML
<dl>
	<dt>{$date_comment} - {$dico->t('Commande')} {$row['num_cde']}</dt>
	<dd>{$row['commentaires']}</dd>
</dl>
HTML;
				$liste_comments[] = $html_comments;
			}
			$i++;
		}
		$nbre_reponses = $i;
		$html_results .= '<tr>';
		$html_results .= '<td>'.$month.'</td>';
		$html_results .= '<td>'.$nbre_reponses.'</td>';
		$html_results .= '<td>'.pourcentage_satisfait($satisfait, $nbre_reponses).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q1, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q2, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q3, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q7, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q4, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q5, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($q6, $nbre_reponses, 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($note, $nbre_reponses, 28).'</td>';
		$html_results .= '</tr>';
	}
	$html_results .= '<table>';
}
else {
	$datas = array();
}

$annees = array();
for($i=2011; $i<=date('Y'); $i++) {
	$annees[$i] = $i;
}
$langues = array( "fr_FR" => "FR", "es_ES" => "ES");


/* 
 * Valeurs renvoyées dans le template
 */

$titre_page = $dico->t("StatsSondagesSatisfaction");

$main = <<<HTML
{$form->form_start()}
{$form->fieldset_start($dico->t(""))}
<p>{$form->select(array('name' => "annee", 'label' => $dico->t('SelectAnnee'), 'options' => $annees, 'value' => $datas['annee'] ))}
{$form->select(array('name' => "langue", 'label' => $dico->t('SelectLangue'), 'options' => $langues, 'value' => $datas['langue'] ))}
{$form->input(array('type'=>'submit', 'name'=>'search', 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
{$form->form_end()}
HTML;
$main .= $html_results;
if (count($liste_comments) > 0) {
	$nouvelle_liste = array_reverse($liste_comments);
	$main .= '<div class="commentaires_sondages">';
	foreach($nouvelle_liste as $comment) {
		$main .= $comment;
	}
	$main .= '</div>';
}

$right = "";


?>
