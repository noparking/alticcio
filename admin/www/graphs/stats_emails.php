<?php
/*
 * On inclue la classe PHP
 */
require_once dirname(__FILE__)."/../../core/outils/exterieurs/artichow/LinePlot.class.php";


/*
 * On vérifie les données transmises dans l'URL
 * values = valeurs de la courbes
 * attributs = noms des valeurs à afficher en Y
 */
if (isset($_GET['values']) === FALSE) {
	exit;
}
else {
	$data = @unserialize($_GET['values']);
}

//if (isset($_GET['attributs']) === FALSE) {
//	exit;
//}
//else {
//	$attributs = @unserialize($_GET['attributs']);
//}

   
/*
 * On créé la courbe
 */
$plot = new LinePlot($data);
$plot->setFillColor(new Color(97, 187, 226, 90));
$plot->setColor(new Color(97, 187, 226, 0));
$plot->setThickness(2);
$plot->yAxis->setLabelPrecision(1);
//$plot->xAxis->setLabelText($attributs);

/*
 * On créé le graphique avec la courbe
 */
$graph = new Graph(700, 300);
$graph->setAntiAliasing = TRUE;
$graph->border->setColor(new Color(255, 255, 255, 0));
$graph->add($plot);
   
/*
 * On affiche le graphique
 */
$graph->draw();

?>