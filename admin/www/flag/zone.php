<?php

include "drapeaux.inc.php";

$x = $_GET['x'];
$y = $_GET['y'];
$size = $_GET['size'];
$motif = $_GET['motif'];

$class = "Drapeau$motif";
$drapeau = new $class($size);

echo json_encode(array(
	"zone" => $drapeau->get_zone($x, $y),
));
