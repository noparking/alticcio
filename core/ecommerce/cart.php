<?php

class Cart {

	private $sql;
	private $config;

	public function __construct($sql, $config) {
		$this->sql = $sql;
		$this->config = $config;

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
	
	public function add($id_produits, $id_sku, $qte) {
		if ($qte < 0) {
			return false;
		}

		$this->set_token();
		$id = "$id_produits-$id_sku";
		$key = $id;

		if (isset($_SESSION['cart']['items'][$id])) {
			$_SESSION['cart']['items'][$id]['qte'] += $qte;
		}
		else {
			$_SESSION['cart']['items'][$id] = array(
				'qte' => $qte,
				'id_produits' => $id_produits,
				'id_sku' => $id_sku,
			);
		}

		$perso = array(
			'id_produits' => $id_produits,
			'id_sku' => $id_sku,
			'qte' => $qte,
			'texte' => "",
			'fichier' => "",
			'nom_fichier' => "",
		);
		
		if (isset($_POST['perso_texte'])) {
			$perso['texte'] = $_POST['perso_texte'];
			$key .= $perso['texte'];;
		}
		
		if (isset($_POST['perso_objet'])) {
			$perso['objet'] = $_POST['perso_objet'];
			$key .= $perso['objet'];
		}

		if (isset($_FILES['perso_fichier']) and $_FILES['perso_fichier']['error'] == UPLOAD_ERR_OK) {
			$uploads_dir = $this->config->get("medias_path")."/files/personnalisations/";
			$tmp_name = $_FILES['perso_fichier']['tmp_name'];
			$original_name = $_FILES['perso_fichier']['name'];
			preg_match("/(\.[^\.]*)$/", $original_name, $matches);
			$ext = $matches[1];
			$name = md5_file($tmp_name).$ext;
			move_uploaded_file($tmp_name, "$uploads_dir/$name");
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
		$id = "$id_produits-$id_sku";
		foreach ($_SESSION['cart']['personnalisations'] as $p) {
			if ($p['id_sku'] == $id_sku and $p['id_produits'] == $id_produits) {
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
		$id = "$id_produits-$id_sku";
		unset($_SESSION['cart']['personnalisations'][$perso]);
		$_SESSION['cart']['items'][$id]['qte'] -= $qte;
		if ($_SESSION['cart']['items'][$id]['qte'] == 0) {
			unset($_SESSION['cart']['items'][$id]);
		}
	}

	public function qte_min_to_add($id_produits, $id_sku, $qte_min) {
		$id = "$id_produits-$id_sku";
		if (isset($_SESSION['cart']['items'][$id])) {
			return max(1, $qte_min - $_SESSION['cart']['items'][$id]['qte']);
		}
		else {
			return $qte_min;
		}
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
SELECT p1.phrase AS phrase_commercial, p2.phrase AS phrase_ultralog, s.id, s.ref_ultralog, px.montant_ht FROM dt_sku AS s
LEFT OUTER JOIN dt_phrases AS p1 ON p1.id = s.phrase_commercial AND p1.id_langues = $id_langues
LEFT OUTER JOIN dt_phrases AS p2 ON p2.id = s.phrase_ultralog AND p2.id_langues = $id_langues
LEFT OUTER JOIN dt_prix AS px ON px.id_sku = s.id
WHERE s.id IN ($sku_ids)
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$data[$row['id']]['nom'] = addslashes($row['phrase_commercial'] ? $row['phrase_commercial'] : $row['phrase_ultralog']);
			$data[$row['id']]['ref'] = $row['ref_ultralog'];
			$data[$row['id']]['prix_unitaire'] = $row['montant_ht'];
		}
		foreach ($_SESSION['cart']['personnalisations'] as $perso) {
			$id_sku = $perso["id_sku"];
			$array = array(
				'id_produits' => $perso["id_produits"],
				'id_sku' => $id_sku,
				'ref' => $data[$id_sku]['ref'],
				'nom' =>  $data[$id_sku]['nom'],
				'prix_unitaire' =>  $data[$id_sku]['prix_unitaire'],
				'quantite' => $perso['qte'],
				'personnalisation_texte' => $perso['texte'],
				'personnalisation_fichier' => $perso['fichier'],
				'personnalisation_nom_fichier' => $perso['nom_fichier'],
			);
			if (isset($perso['objet'])) {
				$array['personnalisation_objet'] = $perso['objet'];
			}
			$produits[] = $array;
		}

		return $produits;
	}

	public function set_token() {
		$token = substr(uniqid("", true), 0, 16);
		$_SESSION['cart']['token'] = $token;
	}

	public function token() {
		return $_SESSION['cart']['token'];
	}
}
