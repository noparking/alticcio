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
SELECT a.id, a.titre, a.fichier, a.id_types_assets, a.actif, a.public FROM dt_assets AS a
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

	public function codes_types() {
		$q = <<<SQL
SELECT id, code FROM dt_types_assets
SQL;
		$codes_types = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$codes_types[$row['id']] = $row['code'];
		}
		
		return $codes_types;
	}

	public function save($data) {
		$time = time();
		if (isset($data['asset']['id'])) {
			$q = <<<SQL
DELETE FROM dt_assets_links WHERE id_assets = {$data['asset']['id']}
SQL;
			$this->sql->query($q);

			$values = array();
			foreach (array('gamme', 'produit', 'sku') as $link_type) {
				if (isset($data['asset_links'][$link_type])) {
					foreach ($data['asset_links'][$link_type] as $link_id => $infos) {
						$classement = (int)$infos['classement'];
						$values[] = "({$data['asset']['id']}, '{$link_type}', $link_id, {$classement})";
					}
				}
			}
			$values = implode(",", $values);
			$q = <<<SQL
INSERT INTO dt_assets_links (id_assets, link_type, link_id, classement) VALUES $values 
SQL;
			$this->sql->query($q);
		}
		else {
			$data['asset']['date_creation'] = $time;
		}
		$data['asset']['date_modification'] = $time;

		$file = $data['file'];
		$path = $data['path'];
		$codes_types = $this->codes_types();
		$dir = $path.$codes_types[$data['asset']['id_types_assets']]."/";
		if (is_array($file)) {
			$file_name = $file['name'];
			move_uploaded_file($file['tmp_name'], $dir.$file_name);
			$data['asset']['fichier'] = $file['name'];
		}
		else if (file_exists($file)) {
			$file_name = $file['name'];
			copy($file, $dir.$file_name);
			$data['asset']['fichier'] = $file['name'];
		}

		return parent::save($data);
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
}
