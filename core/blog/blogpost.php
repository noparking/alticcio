<?php
#TODO: faire dériver Blogpost de AbstractContent
class Blogpost {
	
	const UNCOMPLETED = -1;
	const UNDATED = -2;

	public $table = "dt_billets";
	public $type = "blogpost";
	public $sql;
	public $values;
	public $themes;
	
	public function __construct($sql) {
		$this->sql = $sql;
	}
	
	public function themes() {
		$q = <<<SQL
SELECT t.id, t.nom, t.id_parent FROM dt_themes_blogs AS t
SQL;
		$res = $this->sql->query($q);
		
		while ($row = $this->sql->fetch($res)) {
			$themes[] = $row;
		}
		
		return $themes;
	}
	
	public function upload_image($path, $url) {
		if (isset($_FILES['upload'])) {
			preg_match("/.*(\.[^\.]+)/", $_FILES['upload']['name'], $matches);
			$file = md5_file($_FILES['upload']['tmp_name']).$matches[1];
			move_uploaded_file($_FILES['upload']['tmp_name'], $path.$file);
			echo <<<HTML
<script type="text/javascript">
	window.parent.CKEDITOR.tools.callFunction({$_GET['CKEditorFuncNum']}, '{$url}/{$file}', '');
</script>
HTML;
		}
	}
	
	public function liste() {
		$q = <<<SQL
SELECT b.* FROM dt_billets AS b 
INNER JOIN dt_billets_themes_blogs AS bt ON bt.id_billets = b.id 
INNER JOIN dt_themes_blogs AS t ON t.id = bt.id_themes_blogs 
SQL;
		$res = $this->sql->query($q);
		
		$liste = array();
		while ($row = $this->sql->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}
	
	public function load($id) {
		$q = "SELECT * FROM dt_billets WHERE id = $id";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		foreach ($row as $key => $value) {
			$this->values[$key] = $value;
		}
		$this->id = $id;
		
		$this->themes = array();
		$q = <<<SQL
SELECT * FROM dt_billets_themes_blogs AS btb INNER JOIN dt_themes_blogs AS tb ON btb.id_themes_blogs = tb.id
WHERE btb.id_billets = $id
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$this->themes[$row['id_themes_blogs']] = $row;
		}
	}

	private function transform_text($chaine) {
		//return nl2br($chaine);
		return $chaine;
	}
	
	
	public function save($data) {
		if (empty($data['blogpost']['titre'])) {
			return self::UNCOMPLETED;
		}
		else if (empty($data['blogpost']['date_affichage'])) {
			return self::UNDATED;
		}
		else {
			if (isset($data['blogpost']['id'])) {
				$id = $data['blogpost']['id'];
				$data['blogpost']['date_update'] = time();
				
				$values = array();
				foreach ($data['blogpost'] as $field => $value) {
					$values[] = "$field = '".$this->transform_text($value)."'";
				}
				if (!isset($data['blogpost']['affichage'])) {
					$values[] = "affichage = 0";
				}
				$q = "UPDATE dt_billets SET ".implode(",", $values);
				$q .= " WHERE id=".$id;
				$this->sql->query($q);
				
				$q = "DELETE FROM dt_billets_themes_blogs WHERE id_billets = $id";
				$this->sql->query($q);
			}
			else {
				$data['blogpost']['date_creation'] = time();
				$data['blogpost']['date_update'] = time();
				
				$fields = array();
				$values = array();
				foreach ($data['blogpost'] as $field => $value) {
					$fields[] = $field;
					$values[] = "'".$this->transform_text($value)."'";
				}
				$q = "INSERT INTO dt_billets (".implode(",", $fields).") VALUES (".implode(",", $values).")";
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
					$q = "INSERT INTO dt_billets_themes_blogs (id_billets, id_themes_blogs) VALUES ".implode(",", $themes_blogs);
					$this->sql->query($q);
				}
			}
			return $id;
		}
	}
	
	public function delete() {
		$q = "DELETE FROM dt_billets_themes_blogs WHERE id_billets = {$this->id}";
		$this->sql->query($q);
		$q = "DELETE FROM dt_billets WHERE id = {$this->id}";
		$this->sql->query($q);
	}
}
