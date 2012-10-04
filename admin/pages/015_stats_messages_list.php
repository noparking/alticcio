<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "stats/statscontacts");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/stats/messages');


$form = new Form(array(
	'id' => "form-search-stats",
	'class' => "form-search-stats",
	'required' => array(),
));

if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$version = $datas['version'];
}
else {
	$version = "DTFR";
}

$q_filiales = "SELECT id, code_version FROM dt_filiales ORDER BY code_version";
$r_filiales = $sql->query($q_filiales);
$liste_filiales[0] = "...";
while($row_filiales = $sql->fetch($r_filiales)) {
	$liste_filiales[$row_filiales['code_version']] = $row_filiales['code_version'];
}

/*
 * On initialise la connexion MYSQL
 * On initialise la classe StatsContacts 
 */
$sql = new Mysql($config->db());
$stats = new StatsContacts($sql, $dico);

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


// Nbre de devis par mois
$q = "SELECT * FROM dt_messages WHERE version = '".$version."' AND type = 'devis' ORDER BY date_envoi";
$total_devis = $stats->lister_totaux_mois($q, $months);
$table_devis = $stats->afficher_tableau_totaux($total_devis, $months);

// Nbre de catalogues par mois
$q = "SELECT * FROM dt_messages WHERE version = '".$version."' AND type = 'catalogue' ORDER BY date_envoi";
$total_cata = $stats->lister_totaux_mois($q, $months);
$table_cata = $stats->afficher_tableau_totaux($total_cata, $months);

// Nbre de contacts par mois
$q = "SELECT * FROM dt_messages WHERE version = '".$version."' AND type = 'contact' ORDER BY date_envoi";
$total_contact = $stats->lister_totaux_mois($q, $months);
$table_contact = $stats->afficher_tableau_totaux($total_contact, $months);


/* 
 * Valeurs renvoyées dans le template
 */

$titre_page = $dico->t("StatsDemandesContacts");

$main = <<<HTML
{$form->form_start()}
{$form->fieldset_start($dico->t(""))}
<p>{$form->select(array('name' => "version", 'label' => $dico->t('SelectFiliale'), 'options' => $liste_filiales, 'value' => $version ))} 
{$form->input(array('type'=>'submit', 'name'=>'search', 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
{$form->form_end()}

<h3>{$dico->t('NbreDevisMois')}</h3>
<img src="'.REPGRAPH.'stats_emails.php?values='.urlencode(serialize()).'" alt="{$dico->t('GraphiqueTauxOuverture')}"/>
$table_devis
<br/>
<h3>{$dico->t('NbreCataloguesMois')}</h3>
$table_cata
<br/>
<h3>{$dico->t('NbreContactsMois')}</h3>
$table_contact
HTML;

$right = "";


?>
