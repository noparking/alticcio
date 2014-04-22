<?php
/*
 * -------------------------
 * On inclue les librairies nécessaires
 */
$config->core_include("extranet/user", "outils/mysql", "outils/form", "outils/phrase", "outils/langue", "produit/catalogue");
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.tablednd.js");
$page->inc("snippets/date-input");

$menu->current('main/products/degressifs');


/*
 * ----------------
 * On initialise les variables
 */
$sql = new Mysql($config->db());
$langue = new Langue($sql);
$id_langue = $langue->id($config->get("langue"));
$catalogue = new Catalogue($sql);

$titre_page = "Importer un dégressif de prix";
$liste_catalogues = array(0=>"...");
foreach($catalogue->liste() as $key => $value) {
    if ($value['statut'] == 1) {
        $liste_catalogues[$value['id']] = $value['nom'];
    }
}
$liste_cles = array("ul"=>"ref_ultralog", "id"=>"id_sku");
$phrase = new Phrase($sql);

$html = "";
$form = new Form(array(
	'id' => "form-upload",
	'class' => "form-upload",
	'files' => array("new_csv_file"),
));
$form_start = $form->form_start();
$template_inline = <<<HTML
#{label} : #{field} #{description}
HTML;


/*
 * ---------------------------------
 * Fonctions de mise en forme
 */
function nettoyage($chaine) {
	return str_replace('"','',$chaine);
}
function prix($chaine) {
	return str_replace(',','.',$chaine);
}


/*
 * --------------------------------
 * On traite le formulaire
 */
if ($form->is_submitted()) {
    $data = $form->escape_values();
    if ($file = $form->value('new_csv_file')) {
	   $dir = $config->get("medias_path")."www/medias/docs/csv/";
	   if (is_array($file)) {
		  preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
		  $ext = $matches[1];
		  if ($ext == ".csv") {
			 $file_name = 'batch_degressifs_'.time().$ext;
			 move_uploaded_file($file['tmp_name'], $dir.$file_name);
			 $lines = file($dir.$file_name);
                $filname_import1 = "import_prix_ul_".time().".csv";
                $fp2 = fopen($dir.$filname_import1, "a+");
                $filname_import2 = "import_degressifs_ul_".time().".csv";
                $fp3 = fopen($dir.$filname_import2, "a+");
			 $n = 0;
                $n1 = 0;
                $n2 = 0;
                $n3 = 0;
			 foreach ($lines as $line) {
				$liste_prix = explode(";", $line);
                    $total_prix = count($liste_prix);
                    if ($n > 0) {
                        if ($liste_prix[0] > 0) {
                            // On génère les fichiers CSV pour ULTRALOG
                            // pour les prix unitaires
                            if ($liste_prix[2] > 0 AND $liste_prix[3] > 0) {
                                $new_line = $liste_prix[0].';"'.nettoyage($liste_prix[1]).'";'.nettoyage(prix($liste_prix[3])).'';
                                fputs($fp2, $new_line."\r\n");
                            }
                            // pour les dégressifs
                            for($c=4; $c<29; $c++) {
                                if (!empty($liste_prix[$c])) {
                                    if ($c%2 == 0) {
                                        $p = $c+1;
                                        $next = $c+2;
                                        $max = 100;
                                        if (!empty($liste_prix[$next])) {
                                            if ($next > 0) {
                                                $max = $liste_prix[$next]-1;
                                            }
                                        }
                                        else {
                                            if ($liste_prix[$c] >= $max) {
                                                $max = 1000;
                                            }
                                            if ($liste_prix[$c] >= $max) {
                                                $max = 10000;
                                            }
                                        }
                                        if (!empty($liste_prix[$c]) AND !empty($liste_prix[$p])) {
                                            $new_lineb = $liste_prix[0].';"'.nettoyage($liste_prix[1]).'";'.$liste_prix[$c].';'.$max.';"'.nettoyage(prix($liste_prix[$p])).'"';
                                            fputs($fp3, $new_lineb."\r\n");
                                        }
                                    }
                                }
                            }
                            // On récupère l'id_sku
                            if ($data['cleprimaire'] == "ul") {
                                // on récupère l'id_sku des ref_ultralog
                                $q0 = "SELECT id FROM dt_sku WHERE ref_ultralog = '".$liste_prix[0]."' ";
                                $rs0 = $sql->query($q0);
                                $row0 = $sql->fetch($rs0);
                                $id_sku = $row0['id'];
                            }
                            else {
                                $id_sku = $liste_prix[0];
                            }
                            // On met à jour le prix unitaire
                            if ($id_sku > 0) { 
                                // si on a une première quantité et un prix, on met à jour le prix unitaire
                               if ($liste_prix[2] > 0 AND $liste_prix[3] > 0) {
                                    $q1 = "SELECT id FROM dt_prix
                                            WHERE id_sku = ".$id_sku."
                                            AND id_catalogues = ".intval($data['catalogues'])." ";
                                    $rs1 = $sql->query($q1);
                                    if (mysql_num_rows($rs1) > 0) { 
                                        $q2 = "UPDATE dt_prix SET montant_ht = '".nettoyage(prix($liste_prix[3]))."'
                                                WHERE id_sku = ".$id_sku."
                                                AND id_catalogues = ".intval($data['catalogues'])." ";
                                        $rs2 = $sql->query($q2);
                                    }
                                    else {
                                        $q2 = "INSERT INTO dt_prix SET id_sku = ".$id_sku.",
                                                montant_ht = '".nettoyage(prix($liste_prix[3]))."',
                                                id_catalogues = ".intval($data['catalogues'])." ";
                                        $rs2 = $sql->query($q2);
                                    }
                                    // si la première quantité est supérieure à 1, on déduit que le mimimum de commande est égal à cette quantité
                                    if ($liste_prix[2] > 1) {
                                        $q2b = "UPDATE dt_sku SET min_commande = ".$liste_prix[2]." WHERE id = ".$id_sku;
                                        $rs2b = $sql->query($q2b);
                                    }
                                    $q3 = "DELETE FROM dt_prix_degressifs
                                            WHERE id_sku = ".$id_sku."
                                            AND id_catalogues = ".intval($data['catalogues'])." ";
                                    $rs3 = $sql->query($q3);
                                    for($i=4; $i<=$total_prix; $i++) {
                                        if (!empty($liste_prix[$i]) AND !empty($liste_prix[$i+1]) AND $liste_prix[$i] > 1 ) {
                                            $q4 = "INSERT INTO dt_prix_degressifs SET id_sku =".$id_sku.",
                                                    id_catalogues = ".intval($data['catalogues']).",
                                                    quantite = ".nettoyage($liste_prix[$i]).",
                                                    montant_ht = '".nettoyage(prix($liste_prix[$i+1]))."',
                                                    pourcentage = '".(100-(round(nettoyage(($liste_prix[$i+1]*100)/$liste_prix[3]),2)))."' ";
                                            $rs4 = $sql->query($q4);
                                        }
                                        $i = $i+1;
                                    }
                                $n3++;
                                }
                            $n2++;
                            }
                        $n1++;
                        }
                    }
                    $n++;
			 }
                fclose($fp2);
                fclose($fp3);
			 $html .= '<p class="message_succes">'.$dico->t('FichierTelecharge').' : '.$file_name.'</p>';
			 $html .= '<p class="message">'.($n-1).' items dans le fichier à importer</p>';
			 $html .= '<p class="message">'.$n1.' items avec une référence UL ou un id_sku</p>';
                $html .= '<p class="message">'.$n2.' items avec un sku en base</p>';
                $html .= '<p class="message">'.$n3.' items avec un prix et un degressif potentiel</p>';
                $html .= '<p class="message"><a href="http://medias.doublet.pro/medias/docs/csv/'.$filname_import1.'">Télécharger le fichier des prix unitaires UL</a></p>';
                $html .= '<p class="message"><a href="http://medias.doublet.pro/medias/docs/csv/'.$filname_import2.'">Télécharger le fichier des prix dégressifs UL</a></p>';
		  }
		  else {
			 $html .= '<p class="message_error">'.$dico->t('VotreFichierNonCSV').'</p>';
		  }
	   }
    }
}
else {
    /*
     * -------------------------
     * On affiche l'étape 1
     */
    $html = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('TelechargerDegressif'), 'class' => "produit-section produit-section-images", 'id' => "produit-section-images-new"))}
<p>{$form->input(array('type' => "file", 'name' => "new_csv_file", 'label' => $dico->t('SelectFichier') ))}<br/>{$dico->t('DegressifEnCSV')}</p>
<p>{$form->select(array('name' => "catalogues", 'label' => $dico->t('SelectCatalogue'), 'options' => $liste_catalogues ))}<br/>{$dico->t('AideChoixCatalogue')}</p>
<p>{$form->select(array('name' => "cleprimaire", 'label' => $dico->t('SelectClePrimaire'), 'options' => $liste_cles ))}<br/>{$dico->t('AideClePrimaireDegressifs')}</p>
<p>{$form->input(array('type' => "submit", 'name' => "add-image", 'value' => $dico->t('Ajouter')))}</p>
{$form->fieldset_end()}
HTML;
}
$form_end = $form->form_end();

$main = $html;
$right = "";


?>