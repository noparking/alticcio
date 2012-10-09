<?php

$js = array("jquery.min.js");
if (file_exists(dirname(__FILE__)."/../www/medias/js/$directory/$name.js")) {
	$js[] = "$directory/$name.js";
}
else if (file_exists(dirname(__FILE__)."/../www/medias/js/default/$name.js")) {
	$js[] = "$directory/$name.js";
}

foreach ($js as $file) {
	echo <<<HTML
<script type="text/javascript" src="{$config->media("$file")}"></script>
HTML;
}
?>

</body>
</html>
