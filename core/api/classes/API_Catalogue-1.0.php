<?php

class API_Catalogue {
	
	private $api;
	private $sql;
	private $language;

	function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;
		$this->language = $api->info('language');
	}

	function all() {
		$q = <<<SQL
SELECT id, nom FROM dt_catalogues
SQL;
		$catalogues = array();
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$catalogues[$row['id']] = $row['nom'];
			$catalogues[$row['nom']] = $row['id'];
		}

		return $catalogues;
	}

	function id() {
		$name = $this->api->info("name");
		$q = <<<SQL
SELECT id FROM dt_catalogues WHERE nom LIKE '$name'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['id'];
		}
		else {
			return false;
		}
	}

	function tree($id_catalogue, $id_parent = 0) {
		$q = <<<SQL
SELECT id, id_parent, nom FROM dt_catalogues_categories
WHERE id_catalogues = $id_catalogue AND id_parent = $id_parent
ORDER BY classement, nom ASC
SQL;
		$res = $this->sql->query($q);
		$tree = array();
		while ($row = $this->sql->fetch($res)) {
			$categories = $this->tree($id_catalogue, $row['id']);
			$tree[] = array(
				'id' => $row['id'],
				'name' => $row['nom'],
				'nb' => count($categories),
				'categories' => $categories,
			);
		}

		return $tree;
	}

	function products_tree($id_catalogue, $id_parent = 0) {
		$q = <<<SQL
SELECT id, id_parent, nom FROM dt_catalogues_categories
WHERE id_catalogues = $id_catalogue AND id_parent = $id_parent
ORDER BY classement, nom ASC
SQL;
		$res = $this->sql->query($q);
		$tree = array();
		while ($row = $this->sql->fetch($res)) {
			$categories = $this->products_tree($id_catalogue, $row['id']);
			$q = <<<SQL
SELECT p.id, ph.phrase AS nom FROM dt_produits AS p
INNER JOIN dt_catalogues_categories_produits AS ccp ON ccp.id_produits = p.id
LEFT OUTER JOIN dt_phrases AS ph ON ph.id = p.phrase_nom
INNER JOIN dt_langues AS l ON l.id = ph.id_langues
WHERE ccp.id_catalogues_categories = {$row['id']}
AND l.code_langue = '{$this->language}'
ORDER BY ccp.classement ASC
SQL;
			$res2 = $this->sql->query($q);
			$products = array();
			while ($row2 = $this->sql->fetch($res2)) {
				$products[] = array(
					'id' => $row2['id'],
					'name' => $row2['nom'],
				);
			}
			$tree[] = array(
				'id' => $row['id'],
				'name' => $row['nom'],
				'nb_categories' => count($categories),
				'categories' => $categories,
				'nb_products' => count($products),
				'products' => $products,
			);
		}

		return $tree;
	}

	function categorie_name($id_categorie) {
		$id = (int)$id_categorie;
		$q = <<<SQL
SELECT nom FROM dt_catalogues_categories WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['nom'];
		}
		else {
			return false;
		}
	}

	function produits($id_categorie, $limit, $offset) {
		$produit = new API_Produit($this->api);
		$id = (int)$id_categorie;
		$q = <<<SQL
SELECT SQL_CALC_FOUND_ROWS p.id
FROM dt_catalogues_categories_produits AS ccp
INNER JOIN dt_produits AS p ON ccp.id_produits = p.id
WHERE id_catalogues_categories = $id
AND p.actif = 1
ORDER BY ccp.classement ASC
{$this->sql->limit($limit, $offset)}
SQL;
		$res = $this->sql->query($q);
		$total = $this->sql->found_rows();
		$produits = array();
		while ($row = $this->sql->fetch($res)) {
			$produits[] = $produit->infos($row['id']);
		}

		return array('products' => $produits, 'nb' => count($produits), 'total' => (int)$total, 'limit' => (int)$limit, 'offset' => (int)$offset);
	}

	function produit($id_produit) {
		$id = (int)$id_produit;
		$produit = new API_Produit($this->api);
		return $produit->infos($id);
	}

	function commande($token) {
		$q = <<<SQL
SELECT id FROM dt_commandes WHERE token = '$token'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return 1;
		}
		else {
			return 0;
		}
	}

	function home($id_catalogue) {
		$id = (int)$id_catalogue;
		$q = <<<SQL
SELECT home FROM dt_catalogues WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['home'];
		}
		else {
			return false;
		}
	}

	function bloc($id_categorie) {
		$id = (int)$id_categorie;
		$q = <<<SQL
SELECT id_blocs FROM dt_catalogues_categories WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['id_blocs'];
		}
		else {
			return false;
		}
	}
}
