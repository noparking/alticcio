<?php

$page->template('png');

$config->core_include("tribune/tribune2", "tribune/cotes", "tribune/sieges");

$tribune = new Tribune2($_REQUEST);

function scale($x) {
	$echelle = isset($_REQUEST['echelle']) ? $_REQUEST['echelle'] : 10;
	return floor($x / $echelle);
}

function scaletxt($x) {
	return round($x / 10.0, 1). " cm";
}

$largeur_exploitable = $tribune->largeur_exploitable();
$profondeur = $tribune->profondeur_reelle();
$profondeur_gradins = $tribune->profondeur_gradins();
$profondeur_marche = $tribune->demi_marche_profondeur();
$siege_type = $tribune->params['siege_type'];

$width = $l = scale($tribune->params['largeur']);
$height = $p = scale($profondeur);
$p_gradins = scale($profondeur_gradins);
$p_sans_marche = scale($profondeur - $profondeur_marche);
// Ajout des zones pour les cotes
$x = 0;
$y = 0;
$height += 120; // en haut et en bas
$width += 70;
$image = imagecreate($width, $height + scale($profondeur_marche));

$couleur_fond = imagecolorallocate($image, 255, 255, 255);
//$couleur_tribune = imagecolorallocate($image, 200, 200, 200);
$couleur_tribune = imagecolorallocate($image, 255, 255, 255);
$couleur_contours_tribune = imagecolorallocate($image, 0, 0, 0);
$couleur_gradin = imagecolorallocate($image, 50, 100, 50);
$couleur_siege = imagecolorallocate($image, 255, 255, 255);
$couleur_siege_contours = imagecolorallocate($image, 109, 199, 217);
$couleur_degagement = imagecolorallocate($image, 100, 200, 100);
//$couleur_gardecorps = imagecolorallocate($image, 200, 100, 100);
$couleur_gardecorps = imagecolorallocate($image, 0, 0, 0);
//$couleur_bardage = imagecolorallocate($image, 100, 100, 200);
$couleur_bardage = imagecolorallocate($image, 70, 70, 70);
$couleur_passage_bardage = imagecolorallocate($image, 180, 180, 180);
$couleur_log = imagecolorallocate($image, 0, 200, 0);
$couleur_marche = imagecolorallocate($image, 0, 0, 0);
$couleur_cotes = imagecolorallocate($image, 150, 150, 150);

$offset_droite = 0;
$offset_gauche = $x;
$offset_haut = $y;

imagefill($image, 0, 0, $couleur_fond);

// Les pieds arrière
imageline($image, $offset_gauche, $offset_haut, $offset_gauche, $offset_haut + scale($tribune->debord_reel_pied_arriere()), $couleur_contours_tribune);
imageline($image, $offset_gauche + $l - 1, $offset_haut, $offset_gauche + $l - 1, $offset_haut + scale($tribune->debord_reel_pied_arriere()), $couleur_contours_tribune);
$offset_haut += scale($tribune->debord_reel_pied_arriere());

// La salle
imagefilledrectangle($image, $offset_gauche, $offset_haut, $offset_gauche + $l - 1, $offset_haut + $p_gradins - 1, $couleur_tribune);
imagerectangle($image, $offset_gauche, $offset_haut, $offset_gauche + $l - 1, $offset_haut + $p_gradins - 1, $couleur_contours_tribune);
$depassement_sieges = 0;
if ($tribune->siege_type() == "nez") {
	$depassement_sieges = $tribune->params['siege_profondeur'] * 0.5;
}
$cotes = new Cotes($image, $x, $y, $x + $l - 1, $y + $p_sans_marche - 1, 2, $couleur_cotes);

$sieges = new Sieges($image, scale($tribune->params['siege_profondeur']), $couleur_siege, $couleur_siege_contours, 2);

// Bardages et garades corps

if (isset($_REQUEST['garde_corps_arriere']) and $_REQUEST['garde_corps_arriere']) {
	$x1 = $offset_gauche;
	$y1 = $offset_haut;
	$x2 = $x1 + $l - 1;
	$y2 = $y1 + scale($tribune->garde_corps_arriere());
	if ($x2 != $x1) {
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_gardecorps);
	}
	//$offset_haut += $y2 - $y1;
}

if (isset($_REQUEST['bardage_gauche']) and $_REQUEST['bardage_gauche']) {
	$x1 = $offset_gauche;
	$y1 = $offset_haut;
	$x2 = $x1 + scale($tribune->bardage("gauche"));
	$y2 = $y1 + scale($profondeur_gradins);
	if ($x2 != $x1) {
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_bardage);
	}
	$offset_gauche += $x2 - $x1;
}

if (isset($_REQUEST['garde_corps_gauche']) and $_REQUEST['garde_corps_gauche']) {
	$x1 = $offset_gauche;
	$y1 = $offset_haut;
	$x2 = $x1 + scale($tribune->garde_corps("gauche"));
	$y2 = $y1 + scale($profondeur_gradins);
	if ($x2 != $x1) {
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_gardecorps);
	}
	$offset_gauche += $x2 - $x1;
}

if (isset($_REQUEST['garde_corps_droite']) and $_REQUEST['garde_corps_droite']) {
	$x1 = $offset_gauche + scale($largeur_exploitable) + $offset_droite;
	$y1 = $offset_haut;
	$x2 = $x1 + scale($tribune->garde_corps("droite"));
	$y2 = $y1 + scale($profondeur_gradins);
	if ($x2 != $x1) {
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_gardecorps);
	}
	$offset_droite = $x2 - $x1;
}

if (isset($_REQUEST['bardage_droite']) and $_REQUEST['bardage_droite']) {
	$x1 = $offset_gauche + scale($largeur_exploitable) + $offset_droite;
	$y1 = $offset_haut;
	$x2 = $x1 + scale($tribune->bardage("droite"));
	$y2 = $y1 + scale($profondeur_gradins);
	if ($x2 != $x1) {
		imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleur_bardage);
	}
	$offset_droite = $x2 - $x1;
}

$cotes->bottom($offset_gauche - $x, scale($largeur_exploitable), scaletxt($largeur_exploitable), scale($depassement_sieges));
$cotes->bottom(0, scale($tribune->params['largeur']), scaletxt($tribune->params['largeur']));
$cotes->right(scale($tribune->debord_reel_pied_arriere()), $p_gradins, scaletxt($profondeur_gradins));
$cotes->right(0, scale($tribune->debord_reel_pied_arriere()), scaletxt($tribune->debord_reel_pied_arriere()));
$cotes->right(0, $p, scaletxt($profondeur));
$cotes->right($p_sans_marche, scale($profondeur_marche), scaletxt($profondeur_marche));

$structure = $tribune->structure();
$largeur_pour_siege = false; // passe à true dès que la largeur d'un siège a été côtée
foreach ($structure as $i => $gradin) {

	$gradin_profondeur = $tribune->gradin_profondeur($i);

	for ($passage = 0; $passage <= 1; $passage++) {
		$offset_gauche_gradin = $offset_gauche;
		foreach ($gradin as $element) {
			if (is_array($element)) {
				$x0 = $offset_gauche_gradin + scale(($element[1] - $tribune->params['siege_largeur']) / 2);
				$y0 = $offset_haut;
				for ($j = 0; $j < $element[0]; $j++) {
					if ($passage == 1) {
						$x1 = $offset_gauche_gradin + scale($j * $element[1] + ($element[1] - $tribune->params['siege_largeur']) / 2);
						$y1 = $offset_haut;
						if ($i == 0 and $tribune->siege_type() == "fond") {
							$y1 += scale($tribune->garde_corps_arriere());
						}
						$x2 = $x1 + scale($tribune->params['siege_largeur']) - 1;
						$y2 = $y1 + scale($tribune->params['siege_profondeur']) - 1;
						$sieges->place($siege_type, $x1, $y1, $x2, $y2, scale($gradin_profondeur));
						if ($i == (count($structure) - 1) and $j == 0 and !$largeur_pour_siege) {
							$cotes->bottom($x1, $x2 - $x1 + 1, scaletxt($element[1]), scale($depassement_sieges));
							$largeur_pour_siege = true;
						}
						if ($i == 0 and $tribune->siege_type() != "fond") {
							$cotes->right($y1, scale($tribune->gradin_profondeur(0)), scaletxt($tribune->gradin_profondeur(0)));
						}
					}
				}
				if ($passage == 1) {
					$sieges->bloc($siege_type, $x0, $y0, $x2, $y2, scale($gradin_profondeur));
				}
				$offset_gauche_gradin += scale($element[0] * $element[1]);
			}
			else {
				if ($passage == 0) {
					$x1 = $offset_gauche_gradin;
					$y1 = $offset_haut;
					$x2 = $x1 + scale($element) - 1;
					$y2 = $offset_haut + scale($profondeur_marche) - 1;
					if ($profondeur_marche) {
						if ($tribune->siege_type() == "fond") {
							imagerectangle($image, $x1, $y1, $x2, $y2, $couleur_marche);
						}
						if ($tribune->siege_type() != "fond" or $i == count($structure) - 1) {
							imagerectangle($image, $x1, $y1 + scale($gradin_profondeur) - 1, $x2, $y2 + scale($gradin_profondeur) - 1, $couleur_marche);
						}
					}
					else {
						if ($tribune->siege_type() == "fond") {
							imageline($image, $x1, $y1, $x2, $y1, $couleur_marche);
						}
						else {
							imageline($image, $x1, $y1 + scale($gradin_profondeur) - 1, $x2, $y1 + scale($gradin_profondeur) - 1, $couleur_marche);
						}
					}
					$cotes->bottom($x1, $x2 - $x1 + 1, scaletxt($element), scale(max($profondeur_marche, $depassement_sieges)));
				}

				$offset_gauche_gradin += scale($element);
			}
			if (($i == 0  and $tribune->siege_type() == "fond") or ($i == 1)) {
				$cotes->right($offset_haut, scale($gradin_profondeur), scaletxt($gradin_profondeur));
			}
		}
	}

	$offset_haut += scale($gradin_profondeur);
}

if ($tribune->siege_type() == "nez") {
	$y1 -= scale($depassement_sieges); 
}
else if ($tribune->siege_type() == "sur") {
	$y1 = scale($profondeur - $profondeur_marche - $tribune->params['siege_profondeur']);
}
$cotes->right($y1, scale($tribune->params['siege_profondeur']), scaletxt($tribune->params['siege_profondeur']));

$cotes->draw();

ob_start();
imagepng($image);
$png = ob_get_contents();
imagedestroy($image);

