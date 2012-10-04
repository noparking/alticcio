<?php

$url = isset($_POST['url']) ? $_POST['url'] : "";
$url_encoded = urlencode($url);

?>
<html>
<head>
</head>

<body>
<form action="" method="POST">
URL <input name="url" style="width:400px;" />
<input type="submit" value="OK" />
</form>

<?php
if ($url) {
	echo <<<HTML
<h4>{$url}</h4>
<img src="qrcode.php?url={$url_encoded}&width=6" />
HTML;
}
?>
</body>
</html>
