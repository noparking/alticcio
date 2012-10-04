<?php

require "../../outils/dico.php";

$dico = new Dico("en_UK");
$dico->add("traductions");

header('Content-Type: text/html; charset=utf-8'); 
?>

<html>
<head>
<style>
span.untranslated-term {
	color: red;
}
span.default-term {
	color: green;
}
</style>
</head>
<body>
<?php
echo "<p>".$dico->t("HelloWorld")."</p>\n";
echo "<p>".$dico->t("Bonjour le monde !!!")."</p>\n";
echo "<p>".$dico->t("PhraseDefault")."</p>\n";
echo "<p>".$dico->t("Phrase par défault")."</p>\n";
echo "<p>".$dico->t("Phrase non traduite")."</p>\n";
?>
</body>
</html>