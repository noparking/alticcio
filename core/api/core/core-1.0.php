<?php
class API {
	
	private $key;
	private $key_id;
	private $key_roles;
	private $key_enabled;
	private $key_infos;

	private $func;
	private $args;

	private $errors = array();
	private $request = "";
	private $method;
	private $ip;

	private $table_prefix;

	private $params;

	public function __construct($table_prefix = "", $params = array()) {
		$this->table_prefix = $table_prefix;
		$this->params = $params;
	}

	private function table($name) {
		return $this->table_prefix.$name;
	}

	private function base_url() {
		return isset($this->params['base_url']) ? $this->params['base_url'] : "";
	}

	private function param($param) {
		if (isset($_GET[$param])) {
			return $_GET[$param];
		}
		else if (isset($this->params[$param])) {
			return $this->params[$param];
		}
		else {
			return "";
		}
	}

	public function get($uri) {
		$this->prepare($this->base_url(), "get", $uri);
		return $this->execute();
	}

	public function post($uri, $post) {
		$this->prepare($this->base_url(), "post", $uri, $post);
		return $this->execute();
	}

	public function prepare($base_url = "", $method = null, $uri = null, $post = null) {
		if ($method === null) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		if ($uri === null) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		$this->post = ($post === null) ? $_POST : $post;

		$this->key_id = null;
		$this->key_roles = array();
		$this->key_enabled = false;
		$this->key_infos = array();

		$this->request = trim(preg_replace("/\?(.*)$/", "", preg_replace("!^$base_url!", "", $uri)), "/");
		$elements = explode("/", $this->request);
		$this->method = strtolower($method);
		array_unshift($elements, $this->method);

		$func = implode("_", $elements);
		$this->args = array();
		while (count($elements) and !function_exists($func)) {
			array_unshift($this->args, array_pop($elements));
			$func = implode("_", $elements);
		}
		$this->func = function_exists($func) ? $func : null;

		$this->ip = $_SERVER['REMOTE_ADDR'];

		$this->tracked = $this->param('track');
		
		$this->widget_id = $this->param('widget');

		$this->key = $this->param('key');
		$q = <<<SQL
SELECT * FROM {$this->table('keys')} WHERE `key` = '{$this->key}'
SQL;
		$res = mysql_query($q);

		if ($row = mysql_fetch_assoc($res)) {
			$this->key_infos = $row;
			$this->key_id = $row['id'];
			if ($row['active']) {
				$this->key_enabled = true;
			}
		}
		if ($this->key_id === null) {
			$this->key = null;
		}

		$this->key_roles = array();
		if ($this->key_id) {
		$q = <<<SQL
SELECT ar.id, ar.name FROM {$this->table('roles')} as ar
INNER JOIN {$this->table('keys_roles')} AS akr ON akr.id_role = ar.id
WHERE akr.id_key = {$this->key_id}
SQL;
			$res = mysql_query($q);

			while ($row = mysql_fetch_assoc($res)) {
				$this->key_roles[$row['id']] = $row['name'];
			}
		}
	}

	public function key() {
		return $this->key;
	}

	public function info($info) {
		return isset($this->key_infos[$info]) ? $this->key_infos[$info]: null;
	}

	public function vocabulary() {
		$q = <<<SQL
SELECT term_key, term_value FROM {$this->table('keys_vocabulary')} WHERE id_key = {$this->key_id}
SQL;
		$res = mysql_query($q);

		$voca = array();
		while ($row = mysql_fetch_assoc($res)) {
			$voca[$row['term_key']] = $row['term_value'];
		}
		
		return $voca;
	}

	public function data() {
		$q = <<<SQL
SELECT data_key, data_value FROM {$this->table('keys_data')} WHERE id_key = {$this->key_id}
SQL;
		$res = mysql_query($q);

		$data = array();
		while ($row = mysql_fetch_assoc($res)) {
			$data[$row['data_key']] = $row['data_value'];
		}
		
		return $data;
	}

	public function func() {
		return $this->func;
	}

	public function args() {
		return $this->args;
	}

	public function errors($errors) {
		foreach ($errors as $code => $message) {
			$this->errors[$code] = $message;
		}
	}

	public function error($code, $message = null) {
		if ($message === null) {
			$message = isset($this->errors[$code]) ? $this->errors[$code] : "";
		}
		$data = array(
			'error' => $code,
			'message' => $message,
		);
		return $data;
	}

	public function check_key() {
		return $this->key === null ? false : true;
	}

	public function check_ip() {
		if ($this->key_infos['ip']) {
			return $this->key_infos['ip'] == $this->ip;
		}
		else {
			return true;
		}
	}

	public function check_domain() {
		if ($this->key_infos['domain']) {
			if ($this->key_infos['ip'] == $this->ip) {
				return true;
			}
			else {
				$hosts = gethostbynamel($this->key_infos['domain']);
				foreach ($hosts as $ip) {
					if ($this->ip == $ip) {
						$q = <<<SQL
UPDATE {$this->table('keys')} SET ip = '$ip' WHERE `key` = '{$this->key}'
SQL;
						mysql_query($q);
						$this->key_infos['ip'] = $ip;
						return true;
					}
				}
				return false;
			}
		}
		else {
			return true;
		}
	}

	public function check_key_enabled() {
		return $this->key_enabled;
	}

	public function check_function() {
		return $this->func === null ? false : true;
	}

	public function check_function_args() {
		$rf = new ReflectionFunction($this->func);
		$nb_args = 1;
		if ($this->method == "post") {
			$nb_args++;
		}
		return $rf->getNumberOfRequiredParameters() <= count($this->args) + $nb_args;
	}

	public function check_permission() {
		switch ($this->key_permission()) {
			case 1 : return true;
			case -1: return false;
			case 0 :
				if ($this->role_permission() == 1) {
					return true;
				}
				else {
					return false;
				}
		}
	}

	public function execute() {
		if (!$this->check_key()) {
			$data = $this->error(101);
		}
		else if (!$this->check_key_enabled()) {
			$data = $this->error(102);
		}
		else if (!$this->check_domain()) { // domain must be checked before ip
			$data = $this->error(107);
		}
		else if (!$this->check_ip()) {
			$data = $this->error(106);
		}
		else if (!$this->check_function()) {
			$data = $this->error(103);
		}
		else if (!$this->check_permission()) {
			$data = $this->error(104);
		}
		else if (!$this->check_function_args()) {
			$data = $this->error(105);
		}
		else {
			$args = array($this);
			if ($this->method == "post") {
				$args[] = $this->post;
			}
			$args = array_merge($args, $this->args);
			$data = call_user_func_array($this->func, $args);
		}
		$this->log(isset($data['error']) ? $data['error'] : 0);

		return $data;
	}

	public function key_id($key = null) {
		if ($key === null) {
			$key = $this->key;
		}
		if ($key !== null) {
			$q = <<<SQL
SELECT id FROM {$this->table('keys')} WHERE `key` = '$key'
SQL;
			$res = mysql_query($q);
			if ($row = mysql_fetch_assoc($res)) {
				return $row['id'];
			}
		}
		
		return null;
	}

	public function key_rules($key = null) {
		if ($key === null) {
			$key = $this->key;
		}
		$rules = array();
		$key_id = $this->key_id($key);
		if ($key_id) {
			$q = <<<SQL
SELECT * FROM {$this->table('keys_rules')} WHERE id_key = $key_id
SQL;
			$res = mysql_query($q);

			while ($row = mysql_fetch_assoc($res)) {
				$rules[$row['id']] = $row;
			}
		}

		return $rules;
	}

	public function key_roles() {
		return $this->key_roles;
	}

	private function role_id($role) {
		$q = <<<SQL
SELECT id FROM {$this->table('roles')} WHERE `name` = '$role'
SQL;
		$res = mysql_query($q);
		if ($row = mysql_fetch_assoc($res)) {
			return $row['id'];
		}

		return null;
	}

	public function role_rules($role) {
		$rules = array();
		$role_id = $this->role_id($role);
		if ($role_id) {
			$q = <<<SQL
SELECT * FROM {$this->table('roles_rules')} WHERE id_role = $role_id
SQL;
			$res = mysql_query($q);

			while ($row = mysql_fetch_assoc($res)) {
				$rules[$row['id']] = $row;
			}
		}

		return $rules;
	}

	private function key_permission() {
		$applied_rule = "*";
		$permission = 0;
		foreach ($this->key_rules() as $rule) {
			if (strtolower($rule['method']) == $this->method) {
				if (API_Rule::apply($this->request, $rule['uri'])) {
					if (API_Rule::over($rule['uri'], $applied_rule)) {
						$applied_rule = $rule['uri'];
						switch ($rule['type']) {
							case 'allow' : $permission = 1; break;
							case 'deny' : $permission = -1; break;
						}
					}
					else if ($applied_rule == $rule['uri']) {
						if ($rule['type'] == "deny") {
							$permission = -1;
						}
						else if ($permission == 0) {
							$permission = 1;
						}
					}
				}
			}
		}
		return $permission;
	}

	private function role_permission() {
		$applied_rule = "*";
		$permission = 0;
		foreach ($this->key_roles() as $role) {
			foreach ($this->role_rules($role) as $rule) {
				if (strtolower($rule['method']) == $this->method) {
					if (API_Rule::apply($this->request, $rule['uri'])) {
						if (API_Rule::over($rule['uri'], $applied_rule)) {
							$applied_rule = $rule['uri'];
							switch ($rule['type']) {
								case 'allow' : $permission = 1; break;
								case 'deny' : $permission = -1; break;
							}
						}
						else if ($applied_rule == $rule['uri']) {
							if ($rule['type'] == "deny") {
								$permission = -1;
							}
							else if ($permission == 0) {
								$permission = 1;
							}
						}
					}
				}
			}
		}
		return $permission;
	}

	public function track($action, $item = 0) {
		$id_widgets = (int)$this->widget_id;
		$date = $_SERVER['REQUEST_TIME'];
		$q = <<<SQL
INSERT INTO {$this->table('tracker')} (`id_widgets`, `id_keys`, `tracked`, `action`, `item`, `date`)
VALUES ($id_widgets, {$this->key_id}, '{$this->tracked}', '$action', $item, $date)
SQL;
		mysql_query($q);
	}

	private function log($status) {
		$date = $_SERVER['REQUEST_TIME'];
		$q = <<<SQL
INSERT INTO {$this->table('logs')} (`id_key`, `method`, `uri`, `status`, `date`)
VALUES ({$this->key_id}, '{$this->method}', '{$this->request}', '$status', $date)
SQL;
		mysql_query($q);
	}

	public function last_access() {
		$q = <<<SQL
SELECT MAX(`date`) AS last_access FROM {$this->table('logs')}
WHERE status = 0 AND id_key = {$this->key_id}
SQL;
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);
		
		return $row['last_access'];
	}

	public function last_call() {
		$q = <<<SQL
SELECT MAX(`date`) AS last_call FROM {$this->table('logs')}
WHERE status = 0 AND id_key = {$this->key_id} AND method = '{$this->method}' AND uri = '{$this->request}'
SQL;
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);
		
		return $row['last_call'];
	}

}

