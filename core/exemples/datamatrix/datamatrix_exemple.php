<?php


$url = isset($_POST['url']) ? $_POST['url'] : "";

$search = array("http://www.", "http://", "https://www", "https://", "rtsp://");
$replace = array(2, 1, 4, 3, 5);
$url_encoded = urlencode("04".str_replace($search, $replace, $url));

$encoding = isset($_POST['encoding']) ? $_POST['encoding'] : "ENCODING_TEXT";
$encoding_encoded = urlencode($encoding);

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
	$all_datametrix = <<<HTML
	<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=3" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=6" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&color=navy-lightgray-yellow" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=3&color=navy-lightgray-yellow" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=6&color=navy-lightgray-yellow" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&color=navy-lightgray-yellow&quiet=3" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=3&color=navy-lightgray-yellow&quiet=6" />
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=6&color=navy-lightgray-yellow&quiet=9" />
HTML;
	echo <<<HTML
<h4>{$url}</h4>
<img src="datamatrix.php?url={$url_encoded}&encoding={$encoding_encoded}&width=6" />
HTML;
}
?>
</body>
</html>
