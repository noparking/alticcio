<?php

if (isset($_POST['dteditor']) and $_POST['dteditor'] == "img") {
	if (isset($_FILES['image']) and preg_match("/\.(jpg|jpeg|gif|png)$/i", $_FILES['image']['name'])) {
		$uploaddir = $config->get("medias_path")."/www/medias/images/dteditor/";
		preg_match("/\.([^\.]*)$/",  $_FILES['image']['name'], $matches);
		$ext = $matches[1];

		move_uploaded_file($_FILES['image']['tmp_name'], $uploaddir."temp");

		switch(strtolower($ext)) {
			case 'jpeg':
			case 'jpg':
				$img_src = imagecreatefromjpeg($uploaddir."temp");
				$save_function = "imagejpeg";
				break;
			case 'gif':
				$img_src = imagecreatefromgif($uploaddir."temp");
				$save_function = "imagegif";
				break;
			case 'png':
				$img_src = imagecreatefrompng($uploaddir."temp");
				$save_function = "imagepng";
				break;
		}
		$src_w = imagesx($img_src);
		$src_h = imagesy($img_src);
		if ($largeur = preg_replace("/x.*$/", "", $_POST['size'])) {
			if ($largeur > $src_w) {
				$largeur = $src_w;
			}
			$hauteur = ceil(($src_h / $src_w) * $largeur);

			$img_dest = imagecreatetruecolor($largeur, $hauteur);
			imagecopyresized($img_dest, $img_src, 0, 0, 0, 0, $largeur, $hauteur, $src_w, $src_h);
			$save_function($img_dest, $uploaddir."temp");
		}
		else {
			$save_function($img_src, $uploaddir."temp");
		}


		$file_name = md5_file($uploaddir."temp").".".$ext;
		rename($uploaddir."temp", $uploaddir.$file_name);

		$alt = $title = (isset($_POST['title']) ? $_POST['title'] : $_POST['title']);
		$url = $config->core_media("dteditor/$file_name");
		$data = array(
			'status' => 1,
			'src' => $url,
			'title' => $alt,
			'alt' => $alt,
		);
	}
	else {
		$data = array(
			'status' => 0,
			'error' => $dico->t("FichierInvalide"),
		);
	}
	echo json_encode($data);
	exit;
}
