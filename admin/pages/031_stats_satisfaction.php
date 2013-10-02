<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form");
$config->core_include("stats/statsatifaction");
$page->css[] = $config->media("sondages.css");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/satisfaction');


/*
 * On initialise la connexion MYSQL
 */
$sql = new Mysql($config->db());

$stat = new StatSatisfaction($sql);

$form = new Form(array(
	'id' => "form-search-satisfaction",
	'class' => "form-search-satisfaction",
	'required' => array(),
));

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
 * Fonctions de traitement
 */
/*
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
		return round((($nb_satisfait*100)/$nb_total),1).'%';
	}
	else {
		return "";
	}
}					
*/
	
/*
 * Mesure de la satisfaction :
 * Si la réponse d'un sondage comprend que des notes de 3 et/ou 4, on le considère comme satisfait
 */
/*
$q = "SELECT * FROM dt_sondage_satisfaction WHERE satisfait = 0";
$rs = $sql->query($q);
while($row = $sql->fetch($rs)) {
	if ($row['q1'] >= 3 AND $row['q2'] >= 3 AND $row['q3'] >= 3 AND $row['q4'] >= 3 AND $row['q5'] >= 3 AND $row['q6'] >= 3 AND $row['q7'] >= 3) {
		$q1 = "UPDATE dt_sondage_satisfaction SET satisfait = 1 WHERE id = ".$row['id'];
		$rs1 = $sql->query($q1);
	}
}					
*/

/*
 * Traitement des résultats après envoi du formulaire
 */
/*
$html_results = "";
if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	if ($datas['annee'] > 2010) {
		$debut_annee = mktime(0,0,0,1,1,$datas['annee']);
		$fin_annee = mktime(0,0,0,1,1,($datas['annee']+1));
		$langue_form = $datas['langue'];
	}
	else {
		$debut_annee = mktime(0,0,0,1,1,2010);
		$fin_annee = mktime(0,0,0,1,1,2011);
		$langue_form = 'fr_FR';
	}

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
	
	// requête en base
	$q = "SELECT DATE_FORMAT(FROM_UNIXTIME(s.date_reponse), '%Y') AS annee,
				DATE_FORMAT(FROM_UNIXTIME(s.date_reponse), '%m') AS mois, 
				COUNT(*) AS total, 
				SUM(satisfait) AS satisfait, 
				SUM(q1) AS q1,
				SUM(q2) AS q2,
				SUM(q3) AS q3,
				SUM(q4) AS q4,
				SUM(q5) AS q5,
				SUM(q6) AS q6,
				SUM(q7) AS q7,
				SUM(scoring) AS scoring
			FROM dt_sondage_satisfaction as s
			WHERE date_reponse > ".$debut_annee." AND date_reponse < ".$fin_annee."
			AND langue = '".$langue_form."' 
			GROUP BY annee, mois
			ORDER BY annee, mois";
	$rs = $sql->query($q);					
	while($row = $sql->fetch($rs)) {
		$html_results .= '<tr>';
		$html_results .= '<td>'.$row['mois'].'</td>';
		$html_results .= '<td>'.$row['total'].'</td>';
		$html_results .= '<td>'.pourcentage_satisfait($row['satisfait'], $row['total']).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q1'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q2'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q3'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q7'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q4'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q5'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['q6'], $row['total'], 4).'</td>';
		$html_results .= '<td style="text-align:center;">'.moyenne($row['scoring'], $row['total'], 28).'</td>';
		$html_results .= '</tr>';
	}
	$html_results .= '<table>';
	
	$q1 = "SELECT commentaires, date_reponse, num_cde
			FROM dt_sondage_satisfaction
			WHERE commentaires != '' AND date_reponse > ".$debut_annee."
				AND date_reponse < ".$fin_annee." AND langue = '".$langue_form."'
			ORDER BY date_reponse DESC ";
	$rs1 = $sql->query($q1);
	$html_results .= '<div class="commentaires_sondages">';
	while($row1 = $sql->fetch($rs1)) {
		$date_comment = date('d M Y', $row1['date_reponse']);
		$html_results .= '<dl>';
		$html_results .= '<dt>'.$date_comment.' - '.$dico->t('Commande').' '.$row1['num_cde'].'</dt>';
		$html_results .= '<dd>'.$row1['commentaires'].'</dd>';
		$html_results .= '</dl>';
	}
	$html_results .= '</div>';
}
else {
	$datas['annee'] = "";
	$datas['langue'] = "";
}
*/



// on contrôle que tous les enregistrements sont tous ok.
$stat->check_satisfaction();


// on prépare les données, une fois le formulaire retourné
$html_results = "";
if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$resultats = $stat->resultats($datas['langue'], $datas['annee'], 0);
	$commentaires = $stat->commentaires($datas['langue'], $datas['annee'], 0);
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
	foreach($resultats as $resultat) {
		$html_results .= '<tr>';
		$html_results .= '<td>'.$resultat['mois'].'</td>';
		$html_results .= '<td>'.$resultat['total'].'</td>';
		$html_results .= '<td>'.$resultat['tx_satisfait'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q1'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q2'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q3'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q7'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q4'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q5'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_q6'].'</td>';
		$html_results .= '<td style="text-align:center;">'.$resultat['moy_scoring'].'</td>';
		$html_results .= '</tr>';
	}
	$html_results .= '<table>';
	$html_results .= '<div class="commentaires_sondages">';
	foreach($commentaires as $commentaire) {
		$html_results .= '<dl>';
		$html_results .= '<dt>'.$commentaire['date'].' - '.$dico->t('Commande').' '.$commentaire['cde'].'</dt>';
		$html_results .= '<dd>'.$commentaire['texte'].'</dd>';
		$html_results .= '</dl>';
	}
	$html_results .= '</div>';
}
else {
	$datas['annee'] = "";
	$datas['langue'] = "";
}



/* 
 * Valeurs renvoyées dans le template
 */
$annees = array();
for($i=2011; $i<=date('Y'); $i++) {
	$annees[$i] = $i;
}
$langues = array( "fr_FR" => "FR", "es_ES" => "ES");

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

$right = "";
?>
