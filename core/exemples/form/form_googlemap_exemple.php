<?php

include "../../outils/form.php";

$api_key = "AIzaSyDlsgl4rFOCgEKULumJC-_6-TbaLwVHiXU";

$form = new Form(array(
	'id' => "myform",
	'class' => "formclass",
	'googlemap' => array('mygooglemap'),
));

$error_geolocalisation = "Impossible de géolocaliser cette adresse";
$lat = 48.856614;
$lng = 2.3522219;

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
	.formclass-input-googlemap { height: 300px; width: 400px; }
</style>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=$api_key&sensor=false"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
</head>
<body>

{$form->form_start()}
<h2>Adresse de livraison</h2>
{$form->googleaddress(array('name' => "livraison[adresse]", 'map' => "livraison", 'error' => $error_geolocalisation))}
{$form->googlemap(array('name' => "livraison", 'lat' => "$lat", 'lng' => "$lng", 'zoom' => 8))}
<h2>Adresse de pose</h2>
{$form->googleaddress(array('name' => "pose[adresse]", 'map' => "pose", 'error' => $error_geolocalisation, 'zoom' => 16))}
{$form->googlemap(array('name' => "pose", 'lat' => "$lat", 'lng' => "$lng", 'zoom' => 8))}
{$form->input(array('type' => "submit", 'name' => "valider", 'value' => "Valider", 'template' => "#{field}"))}
{$form->form_end()}
<pre>
HTML;

if (isset($_POST['livraison'])) {
	print_r($_POST['livraison']);
}

if (isset($_POST['pose'])) {
	print_r($_POST['pose']);
}

echo <<<HTML
</pre>
</body>
</html>
HTML;
