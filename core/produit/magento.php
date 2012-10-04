<?php

class Magento {

	private $sql;
	private $site;
	private $wsdl;
	private $user;
	private $key;
	private $images_path;
	private $proxy = null;
	private $sid = null;
	private $auto_sku = array();

	public function __construct($sql, $params) {
		$this->sql = $sql;
		$this->site = $params['site'];	
		$this->wsdl = $params['wsdl'];
		$this->user = $params['user'];
		$this->key = $params['key'];
		$this->images_path = $params['images_path'];
	}

	public function get_proxy() {
		if ($this->proxy === null) {
			$this->proxy = new SoapClient($this->wsdl);
			$this->sid = $this->proxy->login($this->user, $this->key);
		}
		return array($this->proxy, $this->sid);
	}

	public function prix_mini_categories($id_produits) {
		$q = <<<SQL
SELECT MIN(p.montant_ht) AS prix_mini FROM dt_prix AS p
INNER JOIN dt_sku_variantes AS sv ON sv.id_sku = p.id_sku
WHERE sv.id_produits = $id_produits
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['prix_mini'];
	}

	public function quantity($id_sku) {
		$q = <<<SQL
SELECT min_commande FROM dt_sku WHERE id = $id_sku
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return $row['min_commande'] > 1 ? $row['min_commande'] : 0;
	}

	public function save_produit($produit, $id, $id_doublet) {
		$phrases = $produit->get_phrases();
		$values = $produit->values;
		switch ($values['id_types_produits']) {
			case 4 : $type_produit = 1; break;
			case 2 : $type_produit = 2; break;
			case 1 : $type_produit = 3; break;
			case 3 : $type_produit = 4; break;
		}
		switch ($values['offre']) {
			case 0 : $offre = 8; break;
			case 1 : $offre = 7; break;
			case 2 : $offre = 6; break;
			case 3 : $offre = 5; break;
		}
		$stack1 = array();
		$stack2 = array();
		foreach ($produit->attributs_data() as $data) {
			$nom = $phrases['attributs'][$data['id_attributs']]['fr_FR'];
			$valeur = $data['valeur_numerique'];
			if ($data['fiche_technique']) {
				$stack1[] = "<li><strong>$nom : </strong>$valeur</li>";
			}
			if ($data['pictos_vente']) {
				$stack2[] = "<li><strong>$nom : </strong>$valeur</li>";
			}
		}
		$caracteristiques_techniques = "";
		if (count($stack1)) {
			$caracteristiques_techniques .= "<ul id=technical_data_sheet>";
			$caracteristiques_techniques .= implode("\n", $stack1);
			$caracteristiques_techniques .= "</ul>";
		}
		$pictos = "";
		if (count($stack2)) {
			$pictos .= "<ul id=pictos_vente>";
			$pictos .= implode("\n", $stack2);
			$pictos .= "</ul>";
		}

		$params = array(
			'name' => $phrases['phrase_nom']['fr_FR'],
			'type_produit' => $type_produit,
			'status' => $values['actif'] ? 1 : 2,
			'offre' => $offre,
			'url_key' => $phrases['phrase_url_key']['fr_FR'],
			'short_description' => $phrases['phrase_description_courte']['fr_FR'],
			'description' => $phrases['phrase_description']['fr_FR'],
			'entretien' => $phrases['phrase_entretien']['fr_FR'],
			'emploi' => $phrases['phrase_mode_emploi']['fr_FR'],
			'avantages' => $phrases['phrase_avantages_produit']['fr_FR'],
			'caracteristiques_techniques' => $caracteristiques_techniques,
			'pictos' => $pictos,
			'meta_title' => $phrases['phrase_meta_title']['fr_FR'],
			'meta_description' => $phrases['phrase_meta_description']['fr_FR'],
			'meta_keyword' => $phrases['phrase_meta_keywords']['fr_FR'],
			'prix_mini_categories' =>  $this->prix_mini_categories($id_doublet),
		);
		list($proxy, $sid) = $this->get_proxy();
		$proxy->call($sid, 'product.update', array($id, $params));
		
		// Produits associés
		$associes = array();
		$quantities = array();
		foreach ($produit->variantes() as $sku_id => $pasbesoin) {
			$entity_id = $this->get_entity_id("dt_sku", $sku_id, $this->site);
			$associes[$entity_id] = $entity_id;
			$quantities[$entity_id] = $this->quantity($sku_id);
		}
		foreach ($produit->accessoires() as $sku_id => $pasbesoin) {
			$entity_id = $this->get_entity_id("dt_sku", $sku_id, $this->site);
			$associes[$entity_id] = $entity_id;
			$quantities[$entity_id] = $this->quantity($sku_id);
		}
		foreach ($produit->composants() as $sku_id => $pasbesoin) {
			$entity_id = $this->get_entity_id("dt_sku", $sku_id, $this->site);
			$associes[$entity_id] = $entity_id;
			$quantities[$entity_id] = $this->quantity($sku_id);
		}
		$list = $proxy->call($sid, 'product_link.list', array('grouped', $id));
		foreach ($list as $product) {
			if (!isset($associes[$product['product_id']]) or ($product['qty'] === null) or ($product['qty'] != $quantities[$product['product_id']])) {
				$proxy->call($sid, 'product_link.remove', array('grouped', $id, $product['product_id']));
			}
			else {
				unset($associes[$product['product_id']]);
			}	
		}
		foreach ($associes as $sku_id) {
			$proxy->call($sid, 'product_link.assign', array('grouped', $id, $sku_id, array('qty' => $quantities[$sku_id])));
		}

		// Ventes incitatives
		$complementaires = array();
		foreach ($produit->complementaires() as $complementaire_id => $pasbesoin) {
			$entity_id = $this->get_entity_id("dt_produits", $complementaire_id, $this->site);
			$complementaires[$entity_id] = $entity_id;
		}
		$list = $proxy->call($sid, 'product_link.list', array('up_sell', $id));
		foreach ($list as $product) {
			if (!isset($complementaires[$product['product_id']])) {
				$proxy->call($sid, 'product_link.remove', array('up_sell', $id, $product['product_id']));
			}
			else {
				unset($complementaire[$product['product_id']]);
			}
		}
		foreach ($complementaires as $complementaire_id) {
			$proxy->call($sid, 'product_link.assign', array('up_sell', $id, $complementaire_id));
		}

		// Produits similaires
		$similaires = array();
		foreach ($produit->similaires() as $similaire_id => $pasbesoin) {
			$entity_id = $this->get_entity_id("dt_produits", $similaire_id, $this->site);
			$similaires[$entity_id] = $entity_id;
		}
		$list = $proxy->call($sid, 'product_link.list', array('related', $id));
		foreach ($list as $product) {
			if (!isset($similaires[$product['product_id']])) {
				$proxy->call($sid, 'product_link.remove', array('related', $id, $product['product_id']));
			}
			else {
				unset($similaires[$product['product_id']]);
			}
		}
		foreach ($similaires as $similaire_id) {
			$proxy->call($sid, 'product_link.assign', array('related', $id, $similaire_id));
		}

		// Images
		$list = $proxy->call($sid, 'product_media.list', array($id));
		foreach ($list as $image) {
			$proxy->call($sid, 'product_media.remove', array($id, $image['file']));
		}
		foreach ($produit->images() as $image) {
			if ($image['affichage'] and file_exists($this->images_path."/".$image['ref'])) {
				$data = array(
					'file' => array(
						'content' => base64_encode(file_get_contents($this->images_path."/".$image['ref'])),
						'mime' => "image/jpeg",
					),
					'label' => $phrases['image'][$image['id']]['phrase_legende']['fr_FR'],
					'types' => array('image', 'small_image', 'thumbnail'),
					'exclude' => 0,
				);
				$proxy->call($sid, 'product_media.create', array($id, $data));
			}
		}
	}

	public function get_entity_id($dt_table, $dt_id, $site) {
		$q = <<<SQL
SELECT entity_id FROM dt_sites_tiers
WHERE dt_table = '$dt_table'
AND dt_id = $dt_id AND site = '$site'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		return (int)$row['entity_id'];
	}

	public function get_set_id($proxy, $sid) {
		static $set_id = null;
		if ($set_id === null) {
			$attributeSets = $proxy->call($sid, 'product_attribute_set.list');
			$set_id = $attributeSets[0]['set_id'];
			foreach ($attributeSets as $set) {
				if ($set['name'] == "doublet") {
					$set_id = $set['set_id'];
				}
			}
		}
		return $set_id;
	}

	public function create_sku($obj) {
		$obj->save(); // save pour mettre à jour le timestamp
		list($proxy, $sid) = $this->get_proxy();
		$newProductData = array(
			'websites' => array(2),
		);
		$sku = $obj->values['ref_ultralog'];
		if (!$sku) {
			$sku = "DtSKU-".$obj->id;
			$this->auto_sku[] = array('ref' => $obj->values['ref_ultralog'], 'sku' => $sku);
		}
		try {
			$id = $proxy->call($sid, 'product.create', array('simple', $this->get_set_id($proxy, $sid), $sku, $newProductData));
		} catch (SoapFault $e) {
            if ($e->faultcode == 1) {
				$sku = "DtSKU-".$obj->id;
				$this->auto_sku[] = array('ref' => $obj->values['ref_ultralog'], 'sku' => $sku);
				$id = $proxy->call($sid, 'product.create', array('simple', $this->get_set_id($proxy, $sid), $sku, $newProductData));
			}
			else {
				throw $e;
			}
        }

		return $id;
	}

	public function create_produit($obj) {
		$obj->save(); // save pour mettre à jour le timestamp
		list($proxy, $sid) = $this->get_proxy();
		$newProductData = array(
			'websites' => array(2),
		);
		$sku = $obj->values['ref'];
		if (!$sku) {
			$sku = "DtProd-".$obj->id;
			$this->auto_sku[] = array('ref' => $obj->values['ref'], 'sku' => $sku);
		}
		try {
			$id = $proxy->call($sid, 'product.create', array('grouped', $this->get_set_id($proxy, $sid), $sku, $newProductData));
		} catch (SoapFault $e) {
            if ($e->faultcode == 1) {
				$sku = "DtProd-".$obj->id;
				$this->auto_sku[] = array('ref' => $obj->values['ref'], 'sku' => $sku);
				$id = $proxy->call($sid, 'product.create', array('grouped', $this->get_set_id($proxy, $sid), $sku, $newProductData));
			}
			else {
				throw $e;
			}
        }

		return $id;

	}

	public function save_sku($sku, $id) {
		$phrases = $sku->get_phrases();
		$values = $sku->values;
		$prix = $sku->prix();
		$params = array(
			'name' => $phrases['phrase_ultralog']['fr_FR'],
			'status' => $values['actif'] ? 1 : 2,
			'price' => $prix['montant_ht'],
		);
		list($proxy, $sid) = $this->get_proxy();
		$proxy->call($sid, 'product.update', array($id, $params));

		$tierPrices = array();
		foreach ($sku->prix_degressifs() as $prix) {
			$tierPrices[] = array(
				'website'           => 'all',
				'customer_group_id' => 'all',
				'qty'               => $prix['quantite'],
				'price'             => $prix['montant_ht'],
			);
		}
		$proxy->call($sid, 'product_tier_price.update', array($id, $tierPrices));

		// mise à jour des prix_mini_categories des produits groupés dont le sku est une déclinaison
		$q = <<<SQL
SELECT id_produits FROM dt_sku_variantes
WHERE id_sku = {$sku->id}
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$id_produits = $row['id_produits'];
			$id_entity = $this->get_entity_id("dt_produits", $id_produits, $this->site);
			if ($id_entity) {
				$params = array(
					'prix_mini_categories' => $this->prix_mini_categories($id_produits),
				);
				$proxy->call($sid, 'product.update', array($id_entity, $params));
			}
		}
	}

	public function save_categorie($categorie, $id) {
		list($proxy, $sid) = $this->get_proxy();
		$produits = array();
		foreach ($categorie->produits() as $id_produits) {
			$entity_id = $this->get_entity_id("dt_produits", $id_produits, $this->site);
			$produits[$entity_id] = $entity_id;
		}
		$filters = array(
    		'product_id' => array('in' => $produits)
		);
		$list = $proxy->call($sid, 'product.list', array($filters));
		$skus = array();
		foreach ($list as $product) {
			$product_id = (int)$product['product_id'];
			$sku = $product['sku'];
			$skus[$product_id] = $sku;
		}
		$list = $proxy->call($sid, 'category.assignedProducts', array($id, 2));
		foreach ($list as $product) {
			$product_id = (int)$product['product_id'];
			if (!isset($produits[$product_id])) {
				// Apparemment, bug magento, ça ne fonctionne pas avec l'id avec l'id, on prend donc le sku
				$sku = $product['sku'];
				$proxy->call($sid, 'category.removeProduct', array($id, $sku));
			}
			else {
				unset($produits[$product_id]);
			}
		}
		foreach ($produits as $product_id) {
			$sku = $skus[$product_id];
			$proxy->call($sid, 'category.assignProduct', array($id, $sku));
		}
	}

	public function synchronize($items) {
		$now = $_SERVER['REQUEST_TIME'];
		$q = <<<SQL
SELECT date_synchro, item FROM dt_sites_tiers_synchro WHERE site = '{$this->site}'
SQL;
		$res = $this->sql->query($q);
		$date_synchro = array();
		while ($row = $this->sql->fetch($res)) {
			$date_synchro[$row['item']] = (int)$row['date_synchro'];
		}
		$infos = array(
			'last_synchro' => $date_synchro,
			'current_synchro' => $now,
		);
		// création des produits/sku/catégories
		foreach ($items as $type => $obj) {
			if ($type == "categorie") {
			}
			else {
				switch ($type) {
					case "produit" :
						$table = "dt_produits";
						$method = "create_produit";
						break;
					case "sku" :
						$table = "dt_sku";
						$method = "create_sku";
						break;
				}
				$q = <<<SQL
SELECT t.id FROM `$table` AS t
LEFT OUTER JOIN `dt_sites_tiers` AS st ON st.dt_table = '$table' AND st.dt_id = t.id AND st.site = '{$this->site}'
WHERE st.id IS NULL
SQL;
				$res = $this->sql->query($q);
				while ($row = $this->sql->fetch($res)) {
					$id = (int)$row['id'];
					$obj->load($id);
					$entity_id = $this->{$method}($obj);
					$q = <<<SQL
INSERT INTO `dt_sites_tiers` (dt_table, dt_id, site, entity_id)
VALUES ('$table', $id, '{$this->site}', $entity_id)
SQL;
					$this->sql->query($q);
				}
			}
		}

		// mise à jour des produits/sku/catégories
		foreach ($items as $type => $obj) {
			if ($type == "categorie") {
				$q = <<<SQL
SELECT id, date_modification, correspondance FROM dt_catalogues_categories
WHERE correspondance <> 0 AND date_modification >= {$date_synchro[$type]}
ORDER BY date_modification ASC
SQL;
				$res = $this->sql->query($q);
				$infos[$type] = array();
				while ($row = $this->sql->fetch($res)) {
					$id = (int)$row['id'];
					$entity_id = (int)$row['correspondance'];
					$infos[$type][] = $id;
					$obj->load($id);
					$this->save_categorie($obj, $entity_id);
					if ($date_synchro[$type]) {
						$q = <<<SQL
UPDATE dt_sites_tiers_synchro SET date_synchro = {$row['date_modification']}
WHERE site = '{$this->site}' AND item = '{$type}'
SQL;
						$this->sql->query($q);
					}
					else {
						$q = <<<SQL
INSERT INTO dt_sites_tiers_synchro (site, item, date_synchro) VALUES ('{$this->site}', {$type}, {$row['date_modification']})
SQL;
						$this->sql->query($q);
					}
				}
			}
			else {
				switch ($type) {
					case "produit" :
						$table = "dt_produits";
						$method = "save_produit";
						break;
					case "sku" :
						$table = "dt_sku";
						$method = "save_sku";
						break;
				}
				$q = <<<SQL
SELECT t.id, st.entity_id, t.date_modification FROM `$table` AS t
INNER JOIN `dt_sites_tiers` AS st ON st.dt_table = '$table' AND st.dt_id = t.id
WHERE t.date_modification <> 0 AND t.date_modification >= {$date_synchro[$type]} AND st.site = '{$this->site}'
ORDER BY t.date_modification ASC
SQL;
				$res = $this->sql->query($q);
				$infos[$type] = array();
				while ($row = $this->sql->fetch($res)) {
					$id = (int)$row['id'];
					$entity_id = (int)$row['entity_id'];
					$infos[$type][] = $id;
					$obj->load($id);
					$this->{$method}($obj, $entity_id, $id);
					if ($date_synchro[$type]) {
						$q = <<<SQL
UPDATE dt_sites_tiers_synchro SET date_synchro = {$row['date_modification']}
WHERE site = '{$this->site}' AND item = '{$type}'
SQL;
						$this->sql->query($q);
					}
					else {
						$q = <<<SQL
INSERT INTO dt_sites_tiers_synchro (site, item, date_synchro) VALUES ('{$this->site}', {$type}, {$row['date_modification']})
SQL;
						$this->sql->query($q);
					}
				}
			}

			if ($date_synchro[$type]) {
				$q = <<<SQL
UPDATE dt_sites_tiers_synchro SET date_synchro = $now
WHERE site = '{$this->site}' AND item = '{$type}'
SQL;
				$this->sql->query($q);
			}
			else {
				$q = <<<SQL
INSERT INTO dt_sites_tiers_synchro (site, item, date_synchro) VALUES ('{$this->site}', {$type}, $now)
SQL;
				$this->sql->query($q);
			}
		}

		return $infos;
	}

	public function warn($to) {
		if (count($this->auto_sku)) {
			$message = "Certaines références n'ont pas pu être crées en tant que SKU. Voici la liste des SKU créés automatiquement :\n";
			foreach ($this->auto_sku as $auto_sku) {
				$message .= $auto_sku['sku']." (ref: ".$auto_sku['ref'].")\n";
			}
			mail($to, "SKU créés automatiquement", $message);
		}
	}

	public function synchronize_prices($obj, $timestamp) {
		$infos = array();

		// mise à jour des prix des produits
		$type = "sku";
		$table = "dt_sku";
		$method = "save_sku";
		$q = <<<SQL
SELECT t.id, st.entity_id, t.date_modification FROM `$table` AS t
INNER JOIN `dt_sites_tiers` AS st ON st.dt_table = '$table' AND st.dt_id = t.id
WHERE t.date_modification <> 0 AND t.date_modification >= {$timestamp} AND st.site = '{$this->site}'
ORDER BY t.date_modification ASC
SQL;
		$res = $this->sql->query($q);
		$infos[$type] = array();
		$i = 1;
		while ($row = $this->sql->fetch($res)) {
			$id = (int)$row['id'];
			$entity_id = (int)$row['entity_id'];
			$infos[$type][] = $id;
			$obj->load($id);
echo "({$i}) Update SKU $id (magento : $entity_id) ({$row['date_modification']})\n";
			$this->{$method}($obj, $entity_id, $id);
			$i++;
		}

		return $infos;
	}

	public function restaure($sku, $produit) {
		$infos = array();

		// restauration des sku
		$type = "sku";
		$table = "dt_sku";
		$method = "save_sku";
		$q = <<<SQL
SELECT t.id, st.entity_id, t.date_modification FROM `$table` AS t
INNER JOIN `dt_sites_tiers` AS st ON st.dt_table = '$table' AND st.dt_id = t.id
WHERE t.id IN (1242,1483,1644,1759,1830,1867,1905,1919) AND st.site = '{$this->site}'
ORDER BY t.date_modification ASC
SQL;
		$res = $this->sql->query($q);
		$infos[$type] = array();
		$i = 1;
		while ($row = $this->sql->fetch($res)) {
			$id = (int)$row['id'];
			$entity_id = (int)$row['entity_id'];
			$infos[$type][] = $id;
			$sku->load($id);
echo "({$i}) Restaure SKU $id (magento : $entity_id) ({$row['date_modification']})\n";
			$this->{$method}($sku, $entity_id, $id);
			$i++;
		}

		// restauration des produits
		$type = "produit";
		$table = "dt_produits";
		$method = "save_produit";
		$q = <<<SQL
SELECT t.id, st.entity_id, t.date_modification FROM `$table` AS t
INNER JOIN `dt_sites_tiers` AS st ON st.dt_table = '$table' AND st.dt_id = t.id
WHERE t.id IN (74) AND st.site = '{$this->site}'
ORDER BY t.date_modification ASC
SQL;
		echo $q;
		$res = $this->sql->query($q);
		$infos[$type] = array();
		while ($row = $this->sql->fetch($res)) {
			$id = (int)$row['id'];
			$entity_id = (int)$row['entity_id'];
			$infos[$type][] = $id;
			$produit->load($id);
echo "({$i}) Restaure Product $id (magento : $entity_id) ({$row['date_modification']})\n";
			$this->{$method}($produit, $entity_id, $id);
			$i++;
		}

		return $infos;
	}
}
