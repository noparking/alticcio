<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "stats/statsemailing", "stats/statsblocks");

/*
 * Info pour la navigation
 */
$menu->current('main/stats/emailing');


/*
 * On initialise la connexion MYSQL
 * On initialise la classe StatsEmailing (données de la campagne)
 * On initialise la class Form
 */
$sql = new Mysql($config->db());
$stats = new StatsEmailing($sql, $dico);


$form = new Form(array(
	'id' => "form-search-stats",
	'class' => "form-search-stats",
	'required' => array(),
));


/*
 * Données pour recherche
 * On liste les années
 * On liste les filiales
 */
$first_year = 2009;
$current_year = date("Y");

function get_date_debut($annee) {
	return mktime(0,0,0,1,1,$annee);
}
function get_date_fin($annee) {
	return mktime(0,0,0,12,31,$annee);
}

$q_filiales = "SELECT id, code_version FROM dt_filiales ORDER BY code_version";
$r_filiales = $sql->query($q_filiales);


/*
 * Traitement du formulaire de recherche avancée
 */
if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$form_year = $datas['annee'];
	$form_filiale = $datas['filiales'];	
}
else {
	$form_year = $current_year;
	$form_filiale = 1;
}

$params = "";
if (!empty($form_filiale)) {
	$params .= "AND e.id_filiales = ".$form_filiale." ";
}
if (!empty($form_year)) {
	$params .= "AND e.date_envoi >= ".get_date_debut($form_year)." AND e.date_envoi <= ".get_date_fin($form_year);
}
switch($url->get('id')) {
	case "nom":
		$params .= " ORDER BY e.emailing ";
		break;
	case "envoyes":
		$params .= " ORDER BY e.nb_emails_send DESC ";
		break;
	case "npai":
		$params .= " ORDER BY e.pourcentage_npai DESC ";
		break;
	case "desabonnements":
		$params .= " ORDER BY e.pourcentage_desabonnements DESC ";
		break;
	case "ouverture":
		$params .= " ORDER BY e.pourcentage_ouverture DESC ";
		break;
	case "clics":
		$params .= " ORDER BY e.pourcentage_clic DESC ";
		break;
	case "reactivite":
		$params .= " ORDER BY e.pourcentage_reactivite DESC ";
		break;
	default:
		$params .= " ORDER BY e.date_envoi DESC LIMIT 10";
		break;
}

/* 
 * traitement des résultats
 */
$datas_stats = $stats->get_list($params);
$total_emailings = count($datas_stats);
$n = 0;
$somme_npai = 0;
$somme_opened = 0;
$somme_clics = 0;
$somme_reactivite = 0;
$somme_desabonnes = 0;
$html_ligne = "";
foreach($datas_stats as $id_emailing => $val_emailing) {
	$somme_npai = $somme_npai + $val_emailing['pourcentage_npai'];
	$somme_opened = $somme_opened + $val_emailing['pourcentage_ouverture'];
	$somme_clics = $somme_clics + $val_emailing['pourcentage_clic'];
	$somme_reactivite = $somme_reactivite + $val_emailing['pourcentage_reactivite'];
	$somme_desabonnes = $somme_desabonnes + $val_emailing['pourcentage_desabonnements'];
	$values_graphique_reac[] = $val_emailing['pourcentage_reactivite'];
	$values_graphique_open[] = $val_emailing['pourcentage_ouverture'];
	$values_graphique_clic[] = $val_emailing['pourcentage_clic'];
	$img_emailing = $config->get("medias_url").'medias/images/emailing/'.$val_emailing['img_emailing'];
	$html_ligne .= <<<HTML
	<tr>
		<td>{$n}</td>
		<td><img src="{$img_emailing}" alt="" style="width:90px;" /></td>
		<td>{$page->l($dico->t($val_emailing['emailing']), $url->make("StatsEmailingEdit", array('action' => "edit", "id"=>$val_emailing['id'])))}</td>
		<td class="align_center">{$val_emailing['nb_emails_send']}</td>
		<td class="align_center">{$val_emailing['pourcentage_npai']} %</td>
		<td class="align_center">{$val_emailing['pourcentage_desabonnements']} %</td>
		<td class="align_center">{$val_emailing['pourcentage_ouverture']} %</td>
		<td class="align_center">{$val_emailing['pourcentage_clic']} %</td>
		<td class="align_center">{$val_emailing['pourcentage_reactivite']} %</td>
	</tr>
HTML;
	$n++;
}


/* 
 * Valeurs renvoyées dans le template
 * Colonne centrale
 */
$buttons['new'] = $page->l($dico->t('Nouveau'), $url->make("StatsEmailingEdit", array('action' => "edit")));
if ($url->get('action') == "graph") {
	$buttons['list'] = $page->l($dico->t('VoirListe'), $url->make("StatsEmailingList", array('action' => "list")));
}
else {
	$buttons['graphs'] = $page->l($dico->t('Graphiques'), $url->make("StatsEmailingList", array('action' => "list", 'id' => '#graph')));
}

$titre_page = $dico->t("StatsEmailings");

$main = <<<HTML
<div id="tableau_donnees">
	<table summary="" name="" class="">
	<caption></caption>
		<tr>
			<th></th>
			<th></th>
			<th>{$page->l($dico->t('Emailings'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'nom')))}</th>
			<th>{$page->l($dico->t('NbreEmailEnvoyes'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'envoyes')))}</th>
			<th>{$page->l($dico->t('TxNPAI'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'npai')))}</th>
			<th>{$page->l($dico->t('TxDesabonnement'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'desabonnements')))}</th>
			<th>{$page->l($dico->t('TxOuvertures'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'ouverture')))}</th>
			<th>{$page->l($dico->t('TxClics'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'clics')))}</th>
			<th>{$page->l($dico->t('TxReactivite'), $url->make("StatsEmailingList", array('action' => "list", 'id' => 'reactivite')))}</th>
		</tr>
		{$html_ligne}
	</table>
</div>
HTML;

$main .= '<div id="graphiques">';
$main .= '<a name="graph"></a>';
$main .= '<img src="'.REPGRAPH.'stats_emails.php?values='.urlencode(serialize($values_graphique_open)).'" alt="'.$dico->t('GraphiqueTauxOuverture').'"/>';
$main .= '<p>'.$dico->t('GraphiqueTauxOuverture').'</p>';
$main .= '<img src="'.REPGRAPH.'stats_emails.php?values='.urlencode(serialize($values_graphique_reac)).'" alt="'.$dico->t('GraphiqueTauxReactivite').'"/>';
$main .= '<p>'.$dico->t('GraphiqueTauxReactivite').'</p>';
$main .= '<img src="'.REPGRAPH.'stats_emails.php?values='.urlencode(serialize($values_graphique_clic)).'" alt="'.$dico->t('GraphiqueTauxClics').'"/>';
$main .= '<p>'.$dico->t('GraphiqueTauxClics').'</p>';
$main .= '</div>';


/*
 * Colonne de droite
 */
$right = "";

$liste_filiales[0] = "...";
$annee_envoi[0] = "...";
while($row_filiales = $sql->fetch($r_filiales)) {
	$liste_filiales[$row_filiales['id']] = $row_filiales['code_version'];
}
for($y=$first_year; $y<=$current_year; $y++) {
	$annee_envoi[$y] = $y;
}

$right .= <<<RIGHT
{$form->form_start()}
{$form->fieldset_start($dico->t("RechercheAvancee"))}
<p>{$form->select(array('name' => "filiales", 'label' => $dico->t('SelectFiliale'), 'options' => $liste_filiales, 'value' => $form_filiale ))}</p>
<p>{$form->select(array('name' => "annee", 'label' => $dico->t('SelectAnnee'), 'options' => $annee_envoi, 'value' => $form_year ))}</p>
<p>{$form->input(array('type'=>'submit', 'name'=>'search', 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
{$form->form_end()}
RIGHT;

if ($total_emailings > 0) {
	$right .= '<div id="stats_moyenne">';
	$right .= '<p>'.$dico->t('MoyenneNPAI').' : <strong>'.round(($somme_npai/$total_emailings),2).'</strong> %</p>';
	$right .= '<p>'.$dico->t('MoyenneDesabonnements').' : <strong>'.round(($somme_desabonnes/$total_emailings),2).'</strong> %</p>';
	$right .= '<p>'.$dico->t('MoyenneOuverture').' : <strong>'.round(($somme_opened/$total_emailings),2).'</strong> %</p>';
	$right .= '<p>'.$dico->t('MoyenneClics').' : <strong>'.round(($somme_clics/$total_emailings),2).'</strong> %</p>';
	$right .= '<p>'.$dico->t('MoyenneReactivite').' : <strong>'.round(($somme_reactivite/$total_emailings),2).'</strong> %</p>';
	$right .= '</div>';
}
?>
