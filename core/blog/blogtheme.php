<?php

class BlogTheme {
	
	public $sql;
	public $table = "dt_themes_blogs";
	public $type = "theme";
	public $values;

	function __construct($sql) {
		$this->sql = $sql;
	}

	public function all_themes($id_blog = null) {
		$q = <<<SQL
SELECT t.id, t.nom, t.id_parent, t.titre_url FROM dt_themes_blogs AS t
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

	public function load($id) {
		$q = <<<SQL
SELECT tb.id, tb.nom, tb.affichage, tb.id_parent, tb.titre_url, btb.id_blogs, bl.id_langues FROM dt_themes_blogs AS tb
LEFT OUTER JOIN dt_blogs_themes_blogs AS btb ON btb.id_themes_blogs = tb.id
LEFT OUTER JOIN dt_blogs_langues AS bl ON bl.id_blogs = btb.id_blogs
WHERE tb.id = $id
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			foreach ($row as $cle => $valeur) {
				$this->$cle = $valeur;
				$this->values[$cle] = $valeur;
			}
		}
	}

	function billets() {
		$date_affichage = time();
		$q = <<<SQL
SELECT DISTINCT(b.id), b.titre, b.texte, b.date_affichage, b.titre_url
FROM dt_billets AS b
INNER JOIN dt_billets_themes_blogs AS bitb ON bitb.id_billets = b.id AND bitb.id_themes_blogs = {$this->id}
WHERE b.affichage = 1 AND date_affichage <= {$date_affichage}
ORDER BY date_affichage DESC
SQL;
		$billets = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$billets[] = $row;
		}
		
		return $billets;
	}

	public function save($data) {
		if (isset($data['theme']['id'])) {
			$id = $data['theme']['id'];

			$values = array();
			foreach ($data['theme'] as $field => $value) {
				$values[] = "$field = '$value'";
			}
			$q = "UPDATE dt_themes_blogs SET ".implode(",", $values);
			$q .= " WHERE id=".$id;
			$this->sql->query($q);

			$q = "DELETE FROM dt_blogs_themes_blogs WHERE id_themes_blogs = $id";
			$this->sql->query($q);
		}
		else {
			$fields = array();
			$values = array();
			foreach ($data['theme'] as $field => $value) {
				$fields[] = $field;
				$values[] = "'$value'";
			}
			$q = "INSERT INTO dt_themes_blogs (".implode(",", $fields).") VALUES (".implode(",", $values).")";
			$this->sql->query($q);
			
			$id = $this->sql->insert_id();

		}
		if ($data['id_blog']) {
			$q = "INSERT INTO dt_blogs_themes_blogs (id_blogs, id_themes_blogs) VALUES ({$data['id_blog']}, $id)";
			$this->sql->query($q);
		}

		return $id;
	}

	public function delete($data) {
		$id = $data['theme']['id'];
		$q = <<<SQL
SELECT id FROM dt_themes_blogs WHERE id_parent = $id
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$data2 = array('theme' => $row);
			$this->delete($data2);
		}
		$q = <<<SQL
DELETE FROM dt_themes_blogs WHERE id = $id
SQL;
		$this->sql->query($q);
	}

	public function blogs() {
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
}
