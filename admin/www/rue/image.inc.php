<?php

function get_image_texte($sizes, $fonts, $texts, $font_path, $color = array(0, 0, 0), $factor = 1, $bg_transparent = false) {

	// Create the image
	$width = 400 * $factor;
	$height = 280 * $factor;
	$im = imagecreatetruecolor($width, $height);

	// Create some colors
	$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
	$color = imagecolorallocate($im, $color[0], $color[1], $color[2]);
	$white = imagecolorallocate($im, 255, 255, 255);

	if ($bg_transparent) {
		imagealphablending($im, false);
 		imagefilledrectangle($im, 0, 0, $width, $height, $transparent);
 		imagealphablending($im, true);
	}
	else {
		imagefill($im, 0, 0, $white);
	}

	putenv('GDFONTPATH=' . $font_path);

	// Add the text
	$offsets = array();
	$total_height = 0;
	$space = 10 * $factor;
	$coords=  array();
	foreach ($texts as $i => $text) {
		list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imageftbbox($sizes[$i] * $factor, 0,  $fonts[$i], "qb");
		$height = $lly - $uly + $space;
		$offsets[] = $height;
		$total_height += $height;
	}
	$offset = 0;
	foreach ($texts as $i => $text) {
		$offset += $offsets[$i];
		list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly) = imageftbbox($sizes[$i] * $factor, 0,  $fonts[$i], $text);
		$x = 200 * $factor - (($urx - $ulx) / 2);
		$y = 140 * $factor - ($total_height/2) + $offset - $space;
		if ($text) {
			imagefttext($im, $sizes[$i] * $factor, 0, $x, $y, $color, $fonts[$i], $text);
		}
	}

	if ($bg_transparent) {
		imagealphablending($im, false);
 		imagesavealpha($im, true);
	}

	return $im;
}

function get_image_fond($fond, $factor = 1, $format = "png", $invert = false) {

	// Create the image
	$im = imagecreatetruecolor(400 * $factor, 280 * $factor);

	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$black = imagecolorallocate($im, 0, 0, 0);
	$blue = imagecolorallocate($im, 0, 49, 150);
	$green = imagecolorallocate($im, 47, 95, 45);
	if ($invert) {
		$old_black = $black;
		$black = $white;
		$white = $old_black;
	}
	if ($format == "svg") {
		$blue = $black;
		$green = $white;
	}

	$width = 10 * $factor;
	$y1 = $x1 = 15 * $factor;
	$x2 = imagesx($im) - 15 * $factor;
	$y2 = imagesy($im) - 15 * $factor;

	switch ($fond) {
		case "rue1" :
			imagefill($im, 0, 0, $blue);
			imagefilledrectangle($im, $x1, $y1, $x1 + $width, $y2, $white);
			imagefilledrectangle($im, $x2 - $width, $y1, $x2, $y2, $white);
			imagefilledrectangle($im, $x1, $y1, $x2, $y1 + $width, $white);
			imagefilledrectangle($im, $x1, $y2 - $width, $x2, $y2, $white);
			break;
		case "rue2" :
			imagefill($im, 0, 0, $blue);
			imagefilledarc($im, $x1 , $y1 , 60 * $factor, 60 * $factor, 0, 90, $white, IMG_ARC_PIE);
			imagefilledarc($im, $x1 , $y1 , 40 * $factor, 40 * $factor, 0, 90, $blue, IMG_ARC_PIE);
			imagefilledarc($im, $x1 , $y2 , 60 * $factor, 60 * $factor, 270, 0, $white, IMG_ARC_PIE);
			imagefilledarc($im, $x1 , $y2 , 40 * $factor, 40 * $factor, 270, 0, $blue, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y1 , 60 * $factor, 60 * $factor, 90, 180, $white, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y1 , 40 * $factor, 40 * $factor, 90, 180, $blue, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y2 , 60 * $factor, 60 * $factor, 180, 270, $white, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y2 , 40 * $factor, 40 * $factor, 180, 270, $blue, IMG_ARC_PIE);
			imagefilledrectangle($im, $x1, $y1 + 20 * $factor, $x1 + $width, $y2 - 20 * $factor, $white);
			imagefilledrectangle($im, $x2 - $width, $y1 + 20 * $factor, $x2, $y2 - 20 * $factor, $white);
			imagefilledrectangle($im, $x1 + 20 * $factor, $y1, $x2 - 20 * $factor, $y1 + $width, $white);
			imagefilledrectangle($im, $x1 + 20 * $factor, $y2 - $width, $x2 - 20 * $factor, $y2, $white);
			break;
		case "rue3" :
			imagefill($im, 0, 0, $green);
			imagefilledrectangle($im, $x1, $y1, $x2, $y2, $blue);
			imagefilledarc($im, $x1 , $y1 , 60 * $factor, 60 * $factor, 0, 90, $green, IMG_ARC_PIE);
			imagefilledarc($im, $x1 , $y2 , 60 * $factor, 60 * $factor, 270, 0, $green, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y1 , 60 * $factor, 60 * $factor, 90, 180, $green, IMG_ARC_PIE);
			imagefilledarc($im, $x2 , $y2 , 60 * $factor, 60 * $factor, 180, 270, $green, IMG_ARC_PIE);
			break;
		default :
			imagefill($im, 0, 0, $blue);
			break;
	}

	return $im;
}
