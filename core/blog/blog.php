<?php

class Blog {

	private $sql;

	public function __construct($sql) {
		$this->sql = $sql;
	}
	public function all_themes($id_blog = null) {
		$q = <<<SQL
SELECT t.id, t.nom, t.id_parent FROM dt_themes_blogs AS t
SQL;
		if ($id_blog !== null) {
			$q .= " " . <<<SQL
INNER JOIN dt_blogs_themes_blogs AS btb ON t.id = btb.id_themes_blogs
WHERE id_blogs = $id_blog
SQL;
		}
		$res = $this->sql->query($q);
		
		$themes = array();
		while ($row = $this->sql->fetch($res)) {
			$themes[] = $row;
		}
		
		return $themes;
	}

	public function save($data) {
		if (isset($data['blog']['id'])) {
			$id = $data['blog']['id'];

			$values = array();
			foreach ($data['blog'] as $field => $value) {
				$values[] = "$field = '$value'";
			}
			$q = "UPDATE dt_blogs SET ".implode(",", $values);
			$q .= " WHERE id=".$id;
			$this->sql->query($q);
			
			$q = "DELETE FROM dt_blogs_themes_blogs WHERE id_blogs = $id";
			$this->sql->query($q);
			
			$q = "DELETE FROM dt_blogs_langues WHERE id_blogs = $id";
			$this->sql->query($q);
		}
		else {
			$fields = array();
			$values = array();
			foreach ($data['blog'] as $field => $value) {
				$fields[] = $field;
				$values[] = "'$value'";
			}
			$q = "INSERT INTO dt_blogs (".implode(",", $fields).") VALUES (".implode(",", $values).")";
			$this->sql->query($q);
			
			$id = $this->sql->insert_id();

		}

		if (isset($data['themes'])) {
			$themes_blogs = array();
			foreach ($data['themes'] as $theme => $checked) {
				if ($checked) {
					$themes_blogs[] = "($id, $theme)";
				}
			}
			if (count($themes_blogs)) {
				$q = "INSERT INTO dt_blogs_themes_blogs (id_blogs, id_themes_blogs) VALUES ".implode(",", $themes_blogs);
				$this->sql->query($q);
			}
		}

		if (isset($data['langues'])) {
			$langues_blogs = array();
			foreach ($data['langues'] as $langue => $checked) {
				if ($checked) {
					$langues_blogs[] = "($id, $langue)";
				}
			}
			if (count($langues_blogs)) {
				$q = "INSERT INTO dt_blogs_langues (id_blogs, id_langues) VALUES ".implode(",", $langues_blogs);
				$this->sql->query($q);
			}
		}

		return $id;
	}

	public function load($id) {
		$q = <<<SQL
SELECT id, nom, `access` FROM dt_blogs WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			foreach ($row as $cle => $valeur) {
				$this->$cle = $valeur;
			}
		}
		$q = <<<SQL
SELECT id_themes_blogs FROM dt_blogs_themes_blogs WHERE id_blogs = $id
SQL;
		$res = $this->sql->query($q);
		$this->themes = array();
		while ($row = $this->sql->fetch($res)) {
			$this->themes[] = $row['id_themes_blogs'];
		}
		$q = <<<SQL
SELECT id_langues FROM dt_blogs_langues WHERE id_blogs = $id
SQL;
		$res = $this->sql->query($q);
		$this->langues = array();
		while ($row = $this->sql->fetch($res)) {
			$this->langues[] = $row['id_langues'];
		}
	}

	public function delete($data) {
		$id = $data['blog']['id'];
		$q = "DELETE FROM dt_blogs_themes_blogs WHERE id_blogs = $id";
		$this->sql->query($q);

		$q = "DELETE FROM dt_blogs_langues WHERE id_blogs = $id";
		$this->sql->query($q);

		$q = "DELETE FROM dt_blogs WHERE id = $id";
		$this->sql->query($q);
	}

	public function themes() {
		return $this->themes;
	}

	public function langues() {
		return $this->langues;
	}

	public function liste() {
		$q = <<<SQL
SELECT id, nom FROM dt_blogs
SQL;
		$res = $this->sql->query($q);
		
		$blogs = array();
		while ($row = $this->sql->fetch($res)) {
			$blogs[$row['id']] = $row['nom'];
		}
		
		return $blogs;
	}

	function billets($id_blogs, $nb) {
		$q = <<<SQL
SELECT DISTINCT(b.id), b.titre, b.texte, b.date_affichage, b.titre_url
FROM dt_billets AS b
INNER JOIN dt_billets_themes_blogs AS bitb ON bitb.id_billets = b.id
INNER JOIN dt_blogs_themes_blogs AS bltb ON bltb.id_themes_blogs = bitb.id_themes_blogs
WHERE b.affichage = 1 AND bltb.id_blogs = $id_blogs
ORDER BY date_affichage DESC
LIMIT 0, $nb
SQL;
		$billets = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$billets[] = $row;
		}
		
		return $billets;
	}
}
