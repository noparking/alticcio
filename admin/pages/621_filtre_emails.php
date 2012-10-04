<?php
/*
 * Configuration
 */
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$sql = new Mysql($config->db());
$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));
$phrase = new Phrase($sql);
$form = new Form(array(
	'id' => "form-upload",
	'class' => "form-upload",
	'files' => array("new_csv_file"),
));

$html = "";
if ($form->is_submitted()) {
	$data = $form->escape_values();
	if ($file = $form->value('new_csv_file')) {
		$dir = $config->get("medias_path")."www/medias/docs/csv/";
		if (is_array($file)) {
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			if ($ext == ".csv") {
				$file_name = 'import_emails_'.time().$ext;
				move_uploaded_file($file['tmp_name'], $dir.$file_name);
				$sql->query('TRUNCATE dt_filtre_emails');
				$lines = file($dir.$file_name);
				$n = 0;
				foreach ($lines as $line) {
					$new_email = str_replace("\"", "", $line);
					$new_email = trim(str_replace(" ", "", $new_email));
					$new_email = mysql_real_escape_string($new_email);
					$q = "INSERT INTO dt_filtre_emails SET id='', email='".$new_email."', niveau=0 ";
					$sql->query($q);
					$n++;
				}
				$html .= '<p class="message_succes">'.$dico->t('FichierTelecharge').' : '.$file_name.'</p>';
				$html .= '<p class="message">'.$n.' '.$dico->t('ItemsAjoutes').'</p>';
				$html .= '<p class="message">'.$page->l($dico->t('ExporterEmailsCorrects'), $url->make('FiltreEmails', array("action"=>"export"))).'</p>';
			}
			else {
				$html .= '<p class="message_error">'.$dico->t('VotreFichierNonCSV').'</p>';
			}
		}
	}
}


if ($url->get('action') == "export") {
	// ON récupère les adresses emails
	$q = "SELECT id, email FROM dt_filtre_emails ORDER BY email";
	$rs = $sql->query($q);
	$liste_emails = array();
	while ($row = $sql->fetch($rs)) {
		$liste_emails[$row['id']] = trim(str_replace(array("'"," "),"",$row['email']));
	}
	// On les filtre
	$previous_email = "";
	foreach($liste_emails as $key => $email) {
		if ($previous_email == $email) {
			// doublons
			$query = "UPDATE dt_filtre_emails SET niveau = 2 WHERE id = ".$key;
    		$sql->query($query);
		}
		else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			// adresses non valides
	    	$query = "UPDATE dt_filtre_emails SET niveau = 1 WHERE id = ".$key;
	    	$sql->query($query);
		}
		$previous_email = $email;
	}
	// On les exporte
	$dir = $config->get("medias_path")."www/medias/docs/csv/";
	$filename1 = "export_emails_ok_".time().".csv";
	$filename2 = "export_emails_prb_".time().".csv";
	$fp1 = fopen($dir.$filename1,"a+");
	$q_export1 = "SELECT email FROM dt_filtre_emails WHERE niveau = 0 ORDER BY email";
	$rs_export1 = $sql->query($q_export1);
	while($row_export1 = $sql->fetch($rs_export1)) {
		fputs($fp1, $row_export1['email']."\r\n");
	}
	fclose($fp1);
	$fp2 = fopen($dir.$filename2,"a+");
	$q_export2 = "SELECT email FROM dt_filtre_emails WHERE niveau = 1 ORDER BY email";
	$rs_export2 = $sql->query($q_export2);
	while($row_export2 = $sql->fetch($rs_export2)) {
		fputs($fp2, $row_export2['email']."\r\n");
	}
	fclose($fp2);
	$html .= '<p class="message"><a href="'.$config->get("medias_url").'medias/docs/csv/'.$filename1.'">'.$dico->t('ExporterEmailsCorrects').'</a></p>';
	$html .= '<p class="message"><a href="'.$config->get("medias_url").'medias/docs/csv/'.$filename2.'">'.$dico->t('ExporterEmailsIncorrects').'</a></p>';
	
}


$menu->current('main/params/tools');

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.form.js");


$titre_page = $dico->t('FiltrerEmails');


$form_start = $form->form_start();
$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;
$main = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images", 'id' => "produit-section-images-new"))}
{$form->input(array('type' => "file", 'name' => "new_csv_file", 'label' => $dico->t('SelectFichier') ))}
{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter')))}
{$form->fieldset_end()}
$html
HTML;

$form_end = $form->form_end();
?>