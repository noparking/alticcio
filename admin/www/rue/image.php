<?php

$font_path = isset($_GET['fpath']) ? urldecode($_GET['fpath']) : "";

$format = isset($_GET['format']) ? $_GET['format'] : "png";
$fond = isset($_GET['fond']) ? $_GET['fond'] : "";

$sizes = isset($_GET['size']) ? explode("\n", urldecode($_GET['size'])) : array();
$fonts = isset($_GET['font']) ? explode("\n", urldecode($_GET['font'])) : array();
$texts = isset($_GET['text']) ? explode("\n", urldecode($_GET['text'])) : array();

include "image.inc.php";

switch ($format) {
	case "svg" :
		header('Content-type: image/svg+xml');
		header('Content-Disposition: inline; filename=rue.svg');
		$factor = 5;
		break;
	default :
		header('Content-type: image/png');
		$factor = isset($_GET['factor']) ? $_GET['factor'] : 1;
		break;
}

switch ($fond) {
	case "rue1" :
		$color_fond = "#003196";
		$fillcolor_fond = "#ffffff";
		$image_fond = get_image_fond($fond, $factor, $format);
		break;
	case "rue2" :
		$color_fond = "#003196";
		$fillcolor_fond = "#ffffff";
		$image_fond = get_image_fond($fond, $factor, $format);
		break;
	case "rue3" :
		$color_fond = "#2F5F2D";
		$fillcolor_fond = "#003196";
		$image_fond = get_image_fond($fond, $factor, $format, true);
		break;
	default :
		$fillcolor_fond = "#003196";
		$image_fond = get_image_fond($fond, $factor, $format, true);
		break;
}

switch ($format) {
	case "svg" :
		$file_fond = uniqid("fond_");
		imagepng($image_fond, "/tmp/$file_fond.png");
		passthru("convert /tmp/$file_fond.png /tmp/$file_fond.bmp");
		passthru("potrace -s --opaque --fillcolor '$fillcolor_fond' --color '$color_fond' /tmp/$file_fond.bmp");
		$svg = file_get_contents("/tmp/$file_fond.svg");

		if (count($texts)) {
			$image_texte = get_image_texte($sizes, $fonts, $texts, $font_path, array(0, 0, 0), $factor);
			$file_texte = uniqid("texte_");
			imagepng($image_texte, "/tmp/$file_texte.png");
			passthru("convert /tmp/$file_texte.png /tmp/$file_texte.bmp");
			passthru("potrace -s --color '#ffffff' /tmp/$file_texte.bmp");

			$texte_svg = file("/tmp/$file_texte.svg");
			$texte_data = "";
			$switch = false;
			foreach ($texte_svg as $line) {
				if (strpos($line, "<g") !== false) {
					$switch = true;
				}
				if ($switch) {
					$texte_data .= $line;
				}
			}
			echo str_replace("</svg>", $texte_data, $svg);

			unlink("/tmp/$file_texte.png");
			unlink("/tmp/$file_texte.bmp");
			unlink("/tmp/$file_texte.svg");
		}

		unlink("/tmp/$file_fond.png");
		unlink("/tmp/$file_fond.bmp");
		unlink("/tmp/$file_fond.svg");
		break;
	default :
		if (count($texts)) {
			$image_texte = get_image_texte($sizes, $fonts, $texts, $font_path, array(255, 255, 255), $factor, true);
			$width = imagesx($image_texte);
			$height = imagesy($image_texte);
			imagecopy($image_fond, $image_texte, 0, 0, 0, 0, $width, $height);
		}
		imagepng($image_fond);
		break;
}
