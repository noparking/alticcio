<?php

header('Content-type: text/html; charset=UTF-8');

include dirname(__FILE__)."/../../includes/config.php";
$font_path = $config->get("font_path");

$all_sizes = array(12, 18, 24, 30, 36, 42);

$texts = isset($_POST['text']) ? $_POST['text'] : array("", "", "");
$sizes = isset($_POST['size']) ? $_POST['size'] : array(36, 24, 18);
$fonts = isset($_POST['font']) ? $_POST['font'] : array("arial", "arial", "arial");
$fond = isset($_POST['fond']) ? $_POST['fond'] : "";

$all_fonts = get_fonts($font_path);

function get_fonts($font_path) {
	$all_fonts = array();
	foreach (scandir($font_path) as $font_file) {
		$dir = $font_path."/".$font_file;
		if (is_dir($dir) and !in_array($font_file, array(".", ".."))) {
			$all_fonts = array_merge($all_fonts, get_fonts($dir));
		}
		if (preg_match("/([^\/]*)\.ttf/", $font_file, $matches)) {
			$all_fonts[] = $matches[1];
		}
		sort($all_fonts);
	}
	return $all_fonts;
}

function get_line_form($i) {
	global $sizes, $texts, $fonts, $all_sizes, $all_fonts;

	$size_options = "";
	foreach ($all_sizes as $size) {
		$selected = $sizes[$i] == $size ? "selected='selected'" : "";
		$size_options .= "<option value='$size' $selected>$size</option>";
	}
	
	$font_options = "";
	foreach ($all_fonts as $font) {
		$selected = $fonts[$i] == $font ? "selected='selected'" : "";
		$font_options .= "<option value='$font' $selected>$font</option>";
	}
	
	$text = $texts[$i];

	return <<<HTML
<div>
	<input name="text[{$i}]" value="{$text}" />
	<select name="font[{$i}]">
		<?php print $font_options ?>
	</select>
	<select name="size[{$i}]">
		<?php print $size_options ?>
	</select>
</div>
HTML;
}
?>

<html>
<head>
	<title>Image</title>
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.form.js" type="text/javascript"></script>
	<script src="js/acheter.js" type="text/javascript"></script>
</head>

<body>
<form method="POST" action="">
<?php 
	for ($i = 0; $i < 3; $i++) {
		print get_line_form($i);
	}
?>
<input type="radio" name="fond" value="rue1" <?php echo $fond == "rue1" ? 'checked="checked"' : "" ?> />
<img alt="Plaque de rue 1" src="image.php?fond=rue1&factor=0.25" />
<input type="radio" name="fond" value="rue2" <?php echo $fond == "rue2" ? 'checked="checked"' : "" ?> />
<img alt="Plaque de rue 2" src="image.php?fond=rue2&factor=0.25" />
<input type="radio" name="fond" value="rue3" <?php echo $fond == "rue3" ? 'checked="checked"' : "" ?> />
<img alt="Plaque de rue 3" src="image.php?fond=rue3&factor=0.25" />
<br />
<input type="submit" name="display" value="Afficher" />
</form>

<?php
$text = implode("\n", $texts);
$alt = implode(" ", $texts);
$size = urlencode(implode("\n", $sizes));
$font = urlencode(implode("\n", $fonts));
$image = "image.php?text=".urlencode($text)."&size=".$size."&font=".$font."&fpath=".urlencode($font_path)."&fond=".$fond;
?>
<img src="<?php print $image ?>" alt="<?php print $alt ?>" title="<?php print $alt ?>" />
<p><a href="<?php print $image."&format=svg" ?>">Format SVG</a></p>

<?php
	if (trim($text)) {
		$hidden_inputs = "";
		foreach (array("sizes", "fonts", "texts") as $tab_name) {
			foreach ($$tab_name as $value) {
				$hidden_inputs .= <<<HTML
<input type="hidden" name="{$tab_name}[]" value="{$value}" />
HTML;
			}
		}
		echo <<<HTML
<br />
<br />
<form method="POST" action="acheter.php" id="form-acheter">
	{$hidden_inputs}
	<input type="hidden" name="fpath" value="{$font_path}" />
	<input type="submit" value="Acheter" />
</form>
HTML;
	}
?>

<div id="attente" style="display: none">
<h3>
<img src="wait.gif" title="wait" alt="wait" />
Création du produit personnalisé en cours.
<img src="wait.gif" title="wait" alt="wait" />
</h3>
<h3>Veuillez patienter (cela peut prendre du temps).</h3>
<h3>Vous allez être redirigé sur notre boutique.</h3>
</div>

</body>
</html>
