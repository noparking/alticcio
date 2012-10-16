<?php

require_once dirname(__FILE__)."/../produit/abstract_object.php";

class Diaporama extends AbstractObject {

	public $type = "diaporama";
	public $table = "dt_diaporamas";
	public $images_table = "dt_images_diaporamas";
	public $phrase_fields = array('phrase_titre', 'phrase_description', 'phrase_url_key');

	public function liste($lang, &$filter = null) {
		$q = <<<SQL
SELECT d.id, d.ref, p.phrase AS titre, d.section, d.actif FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS p ON p.id = d.phrase_titre
LEFT OUTER JOIN dt_langues AS l ON l.id = p.id_langues
WHERE (l.code_langue = '$lang' OR d.phrase_titre = 0)
SQL;
		if ($filter === null) {
			$filter = $this->sql;
		}
		$res = $filter->query($q);

		$liste = array();
		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
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

	public function is_free($name, $id = null) {
		$q = "SELECT * FROM dt_diaporamas WHERE ref = '$name'";
		if ($id !== null) {
			$q .= " AND id != $id";
		}
		$res = $this->sql->query($q);
		return $this->sql->fetch($res) ? false : true;
	}

	public function save($data) {
		if (!$this->is_free($data['diaporama']['ref'], isset($data['diaporama']['id']) ? $data['diaporama']['id'] : null)) {
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

	public function delete($data) {
		return parent::delete($data);
	}

	public function last_from_section($section, $number, $id_langues) {
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.section, ph.phrase AS titre, ph2.phrase AS url_key
FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS ph ON d.phrase_titre = ph.id AND ph.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS ph2 ON d.phrase_url_key = ph2.id AND ph2.id_langues = $id_langues
WHERE d.section = '$section' AND actif = 1
ORDER BY d.id DESC
SQL;
		if ($number) {
			$q .= " LIMIT 0, $number";
		}

		$diaporamas = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$diaporamas[] = $row;
		}
		return $diaporamas;
	}

	public function infos($ref, $id_langues) {
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.section, ph.phrase AS titre FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS ph ON d.phrase_titre = ph.id AND ph.id_langues = $id_langues
WHERE ref = '$ref' AND actif = 1
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row;
		}
		return false;
	}

	public function get($id, $id_langues) {
		$id_condition = is_numeric($id) ? "d.id = $id" : "p3.phrase = '".addslashes($id)."'";
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.id_themes_photos, d.section, p1.phrase as titre, p2.phrase AS description
FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS p1 ON d.phrase_titre = p1.id AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON d.phrase_description = p2.id AND p2.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p3 ON d.phrase_url_key = p3.id AND p3.id_langues = $id_langues
WHERE {$id_condition} AND actif = 1
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			$id_themes_photos = $row['id_themes_photos'];
			$q = <<<SQL
SELECT id.id, id.ref, phr.phrase AS legende
FROM dt_images_diaporamas AS id
LEFT OUTER JOIN dt_phrases AS phr ON id.phrase_legende = phr.id AND phr.id_langues = $id_langues
WHERE id.id_diaporamas = {$row['id']}
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
}
