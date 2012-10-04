<?php

$page->template('png');

$config->core_include("tribune/tribune2", "tribune/cotes");

$tribune = new Tribune2($_REQUEST);

function scale($x) {
	$echelle = isset($_REQUEST['echelle']) ? $_REQUEST['echelle'] : 10;
	return floor($x / $echelle);
}

function scaletxt($x) {
	return round($x / 10.0, 1). " cm";
}

$siege_offset = 0;
$siege_pied = $tribune->siege_pied();
switch ($tribune->params['siege_type']) {
	case "fauteuil_fond" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-fond.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-fond-plie.png");
		break;
	case "fauteuil_nez" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-nez.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-nez-plie.png");
		break;
	case "fauteuil_sur" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-sur.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/fauteuil-sur-plie.png");
		break;
	case "banc" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/banc.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/banc.png");
		break;
	case "banquette" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/banquette.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/banquette-plie.png");
		break;
	case "coque" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/coque-sans-dossier.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/coque-sans-dossier.png");
		break;
	case "coque_dossier" :
		$siege = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/coque-avec-dossier.png");
		$siege_plie = imagecreatefrompng(dirname(__FILE__)."/../www/medias/images/coque-avec-dossier.png");
		break;
}

$profondeur = $tribune->profondeur_reelle();
$hauteur = $tribune->hauteur_reelle();

$width = scale($profondeur);
if ($tribune->params['type'] == "telescopique") {
	$width += scale($tribune->gradin_profondeur(0)) + 100;
	if ($tribune->siege_type() != "fond") {
		$width += scale($tribune->params['gradin_profondeur']);
	}
}
$height = scale($hauteur);

$y_top = 0;
$y_bottom = 70;
$x_left = 50;
$x_right = 70;

$profondeur_marche = $tribune->demi_marche_profondeur();
$hauteur_marche = $tribune->demi_marche_hauteur();
if ($profondeur_marche) {
}

if ($tribune->params['type'] == "telescopique") {
	// un peu plus de place pour les roulettes
	$rayon_roulette = 5;
	$image = imagecreate($x_left + $x_right + $width, $y_top + $y_bottom + $height + $rayon_roulette);
}
else {
	$rayon_roulette = 0;
	$image = imagecreate($x_left + $x_right + $width, $y_top + $y_bottom + $height);
}


$couleur_fond = imagecolorallocate($image, 255, 255, 255);
//$couleur_tribune = imagecolorallocate($image, 200, 200, 200);
$couleur_tribune = imagecolorallocate($image, 255, 255, 255);
$couleur_gradin = imagecolorallocate($image, 50, 100, 50);
$couleur_siege = imagecolorallocate($image, 0, 0, 0);
$couleur_degagement = imagecolorallocate($image, 100, 200, 100);
//$couleur_gardecorps = imagecolorallocate($image, 200, 100, 100);
$couleur_gardecorps = imagecolorallocate($image, 0, 0, 0);
//$couleur_bardage = imagecolorallocate($image, 100, 100, 200);
$couleur_bardage = imagecolorallocate($image, 150, 150, 150);
$couleur_passage_bardage = imagecolorallocate($image, 180, 180, 180);
$couleur_log = imagecolorallocate($image, 0, 200, 0);
$couleur_marche = imagecolorallocate($image, 0, 0, 0);
$couleur_cotes = imagecolorallocate($image, 150, 150, 150);

imagefill($image, 0, 0, $couleur_fond);

$cotes = new Cotes($image, $x_left, $y_top, $x_left + $width - 1, $y_top + $height - 1, 2, $couleur_cotes);
if ($tribune->params['type'] == "telescopique") {
	$cotes2 = new Cotes($image, $x_left, $y_top, $x_left + scale($profondeur) - 1, $y_top + $height - 1, 2, $couleur_cotes);
}
else {
	$cotes2 = $cotes;
}

// marche
if ($profondeur_marche) {
	imagerectangle($image, $x_left, $height + $y_top - 1, $x_left + scale($profondeur_marche), $height + $y_top - scale($hauteur_marche), $couleur_marche);
	$cotes->left($height + $y_top - scale($hauteur_marche), scale($hauteur_marche), scaletxt($hauteur_marche));
	$cotes->bottom(0, scale($profondeur_marche) + 1, scaletxt($profondeur_marche));
}

$structure = $tribune->structure();
for ($passage = 0; $passage <= 1; $passage++) {

	$profondeur_totale = $profondeur_marche;
	$hauteur_gradins = 0;
	foreach ($structure as $i => $gradin) {
		$gradin_profondeur = $tribune->gradin_profondeur(count($structure) - $i - 1);
		$profondeur_totale += $gradin_profondeur;
		$hauteur_gradins += $tribune->params['gradin_hauteur'];
		if ($passage == 0) {
			$x1 = $x_left + scale($profondeur_marche + $i * ($tribune->params['gradin_profondeur']));
			$x2 = $x1 + scale($gradin_profondeur);
			$y2 = $y_top + $height - 1;
			$y1 = $y2 - scale($tribune->params['gradin_hauteur'] * ($i + 1)) + 1;
			if ($i == 0) {
				$cotes->left($y1, scale($tribune->params['gradin_hauteur']), scaletxt($tribune->params['gradin_hauteur']));
				$cotes->bottom(scale($profondeur_marche), scale($gradin_profondeur) + 1, scaletxt($gradin_profondeur));
			}
			if ($i == count($structure) - 1) {
				$cotes->bottom($x1 - $x_left, scale($gradin_profondeur) + 1, scaletxt($gradin_profondeur));
			}
			imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_tribune);
			imagerectangle($image, $x1, $y1, $x2, $y2, $couleur_marche);
			if ($tribune->params['type'] == "telescopique") {
				imagearc($image, $x1 + $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
				imagearc($image, $x2 - $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
				imagearc($image, $x2 - $rayon_roulette * 3, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
			}
		}
		else if ($passage == 1) {
			$x1 = $x_left + scale($profondeur_marche + $i * $tribune->params['gradin_profondeur']);
			$x2 = $x1 + scale($gradin_profondeur);
			$y2 = $y_top + $height - 1;
			$y1 = $y2 - scale($tribune->params['gradin_hauteur'] * ($i + 2)) + 1;
			//imageline($image, $x2, $y1, $x2, $y2, $couleur_marche); // ligne du fond
			$imagesx = imagesx($siege);
			$imagesy = imagesy($siege);
			$y1 = $y1 + scale($tribune->params['gradin_hauteur']) - scale($tribune->params['siege_hauteur'] + $siege_pied);
			$y2 = $y1 + scale($tribune->params['siege_hauteur'] + $siege_pied) - 1;
			$x2 = $x1 + ($imagesx * scale($tribune->params['siege_hauteur'] + $siege_pied) / $imagesy) - 1;
			if ($tribune->siege_type() == "fond") {
				$siege_offset = scale($gradin_profondeur) - ($x2 - $x1 + 1);
				if ($i == (count($structure) - 1)) {
					$siege_offset -= scale($tribune->garde_corps_arriere());
				}
			}
			imagecopyresized($image, $siege, $siege_offset + $x1, $y1, 0, 0,  $x2 - $x1 + 1, $y2 - $y1 + 1, $imagesx, $imagesy);
			if ($i == 0) {
				$hauteur_siege = $tribune->params['siege_hauteur'] + $siege_pied;
				if (!in_array($tribune->params['siege_type'], array("coque", "coque_dossier", "banc"))) {
					$cotes->left($y1, scale($hauteur_siege), scaletxt($hauteur_siege));
				}
			}
		}
	}
	if ($passage == 0) {
		imageline($image, $x2, $y2, $x2 + scale($tribune->debord_reel_pied_arriere()), $y2, $couleur_marche);
		$cotes->bottom(scale($profondeur_totale), scale($tribune->debord_reel_pied_arriere()), scaletxt($tribune->debord_reel_pied_arriere()));
		$cotes->bottom(scale($profondeur_marche), scale($profondeur_totale - $profondeur_marche) + 1, scaletxt($profondeur_totale - $profondeur_marche));
		$profondeur_totale += $tribune->debord_reel_pied_arriere();
		$cotes->bottom(0, scale($profondeur_totale) + 1, scaletxt($profondeur_totale));

		$hauteur_totale = $tribune->hauteur_reelle();
		$hauteur_garde_corps = $tribune->garde_corps_arriere_hauteur();
		$hauteur_dernier_plateau = $hauteur_gradins;
		$emmarchement_supplementaire = 0;
		if ($tribune->siege_type() == "fond") { // emmarchement supplémentaire si pas de rang déporté
			$y1 = scale($hauteur_totale - $hauteur_gradins - $tribune->params['gradin_hauteur']);
			$y2 = $y1 + scale($tribune->params['gradin_hauteur']);
			imageline($image, $x2, $y1, $x2, $y2, $couleur_marche);
			$emmarchement_supplementaire = $tribune->params['gradin_hauteur'];
		}
		else {
			$hauteur_dernier_plateau -= $tribune->params['gradin_hauteur'];
		}
		$x_2 = $x2;
		$x_1 = $x_2 - scale($tribune->garde_corps_arriere());
		$y_1 =  scale($hauteur_totale - $hauteur_gradins - $emmarchement_supplementaire - $hauteur_garde_corps);
		$y_2 = $y_1 + scale($hauteur_garde_corps);
		imagefilledrectangle($image, $x_1, $y_1, $x_2, $y_2, $couleur_gardecorps); // garde_corps arrière
		$cotes2->right(0, scale($hauteur_totale), scaletxt($hauteur_totale));
		$cotes->right(scale($hauteur_totale - $hauteur_gradins), scale($hauteur_gradins), scaletxt($hauteur_gradins));
		$cotes2->right(scale($hauteur_totale - $hauteur_dernier_plateau), scale($hauteur_dernier_plateau), scaletxt($hauteur_dernier_plateau));
		$cotes2->right(scale($hauteur_totale - $hauteur_gradins - $emmarchement_supplementaire - $hauteur_garde_corps) + 1, scale($hauteur_garde_corps), scaletxt($hauteur_garde_corps));
	}

	// tribune repliée
	if ($tribune->params['type'] == "telescopique" and $passage == 0) {
		$hauteur_rangement = $tribune->hauteur_rangement();
		$cotes->right(scale($hauteur_totale - $hauteur_rangement), scale($hauteur_rangement), scaletxt($hauteur_rangement));
		$x1 = $x_left + scale($profondeur_totale) + 100;
		$y2 = $height - 1;
		$y1 = $y2 - scale($tribune->params['gradin_hauteur']);
		imagefilledrectangle($image, $x1, $y1, $x1 + scale($tribune->params['gradin_profondeur']) - 1, $y2, $couleur_tribune);
		/*if ($tribune->siege_type() != "fond") {
			imagefilledrectangle($image, $x1, $y1, $x1 + scale($tribune->params['gradin_profondeur']), $y2, $couleur_tribune);
			imageline($image, $x1 + scale($tribune->params['gradin_profondeur']), $y1, $x1 + scale($tribune->params['gradin_profondeur']), $y2, $couleur_marche);
		}
		else {*/
			imageline($image, $x1, $y1, $x1, $y2, $couleur_marche);
		//}
		$profondeur_gradin = $tribune->siege_type() == "fond" ? $tribune->gradin_profondeur(0) : $tribune->params['gradin_profondeur'];
		$x2 = $x1 + scale($profondeur_gradin) - 1;
		$y1 = $y2 - scale($tribune->params['gradin_hauteur'] * count($structure)) + 1;
		$cotes->bottom($x1 - $x_left + 1, $x2 - $x1, scaletxt($profondeur_gradin));
		imageline($image, $x2, $y1, $x2, $y2, $couleur_marche);
		imageline($image, $x1, $y2, $x2, $y2, $couleur_marche);
		// roulettes
		imagearc($image, $x1 + $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
		imagearc($image, $x2 - $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
		imagearc($image, $x2 - $rayon_roulette * 3, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
		// rang déporté
		$x_2 = $x2;
		$y_1 = $y1;
		if ($tribune->siege_type() != "fond") {
			$x_2 += scale($tribune->gradin_profondeur(0));
			imagerectangle($image, $x2, $y1, $x_2, $y1 + scale($hauteur_gradins), $couleur_marche);
			imagearc($image, $x2 + $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
			imagearc($image, $x_2 - $rayon_roulette, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
			imagearc($image, $x_2 - $rayon_roulette * 3, $y2, $rayon_roulette * 2, $rayon_roulette * 2, 0, 180, $couleur_marche);
			$cotes->bottom($x2 - $x_left, scale($tribune->gradin_profondeur(0)), scaletxt($tribune->gradin_profondeur(0)));
			$cotes->bottom($x1 - $x_left + 1, $x_2 - $x1, scaletxt($profondeur_gradin + $tribune->gradin_profondeur(0)));
		}
		else { // emmarchement supplémentaire si pas de rang déporté
			$y1 = scale($hauteur_totale - $hauteur_gradins - $tribune->params['gradin_hauteur']);
			$y_2 = $y1 + scale($tribune->params['gradin_hauteur']);
			imageline($image, $x2, $y1, $x2, $y_2, $couleur_marche);
			$y_1 -= scale($tribune->params['gradin_hauteur']);
		}
		foreach ($structure as $i => $gradin) {
			$y2 -= scale($tribune->params['gradin_hauteur']);
			if ($i != count($structure) - 1 or $tribune->siege_type() == "fond") {
				imageline($image, $x1, $y2, $x2, $y2, $couleur_marche);
				/*if ($tribune->siege_type() == "fond") {
					$siege_offset = scale($gradin_profondeur - ($tribune->params['siege_largeur'] + $siege_pied)) - 1;
				}*/
			}
			$imagesx = imagesx($siege_plie);
			$imagesy = imagesy($siege_plie);
			$xoffset = (($i + 1) == count($structure) and $tribune->siege_type() != "fond") ? scale($tribune->params['gradin_profondeur']) : 0;
			$x2bis = $x1 + scale($tribune->params['siege_hauteur'] + $siege_pied) - 1;
			$y1 = $y2 - ($imagesy * scale($tribune->params['siege_hauteur'] + $siege_pied) / $imagesx) - 1;
			imagecopyresized($image, $siege_plie, $x1 + $xoffset + $siege_offset, $y1, 0, 0, $x2bis - $x1 + 1, $y2 - $y1 + 1, $imagesx, $imagesy);
		}
		// garde corps arrière
		imagefilledrectangle($image, $x_2, $y_1, $x_2 - scale($tribune->garde_corps_arriere_hauteur()), $y_1 - scale($tribune->garde_corps_arriere()), $couleur_gardecorps);
	}

}
$cotes->draw();
$cotes2->draw();

ob_start();
imagepng($image);
$png = ob_get_contents();
imagedestroy($image);
