<?php

header("HTTP/1.0 404 Not Found"); 

$page->template("error");

$titre = "Page introuvable";

$contenu = <<<HTML
Erreur 404 : page non trouvée.
HTML;

?>
