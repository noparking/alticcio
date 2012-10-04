<?php

include "drapeaux.inc.php";

?>
<html>

<head>
<link href="style.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>

<div id="flag">
<h1>Choisissez un modèle de drapeau.</h1>
</div>

<ul>
<?php
for ($i = 1; $i <= 13; $i++) {
	echo <<<HTML
<li class="flag" id="flag-$i"><img src="drapeau.php?motif=$i&size=60" alt="drapeau" /></li>
HTML;
}
?>
</ul>

<ul class="colors">
<li class="color" style="background-color: #FFF"></li>
<li class="color" style="background-color: #75AADB"></li>
<li class="color" style="background-color: #00F"></li>
<li class="color" style="background-color: #00247D"></li>
<li class="color" style="background-color: #009246"></li>
<li class="color" style="background-color: #14D53A"></li>
<li class="color" style="background-color: #FF0"></li>
<li class="color" style="background-color: #FCD116"></li>
<li class="color" style="background-color: #EF7404"></li>
<li class="color" style="background-color: #CF142B"></li>
<li class="color" style="background-color: #F00"></li>
<li class="color" style="background-color: #000"></li>
</ul>

<script src="jquery.js" language="JavaScript" type="text/javascript"></script>
<script src="jquery.color.utils.js" language="JavaScript" type="text/javascript"></script>
<script src="flag.js" language="JavaScript" type="text/javascript"></script>

</body>
</html>
