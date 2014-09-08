<?php

require_once "abstract_object.php";

class Personnalisation {

	const UPLOAD_ERROR = 1;
	const INVALID_FORMAT = 2;
	const TOO_SMALL_FILE = 3;
	const TOO_LARGE_FILE = 4;
	const TOO_SMALL_WIDTH = 5;
	const TOO_LARGE_WIDTH = 6;
	const TOO_SMALL_HEIGHT = 7;
	const TOO_LARGE_HEIGHT = 8;

	function __construct($sql, $url, $path_files, $path_www) {
		$this->sql = $sql;
		$this->url = $url;
		$this->path_files = $path_files;
		$this->path_www = $path_www;
	}

	function get_default($id_produits, $min_statut = 0) {
		$personnalisations = array(
			'textes' => array(),
			'images' => array(),
		);
		$q = <<<SQL
SELECT * FROM dt_produits_perso_textes WHERE id_produits = {$id_produits}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			if ($row['statut'] >= $min_statut) {
				$personnalisations['textes'][$row['id']] = $row;
			}
		}

		$q = <<<SQL
SELECT * FROM dt_produits_perso_images WHERE id_produits = {$id_produits}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			if ($row['statut'] >= $min_statut) {
				$personnalisations['images'][$row['id']] = $row;
			}
		}

		return $personnalisations;
	}

	function edit_default($id_produits, $nl_tag = false) {
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
color: black;
border: none;
CSS;
			$css .= $texte['css'];
			$css = preg_replace("/\s+/", " ", $css);
			$readonly = "";
			$editable = "";
			switch ($texte['statut']) {
				case 0 :
					$readonly = 'readonly disabled="disabled"';
					break;
				case 1 :
					$editable = "editable";
					break;
				case 2 :
					$editable = "editable required";
					break;
			}
			$maxlength = $texte['max_caracteres'] ? 'maxlength="'.$texte['max_caracteres'].'"' : "";
			$name = "personnalisation[textes][$id_texte]";
			$contenu = $texte['contenu'];
			if ($nl_tag) {
				$contenu = str_replace("\n", $nl_tag, $texte['contenu']);
			}
			$html .= <<<HTML
<textarea {$readonly} {$maxlength} class="personnalisation-produit-texte {$editable}" style="{$css}" name="{$name}">{$contenu}</textarea>
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
			if ($image['statut']) {
				$input = <<<HTML
<table style="height: 100%; width: 100%;"><tr><td style="vertical-align: middle;">
<input type="file" style="display: none;" />
</td></tr></table>
HTML;
				$editable = "editable";
				if ($image['statut'] == 2) {
					$editable .= " required";
				}
			}
			$html .= <<<HTML
<div class="personnalisation-produit-image {$editable}" style="{$css}" id_image="{$id_image}">{$input}</div>
HTML;
		}
		$html .= <<<HTML
</div>
</div>
HTML;

		return $html;
	}

	function add_image($id_image, $fichier) {
		$return = array(
			'id_image' => $id_image,
			'url' => "",
			'error' => 0,
			'image' => null,
			'fichier' => "",
			'apercu' => "",
		);
		if ($fichier['error'] == 0) {
			$name = $fichier['name'];
			$tmp_name = $fichier['tmp_name'];
			preg_match("/(\.[^\.]*)$/", $name, $matches);
			$ext = $matches[1];
			$md5 = md5_file($tmp_name);
			$file_name = $md5.$ext;
			$web_name = $md5.".png";
			move_uploaded_file($tmp_name, $this->path_files.$file_name);
			try {
				$im = new Imagick($this->path_files.$file_name);
				$data_image = $this->data_image($id_image);
				if ($error = $this->check_image($im, $data_image)) {
					$return['error'] = $error;
					$return['image'] = $data_image;

					return $return;
				}
				else {
					if (!file_exists($this->path_www.$web_name)) {
						$im->setImageFormat('png');

						$im->resizeImage(500, 500, Imagick::FILTER_LANCZOS, 1, true);

						$im->writeImage( $this->path_www.$web_name);
					}
				}
				$im->clear();
				$im->destroy(); 
			}
			catch (ImagickException $e) {
				$return['error'] = self::INVALID_FORMAT;

				return $return;
			}

			$return['url'] = $this->url.$web_name;
			$return['apercu'] = $web_name;
			$return['fichier'] = $file_name;

			return $return;
		}

		$return['error'] = self::UPLOAD_ERROR;
		
		return $return;
	}

	function data_image($id_image) {
		$q = <<<SQL
SELECT * FROM dt_produits_perso_images WHERE id = $id_image
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row;
	}

	function check_image($im, $data_image) {
		$format = $im->getImageFormat();
		if ($this->is_vector($format) or $format == "TIFF") {
			return 0;
		}
		else if ($format == "JPEG") {
			$length = $im->getImageLength();
			if ($data_image['min_poids'] and ($length < (1000.0 * $data_image['min_poids']))) {
				return self::TOO_SMALL_FILE;
			}
			if ($data_image['max_poids'] and ($length > (1000.0 * $data_image['max_poids']))) {
				return self::TOO_LARGE_FILE;
			}
			
			$width = $im->getImageWidth();
			if ($data_image['min_largeur'] and ($width < $data_image['min_largeur'])) {
				return self::TOO_SMALL_WIDTH;
			}
			if ($data_image['max_largeur'] and ($width > $data_image['max_largeur'])) {
				return self::TOO_LARGE_WIDTH;
			}

			$width = $im->getImageHeight();
			if ($data_image['min_hauteur'] and ($width < $data_image['min_hauteur'])) {
				return self::TOO_SMALL_HEIGHT;
			}
			if ($data_image['max_hauteur'] and ($width > $data_image['max_hauteur'])) {
				return self::TOO_LARGE_HEIGHT;
			}
		}
		else {
			return self::INVALID_FORMAT;
		}
	}

	function is_vector($format) {
		switch ($format) {
			case "PDF" :
			case "PS" :
			case "SVG" :
				return true;
		}
		return false;
	}
}
