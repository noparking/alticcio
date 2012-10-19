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

$annees = array();
for($i=2012; $i<=date('Y'); $i++) {
	$annees[$i] = $i;
}

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

$form = new Form(array(
	'id' => "form-search-shorturl",
	'class' => "form-search-shorturl",
	'required' => array(),
));



if ($form->is_submitted() and $form->validate()) {
	$datas = $form->values();
	$stats = new StatsUrl($sql, $dico, $datas['annee']);
	print_r($stats->lister_resultats());
}
else {
	$datas = array();
	$datas['annee'] = date("Y");
	$table_url = "";
}



$titre_page = $dico->t("StatsURL");

$main = <<<HTML
{$form->form_start()}
{$form->fieldset_start($dico->t(""))}
{$form->select(array('name' => "annee", 'label' => $dico->t('SelectAnnee'), 'options' => $annees, 'value' => $datas['annee'] ))}
{$form->input(array('type'=>'submit', 'name'=>'search', 'value'=> $dico->t('Envoyer') )) }</p>
{$form->fieldset_end()}
{$form->form_end()}

<h3>{$dico->t('NbreUrlMois')}</h3>
<br/>
HTML;

$right = "";


?>
