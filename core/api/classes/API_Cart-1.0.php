<?php

class API_Cart {

	private $api;
	private $sql;
	private $id_langues;
	private $id_catalogues = 0;

	public function __construct($api) {
		$this->api = $api;
		$this->key = $api->key();
		$this->sql = $api->sql;

		if (!isset($_SESSION['cart'][$this->key]) or !is_array($_SESSION['cart'][$this->key])) {
			$_SESSION['cart'][$this->key] = array();
		}
		if (!isset($_SESSION['cart'][$this->key]['items']) or !is_array($_SESSION['cart'][$this->key]['items'])) {
			$_SESSION['cart'][$this->key]['items'] = array();
		}
		if (!isset($_SESSION['cart'][$this->key]['personnalisations']) or !is_array($_SESSION['cart'][$this->key]['personnalisations'])) {
			$_SESSION['cart'][$this->key]['personnalisations'] = array();
		}
		if (isset($_SESSION['cart'][$this->key]['id_catalogues'])) {
			$this->id_catalogues = $_SESSION['cart'][$this->key]['id_catalogues'];
		}
	}

	private function id_langues() {
		if (!isset($this->id_langues)) {
			$q = <<<SQL
SELECT id FROM dt_langues WHERE code_langue = '{$this->api->info('language')}'
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->id_langues = $row['id'];
		}

		return $this->id_langues;
	}

	private function id_pays() {
		if (!isset($this->id_pays)) {
			$code_iso = substr($this->api->info('language'), -2);
			$q = <<<SQL
SELECT id FROM dt_pays WHERE code_iso = '$code_iso'
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$this->id_pays = $row['id'];
		}

		return $this->id_pays;
	}

	public function add($id_produits, $id_sku, $qte, $sample = false) {
		$id = "$id_produits-$id_sku";

		if ($qte < 0) {
			return false;
		}

		$this->set_token();
		$key = $id;
		
		if ($sample) {
			$key .= "[sample]";
		}

		if (isset($_SESSION['cart'][$this->key]['items'][$key])) {
			$_SESSION['cart'][$this->key]['items'][$key]['qte'] += $qte;
		}
		else {
			$_SESSION['cart'][$this->key]['items'][$key] = array(
				'qte' => $qte,
				'id_produits' => $id_produits,
				'id_sku' => $id_sku,
				'sample' => $sample,
			);
		}

		$perso = array(
			'id_produits' => $id_produits,
			'id_sku' => $id_sku,
			'qte' => $qte,
			'texte' => "",
			'fichier' => "",
			'nom_fichier' => "",
			'sample' => $sample,
		);
		
		if (isset($this->api->post['perso_texte'])) {
			$perso['texte'] = $this->api->post['perso_texte'];
			$key .= $perso['texte'];
		}
		
		if (isset($this->api->post['perso_objet'])) {
			$perso['objet'] = $this->api->post['perso_objet'];
			$key .= $perso['objet'];
		}

		if (isset($this->api->files['perso_fichier']) and $this->api->files['perso_fichier']['error'] == UPLOAD_ERR_OK) {
			$uploads_dir = $this->api->config['perso_fichier_path'];
			$tmp_name = $this->api->files['perso_fichier']['tmp_name'];
			$original_name = $this->api->files['perso_fichier']['name'];
			preg_match("/(\.[^\.]*)$/", $original_name, $matches);
			$ext = $matches[1];
			$name = md5_file($tmp_name).$ext;
			move_uploaded_file($tmp_name, $uploads_dir.$name);
			$perso['fichier'] = $name;
			$perso['nom_fichier'] = $original_name;
			$key .= $name.$original_name;
		}
		$key = md5($key);

		if (isset($_SESSION['cart'][$this->key]['personnalisations'][$key])) {
			$_SESSION['cart'][$this->key]['personnalisations'][$key]['qte'] += $qte;
		}
		else {
			$_SESSION['cart'][$this->key]['personnalisations'][$key] = $perso;
		}
		
		return $key;
	}

	public function update($perso, $qte) {
		$this->set_token();
		$_SESSION['cart'][$this->key]['personnalisations'][$perso]['qte'] = $qte;
		$qte = 0;
		$id_produits = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_produits'];
		$id_sku = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_sku'];
		$sample = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['sample'];
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		foreach ($_SESSION['cart'][$this->key]['personnalisations'] as $p) {
			if ($p['id_sku'] == $id_sku and $p['id_produits'] == $id_produits and $p['sample'] == $sample) {
				$qte += $p['qte'];
			}
		}
		$_SESSION['cart'][$this->key]['items'][$id]['qte'] = $qte;
	}

	public function remove($perso) {
		$this->set_token();
		$qte = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['qte'];
		$id_produits = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_produits'];
		$id_sku = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_sku'];
		$sample = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['sample'];
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		unset($_SESSION['cart'][$this->key]['personnalisations'][$perso]);
		$_SESSION['cart'][$this->key]['items'][$id]['qte'] -= $qte;
		if ($_SESSION['cart'][$this->key]['items'][$id]['qte'] == 0) {
			unset($_SESSION['cart'][$this->key]['items'][$id]);
		}
	}

	public function add_safe($id_produits, $id_sku, $qte, $sample = false) {
		list($less, $more) = $this->safe_qte($id_produits, $id_sku, $qte, $sample);

		return $this->add($id_produits, $id_sku, $more, $sample);
	}

	public function update_safe($perso, $qte) {
		$id_produits = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_produits'];
		$id_sku = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['id_sku'];
		$sample = $_SESSION['cart'][$this->key]['personnalisations'][$perso]['sample'];

		$this->update($perso, 0);
		list($less, $more) = $this->safe_qte($id_produits, $id_sku, $qte, $sample);

		return $this->update($perso, $more);
	}

	public function safe_qte($id_produits, $id_sku, $qte, $sample = false) {
		$q = <<<SQL
SELECT colisage, min_commande FROM dt_sku WHERE id = $id_sku
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		$qte_min_to_add = $this->qte_min_to_add($id_produits, $id_sku, $row['min_commande'], $row['colisage'], $sample);
		if ($qte < $qte_min_to_add) {
			$qte = $qte_min_to_add;
		}
		if ($row['colisage']) {
			$less = floor($qte / $row['colisage']) * $row['colisage'];
			$more = ceil($qte / $row['colisage']) * $row['colisage'];
		}
		else {
			$less = $qte;
			$more = $qte;
		}
		
		return array($less, $more);
	}

	public function qte_min_to_add($id_produits, $id_sku, $qte_min, $colisage = 0, $sample = false) {
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		if (isset($_SESSION['cart'][$this->key]['items'][$id])) {
			$qte_min = max(1, $qte_min - $_SESSION['cart'][$this->key]['items'][$id]['qte']);
		}
		if ($colisage) {
			$qte_min = ceil($qte_min / $colisage) * $colisage;
		}

		return $qte_min;
	}

	public function emptycart() {
		$_SESSION['cart'][$this->key]['items'] = array();
		$_SESSION['cart'][$this->key]['personnalisations'] = array();
		$this->set_token();
	}

	public function is_empty() {
		return count($_SESSION['cart'][$this->key]['items']) == 0;
	}

	public function items() {
		return $_SESSION['cart'][$this->key]['items'];
	}

	public function personnalisations() {
		return $_SESSION['cart'][$this->key]['personnalisations'];
	}

	public function check_franco() {
		$sku_ids = array();
		foreach ($_SESSION['cart'][$this->key]['items'] as $item) {
			$sku_ids[] = $item['id_sku'];
		}
		$liste_id_sku = implode(",", $sku_ids);
		// S'il y a au moins 1 SKU franco = 0 ou montant_ht = 0
		$q = <<<SQL
SELECT * FROM dt_prix
WHERE id_catalogues = $this->id_catalogues
AND (franco = 0 OR montant_ht = 0) 
AND id_sku IN ($liste_id_sku)
SQL;
		$res = $this->sql->query($q);
		if ($this->sql->fetch($res)) {
			return false;
		}

		return true;
	}

	public function produits($id_langues) {
		$produits = array();
		$data = array();
		$sku_ids = array();
		foreach ($_SESSION['cart'][$this->key]['items'] as $item) {
			$sku_ids[] = $item['id_sku'];
		}
		if (count($sku_ids) == 0) {
			return array();
		}
		$sku_ids = implode(",", $sku_ids);
		$q = <<<SQL
SELECT p1.phrase AS phrase_commercial, p2.phrase AS phrase_ultralog, s.id, s.ref_ultralog FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = s.phrase_commercial AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = s.phrase_ultralog AND p2.id_langues = $id_langues
WHERE s.id IN ($sku_ids)
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$data[$row['id']]['nom'] = addslashes($row['phrase_commercial'] ? $row['phrase_commercial'] : $row['phrase_ultralog']);
			$data[$row['id']]['ref'] = $row['ref_ultralog'];
		}
		foreach ($_SESSION['cart'][$this->key]['personnalisations'] as $perso) {
			$id_sku = $perso["id_sku"];
			$array = array(
				'id_produits' => $perso["id_produits"],
				'id_sku' => $id_sku,
				'ref' => $data[$id_sku]['ref'],
				'nom' =>  $data[$id_sku]['nom'],
				'prix_unitaire' =>  $this->prix_unitaire_pour_qte($id_sku, $perso['qte']),
				'quantite' => $perso['qte'],
				'personnalisation_texte' => $perso['texte'],
				'personnalisation_fichier' => $perso['fichier'],
				'personnalisation_nom_fichier' => $perso['nom_fichier'],
				'echantillon' => $perso['sample'],
			);
			if (isset($perso['objet'])) {
				$array['personnalisation_objet'] = $perso['objet'];
			}
			$produits[] = $array;
		}

		return $produits;
	}

	public function prix_unitaire_pour_qte($id_sku, $qte, $id_catalogues = null) {
		if ($id_catalogues === null) {
			$id_catalogues = $this->id_catalogues;
		}
		$q = <<<SQL
SELECT MIN(montant_ht) AS prix FROM dt_prix_degressifs
WHERE id_sku = $id_sku AND quantite <= $qte AND id_catalogues = {$id_catalogues}
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		if ($row['prix']) {
			$prix = $row['prix'];
		}
		else {
			$q = <<<SQL
SELECT montant_ht FROM dt_prix
WHERE id_sku = $id_sku AND id_catalogues = {$id_catalogues}
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$prix = $row['montant_ht'];
		}

		if ($prix or $id_catalogues == 0) {
			return $prix;
		}
		else {
			return $this->prix_unitaire_pour_qte($id_sku, $qte, 0);
		}
	}

	public function set_token() {
		$token = substr(uniqid("", true), 0, 16);
		$_SESSION['cart'][$this->key]['token'] = $token;
	}

	public function token() {
		return $_SESSION['cart'][$this->key]['token'];
	}

	public function catalogue($id_catalogues) {
		$_SESSION['cart'][$this->key]['id_catalogues'] = $id_catalogues;
		$this->id_catalogues = $id_catalogues;
	}

	public function content($id_pays_livraison = null) {

		if ($id_pays_livraison == null) {
			$id_pays_livraison = $this->id_pays();
		}

		$produits_personnalises = array();
		$noms = array();
		$refs = array();
		$vignettes = array();
		$prix_degressifs = array();
		$prix_catalogue = array(); // les prix sont ils spéciaux pour le catalogue ?
		$qtes_min = array();
		$colisages = array();
		$sku_ids = array();
		$produits_ids = array();
		$francos = array();
		$ecotaxes = array();
		$personnalisations = $this->personnalisations();
		foreach ($personnalisations as $perso) {
			if (!in_array($perso['id_sku'], $sku_ids)) {
				$sku_ids[] = $perso['id_sku'];
				$produits_ids[] = $perso['id_produits'];
			}
		}
		$sku_ids = implode(",", $sku_ids);
		if (!$sku_ids) {
			$this->emptycart();
			return array(
				'products' => array(),
				'total_ht' => 0,
			);
		}
		$produits_ids = implode(",", $produits_ids);
		$q = <<<SQL
SELECT p1.phrase AS phrase_commercial, p2.phrase AS phrase_ultralog, s.id, s.ref_ultralog, px.id as px_id, px.montant_ht, px2.montant_ht AS montant_ht_default, px.franco, s.min_commande, s.colisage FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = s.phrase_commercial AND p1.id_langues = {$this->id_langues()}
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = s.phrase_ultralog AND p2.id_langues = {$this->id_langues()}
LEFT OUTER JOIN dt_prix AS px ON px.id_sku = s.id AND px.id_catalogues = {$this->id_catalogues}
LEFT OUTER JOIN dt_prix AS px2 ON px2.id_sku = s.id AND px2.id_catalogues = 0
WHERE s.id IN ($sku_ids)
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$noms[$row['id']] = $row['phrase_commercial'] ? $row['phrase_commercial'] : $row['phrase_ultralog'];
			$refs[$row['id']] = $row['ref_ultralog'];
			$prix_catalogue[$row['id']] = $row['px_id'] ? $this->id_catalogues : 0;
			$prix_degressifs[$row['id']][1] = $prix_catalogue[$row['id']] ? $row['montant_ht'] : $row['montant_ht_default'];
			$qtes_min[$row['id']] = $row['min_commande'];
			$colisages[$row['id']] = $row['colisage'];
			$francos[$row['id']] = $row['franco'];
		}

		$q = <<<SQL
SELECT id_sku, montant_ht, quantite, id_catalogues FROM dt_prix_degressifs
WHERE id_sku IN ($sku_ids) AND (id_catalogues = {$this->id_catalogues} OR id_catalogues = 0)
ORDER BY quantite ASC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			if ($row['id_catalogues'] == $prix_catalogue[$row['id_sku']]) {
				$prix_degressifs[$row['id_sku']][$row['quantite']] = $row['montant_ht'];
			}
		}

		$q = <<<SQL
SELECT id_produits, ref FROM dt_images_produits WHERE id_produits IN ($produits_ids) AND vignette = 1
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$vignettes[$row['id_produits']] = $row['ref'];
		}

		$q = <<<SQL
SELECT e.id, e.id_sku, e.montant, e.id_pays, e.id_familles_taxes, ph1.phrase AS pays, ph2.phrase AS famille_taxes, e.id_catalogues FROM dt_ecotaxes AS e
LEFT OUTER JOIN dt_pays AS p ON p.id = e.id_pays
LEFT OUTER JOIN dt_phrases AS ph1 ON ph1.id = p.phrase_nom AND ph1.id_langues = {$this->id_langues()}
LEFT OUTER JOIN dt_familles_taxes AS ft ON ft.id = e.id_familles_taxes
LEFT OUTER JOIN dt_phrases AS ph2 ON ph2.id = ft.phrase_taxe AND ph2.id_langues = {$this->id_langues()}
WHERE id_sku IN ($sku_ids) AND (id_catalogues = {$this->id_catalogues} OR id_catalogues = 0) AND id_pays = $id_pays_livraison
SQL;
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			if ($row['id_catalogues'] == $prix_catalogue[$row['id_sku']]) {
				$ecotaxes[$row['id_sku']][] = $row;
			}
		}

		$total_ht = 0;
		$ecotaxes_total = 0;
		$ecotaxes_pays = array();
		foreach ($personnalisations as $key => $perso) {
			$id_sku = $perso['id_sku'];
			$id_produits = $perso['id_produits'];
			$prix_unitaire = $prix_degressifs[$id_sku][1];
			foreach ($prix_degressifs[$id_sku] as $qte => $prix) {
				if ($qte <= $perso['qte']) {
					$prix_unitaire = $prix;
				}
			}
			$ecotaxes_montant = 0;
			
			if (isset($ecotaxes[$id_sku])) {
				foreach ($ecotaxes[$id_sku] as $ecotaxe) {
					$ecotaxes_montant += $ecotaxe['montant'];
					$ecotaxes_pays[$ecotaxe['pays']] = $ecotaxe['pays'];
					$ecotaxes_total += $ecotaxes_montant;
				}
			}
			$prix = ($prix_unitaire + $ecotaxes_montant) * $perso['qte'];
			$total_ht += $prix;
			$produits_personnalises[] = array(
				'id' => $id_sku,
				'id_produits' => $id_produits,
				'nom' => $noms[$id_sku],
				'ref' => $refs[$id_sku],
				'vignette' => isset($vignettes[$id_produits]) ? $vignettes[$id_produits] : "",
				'texte' => $perso['texte'],
				'fichier' => $perso['fichier'],
				'nom_fichier' => $perso['nom_fichier'],
				'qte' => $perso['qte'],
				'prix' => $prix,
				'prix_unitaire' => $prix_unitaire,
				'perso' => $key,
				'qte_min' => $qtes_min[$id_sku],
				'colisage' => $colisages[$id_sku],
				'franco' => $francos[$id_sku],
				'ecotaxe' => $ecotaxes_montant,
			);
		}

		return array(
			'products' => $produits_personnalises,
			'ecotaxes_pays' => $ecotaxes_pays,
			'total_ht' => $total_ht,
		);
	}
}
