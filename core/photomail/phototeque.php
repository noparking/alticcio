<?php

class Phototeque {
	
	private $sql;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function themes($order = "id") {
		if (!$order) $order = 'id';
		switch ($order) {
			case 'id': $direction = "DESC"; break;
			case 'annee': $direction = "DESC"; break;
			case 'nom': $direction = "ASC"; break;
			case 'nb_photos': $direction = "DESC"; break;
		}
		$q = "SELECT tp.id AS id, tp.annee AS annee, phr.phrase AS nom, count(ptp.id_photos) AS nb_photos FROM dt_themes_photos AS tp";
		$q .= " INNER JOIN dt_phrases AS phr ON tp.phrase_nom = phr.id";
		$q .= " INNER JOIN dt_photos_themes_photos AS ptp ON ptp.id_themes_photos = tp.id";
		$q .= " WHERE tp.actif = '1'";
		$q .= " GROUP BY ptp.id_themes_photos";
		if ($order == "iphone") {
			$q .= " ORDER BY annee DESC, nom ASC";
		}
		else {
			$q .= " ORDER BY $order $direction";
		}
		$result = $this->sql->query($q);
		$themes = array();
		while ($row = $this->sql->fetch($result)) {
			$themes[] = $row;
		}
		return $themes;
	}
	
	public function theme($id) {
		$di = (int)$id;
		$q = "SELECT tp.id AS id, tp.annee AS annee, phr.phrase AS nom FROM dt_themes_photos AS tp";
		$q .= " LEFT OUTER JOIN dt_phrases AS phr ON tp.phrase_nom = phr.id";
		$q .= " WHERE tp.id = $id";
		$result = $this->sql->query($q);
		$theme =  $this->sql->fetch($result);
		$theme['photos'] = $this->photos($id);
		return $theme;
	}
	
	public function photos($id_themes_photos) {
		$id_themes_photos = (int)$id_themes_photos;
		$q = "SELECT p.id AS id, p.ref AS ref, p.type AS type, p.acces AS acces, p.date AS date, phr.phrase AS legende, ";
		$q .= " pg.id AS photographe_id, pg.email AS photographe_email, pg.nom AS photographe_nom";
		$q .= " FROM dt_photos AS p INNER JOIN dt_photographes AS pg ON p.id_photographes = pg.id";
		$q .= " LEFT OUTER JOIN dt_phrases AS phr ON p.phrase_legende = phr.id";
		$q .= " INNER JOIN dt_photos_themes_photos AS ptp ON ptp.id_photos = p.id";
		$q .= " WHERE ptp.id_themes_photos = $id_themes_photos";
		$result = $this->sql->query($q);
		$photos = array();
		while ($row = $this->sql->fetch($result)) {
			$photos[] = $row;
		}
		return $photos;
	}
	
	public function photo($id) {
		$id = (int)$id;
		$q = "SELECT p.id AS id, p.ref AS ref, p.type AS type, p.acces AS acces, p.date AS date, phr.phrase AS legende,";
		$q .= " pg.id AS photographe_id, pg.email AS photographe_email, pg.nom AS photographe_nom";
		$q .= " FROM dt_photos AS p INNER JOIN dt_photographes AS pg ON p.id_photographes = pg.id";
		$q .= " LEFT OUTER JOIN dt_phrases AS phr ON p.phrase_legende = phr.id";
		$q .= " WHERE p.id = $id";
		$result = $this->sql->query($q);
		return $this->sql->fetch($result);
	}
}

?>
