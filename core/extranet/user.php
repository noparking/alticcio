<?php

class User {
	
	private $sql;
	
	const LOGGED = 1;
	const CREATED = 2;
	const UPDATED = 3;
	const UNKNOWN = -1;
	const UNAUTHORIZED = -2;
	const WRONGPASSWORD = -3;
	const ALLREADYEXISTS = -4;
	const UNKNOW_KEY = -5;
	
	
	public function __construct($sql = null) {
		$this->sql = $sql;
	}
	
	
	public function login($data) {
		$login = $this->sql->real_escape_string($data['login']);
		$q = "SELECT * FROM dt_users WHERE login = '$login'";
		$result = $this->sql->query($q);
		$user = $this->sql->fetch($result);
		
		if (!$user) {
			return self::UNKNOWN;
		}
		if (!$user['acces']) {
			return self::UNAUTHORIZED;
		}
		if ($user['password'] != sha1($data['password'])) {
			return self::WRONGPASSWORD;
		}
		$_SESSION['extranet']['user'] = $user;
		
		$permissions = array();
		$q = "SELECT perm FROM dt_groupes_users WHERE id = {$user['id_groupes_users']}";
		$result = $this->sql->query($q);
		$row = $this->sql->fetch($result);
		foreach (explode(",", $row['perm']) as $perm) {
			$permissions[] = trim($perm);
		}
		$_SESSION['extranet']['user']['permissions'] = $permissions;

		return self::LOGGED;
	}

	public function perms() {
		return $_SESSION['extranet']['user']['permissions'];
	}

	public function has_perm($permission) {
		if (in_array("all", $_SESSION['extranet']['user']['permissions'])) {
			return true;
		}
		if (in_array($permission, $_SESSION['extranet']['user']['permissions'])) {
			return true;
		}
		list($action, $object) = explode(" ", $permission);
		if (in_array("all $object", $_SESSION['extranet']['user']['permissions'])) {
			return true;
		}
		if (in_array("$action all", $_SESSION['extranet']['user']['permissions'])) {
			return true;
		}
		return false;
	}

	public function has_one_perm($permissions) {
		foreach ($permissions as $permission) {
			if ($this->has_perm($permission)) {
				return true;		
			}
		}
		return false;
	}

	public function has_all_perm($permissions) {
		foreach ($permissions as $permission) {
			if (!$this->has_perm($permission)) {
				return false;
			}
		}
		return true;
	}
	
	public function reset() {
		if ($this->is_logged()) {
			$id = $this->sql->real_escape_string($_SESSION['extranet']['user']['id']);
			$q = "SELECT * FROM dt_users WHERE id = $id";
			$result = $this->sql->query($q);
			$_SESSION['extranet']['user'] = $this->sql->fetch($result);
		}
	}
	
	
	public function profil($id_groupes_users) {
		$q = "SELECT nom FROM dt_groupes_users WHERE id = ".$id_groupes_users;
		$result = $this->sql->query($q);
		$row = $this->sql->fetch($result);
		return $row['nom'];
	}
	
	
	public function data() {
		return $_SESSION['extranet']['user'];
	}
	
	
	public function logout() {
		unset($_SESSION['extranet']['user']);
	}
	
	
	public function is_logged() {
		return isset($_SESSION['extranet']['user']);
	}
	
	
	public function create($data) {
		$data = $this->get_data($data);
		$q = "SELECT login FROM dt_users WHERE login = '".$this->sql->real_escape_string($data['login'])."'";
		$result = $this->sql->query($q);
		if ($this->sql->fetch($result)) {
			return self::ALLREADYEXISTS;
		}
		
		$values = array();
		foreach ($data as $key => $value) {
			$value = $this->sql->real_escape_string($value);
			if (is_numeric($value)) {
				$values[] = "$key = $value";
			}
			else {
				$values[] = "$key = '$value'";
			}
		}
		$q = "INSERT INTO dt_users SET ".implode(", ", $values);
		$this->sql->query($q);
		return self::CREATED;
	}
	
	
	public function delete($id_user) {
		$q = "DELETE FROM dt_users WHERE id = ".$this->sql->real_escape_string($id_user);
		$this->sql->query($q);
	}
	
	
	public function update($id_user, $form_values) {
		$id_user = $this->sql->real_escape_string($id_user);
		$data = $this->get_data($form_values);
		if ($form_values['password'] == "") {
			unset($data['password']);
		}
		if (!isset($form_values['acces'])) {
			$data['acces'] = 0;
		}
		if (isset($data['login'])) {
			$q = "SELECT id, login FROM dt_users WHERE id <> $id_user AND login = '".$this->sql->real_escape_string($data['login'])."'";
			$result = $this->sql->query($q);
			if ($this->sql->fetch($result)) {
				return self::ALLREADYEXISTS;
			}
		}
		
		$values = array();
		foreach ($data as $key => $value) {
			$value = $this->sql->real_escape_string($value);
			if (is_numeric($value)) {
				$values[] = "$key = $value";
			}
			else {
				$values[] = "$key = '$value'";
			}
		}
		$q = "UPDATE dt_users SET ".implode(", ", $values)." WHERE id = $id_user";
		$this->sql->query($q);

		$q = "DELETE FROM dt_users_blogs WHERE id_users = $id_user";
		$this->sql->query($q);

		if (isset($form_values['blogs']) and is_array($form_values['blogs'])) {
			if ($form_values['blogs'][0]) {
				$values = array("($id_user, 0)");
			}
			else {
				$values = array();
				foreach ($form_values['blogs'] as $id_blog => $checked) {
					if ($checked) {
						$values[] = "($id_user, $id_blog)";
					}
				}
			}
			if (count($values)) {
				$q = "INSERT INTO dt_users_blogs (id_users, id_blogs) VALUES ".implode(",", $values);
				$this->sql->query($q);
			}
		}

		return self::UPDATED;
	}
	
	
	public function get_list() {
		$list = array();
		$q = "SELECT u.id, u.login, u.email, u.acces, g.nom FROM dt_users AS u LEFT JOIN dt_groupes_users AS g ON g.id = u.id_groupes_users ORDER BY u.login";
		$result = $this->sql->query($q);
		while ($row = $this->sql->fetch($result)) {
			$list[$row['id']] = array('login' => $row['login'], 'profil' => $row['nom'], 'email' => $row['email'], 'acces' => $row['acces']);
		}
		return $list;
	}
	
	
	public function load($params) {
		$where = array();
		foreach ($params as $key => $value) {
			$value = $this->sql->real_escape_string($value);
			if (is_numeric($value)) {
				$where[] = "$key = $value";
			}
			else {
				$where[] = "$key = '$value'";
			}
		}
		$q = "SELECT * FROM dt_users WHERE ".implode(" AND ", $where);
		$result = $this->sql->query($q);
		return $this->sql->fetch($result);
	}
	
	
	private function get_data($params) {
		$data = array();
		foreach (array('id', 'login', 'password', 'email', 'acces', 'id_groupes_users', 'id_langues') as $field) {
			if (isset($params[$field])) {
				$data[$field] = $params[$field];
			}
		}
		if (isset($data['password'])) {
			$data['password'] = sha1($data['password']);
		}
		return $data;
	}
	
	
	public function list_profils() {
		$q = "SELECT id, nom FROM dt_groupes_users ORDER BY id";
		$result = $this->sql->query($q);
		$liste_profils = array();
		while($row = $this->sql->fetch($result)) {
			$liste_profils[$row['id']] = $row['nom'];
		}
		return $liste_profils;
	}

	public function list_langues() {
		$q = "SELECT id, code_langue FROM dt_langues ORDER BY id";
		$result = $this->sql->query($q);
		$liste_langues = array();
		while($row = $this->sql->fetch($result)) {
			$liste_langues[$row['id']] = $row['code_langue'];
		}
		return $liste_langues;
	}

	public function blogs($id_user = null) {
		if ($id_user === null) {
			$data = $this->data();
			$id_user = $data['id'];
		}
		$q = <<<SQL
SELECT id_blogs FROM dt_users_blogs WHERE id_users = {$id_user}
SQL;
		$res = $this->sql->query($q);
		$blogs = array();
		while($row = $this->sql->fetch($res)) {
			$blogs[] = $row['id_blogs'];
		}
		return $blogs;
	}

	public function access_all_blogs() {
		$data = $this->data();
		$id_user = $data['id'];
	
		$q = <<<SQL
SELECT id_users, id_blogs FROM dt_users_blogs
WHERE id_users = $id_user AND id_blogs = 0
SQL;
		$res = $this->sql->query($q);
		return ($this->sql->fetch($res)) ? true : false;
	}

	public function bloglangues() {
		$data = $this->data();
		$id_user = $data['id'];
	
		$bloglangues = array();
		if ($this->access_all_blogs()) {
			$q = <<<SQL
SELECT id AS id_langues, code_langue FROM dt_langues
SQL;
		}
		else {
			$q = <<<SQL
SELECT DISTINCT(bl.id_langues), l.code_langue FROM dt_blogs_langues AS bl
INNER JOIN dt_users_blogs AS ub ON ub.id_blogs = bl.id_blogs
INNER JOIN dt_langues AS l ON l.id = bl.id_langues
WHERE ub.id_users = $id_user
SQL;
		}
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$bloglangues[$row['id_langues']] = $row['code_langue'];
		}
		return $bloglangues;
	}

	public function blogthemes() {
		$data = $this->data();
		$id_user = $data['id'];
	
		$blogthemes = array();
		if ($this->access_all_blogs()) {
			$q = <<<SQL
SELECT id, nom, id_parent FROM dt_themes_blogs
SQL;
		}
		else {
			$q = <<<SQL
SELECT DISTINCT(btb.id_themes_blogs) AS id, tb.nom, tb.id_parent FROM dt_blogs_themes_blogs AS btb
INNER JOIN dt_users_blogs AS ub ON ub.id_blogs = btb.id_blogs
INNER JOIN dt_themes_blogs AS tb ON tb.id = btb.id_themes_blogs
WHERE ub.id_users = $id_user
SQL;
		}
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$blogthemes[] = $row;
		}
		return $blogthemes;
	}

	public function blogblogs() {
		$data = $this->data();
		$id_user = $data['id'];
	
		$blogblogs = array();
		if ($this->access_all_blogs()) {
			$q = <<<SQL
SELECT id, nom FROM dt_blogs
SQL;
		}
		else {
			$q = <<<SQL
SELECT b.id, b.nom FROM dt_blogs AS b
INNER JOIN dt_users_blogs AS ub ON ub.id_blogs = b.id
WHERE ub.id_users = $id_user
SQL;
		}
		$res = $this->sql->query($q);
		while($row = $this->sql->fetch($res)) {
			$blogblogs[$row['id']] = $row['nom'];
		}
		return $blogblogs;
	}
	
	function init_process_recuperer_password($id) {
		$id = (int) $id;
		$q = <<<SQL
DELETE FROM dt_users_password WHERE id_users = {$id}
SQL;
		$this->sql->query($q);
		$continue = true;
		$key = $id.time().uniqid("aberlaas")."aberlaas.com";
		$key = md5($key);
		$q = <<<SQL
INSERT INTO dt_users_password(id_users, `key`) VALUES ({$id}, '{$key}')
SQL;
		$this->sql->query($q);
		return $key;
	}
	
	function reinit_password($code_url, &$id_user) {
		$code_url = $this->sql->real_escape_string($code_url);
		$q = <<<SQL
SELECT id_users FROM dt_users_password WHERE `key` = '{$code_url}'
SQL;
		$res = $this->sql->query($q);
		$data = $this->sql->fetch($res);
		if (!$data) {
			return self::UNKNOW_KEY;
		}
		$new_password = mt_rand(100000, 999999);
		$update = array(
			'password' => $new_password,
			'acces' => 1,
		);
		$this->update($data['id_users'], $update);
		$id_user = $data['id_users'];
		$q = <<<SQL
DELETE FROM dt_users_password WHERE `key` = '{$code_url}'
SQL;
		$this->sql->query($q);
		return $new_password;
	}
}
