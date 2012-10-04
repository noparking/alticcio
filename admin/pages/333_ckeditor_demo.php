<?php

if (isset($_FILES['upload'])) {
	$uploaddir = dirname(__FILE__)."/../medias/images/upload/";
	$uploadfile = $uploaddir . basename($_FILES['upload']['name']);
	move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile);
	echo <<<HTML
<script type="text/javascript">
	window.parent.CKEDITOR.tools.callFunction({$_GET['CKEditorFuncNum']}, '{$config->media('upload/'.$_FILES['upload']['name'])}', '');
</script>
HTML;
	exit;
}

$menu->current("main/params/demockeditor");

$titre_page = $dico->t("DemoCKEditor");
$right = "";

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("ckeditor/ckeditor.js");
$page->javascript[] = $config->media("ckeditor-demo.js");
$page->jsvars[] = array(
	'upload_url' => $url->make("CKEditorDemo"),
	'lang' => substr($config->get('langue'), 0, 2),
);

$main = <<<HTML
<form action="" method="post">
<textarea id="ckeditor-demo" name="ckeditor_demo"></textarea>
<input type="submit" value="Envoyer" />
</form>
HTML;


