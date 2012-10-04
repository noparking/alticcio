<?php

require "../../outils/exterieurs/jpgraph/src/datamatrix/datamatrix.inc.php";

$data = $_GET['url'];

$encoder = DatamatrixFactory::Create();
$backend = DatamatrixBackendFactory::Create($encoder);

if (isset($_GET['width'])) {
	$backend->SetModuleWidth($_GET['width']);
}

if (isset($_GET['color'])) {
	$colors = explode("-", $_GET['color']);
	$backend->SetColor($colors[0], $colors[1], $colors[2]);
}

if (isset($_GET['quiet'])) {
	$backend->SetQuietZone($_GET['quiet']);
}

if (isset($_GET['encoding'])) {
	$encoder->SetEncoding(constant($_GET['encoding']));
}

$backend->Stroke($data);
