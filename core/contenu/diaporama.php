<?php

require_once dirname(__FILE__)."/../produit/abstract_object.php";

class Diaporama extends AbstractObject {

	public $type = "diaporama";
	public $table = "dt_diaporamas";
	public $images_table = "dt_images_diaporamas";
	public $phrase_fields = array();

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT d.id, d.id_langues, d.ref, d.titre, d.section, d.actif FROM dt_diaporamas AS d
SQL;
		if ($filter === null) {
			$filter = $this->sql;
			$q .= "\n".<<<SQL
ORDER BY d.classement 
SQL;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function langues() {
		$q = <<<SQL
SELECT id, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[$row['id']] = $row['code_langue'];
		}
		
		return $liste;
	}

	public function sections() {
		return array(
			'equipment' => "equipment",
			'branding' => "branding",
			'events' => "events",
			'venues' => "venues",
		);
	}

	public function is_free($name, $id_langues, $id = null) {
		$q = "SELECT * FROM dt_diaporamas WHERE ref = '$name' AND id_langues = $id_langues";
		if ($id !== null) {
			$q .= " AND id != $id";
		}
		$res = $this->sql->query($q);
		return $this->sql->fetch($res) ? false : true;
	}

	public function save($data) {
		if (!$this->is_free($data['diaporama']['ref'], $data['diaporama']['id_langues'], isset($data['diaporama']['id']) ? $data['diaporama']['id'] : null)) {
			return -1;
		}
		if (isset($data['vignette_file'])) {
			$file = $data['vignette_file'];
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			$file_name = md5_file($file['tmp_name']).$ext;
			move_uploaded_file($file['tmp_name'], $this->dir.$file_name);
			$data['diaporama']['vignette'] = $file_name;
		}

		return parent::save($data);
	}

	public function duplicate($data) {
		$id = isset($data['diaporama']['id']) ? $data['diaporama']['id'] : $this->id;
		$q = <<<SQL
SELECT ref, legende, affichage, classement FROM dt_images_diaporamas
WHERE id_diaporamas = $id
SQL;
		$res = $this->sql->query($q);

		$data['diaporama']['ref'] = $data['diaporama']['ref']." - copy";
		while(($new_id = parent::duplicate($data)) == -1) {
			$data['diaporama']['ref'] = $data['diaporama']['ref']." - copy";
		}

		$images = array();
		$fields = "";
		$values = array();
		while ($row = $this->sql->fetch($res)) {
			$row['id_diaporamas'] = $new_id;
			$fields = "(".implode(",", array_keys($row)).")";
			$values[] = "('".implode("','", $row)."')";
		}
		$values = implode(",", $values);

		$q = <<<SQL
INSERT INTO dt_images_diaporamas $fields VALUES $values
SQL;
		$res = $this->sql->query($q);

		return $new_id;
	}

	public function delete($data) {
		return parent::delete($data);
	}

	public function last_from_section($section, $number, $id_langues) {
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.section, d.titre, d.url_key
FROM dt_diaporamas AS d
WHERE d.section = '$section' AND d.id_langues = $id_langues AND actif = 1
ORDER BY d.classement
SQL;
		if ($number) {
			$q .= " LIMIT 0, $number";
		}

		$diaporamas = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$images = array();
			$q = <<<SQL
SELECT * FROM dt_images_diaporamas WHERE id_diaporamas = {$row['id']}
ORDER BY classement ASC
SQL;
			$res2 = $this->sql->query($q);
			while ($row2 = $this->sql->fetch($res2)) {
				$images[] = $row2;
			}
			$row['images'] = $images;
			$diaporamas[] = $row;
		}
		return $diaporamas;
	}

	public function infos($ref, $id_langues) {
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.section, d.titre FROM dt_diaporamas AS d
WHERE ref = '$ref' AND actif = 1 AND id_langues = $id_langues
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row;
		}
		return false;
	}

	public function get($id, $id_langues) {
		$id_condition = is_numeric($id) ? "d.id = $id" : "d.url_key = '".addslashes($id)."'";
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.id_themes_photos, d.section, d.titre, d.description, d.id_langues, d.classement
FROM dt_diaporamas AS d
WHERE {$id_condition} AND actif = 1 AND d.id_langues = $id_langues
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			$id_themes_photos = $row['id_themes_photos'];
			$q = <<<SQL
SELECT id.id, id.ref, id.legende
FROM dt_images_diaporamas AS id
INNER JOIN dt_diaporamas AS d ON d.id = id.id_diaporamas
WHERE id.id_diaporamas = {$row['id']} AND d.id_langues = $id_langues
ORDER BY id.classement
SQL;
			$res = $this->sql->query($q);
			$photos = array();
			while ($row2 = $this->sql->fetch($res)) {
				$photos[] = $row2;
			}
			$row['photos'] = $photos;
			return $row;
		}
		return false;
	}

	public function previous($infos) {
		$q = <<<SQL
SELECT titre, url_key FROM dt_diaporamas
WHERE section = '{$infos['section']}'
AND id_langues = {$infos['id_langues']}
AND actif = 1
AND classement < {$infos['classement']}
ORDER BY classement DESC
LIMIT 1
SQL;
		$res = $this->sql->query($q);
		
		return $this->sql->fetch($res); 
	}

	public function next($infos) {
		$q = <<<SQL
SELECT titre, url_key FROM dt_diaporamas
WHERE section = '{$infos['section']}'
AND id_langues = {$infos['id_langues']}
AND actif = 1
AND classement > {$infos['classement']}
ORDER BY classement ASC
LIMIT 1
SQL;
		$res = $this->sql->query($q);
		
		return $this->sql->fetch($res); 
	}
}
