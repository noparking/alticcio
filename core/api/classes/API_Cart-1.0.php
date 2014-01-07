<?php

class API_Cart {

	private $api;
	private $sql;

	public function __construct($api) {
		$this->api = $api;
		$this->sql = $api->sql;

		if (!isset($_SESSION['cart']) or !is_array($_SESSION['cart'])) {
			$_SESSION['cart'] = array();
		}
		if (!isset($_SESSION['cart']['items']) or !is_array($_SESSION['cart']['items'])) {
			$_SESSION['cart']['items'] = array();
		}
		if (!isset($_SESSION['cart']['personnalisations']) or !is_array($_SESSION['cart']['personnalisations'])) {
			$_SESSION['cart']['personnalisations'] = array();
		}
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

		if (isset($_SESSION['cart']['items'][$key])) {
			$_SESSION['cart']['items'][$key]['qte'] += $qte;
		}
		else {
			$_SESSION['cart']['items'][$key] = array(
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
			$key .= $perso['texte'];;
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

		if (isset($_SESSION['cart']['personnalisations'][$key])) {
			$_SESSION['cart']['personnalisations'][$key]['qte'] += $qte;
		}
		else {
			$_SESSION['cart']['personnalisations'][$key] = $perso;
		}
		
		return $key;
	}

	public function update($perso, $qte) {
		$this->set_token();
		$_SESSION['cart']['personnalisations'][$perso]['qte'] = $qte;
		$qte = 0;
		$id_produits = $_SESSION['cart']['personnalisations'][$perso]['id_produits'];
		$id_sku = $_SESSION['cart']['personnalisations'][$perso]['id_sku'];
		$sample = $_SESSION['cart']['personnalisations'][$perso]['sample'];
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		foreach ($_SESSION['cart']['personnalisations'] as $p) {
			if ($p['id_sku'] == $id_sku and $p['id_produits'] == $id_produits and $p['sample'] == $sample) {
				$qte += $p['qte'];
			}
		}
		$_SESSION['cart']['items'][$id]['qte'] = $qte;
	}

	public function remove($perso) {
		$this->set_token();
		$qte = $_SESSION['cart']['personnalisations'][$perso]['qte'];
		$id_produits = $_SESSION['cart']['personnalisations'][$perso]['id_produits'];
		$id_sku = $_SESSION['cart']['personnalisations'][$perso]['id_sku'];
		$sample = $_SESSION['cart']['personnalisations'][$perso]['sample'];
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		unset($_SESSION['cart']['personnalisations'][$perso]);
		$_SESSION['cart']['items'][$id]['qte'] -= $qte;
		if ($_SESSION['cart']['items'][$id]['qte'] == 0) {
			unset($_SESSION['cart']['items'][$id]);
		}
	}

	public function qte_min_to_add($id_produits, $id_sku, $qte_min, $colisage = 0, $sample = false) {
		$id = "$id_produits-$id_sku";
		if ($sample) {
			$id .= "[sample]";
		}
		if (isset($_SESSION['cart']['items'][$id])) {
			$qte_min = max(1, $qte_min - $_SESSION['cart']['items'][$id]['qte']);
		}
		if ($colisage) {
			$qte_min = ceil($qte_min / $colisage) * $colisage;
		}

		return $qte_min;
	}

	public function emptycart() {
		$_SESSION['cart']['items'] = array();
		$_SESSION['cart']['personnalisations'] = array();
		$this->set_token();
	}

	public function is_empty() {
		return count($_SESSION['cart']['items']) == 0;
	}

	public function items() {
		return $_SESSION['cart']['items'];
	}

	public function personnalisations() {
		return $_SESSION['cart']['personnalisations'];
	}

	public function check_franco($id_catalogues = 0) {
		$sku_ids = array();
		foreach ($_SESSION['cart']['items'] as $item) {
			$sku_ids[] = $item['id_sku'];
		}
		$liste_id_sku = implode(",", $sku_ids);
		// S'il y a au moins 1 SKU franco = 0 ou montant_ht = 0
		$q = <<<SQL
SELECT * FROM dt_prix
WHERE id_catalogues = $id_catalogues
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
		foreach ($_SESSION['cart']['items'] as $item) {
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
		foreach ($_SESSION['cart']['personnalisations'] as $perso) {
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

	public function prix_unitaire_pour_qte($id_sku, $qte, $id_catalogues = 0) {
		$q = <<<SQL
SELECT MIN(montant_ht) AS prix FROM dt_prix_degressifs
WHERE id_sku = $id_sku AND quantite <= $qte AND id_catalogues = $id_catalogues
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);

		if ($row['prix']) {
			$prix = $row['prix'];
		}
		else {
			$q = <<<SQL
SELECT montant_ht FROM dt_prix
WHERE id_sku = $id_sku AND id_catalogues = $id_catalogues
SQL;
			$res = $this->sql->query($q);
			$row = $this->sql->fetch($res);
			$prix = $row['montant_ht'];
		}

		return $prix;
	}

	public function set_token() {
		$token = substr(uniqid("", true), 0, 16);
		$_SESSION['cart']['token'] = $token;
	}

	public function token() {
		return $_SESSION['cart']['token'];
	}
}
