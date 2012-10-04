<?php
/*
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "stats/statsemailing");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->javascript[] = $config->core_media("thickbox.js");
$page->css[] = $config->media("thickbox.css");
$page->css[] = $config->media("jquery-ui-1.7.2.custom.css");
$page->inc("snippets/date-input");

$menu->current('main/stats/emailing');

/*
 * On initialise la connexion MYSQL
 * On initialise la classe StatsEmailing (données de la campagne)
 * Si on un id dans l'URL, on charge les données concernant la campagne indiquée
 */
$sql = new Mysql($config->db());
$stats = new StatsEmailing($sql, $dico);

if ($url->get("id") > 0) {
	$id_stats = $url->get("id");
	$stats_data = $stats->load(array('id' => $id_stats));
}
else {
	$stats_data = array(	'id'=>'',
							'emailing'=>'',
							'date_envoi'=>time(),
							'id_filiales'=>'',
							'nb_desabonnements'=>'',
							'nb_emails_db'=>'',
							'nb_emails_send'=>'',
							'nb_emails_opened'=>'',
							'nb_emails_clics'=>'',
							'img_emailing'=>'',
							'commentaires'=>'');
}


/*
 * On initialise la classe Form pour générer le formulaire
 * On détermine la forme des champs obligatoires
 * On détermine la forme des templates
 */
$form = new Form(array(
	'id' => "form-creation",
	'class' => "form-creation",
	'required' => array('emailing'),
	'enctype' => 'multipart/form-data',
	'files' => array('fichier_emailing'),
));

$form->required_mark = ' <span class="required">('.$dico->t('obligatoire').')</span>';

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;


/*
 * On traite les données si le formulaire est validé
 * On enregistre les infos : si id>0, on met à jour, sinon on créé
 */
if ($form->is_submitted() and $form->validate()) {
	$action_form = "create";
	if ($form->value('id') > 0) {
		$action_form = "update";
	}
	$data = $form->escape_values();
	if (is_array($data['fichier_emailing'])) {
		$dir = $config->get("medias_path")."www/medias/images/emailing/";
		preg_match("/\.[^\.]+$/", $data['fichier_emailing']['name'], $matches);
		$extension = $matches[0];
		$new_filename = 'email-'.time().$extension;
		move_uploaded_file($data['fichier_emailing']['tmp_name'], $dir.$new_filename);
		$data['img_emailing'] = $new_filename;
	}
	switch ($stats->edit($data, $action_form)) {
		case StatsEmailing::CREATED :
			$message = '<div class="message_succes">'.$dico->t("DonneesAjoutees").'</div>';
			$form->reset();
			break;
		case StatsEmailing::UPDATED :
			$message = '<div class="message_succes">'.$dico->t("DonneesUpdated").'</div>';
			$form->reset();
			break;
		case StatsEmailing::ALLREADYEXISTS :
			$message = '<div class="message_error">'.$dico->t("DonneesExistent").'</div>';
			break;
	}
}


/*
 * On recupère le message que l'on doit afficher
 */
if (!isset($message)) {
	$message = "";
}


/*
 * On récupère les codes des filiales
 */
$query = "SELECT id, code_version FROM dt_filiales ORDER BY code_version";
$res = mysql_query($query);
$liste_filiales = array(0 => "...");
while($row = mysql_fetch_array($res)) {
	$liste_filiales[$row['id']] = $row['code_version'];
}

/* 
 * Valeurs renvoyées dans le template
 */
$buttons[] = $page->l($dico->t('Nouveau'), $url->make("StatsEmailingEdit", array('action' => "edit")));
$buttons[] = $page->l($dico->t('VoirListe'), $url->make("StatsEmailingList", array('action' => "list")));

$titre_page = $dico->t("EditerCampagneEmailing");


$main = <<<HTML
$message
{$form->form_start()}
{$form->fieldset_start($dico->t("SonIdentite"))}
{$form->input(array('name' => "emailing", 'label' => $dico->t("NomCampage"), 'value' => $stats_data['emailing']))}
{$form->date(array('name' => "date_envoi", 'id' => "date_envoi", 'label' => $dico->t("DateEnvoi"), 'format' => $dico->d("FormatDate"), 'value' => $stats_data['date_envoi'] )) }
{$form->select(array('name' => "id_filiales", 'options' => $liste_filiales, 'label' => $dico->t("FilialeConcernee"), 'value' => $stats_data['id_filiales']))}
{$form->input(array('name' => "fichier_emailing", 'type' => "file", 'label' => $dico->t("ImageEmailing"), 'value' => ''))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("SesResultats"))}
{$form->input(array('name' => "nb_emails_db", 'label' => $dico->t("NbreEmailDansDB"), 'class' => 'min_field', 'value' => $stats_data['nb_emails_db']))}
{$form->input(array('name' => "nb_emails_send", 'label' => $dico->t("NbreEmailEnvoyes"), 'class' => 'min_field', 'value' => $stats_data['nb_emails_send']))}
{$form->input(array('name' => "nb_desabonnements", 'label' => $dico->t("NbreEmailDesabonnes"), 'class' => 'min_field', 'value' => $stats_data['nb_desabonnements']))}
{$form->input(array('name' => "nb_emails_opened", 'label' => $dico->t("NbreEmailOuvert"), 'class' => 'min_field', 'value' => $stats_data['nb_emails_opened']))}
{$form->input(array('name' => "nb_emails_clics", 'label' => $dico->t("NbreEmailCliques"), 'class' => 'min_field', 'value' => $stats_data['nb_emails_clics']))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("VosCommentaires"))}
{$form->textarea(array('name' => "commentaires", 'id'=> "1", 'value'=> $stats_data['commentaires'], 'template' => "#{field}"))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("Validation"))}
{$form->input(array('name' => "creer", 'type' => "submit", 'value' => $dico->t("Enregistrer"), 'template' => "#{field}"))}
{$form->input(array('name' => "id", 'type' => "hidden", 'value' => $stats_data['id']))}
{$form->fieldset_end()}
{$form->form_end()}
HTML;

$right = "";
if (!empty($stats_data['img_emailing'])) {
	$right = '<div>
				<a href="'.$config->get("medias_url").'medias/images/emailing/'.$stats_data['img_emailing'].'" class="thickbox" >
				<img src="'.$config->get("medias_url").'medias/images/emailing/'.$stats_data['img_emailing'].'" alt="'.$stats_data['emailing'].'" style="width:200px;border:1px solid #CCC;" />
				</a>
			</div>';
}


?>
