<?php

require_once "abstract_content.php";

class StaticPage extends AbstractContent {

	private $sql;
	public $values;

	public function __construct($sql) {
		$this->sql = $sql;
	}

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT p.id, p.nom, l.code_langue, p.actif FROM dt_pages AS p
INNER JOIN dt_langues AS l ON l.id = p.id_langues
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

	public function langues() {
		$langues = array();
		$q = <<<SQL
SELECT id AS id_langues, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$langues[$row['id_langues']] = $row['code_langue'];
		}
		return $langues;
	}

	public function load($id) {
		$id = (int)$id;
		$q = <<<HTML
SELECT * FROM dt_pages
WHERE id = $id
HTML;
		$res = $this->sql->query($q);
		$this->values = $this->sql->fetch($res);
		
		return $this->values; 
	}

	public function is_free($name, $id = null) {
		$q = "SELECT * FROM dt_pages WHERE nom = '$name'";
		if ($id !== null) {
			$q .= " AND id != $id";
		}
		$res = $this->sql->query($q);
		return $this->sql->fetch($res) ? false : true;
	}

	public function save($data) {
		if (isset($data['page']['id'])) {
			if (!$this->is_free($data['page']['nom'], $data['page']['id'])) {
				return -1;
			}
			$id = $data['page']['id'];
			$values = array();
			foreach ($data['page'] as $field => $value) {
				$values[] = "$field = '$value'";
			}
			$q = "UPDATE dt_pages SET ".implode(",", $values)." WHERE id = $id";
			$this->sql->query($q);
		}
		else {
			if (!$this->is_free($data['page']['nom'])) {
				return -1;
			}
			$values = array();
			foreach ($data['page'] as $field => $value) {
				$values[$field] = "'$value'";
			}
			$q = "INSERT INTO dt_pages (".implode(",", array_keys($values)).") VALUES (".implode(",", $values).")"; 
			$this->sql->query($q);
			$id = $this->sql->insert_id();
		}

		return $id;
	}

	public function delete($data) {
		$id = $data['page']['id'];
		$q = "DELETE FROM dt_pages WHERE id = {$id}";
		$this->sql->query($q);

		return 1;
	}

	public function afficher($id_or_name, $display_anyway = false) {
		$q = <<<SQL
SELECT * FROM dt_pages
SQL;
		$q .= is_numeric($id_or_name) ? " WHERE id = $id_or_name" : " WHERE nom = '$id_or_name'";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$statut = $row['actif'] ? "active" : "inactive";
		if ($statut == "actif" and !$this->validate_html($row['contenu'])) {
			$statut = "invalid";
		}
		$html = "<!-- page_start id=\"{$row['id']}\" name=\"{$row['nom']}\" status=\"{$statut}\" -->";
		$html .= ($row['actif'] or $display_anyway) ? $row['contenu'] : "";
		$html .= "<!-- page_end -->";
		preg_match_all("/{bloc=([^}]+)}/", $html, $matches);
		foreach($matches[1] as $bloc_name) {
			$html = str_replace("{bloc=$bloc_name}", $this->afficher_bloc($bloc_name, $display_anyway), $html);
		}
		return $html;
	}

	public function afficher_bloc($id_or_name, $display_anyway = false) {
		$q = <<<SQL
SELECT * FROM dt_blocs
SQL;
		$q .= is_numeric($id_or_name) ? " WHERE id = $id_or_name" : " WHERE nom = '$id_or_name'";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		if ($row) {
			$statut = $row['actif'] ? "actif" : "inactif";
			$html = "<!-- bloc_start id=\"{$row['id']}\" nom=\"{$row['nom']}\" statut=\"{$statut}\" -->";
			$html .= ($row['actif'] or $display_anyway) ? $row['contenu'] : "";
			$html .= "<!-- bloc_end -->";
			preg_match_all("/{bloc=([^}]+)}/", $html, $matches);
			foreach($matches[1] as $bloc_name) {
				$html = str_replace("{bloc=$bloc_name}", $this->afficher_bloc($bloc_name, $display_anyway), $html);
			}
		}
		else {
			$id = is_numeric($id_or_name) ? $id_or_name : "";
			$nom = is_numeric($id_or_name) ? "" : $id_or_name;
			$html = "<!-- bloc_start id=\"{$id}\" nom=\"{$nom}\" statut=\"missing\" -->";
			$html .= "<!-- bloc_end -->";
		}
		return $html;
	}
}
