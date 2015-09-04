<?php

$source = $url->get('action');
$fichier = urldecode($url->get('id'));

$file = $config->get('asset_import', $source).$fichier;

header('Content-type: image/png');
die(thumb($file)); 

function thumb($file) {
	if (!file_exists($file)) {
		return generic_thumb($file);
	}
	$content = file_get_contents($file);
	$f = tmpfile();
	fwrite($f, $content);
	$meta_data = stream_get_meta_data($f);
	try {
		$im = new Imagick($meta_data["uri"]."[0]");
		$im->setImageFormat('png');
		$im->scaleImage(80, 80, true);
		$thumb = $im->getImageBlob(); 
		$im->clear(); 
		$im->destroy();
	}
	catch (ImagickException $e) {
		return generic_thumb($file);
	}
	fclose($f);

	return $thumb;
}

function generic_thumb($file) {
	$ext = substr(strrchr($file, "."), 1);
	$im = new Imagick(dirname(__FILE__)."/../www/medias/images/icon-file.png");

	if (isset($_GET['tiny']) and $_GET['tiny']) {
		$im->scaleImage(30, 30, true);
		$font_size = 8;
		$left = 4;
		$top = 20;
	}
	else {
		$font_size = 24;
		$left = 10;
		$top = 60;
	}
	
	$color = "#232222";
	$pixel = new ImagickPixel();
	$pixel->setColor($color);
	$draw = new ImagickDraw();
	$draw->setFillColor($pixel);
	if ($ext) {
		$draw->setFontSize($font_size);
		$draw->setFontWeight(600);
		$im->annotateImage($draw, $left, $top, 0, $ext);
	}

	$thumb = $im->getImageBlob(); 
	$im->clear(); 
	$im->destroy();
	
	return $thumb;
}
