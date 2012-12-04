<?php

class Client {
	
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
		$login = mysql_real_escape_string($data['login']);
		$q = "SELECT * FROM dt_clients WHERE login = '$login'";
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
		$_SESSION['extranet']['client'] = $user;
		
		$permissions = array();
		$_SESSION['extranet']['client']['permissions'] = $permissions;

		return self::LOGGED;
	}

	public function perms() {
		return $_SESSION['extranet']['client']['permissions'];
	}

	public function has_perm($permission) {
		if (in_array("all", $_SESSION['extranet']['client']['permissions'])) {
			return true;
		}
		if (in_array($permission, $_SESSION['extranet']['client']['permissions'])) {
			return true;
		}
		list($action, $object) = explode(" ", $permission);
		if (in_array("all $object", $_SESSION['extranet']['client']['permissions'])) {
			return true;
		}
		if (in_array("$action all", $_SESSION['extranet']['client']['permissions'])) {
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
			$id = mysql_real_escape_string($_SESSION['extranet']['client']['id']);
			$q = "SELECT * FROM dt_clients WHERE id = $id";
			$result = $this->sql->query($q);
			$_SESSION['extranet']['client'] = $this->sql->fetch($result);
		}
	}
	
	
	public function data() {
		return $_SESSION['extranet']['client'];
	}
	
	
	public function logout() {
		unset($_SESSION['extranet']['client']);
	}
	
	
	public function is_logged() {
		return isset($_SESSION['extranet']['client']);
	}
	
	
	public function create($data) {
		$data = $this->get_data($data);
		$q = "SELECT login FROM dt_clients WHERE login = '".mysql_real_escape_string($data['login'])."'";
		$result = $this->sql->query($q);
		if ($this->sql->fetch($result)) {
			return self::ALLREADYEXISTS;
		}
		
		$values = array();
		foreach ($data as $key => $value) {
			$value = mysql_real_escape_string($value);
			if (is_numeric($value)) {
				$values[] = "$key = $value";
			}
			else {
				$values[] = "$key = '$value'";
			}
		}
		$q = "INSERT INTO dt_clients SET ".implode(", ", $values);
		$this->sql->query($q);
		return self::CREATED;
	}
	
	
	public function delete($id_user) {
		$q = "DELETE FROM dt_clients WHERE id = ".mysql_real_escape_string($id_user);
		$this->sql->query($q);
	}
	
	
	public function update($id_user, $form_values) {
		$id_user = mysql_real_escape_string($id_user);
		$data = $this->get_data($form_values);
		if ($form_values['password'] == "") {
			unset($data['password']);
		}
		if (!isset($form_values['acces'])) {
			$data['acces'] = 0;
		}
		if (isset($data['login'])) {
			$q = "SELECT id, login FROM dt_clients WHERE id <> $id_user AND login = '".mysql_real_escape_string($data['login'])."'";
			$result = $this->sql->query($q);
			if ($this->sql->fetch($result)) {
				return self::ALLREADYEXISTS;
			}
		}
		
		$values = array();
		foreach ($data as $key => $value) {
			$value = mysql_real_escape_string($value);
			if (is_numeric($value)) {
				$values[] = "$key = $value";
			}
			else {
				$values[] = "$key = '$value'";
			}
		}
		$q = "UPDATE dt_clients SET ".implode(", ", $values)." WHERE id = $id_user";
		$this->sql->query($q);

		return self::UPDATED;
	}
	
	
	public function get_list() {
		$list = array();
		$q = "SELECT u.id, u.login, u.email, u.acces FROM dt_clients AS u ORDER BY u.login";
		$result = $this->sql->query($q);
		while ($row = $this->sql->fetch($result)) {
			$list[$row['id']] = array('login' => $row['login'], 'email' => $row['email'], 'acces' => $row['acces']);
		}
		return $list;
	}
	
	
	public function load($params) {
		$where = array();
		foreach ($params as $key => $value) {
			$value = mysql_real_escape_string($value);
			if (is_numeric($value)) {
				$where[] = "$key = $value";
			}
			else {
				$where[] = "$key = '$value'";
			}
		}
		$q = "SELECT * FROM dt_clients WHERE ".implode(" AND ", $where);
		$result = $this->sql->query($q);
		return $this->sql->fetch($result);
	}
	
	
	private function get_data($params) {
		$data = array();
		foreach (array('id', 'login', 'password', 'email', 'acces') as $field) {
			if (isset($params[$field])) {
				$data[$field] = $params[$field];
			}
		}
		if (isset($data['password'])) {
			$data['password'] = sha1($data['password']);
		}
		return $data;
	}
	
	
	function init_process_recuperer_password($id) {
		$id = (int) $id;
		$q = <<<SQL
DELETE FROM dt_clients_password WHERE id_clients = {$id}
SQL;
		$this->sql->query($q);
		$continue = true;
		$key = $id.time().uniqid("aberlaas")."aberlaas.com";
		$key = md5($key);
		$q = <<<SQL
INSERT INTO dt_clients_password(id_clients, `key`) VALUES ({$id}, '{$key}')
SQL;
		$this->sql->query($q);
		return $key;
	}
	
	function reinit_password($code_url, &$id_client) {
		$code_url = mysql_real_escape_string($code_url);
		$q = <<<SQL
SELECT id_clients FROM dt_clients_password WHERE `key` = '{$code_url}'
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
		$this->update($data['id_clients'], $update);
		$id_client = $data['id_clients'];
		$q = <<<SQL
DELETE FROM dt_clients_password WHERE `key` = '{$code_url}'
SQL;
		$this->sql->query($q);
		return $new_password;
	}
}
