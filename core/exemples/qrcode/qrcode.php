<?php

require "../../outils/exterieurs/jpgraph/src/QR/qrencoder.inc.php";

$data = $_GET['url'];

$encoder = new QREncoder();
$backend = QRCodeBackendFactory::Create($encoder);

if (isset($_GET['width'])) {
	$backend->SetModuleWidth($_GET['width']);
}

if (isset($_GET['quiet'])) {
	$backend->SetQuietZone($_GET['quiet']);
}

$backend->Stroke($data);
