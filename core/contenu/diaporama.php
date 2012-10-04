<?php

require_once dirname(__FILE__)."/../produit/abstract_object.php";

class Diaporama extends AbstractObject {

	protected $type = "diaporama";
	protected $table = "dt_diaporamas";
	protected $phrase_fields = array('phrase_titre', 'phrase_description');

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

	public function themes_photos($lang) {
		$q = <<<SQL
SELECT tp.id, p.phrase AS nom, tp.annee FROM dt_themes_photos AS tp
LEFT OUTER JOIN dt_phrases AS p ON p.id = tp.phrase_nom
LEFT OUTER JOIN dt_langues AS l ON l.id = p.id_langues
WHERE (l.code_langue = '$lang' OR tp.phrase_nom = 0)
ORDER BY annee DESC, nom ASC
SQL;
		$res = $this->sql->query($q);

		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[$row['id']] = array(
				'opt' => $row['nom'],
				'group' => $row['annee'],
			);
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

	public function save($data, $dir) {
		if (!$this->is_free($data['diaporama']['ref'], isset($data['diaporama']['id']) ? $data['diaporama']['id'] : null)) {
			return -1;
		}
		if (isset($data['vignette_file'])) {
			$file = $data['vignette_file'];
			preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
			$ext = $matches[1];
			$file_name = md5_file($file['tmp_name']).$ext;
			move_uploaded_file($file['tmp_name'], $dir.$file_name);
			$data['diaporama']['vignette'] = $file_name;
		}

		return parent::save($data);
	}

	public function delete($data) {
		return parent::delete($data);
	}

	public function last_from_section($section, $number, $id_langues) {
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.section, ph.phrase AS titre FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS ph ON d.phrase_titre = ph.id AND ph.id_langues = $id_langues
WHERE d.section = '$section' AND actif = 1
ORDER BY d.id DESC
LIMIT 0, $number
SQL;
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
		$q = <<<SQL
SELECT d.id, d.ref, d.vignette, d.id_themes_photos, d.section, p1.phrase as titre, p2.phrase AS description
FROM dt_diaporamas AS d
LEFT OUTER JOIN dt_phrases AS p1 ON d.phrase_titre = p1.id AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON d.phrase_description = p2.id AND p2.id_langues = $id_langues
WHERE d.id = $id AND actif = 1
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			$id_themes_photos = $row['id_themes_photos'];
			$q = <<<SQL
SELECT p.id, p.ref, phr.phrase AS legende
FROM dt_photos_themes_photos AS ptp
INNER JOIN dt_photos AS p ON p.id = ptp.id_photos AND id_themes_photos = $id_themes_photos
LEFT OUTER JOIN dt_phrases AS phr ON p.phrase_legende = phr.id AND phr.id_langues = $id_langues
ORDER BY ptp.classement
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
