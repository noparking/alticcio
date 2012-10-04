<?php

require_once "abstract_content.php";

class Bloc extends AbstractContent {

	private $sql;
	private $fallbacks_path;
	private $fallbacks_folder;
	private $prefix = "";
	public $values;

	public function __construct($sql, $prefix = "", $fallbacks_path = "") {
		$this->sql = $sql;
		$this->fallbacks_path = $fallbacks_path;
		$this->fallbacks_folder = "blocs/";
		$this->prefix = $prefix;
	}

	public function liste(&$filter = null) {
		$q = <<<SQL
SELECT b.id, b.nom, l.code_langue, b.actif FROM dt_blocs AS b
INNER JOIN dt_langues AS l ON l.id = b.id_langues
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

	public function langues() {
		$langues = array();
		$q = <<<SQL
SELECT id AS id_langues, code_langue FROM dt_langues
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$langues[$row['id_langues']] = $row['code_langue'];
		}
		return $langues;
	}

	public function load($id) {
		$q = <<<HTML
SELECT b.id, b.nom, b.id_langues, b.contenu, b.actif FROM dt_blocs AS b
WHERE b.id = $id
HTML;
		$res = $this->sql->query($q);
		$this->values = $this->sql->fetch($res);
	}

	public function save($data) {
		$sub_blocs = array();
		preg_match_all("/{bloc=([^}]+)}/", $data['bloc']['contenu'], $matches);
		foreach($matches[1] as $other_bloc) {
			if (!in_array($other_bloc, 	$sub_blocs)) {
				$sub_blocs[] = $other_bloc;
			}
		}
		if (isset($data['bloc']['id'])) {
			$id = $data['bloc']['id'];
			$id_blocs = $this->get_id_blocs($data['bloc']['nom']);
			if ($id_blocs != 0 and $id_blocs != $data['bloc']['id']) {
				return -1;
			}
			if (!$this->verifier_existance($sub_blocs)) {
				return -2;
			}
			if (!$this->verifier_dependances($id, $data['bloc']['nom'], $sub_blocs)) {
				return -3;
			}
			$values = array(
				"nom = '{$data['bloc']['nom']}'",
				"contenu = '{$data['bloc']['contenu']}'",
				"id_langues = '{$data['bloc']['id_langues']}'",
				"actif = '{$data['bloc']['actif']}'",
			);
			$q = "UPDATE dt_blocs SET ".implode(",", $values)." WHERE id = $id";
			$this->sql->query($q);
		}
		else {
			$id_blocs = $this->get_id_blocs($data['bloc']['nom']);
			if ($id_blocs != 0) {
				return -1;
			}
			if (!$this->verifier_existance($sub_blocs)) {
				return -2;
			}
			$values = array(
				'nom' => "'{$data['bloc']['nom']}'",
				'contenu' => "'{$data['bloc']['contenu']}'",
				'id_langues' => "'{$data['bloc']['id_langues']}'",
				'actif' => "'{$data['bloc']['actif']}'",
			);
			$q = "INSERT INTO dt_blocs (".implode(",", array_keys($values)).") VALUES (".implode(",", $values).")"; 
			$this->sql->query($q);
			$id = $this->sql->insert_id();
		}
		$this->creer_dependences($id, $sub_blocs);

		return $id;
	}

	public function verifier_existance($sub_blocs) {
		foreach ($sub_blocs as $bloc_name) {
			$q = "SELECT id FROM dt_blocs WHERE id = '$bloc_name' OR nom = '$bloc_name'";
			$res = $this->sql->query($q);
			if (!$this->sql->fetch($res)) {
				return false;
			}
		}
		return true;
	}

	public function verifier_dependances($id, $nom, $sub_blocs) {
		$blocs_interdits = $this->blocs_interdits($id);
		foreach ($sub_blocs as $bloc_name) {
			if (is_numeric($bloc_name)) {
				if ($bloc_name == $id) {
					return false;
				}
				if (in_array($bloc_name, $blocs_interdits)) {
					return false;
				}
			}
			else {
				if ($bloc_name == $nom) {
					return false;
				}
				$id_blocs = $this->get_id_blocs($bloc_name);
				if (in_array($id_blocs, $blocs_interdits)) {
					return false;
				}
			}
		}
		return true;
	}

	public function get_id_blocs($nom) {
		$q = "SELECT id FROM dt_blocs WHERE nom = '{$nom}'";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row ? $row['id'] : 0;
	}

	public function blocs_interdits($id) {
		$blocs_interdits = array();
		$q = "SELECT id_blocs_parent FROM dt_blocs_dependances WHERE id_blocs = $id";	
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$blocs_interdits[] = $row['id_blocs_parent'];
			foreach ($this->blocs_interdits($row['id_blocs_parent']) as $id_blocs) {
				$blocs_interdits[] = $id_blocs;
			}
		}
		return $blocs_interdits;
	}

	public function creer_dependences($id, $sub_blocs) {
		$q = "DELETE FROM dt_blocs_dependances WHERE id_blocs_parent = $id";
		$this->sql->query($q);
		foreach ($sub_blocs as $bloc_name) {
			$id_blocs = is_numeric($bloc_name) ? $bloc_name : $this->get_id_blocs($bloc_name);
			$q = "INSERT INTO dt_blocs_dependances (id_blocs_parent, id_blocs) VALUES ($id, $id_blocs)";
			$this->sql->query($q);
		}
	}

	public function get_dependances($id) {
		$q = <<<SQL
SELECT b.nom, b.id FROM dt_blocs AS b
INNER JOIN dt_blocs_dependances AS bd ON bd.id_blocs_parent = b.id
WHERE bd.id_blocs = $id
SQL;
		$res = $this->sql->query($q);
		$dependances = array();
		while ($row = $this->sql->fetch($res)) {
			$dependances[$row['id']] = $row['nom'];
		}
		return $dependances;
	}

	public function delete($data) {
		$id = $data['bloc']['id'];
		$dependances = $this->get_dependances($id);
		if (count($dependances)) {
			return $dependances;
		}
		$q = "DELETE FROM dt_blocs_dependances WHERE id_blocs = {$id}";
		$this->sql->query($q);
		$q = "DELETE FROM dt_blocs WHERE id = {$id}";
		$this->sql->query($q);

		return 0;
	}

	public function afficher($id_or_name, $my_vars = array(), $tab = "", $display_anyway = false, $no_comment = false, $fallbacks_folder = null) {
		if (!is_array($my_vars)) {
			$tab = $my_vars;
			$my_vars = array();
		}
		if ($fallbacks_folder === null) {
			$fallbacks_folder = $this->fallbacks_folder;
		}
		$q = <<<SQL
SELECT * FROM dt_blocs
SQL;
		$q .= is_numeric($id_or_name) ? " WHERE id = {$id_or_name}" : " WHERE nom = '{$this->prefix}{$id_or_name}'";
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$id = "";
		$nom = "";
		$contenu = "";
		$addtab = "";
		$display = true;
		if ($row) {
			$id = $row['id'];
			$nom = $row['nom'];
			$contenu = trim($row['contenu']);
			if ($row['actif']) {
				$statut = "active";
				if (!$this->validate_html($contenu)) {
					$statut = "invalid";
				}
			}
			else {
				$statut = "inactive";
				$display = false;
			}
		}

		$nom = $id_or_name;
		$class = str_replace("_", "-", $nom);

		$fallback = $this->fallbacks_path.$fallbacks_folder.$nom.".php";
		if (file_exists($fallback)) {
			include $fallback;
			if (!isset($statut)) {
				$contenu = isset($bloc) ? trim($bloc) : "";
				$statut = "fallback";
			}
			if (isset($bloc)) {
				if (preg_match("/^\s+/", $bloc, $matches)) {
					$addtab .= $matches[0];
				}
			}
		}
		
		if (!isset($statut)) {
			$statut = "missing";
		}

		if ($display_anyway) {
			$display = true;
		}

		if (!isset($vars)) {
			$vars = array();
		}
		foreach ($my_vars as $cle => $valeur) {
			$vars[$cle] = $valeur;
		}

		$html = "";
		if (!$no_comment) {
			$html .= "<!-- bloc_start id=\"{$id}\" name=\"{$nom}\" status=\"{$statut}\" -->\n";
		}
		if (isset($before)) {
			$html .= "$tab$before\n";
		}
		if (!$no_comment) {
			$html .= "$tab$addtab<!-- bloc_content_start -->\n";
		}
		$html .= $display ? $tab.$addtab.str_replace("\n", "\n$tab$addtab", str_replace("\n$addtab", "\n", $contenu)) : "";
		if (!$no_comment) {
			$html .= "\n$tab$addtab<!-- bloc_content_stop -->";
		}
		if (isset($after)) {
			$html .= "\n$tab$after";
		}
		if (!$no_comment) {
			$html .= "\n$tab<!-- bloc_end -->\n";
		}
		preg_match_all("/{bloc=([^}]+)}/", $html, $matches);
		foreach($matches[1] as $bloc_name) {
			$subbloc = $this->afficher($bloc_name, $vars, "\t", $display_anyway);
			$subbloc =  str_replace("\n", "\n$tab", $subbloc);
			$html = str_replace("{bloc=$bloc_name}", $subbloc, $html);
		}
		foreach ($vars as $key => $value) {
			$html = str_replace('{'.$key.'}', $value, $html);
		}

		return $html;
	}

	public function mail($name, $my_vars = array()) {
		return $this->afficher("mail_".$name, $my_vars, "", true, true, "mails/"); 
	}
}
