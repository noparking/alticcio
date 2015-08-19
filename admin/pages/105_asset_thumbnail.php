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
	$ext = substr(strstr(basename($file), "."), 1);
	$im = new Imagick(dirname(__FILE__)."/../www/medias/images/icon-file.png");

	$color = "#009ADB";
	$pixel = new ImagickPixel();
	$pixel->setColor($color);
	$draw = new ImagickDraw();
	$draw->setFillColor($pixel);
	$draw->setstrokewidth(0);
	$draw->setStrokeColor($color);
	$draw->setFontSize(10);
	$im->annotateImage($draw, 3, 20, 0, $ext);

	$thumb = $im->getImageBlob(); 
	$im->clear(); 
	$im->destroy();
	
	return $thumb;
}
