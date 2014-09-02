<?php

require_once "abstract_object.php";

class Personnalisation {

	function __construct($sql, $url, $path_files, $path_www) {
		$this->sql = $sql;
		$this->url = $url;
		$this->path_files = $path_files;
		$this->path_www = $path_www;
	}

	function get_default($id_produits) {
		$personnalisations = array(
			'textes' => array(),
			'images' => array(),
		);
		$q = <<<SQL
SELECT * FROM dt_produits_perso_textes WHERE id_produits = {$id_produits}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$personnalisations['textes'][$row['id']] = $row;
		}

		$q = <<<SQL
SELECT * FROM dt_produits_perso_images WHERE id_produits = {$id_produits}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$personnalisations['images'][$row['id']] = $row;
		}

		return $personnalisations;
	}

	function edit_default($id_produits) {
		$html = <<<HTML
<div class="personnalisation-produit" id="personnalisation-produit-{$id_produits}" style="text-align: center;">
<div class="personnalisation-produit-element" style="display: inline-block; position: relative;">
HTML;
		$personnalisations = $this->get_default($id_produits);
		foreach($personnalisations['textes'] as $id_texte => $texte) {
			$css = "";
			$css .= <<<CSS
position: absolute;
z-index: 1;
resize: none;
overflow: hidden;
CSS;
			$css .= $texte['css'];
			$css = preg_replace("/\s+/", " ", $css);
			$readonly = $texte['locked'] ? "readonly" : "";
			$html .= <<<HTML
<textarea {$readonly} class="personnalisation-produit-texte" style="{$css}">{$texte['contenu']}</textarea>
HTML;
		}
		foreach($personnalisations['images'] as $id_image => $image) {
			$css = "";
			if ($image['background']) {
				$css .= "position: relative; z-index: 0;";
			}
			else {
				$css .= "position: absolute; z-index: 1;";
			}
			$css .= <<<CSS
background-image: url({$this->url}{$image['fichier']});
background-size: contain;
background-position: center;
background-repeat: no-repeat;
CSS;
			$css .= $image['css'];
			$css = preg_replace("/\s+/", " ", $css);
			$input = "";
			$editable = "";
			if (!$image['locked']) {
				$input = <<<HTML
<table style="height: 100%; width: 100%;"><tr><td style="vertical-align: middle;">
<input type="file" style="display: none;" />
</td></tr></table>
HTML;
				$editable = "editable";
			}
			$html .= <<<HTML
<div class="personnalisation-produit-image {$editable}" style="{$css}">{$input}</div>
HTML;
		}
		$html .= <<<HTML
</div>
</div>
HTML;

		return $html;
	}

	function add_image($fichier) {
		if ($name = $fichier['name']) {
			$tmp_name = $fichier['tmp_name'];
			preg_match("/(\.[^\.]*)$/", $name, $matches);
			$ext = $matches[1];
			$md5 = md5_file($tmp_name);
			$file_name = $md5.$ext;
			$web_name = $md5.".png";
			move_uploaded_file($tmp_name, $this->path_files.$file_name);

			if (!file_exists($this->path_www.$web_name)) {
				$im = new Imagick($this->path_files.$file_name);
				$im->setImageFormat('png');

				$im->resizeImage(500, 500, imagick::FILTER_LANCZOS, 1, true);

				$im->writeImage( $this->path_www.$web_name);
				$im->clear();
				$im->destroy(); 
			}

			return array(
				'url' => $this->url.$web_name,
			);
		}

		return array(
			'url' => "",
		);
	}
}
