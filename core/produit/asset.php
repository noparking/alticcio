<?php

require_once "abstract_object.php";

class Asset extends AbstractObject {

	public $type = "asset";
	public $table = "dt_assets";
	public $id_field = "id_assets";
	public $phrase_fields = array(
		'phrase_nom',
		'phrase_description',
	);

	public function liste($id_langues, &$filter = null) {
		$q = <<<SQL
SELECT a.id, a.titre, a.fichier, a.actif, a.public,
GROUP_CONCAT(at.code ORDER BY at.code ASC SEPARATOR ', ') AS tags
FROM dt_assets AS a
LEFT OUTER JOIN dt_assets_tags_assets AS ata ON ata.id_assets = a.id
LEFT OUTER JOIN dt_assets_tags AS at ON at.id = ata.id_assets_tags
WHERE 1
GROUP BY a.id
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

	public function save($data) {
		$time = time();
		if (isset($data['asset']['id'])) {
			$q = <<<SQL
DELETE FROM dt_assets_links WHERE id_assets = {$data['asset']['id']}
SQL;
			$this->sql->query($q);

			$values = array();
			if (isset($data['asset_links'])) {
				foreach ($data['asset_links'] as $link_type => $link) {
					foreach ($link as $key => $value) {
						if (isset($value['classement'])) {
							$classement = (int)$value['classement'];
							$link_id = $key;
						}
						else {
							$classement = $key;
							$link_id = $value;
						}
						$values[] = "({$data['asset']['id']}, '{$link_type}', $link_id, {$classement})";

					}
				}
			}
			$values = implode(",", $values);
			if ($values) {
				$q = <<<SQL
INSERT INTO dt_assets_links (id_assets, link_type, link_id, classement) VALUES $values 
SQL;
				$this->sql->query($q);
			}
		}
		else {
			$data['asset']['date_creation'] = $time;
		}
		$data['asset']['date_modification'] = $time;

		if (isset($data['file']) and isset($data['path'])) {
			$file = $data['file'];
			$path = $data['path'];
			if (is_array($file)) {
				preg_match("/(\.[^\.]*)$/", $file['name'], $matches);
				$ext = $matches[1];
				$file_name = md5_file($file['tmp_name']).$ext;
				move_uploaded_file($file['tmp_name'], $path.$file_name);
				$data['asset']['fichier'] = $file['name'];
				$data['asset']['fichier_md5'] = $file_name;
			}
			else if (file_exists($file)) {
				preg_match("/(\.[^\.]*)$/", $file, $matches);
				$ext = $matches[1];
				$file_name = md5_file($file).$ext;
				copy($file, $path.$file_name);
				$data['asset']['fichier'] = basename($file);
				$data['asset']['fichier_md5'] = $file_name;
			}
		}

		$id_assets = parent::save($data);

		if (isset($data['tags'])) {
			if (isset($data['asset']['id'])) {
				$q = <<<SQL
DELETE FROM dt_assets_tags_assets WHERE id_assets = {$data['asset']['id']} 
SQL;
				$this->sql->query($q);
			}

			$values = array();
			foreach ($data['tags'] as $id_assets_tags) {
				$values[] = "($id_assets, $id_assets_tags)";
			}
			$list_values = implode(",", $values); 
			$q = <<<SQL
INSERT INTO dt_assets_tags_assets (id_assets, id_assets_tags) VALUES $list_values
SQL;
			$this->sql->query($q);
		}

		if (isset($data['langues'])) {
			if (isset($data['asset']['id'])) {
				$q = <<<SQL
DELETE FROM dt_assets_langues WHERE id_assets = {$data['asset']['id']} 
SQL;
				$this->sql->query($q);
			}

			$values = array();
			foreach ($data['langues'] as $id_langues) {
				$values[] = "($id_assets, $id_langues)";
			}
			$list_values = implode(",", $values); 
			$q = <<<SQL
INSERT INTO dt_assets_langues (id_assets, id_langues) VALUES $list_values
SQL;
			$this->sql->query($q);
		}

		return $id_assets;
	}

	public function delete($data) {
		$q = <<<SQL
DELETE FROM dt_assets_langues WHERE id_assets = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_assets_tags_assets WHERE id_assets = {$this->id}
SQL;
		$this->sql->query($q);

		$q = <<<SQL
DELETE FROM dt_assets_links WHERE id_assets = {$this->id}
SQL;
		$this->sql->query($q);

		return parent::delete($data);
	}

	public function all_links_by_type($link_type, $filter = null) {
		$liste = array();

		if ($filter === null) {
			$filter = $this->sql;
		}

		$field_ref = "ref";
		$field_nom = "phrase_nom";
		switch ($link_type) {
			case 'gamme' :
				$table_join = "dt_gammes";
				break;
			case 'produit' :
				$table_join = "dt_produits";
				break;
			case 'sku' :
				$table_join = "dt_sku";
				$field_ref = "ref_ultralog";
				$field_nom = "phrase_ultralog";
				break;
			default:
				return $liste;
		}
		$asset_id = isset($this->id) ? $this->id : 0;
		$q = <<<SQL
SELECT t.id, t.{$field_ref} as ref, p.phrase as nom, al.link_id, al.classement
FROM $table_join AS t
LEFT OUTER JOIN dt_assets_links AS al ON t.id = al.link_id AND id_assets = $asset_id AND link_type = '$link_type'
LEFT OUTER JOIN dt_phrases AS p ON p.id = t.{$field_nom} AND p.id_langues = {$this->langue}
SQL;
		$res = $filter->query($q);

		while ($row = $filter->fetch($res)) {
			$liste[$row['id']] = $row;
		}
		
		return $liste;
	}

	public function links() {
		$links = array();

		$q = <<<SQL
SELECT * FROM dt_assets_links
WHERE id_assets = {$this->id}
SQL;
		$res = $this->sql->query($q);

		while ($row = $this->sql->fetch($res)) {
			$links[$row['link_type']][$row['link_id']] = $row;
		}
		
		return $links;
	}


	public function all_tags() {
		$q = <<<SQL
SELECT id, code FROM dt_assets_tags
SQL;
		$res = $this->sql->query($q);

		$tags = array();
		while ($row = $this->sql->fetch($res)) {
			$tags[$row['id']] = $row['code'];
		}

		return $tags;
	}

	public function tags() {
		$q = <<<SQL
SELECT id_assets_tags FROM dt_assets_tags_assets WHERE id_assets = {$this->id}
SQL;
		$res = $this->sql->query($q);

		$tags = array();
		while ($row = $this->sql->fetch($res)) {
			$tags[] = $row['id_assets_tags'];
		}

		return $tags;
	}
	
	public function all_langues() {
		$q = <<<SQL
SELECT id, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);

		$langues = array();
		while ($row = $this->sql->fetch($res)) {
			$langues[$row['id']] = $row['code_langue'];
		}

		return $langues;
	}

	public function langues() {
		$q = <<<SQL
SELECT id_langues FROM dt_assets_langues WHERE id_assets = {$this->id}
SQL;
		$res = $this->sql->query($q);

		$langues = array();
		while ($row = $this->sql->fetch($res)) {
			$langues[] = $row['id_langues'];
		}

		return $langues;
	}

	public function is_image() {
		if (isset($this->values['fichier'])) {
			$ext = pathinfo($this->values['fichier'], PATHINFO_EXTENSION);
			switch (strtolower($ext)) {
				case "jpg" :
				case "jpeg" :
				case "gif" :
				case "png" :
					return true;
			}
		}

		return false;
	}
	
	// retourne les attributs/valeurs pour les assets
	public function attributs_for_asset($attributs_for_assets, $id_langues) {
		$possible_refs = implode("','", $attributs_for_assets);
		$q = <<<SQL
SELECT a.ref, oa.id_attributs, oa.id, p1.phrase AS phrase_option, p2.phrase AS phrase_nom FROM dt_options_attributs AS oa
INNER JOIN dt_attributs AS a ON a.id = oa.id_attributs
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = oa.phrase_option AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = a.phrase_nom AND p2.id_langues = $id_langues
WHERE a.ref IN ('$possible_refs')
SQL;
		$res = $this->sql->query($q);
		$attributs = array();
		while ($row = $this->sql->fetch($res)) {
			if (!isset($attributs[$row['id_attributs']])) {
				$attributs[$row['id_attributs']] = array('ref' => $row['ref'], 'nom' => $row['phrase_nom']);
			}
			$attributs[$row['id_attributs']]['options'][$row['id']] = $row['phrase_option'];
		}
		return $attributs;
	}
}
