<?php
include "drapeaux.inc.php";

$size = $_GET['size'];
$motif = $_GET['motif'];

$class = "Drapeau$motif";

$drapeau = new $class($size);

for($i = 0; $i < 5; $i++) {
	if (isset($_GET["zone$i"])) {
		$color = $_GET["zone$i"];
		$r = base_convert(substr($color, 0, 2), 16, 10);
		$g = base_convert(substr($color, 2, 2), 16, 10);
		$b = base_convert(substr($color, 4, 2), 16, 10);
		$color = array($r, $g, $b);
		$drapeau->zone($i, $color);
	}
}

$drapeau->draw();
