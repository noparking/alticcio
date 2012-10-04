<?php
/*
 * Configuration
 */
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$menu->current('main/params/clean');

$titre_page = $dico->t('NettoyerDoublonsSku');


$ref = $url->get("id");
if ($ref > 0) {
	/*
	 * On met à jour les ref des SKU
	 */
	if ($_POST['refsku'] == "ok") {
		$q = "UPDATE dt_sku SET ref_ultralog =".$_POST['ref']." WHERE id =".$_POST['sku'];
		$sql->query($q);
	}
	
	/*
	 * On affiche les détails des SKU
	 */
	$q1 = "SELECT s.id, s.actif, ph.phrase 
			FROM dt_sku AS s
			INNER JOIN dt_phrases AS ph 
			ON s.phrase_ultralog = ph.id
			AND s.ref_ultralog = ".$ref;
	$rs1 = $sql->query($q1);
	$main = '<p>'.$page->l($dico->t('Retour'), $url->make("CleanSku")).'</p>';
	$main .= '<p>'.$dico->t('Reference').' : '.$ref.'</p>';
	$main .= '<form name="doublons" action="'.$url->make("CleanSku").'" method="post">';
	$main .= '<table border="1">';
	while ($row1 = $sql->fetch($rs1)) {
		$main .= '<tr>';
		$nom_statut = "inactif";
		if ($row1['actif'] == 1) {
			$nom_statut = "actif";
		}
		$main .= '<td>'.$row1['id'].'</td>';
		$main .= '<td>'.$row1['phrase'].'</td>';
		$main .= '<td>'.$nom_statut.'</td>';
		$q2 = "SELECT id_produits FROM dt_sku_variantes WHERE id_sku = ".$row1['id'];
		$rs2 = $sql->query($q2);
		if (mysql_num_rows($rs2) > 0) {
			$row2 = $sql->fetch($rs2);
			$q3 = "SELECT p.id, p.actif, ph.phrase 
					FROM dt_produits AS p
					INNER JOIN dt_phrases AS ph 
					ON ph.id = p.phrase_nom 
					AND p.id = ".$row2['id_produits'];
			$rs3 = $sql->query($q3);
			$row3 = $sql->fetch($rs3);
			$nom_statutb = "inactif";
			if ($row3['actif'] == 1) {
				$nom_statutb = "actif";
			}
			$main .= '<td>'.$row3['id'].'</td>';
			$main .= '<td>'.$row3['phrase'].'</td>';
			$main .= '<td>'.$nom_statutb.'</td>';
		}
		else {
			$main .= '<td> - </td><td> - </td><td> - </td>';
		}
		$main .= '<td><input type="checkbox" name="idsku[]" value="'.$row1['id'].'" /></td>';
		$main .= '</tr>';
	}
	$main .= '</table>';
	$main .= '<input type="hidden" name="sku" value="ok" />';
	$main .= '<input type="submit" name="valider" value="'.$dico->t('Envoyer').'" />';
	$main .= '</form>';
	$main .= '<form name="updsku" action="#" method="post">';
	$main .= '<fieldset><legend>'.$dico->t('MettreJourRefSku').'</legend>';
	$main .= '<p><label for="sku">SKU = <input type="text" name="sku" value="" /></label></p>';
	$main .= '<p><label for="ref">REF = <input type="text" name="ref" value="" /></label></p>';
	$main .= '<input type="hidden" name="refsku" value="ok" />';
	$main .= '<p><input type="submit" name="envoyer" value="'.$dico->t('Envoyer').'" /></p>';
	$main .= '</fieldset></form>';
}
else {
	/*
	 * On supprime les sku doublons
	 */
	if ($_POST['sku'] == "ok") {
		foreach($_POST['idsku'] as $key => $id) {
			$q = "DELETE FROM dt_sku WHERE id = ".$id;
			$q1 = "DELETE FROM dt_sku_accessoires WHERE id_sku = ".$id;
			$q2 = "DELETE FROM dt_sku_attributs WHERE id_sku = ".$id;
			$q3 = "DELETE FROM dt_sku_composants WHERE id_sku = ".$id;
			$q4 = "DELETE FROM dt_sku_variantes WHERE id_sku = ".$id;
			$q5 = "DELETE FROM dt_images_sku WHERE id_sku = ".$id;
			$q6 = "DELETE FROM dt_prix WHERE id_sku = ".$id;
			$q7 = "DELETE FROM dt_prix_degressifs WHERE id_sku = ".$id;
			$sql->query($q);
			$sql->query($q1);
			$sql->query($q2);
			$sql->query($q3);
			$sql->query($q4);
			$sql->query($q5);
			$sql->query($q6);
			$sql->query($q7);
		}
	}
	
	/*
	 * On récupère tous les doublons (champ ref_ultralog).
	 */
	$q = "SELECT id, ref_ultralog FROM dt_sku ORDER BY ref_ultralog";
	$rs = $sql->query($q);
	$doublons = array();
	$prev = 0;
	while($row = $sql->fetch($rs)) {
		if ($prev == $row['ref_ultralog'] AND $prev > 0) {
			$doublons[] = $row['ref_ultralog'];
		}
		$prev = $row['ref_ultralog'];
	}
	$doublons_bis = array_unique($doublons);
	
	/*
	 * Affichage
	 */
	$main = '<p>'.count($doublons_bis).' '.$dico->t('ItemsDoublons').'</p>';
	$main .= '<ul id="liste_sku">';
	foreach($doublons_bis as $id => $ref) {
		$link_ref = $url->make("CleanSku", array("action" => "edit", "id" => $ref ));
		$main .= '<li><a href="'.$link_ref.'">REF = '.$ref.'</li>';
	}
	$main .= '</ul>';
}

?>