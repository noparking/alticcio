<?php

class API_Admin {
	
	private $table_prefix;

	public function __construct($table_prefix = "", $sql = null) {
		$this->table_prefix = $table_prefix;
		$this->sql = $sql;
	}

	private function table($name) {
		return $this->table_prefix.$name;
	}

	private function generate_key() {
		return md5(uniqid("", true));
	}

	public function add_key($params = array()) {
		$fields = array();
		$values = array(); 
		foreach (array('name', 'table_name', 'table_id_name', 'id_table', 'language', 'key', 'domain', 'emails', 'referer') as $field) {
			if (isset($params[$field])) {
				$fields[] = "`$field`";
				$values[] = "'{$params[$field]}'";
			}
		}
		if (!isset($params['key'])) {
			$fields[] = "`key`";
			$values[] = "'{$this->generate_key()}'";
		}

		$fields[] = "`date_creation`";
		$values[] = $_SERVER['REQUEST_TIME'];

		$fields[] = "`active`";
		$values[] = 1;

		$fields_list = implode(",", $fields);
		$values_list = implode(",", $values);
		$q = <<<SQL
INSERT INTO {$this->table('keys')} ($fields_list) VALUES ($values_list)
SQL;
		$this->sql->query($q);

		return $this->sql->insert_id;
	}

	public function update_key($key_id, $params = array()) {
		$set = array();
		$fields = array(
			'key',
			'name',
			'table_name',
			'table_id_name',
			'id_table',
			'language',
			'active',
			'ip',
			'domain',
			'emails',
			'referer',
		);
		if (isset($params['domain'])) {
			$params['ip'] = "";
		}
		foreach ($fields as $attr) {
			if (isset($params[$attr])) {
				$set[] = "`$attr` = '{$params[$attr]}'";
			}
		}
		$q = "UPDATE {$this->table('keys')} SET ".implode(",", $set)." WHERE `id` = '$key_id'";
		$this->sql->query($q);

		return $key_id;
	}

	public function change_key($key_id) {
		$new_key = $this->generate_key();
		$q = <<<SQL
UPDATE {$this->table('keys')} SET `key` = '$new_key', `date_creation` = {$_SERVER['REQUEST_TIME']} WHERE `id` = '$key_id';
SQL;
		$this->sql->query($q);

		return $new_key;
	}

	public function disable_key($key_id) {
		$q = <<<SQL
UPDATE {$this->table('keys')} SET active = FALSE WHERE `id` = $key_id
SQL;
		$this->sql->query($q);
	}

	public function enable_key($key_id) {
		$q = <<<SQL
UPDATE {$this->table('keys')} SET active = TRUE WHERE `id` = $key_id
SQL;
		$this->sql->query($q);
	}

	public function delete_key($key_id) {
		$q = <<<SQL
DELETE FROM {$this->table('keys')} WHERE `id` = '$key_id';
SQL;
		$this->sql->query($q);

		foreach (array('keys_roles', 'keys_rules', 'logs') as $table) {
			$q = <<<SQL
DELETE FROM {$this->table($table)} WHERE `id_key` = '$key_id';
SQL;
			$this->sql->query($q);
		}
	}

	public function key_data($key_id) {
		$q = <<<SQL
SELECT * FROM {$this->table('keys')} WHERE `id` = '$key_id'
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
	
		return $row;
	}

	public function keys() {
		$q = <<<SQL
SELECT * FROM {$this->table('keys')} ORDER BY name ASC 
SQL;
		$res = $this->sql->query($q);
		$keys = array();
		while ($row = $this->sql->fetch($res)) {
			$keys[$row['id']] = $row;
		}
		return $keys;
	}

	public function add_key_rule($key_id, $method, $uri, $type, $log = 1) {
		$q = <<<SQL
INSERT INTO {$this->table('keys_rules')} (`method`, `uri`, `type`, `id_key`, `log`)
VALUES ('$method', '$uri', '$type', $key_id, $log)
SQL;
		$this->sql->query($q);

		return $this->sql->insert_id;
	}

	public function delete_key_rule($id) {
		$q = <<<SQL
SELECT id_key FROM {$this->table('keys_rules')} WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$id_key = $row['id_key'];
		$q = <<<SQL
DELETE FROM {$this->table('keys_rules')} WHERE id = $id
SQL;
		$this->sql->query($q);

		return $id_key;
	}

	public function key_rules($key_id) {
		$q = <<<SQL
SELECT * FROM {$this->table('keys_rules')} WHERE id_key = $key_id ORDER BY method ASC, uri ASC
SQL;
		$res = $this->sql->query($q);
		$rules = array();
		while ($row = $this->sql->fetch($res)) {
			$rules[$row['id']] = $row;
		}
		return $rules;
	}

	public function key_roles($key_id) {
		$q = <<<SQL
SELECT * FROM {$this->table('roles')} AS r
INNER JOIN {$this->table('keys_roles')} AS kr ON kr.id_role = r.id 
WHERE kr.id_key = $key_id
SQL;
		$res = $this->sql->query($q);
		$roles = array();
		while ($row = $this->sql->fetch($res)) {
			$roles[$row['id']] = $row['name'];
		}
		return $roles;
	}

	public function add_role($name) {
		$q = <<<SQL
SELECT id FROM {$this->table('roles')} WHERE `name` = '$name'
SQL;
		$res = $this->sql->query($q);
		if ($row = $this->sql->fetch($res)) {
			return $row['id'];
		}
		else {
			$q = <<<SQL
INSERT INTO {$this->table('roles')} (`name`) VALUES ('$name');
SQL;
			$this->sql->query($q);
			return $this->sql->insert_id;
		}
	}

	public function delete_role($role_id) {
		$q = <<<SQL
DELETE FROM {$this->table('roles')} WHERE id = $role_id;
SQL;
		$this->sql->query($q);

		foreach (array('keys_roles', 'roles_rules') as $table) {
			$q = <<<SQL
DELETE FROM {$this->table($table)} WHERE `id_role` = '$role_id';
SQL;
			$this->sql->query($q);
		}
	}

	public function roles() {
		$roles = array();
		$q = <<<SQL
SELECT id, name FROM {$this->table('roles')} ORDER BY name ASC
SQL;
		$res = $this->sql->query($q);
		while ($row = $this->sql->fetch($res)) {
			$roles[$row['id']] = $row['name'];
		}

		return $roles;
	}

	public function assign_role($key_id, $role_id) {
		$q = <<<SQL
SELECT * FROM {$this->table('keys_roles')} WHERE id_key = $key_id AND id_role = $role_id
SQL;
		$res = $this->sql->query($q);
		if (!$this->sql->fetch($res)) {
			$q = <<<SQL
INSERT INTO {$this->table('keys_roles')} (id_key, id_role) VALUES ($key_id, $role_id)
SQL;
			$this->sql->query($q);
		}
	}

	public function unassign_role($key_id, $role_id) {
		$q = <<<SQL
DELETE FROM {$this->table('keys_roles')} WHERE id_key = '$key_id' AND id_role = '$role_id'
SQL;
		$this->sql->query($q);
	}

	public function role_rules($role_id) {
		$q = <<<SQL
SELECT id, method, uri, type, log
FROM {$this->table('roles_rules')} 
WHERE id_role = $role_id 
ORDER BY method ASC, uri ASC
SQL;
		$res = $this->sql->query($q);
		$rules = array();
		while ($row = $this->sql->fetch($res)) {
			$rules[] = $row;
		}
		return $rules;
	}

	public function add_role_rule($role_id, $method, $uri, $type, $log = 1) {
		$roles_ids = array_keys($this->roles());
		if (in_array($role_id, $roles_ids)) {
			$q = <<<SQL
INSERT INTO {$this->table('roles_rules')} (`method`, `uri`, `type`, `id_role`, `log`)
VALUES ('$method', '$uri', '$type', $role_id, $log)
SQL;
			$this->sql->query($q);

			return $this->sql->insert_id;
		}
		else {
			return null;
		}
	}

	public function delete_role_rule($id) {
		$q = <<<SQL
SELECT id_role FROM {$this->table('roles_rules')} WHERE id = $id
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$id_role = $row['id_role'];
		$q = <<<SQL
DELETE FROM {$this->table('roles_rules')} WHERE id = $id
SQL;
		$this->sql->query($q);

		return $id_role;
	}

	public function watch($params = array()) {
		$q = <<<SQL
SELECT k.key, l.method, l.uri, l.status, l.date FROM {$this->table('logs')} AS l
INNER JOIN {$this->table('keys')} as k ON k.id = l.id_key
ORDER BY l.id
SQL;
		$res = $this->sql->query($q);
		
		$logs = array();
		while ($row = $this->sql->fetch($res)) {
			$logs[] = $row;
		}

		return $logs;
	}
}

