<?php

$menu->current("main/params/dteditor");

$titre_page = "Démo DTEditor";
$right = "";

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->media("dteditor.css");

$main = <<<HTML
<form action="" method="post">
<textarea class="dteditor" id="dteditor-demo1" name="dteditor_demo"></textarea>
<hr />
<textarea class="dteditor" id="dteditor-demo2" name="dteditor_demo2"></textarea>
<input type="submit" value="Envoyer" />
</form>
HTML;
