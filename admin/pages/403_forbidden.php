<?php

header("HTTP/1.0 403 Forbidden"); 

$page->template("error");

$titre = "Accès interdit";

$contenu = <<<HTML
Erreur 403 : accès interdit.
HTML;

?>

