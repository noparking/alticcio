<?php
/*
 * Configuration
 */
$config->core_include("outils/form", "outils/mysql", "outils/phrase", "outils/langue");
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

function nettoyage($chaine) {
	return str_replace('"','',$chaine);
}
function prix($chaine) {
	return str_replace(',','.',$chaine);
}

$sql = new Mysql($config->db());
$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));
$phrase = new Phrase($sql);
$form = new Form(array(
	'id' => "form-upload",
	'class' => "form-upload",
	'files' => array("new_batch"),
));

$liste_types_batchs = array(	0 => "...",
						1 => $dico->t('BatchProduitsApplications'),
						2 => $dico->t('BatchSkuAttributs'),
						3 => $dico->t('BatchCreationSku'),
						4 => $dico->t('BatchDegressifs'),
					);
							
$html = "";
if ($form->is_submitted()) {
	$data = $form->escape_values();
	if ($file = $form->value('new_batch')) {
		$dir = $config->get("medias_path")."www/medias/docs/csv/";
		if (is_array($file)) {
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			if ($ext == ".csv") {
				$file_name = 'batch_'.time().$ext;
				move_uploaded_file($file['tmp_name'], $dir.$file_name);
				chmod($dir.$file_name, 777);
				$lines = file($dir.$file_name);
				$n = 0;
				foreach ($lines as $line) {
					if ($n > 0) {
						$line = trim($line);
						switch($data['nom_batch']) {
							case 1:
								list($id_produits,$id_applications) = explode(";", $line);
								$id_applications = nettoyage($id_applications);
								$id_produits = nettoyage($id_produits);
								$q = "UPDATE dt_produits SET id_applications = ".$id_applications." WHERE id = ".$id_produits;
								break;
							case 2: 
								list($id_sku,$id_attributs,$valeur_num) = explode(";", $line);
								$id_sku = nettoyage($id_sku);
								$id_attributs = nettoyage($id_attributs);
								$valeur_num = nettoyage($valeur_num);
								// on contrôle si l'attribut est associé à la table management
								$q0 = "SELECT id FROM dt_sku_attributs_management WHERE id_attributs = ".$id_attributs." AND id_sku = ".$id_sku." ";
								$rs0 = $sql->query($q0);
								if (mysql_num_rows($rs0) == 0) {
									$qb = "INSERT INTO dt_sku_attributs_management SET id_attributs = ".$id_attributs.", id_sku = ".$id_sku.", groupe = 0, classement = 0";
									$sql->query($qb);
								}
								// on contrôle si l'attribut du sku existe et dans ce cas on update
								$q1 = "SELECT id FROM dt_sku_attributs WHERE id_attributs = ".$id_attributs." AND id_sku = ".$id_sku." ";
								$rs1 = $sql->query($q1);
								if (mysql_num_rows($rs1) > 0) {
									$q = "UPDATE dt_sku_attributs SET valeur_numerique = '".prix($valeur_num)."' WHERE id_attributs = ".$id_attributs." AND id_sku = ".$id_sku." ";
								}
								else {
									$q = "INSERT INTO dt_sku_attributs SET id_attributs = ".$id_attributs.", id_sku = ".$id_sku.", valeur_numerique = '".prix($valeur_num)."' ";
								}
								break;
							case 3:
								list($ref, $designation, $fam, $sfam, $ssfam, $prix) = explode(";", $line);
								$ref = nettoyage($ref);
								$designation = nettoyage($designation);
								$fam = nettoyage($fam);
								$sfam = nettoyage($sfam);
								$ssfam = nettoyage($ssfam);
								$prix = nettoyage($prix);
								//$q1 = "SELECT id FROM dt_sku WHERE ref_ultralog = '".$ref."' ";
								//$rs1 = $sql->query($q1);
								//if (mysql_num_rows($rs1) == 0) {
									// on récupère l'id de la famille de vente
									$q2 = "SELECT id FROM dt_familles_ventes WHERE code = '".$fam.$sfam.$ssfam."' ";
									$rs2 = $sql->query($q2);
									$row2 = $sql->fetch($rs2);
									//// on récupère l'id phrase
									$qph = "SELECT id FROM dt_phrases ORDER BY id DESC LIMIT 1";
									$rsph = $sql->query($qph);
									$rowph = $sql->fetch($rsph);
									$q3 = "INSERT INTO dt_phrases SET id=".($rowph['id']+1).", phrase = '".addslashes(strtolower($designation))."', id_langues = 1, date_creation = ".time().", date_update = ".time()." ";
									$rs3 = $sql->query($q3);
									//// on intègre le sku 
									$q4 = "INSERT INTO dt_sku SET id='', ref_ultralog='".$ref."', phrase_ultralog = ".($rowph['id']+1).", id_familles_vente = ".$row2['id'].", date_creation = ".time().", date_modification = ".time().", actif = 1";
									$rs4 = $sql->query($q4);
									$new_sku = mysql_insert_id();
									//// on intégre le prix
									//$q5 = "INSERT INTO dt_prix SET id='', id_sku = ".$new_sku.", montant_ht = '".$prix."', franco = 1";
									//$rs5 = $sql->query($q5);
								//}
								break;
							case 4:
								$liste_prix = explode(";", $line);
								$total_prix = count($liste_prix);
								$q1 = "SELECT id FROM dt_sku WHERE ref_ultralog = '".nettoyage($liste_prix[0])."' ";
								$rs1 = $sql->query($q1);
								$row1 = $sql->fetch($rs1);
								if (mysql_num_rows($rs1) > 0) {
									// on met à jour le prix unitaire
									$q2 = "UPDATE dt_prix SET montant_ht = '".nettoyage(prix($liste_prix[4]))."', franco=".nettoyage($liste_prix[2])." WHERE id_sku = ".$row1['id']." AND id_catalogues = ".nettoyage($liste_prix[1])." ";
									$rs2 = $sql->query($q2);
									// on supprime les anciens dégressifs
									$q3 = "DELETE FROM dt_prix_degressifs WHERE id_sku = ".$row1['id']." AND id_catalogues = ".nettoyage($liste_prix[1])." ";
									$rs3 = $sql->query($q3);
									// on intègre les nouveaux dégressifs
									for($i=5; $i<=$total_prix; $i++) {
										if (!empty($liste_prix[$i]) AND  $liste_prix[$i] > 1) {
											$q4 = "INSERT INTO dt_prix_degressifs SET id_sku =".$row1['id'].",
													id_catalogues = ".nettoyage($liste_prix[1]).",
													quantite = ".nettoyage($liste_prix[$i]).",
													montant_ht = '".nettoyage(prix($liste_prix[$i+1]))."',
													pourcentage = '".(100-(round(nettoyage(($liste_prix[$i+1]*100)/$liste_prix[4]),2)))."' ";
											//echo $q4.'<br/>';
											$rs4 = $sql->query($q4);
										}
										$i = $i+1;
									}
								}
								break;
							default: 
								$q = "";
						}
						if (!empty($q)) {
							$sql->query($q);
						}
					}
					$n++;
				}
				$html .= '<p class="message_succes">'.$dico->t('FichierTelecharge').' : '.$file_name.'</p>';
				$html .= '<p class="message">'.($n-1).' '.$dico->t('ItemsAjoutes').'</p>';
			}
			else {
				$html .= '<p class="message_error">'.$dico->t('VotreFichierNonCSV').'</p>';
			}
		}
	}
}


$menu->current('main/params/tools');

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.form.js");

$titre_page = $dico->t('BatchDB');

$form_start = $form->form_start();
$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;
$main = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('AjouterUneImage'), 'class' => "produit-section produit-section-images", 'id' => "produit-section-images-new"))}
<p>{$form->select(array('name'=>'nom_batch', 'label' => $dico->t('TypeBatch'), 'id' => 'nom_batch', 'options' => $liste_types_batchs, 'value' => ""))}</p>
<p>{$form->input(array('type' => "file", 'name' => "new_batch", 'label' => $dico->t('SelectFichier') ))}</p>
<p>{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter')))}</p>
{$form->fieldset_end()}
$html
<div id="legend">
	<h3>{$dico->t('StructureFichiersCSV')}</h3>
	<p>{$dico->t('PremiereLigneNomColonne')}</p>
	<p>{$dico->t('BatchProduitsApplications')} :<strong> id_produits; id_applications</strong></p>
	<p>{$dico->t('BatchSkuAttributs')} : <strong>id_sku; id_attributs; valeur_numerique</strong></p>
	<p>{$dico->t('BatchCreationSku')} : <strong>ref_ultralog; designation; fam; sfam; ssfam; prix</strong></p>
	<p>{$dico->t('BatchDegressifs')} : <strong>ref_ultralog; id_catalogues; franco; qte1; prix1; qte2; prix2; ...</strong></p>
</div>
HTML;

$form_end = $form->form_end();
?>