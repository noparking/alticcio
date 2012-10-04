<?php

require_once('../simpletest/autorun.php');
require_once('../../api/admin.php');

mysql_connect("localhost", "root", "");
mysql_set_charset("utf8");
mysql_select_db("doublet_api_test");

class TestOfApi_Admin extends UnitTestCase {

	function tearDown() {
		$tables = array(
			"api_keys",
			"api_keys_rules",
			"api_roles",
			"api_keys_roles",
			"api_roles_rules",
			"api_logs",
		);
		foreach ($tables as $table) {
			mysql_query("TRUNCATE TABLE $table");	
		}
	}

	function count_in_table($table) {
		$q = "SELECT COUNT(*) AS nb FROM {$table}";
		$res = mysql_query($q);
		$row = mysql_fetch_assoc($res);
		return $row['nb'];
	}

	function test_add_key() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$this->assertNotEqual($data['key'], "");

		$admin->add_key();
		$this->assertEqual(count($admin->keys()), 2);
	}

	function test_add_key_with_parameters() {
		$admin = new API_Admin("api_");
		$time = $_SERVER['REQUEST_TIME'];
		
		$key_id = $admin->add_key(array(
			'name' => "toto",
			'table_name' => "client",
			'table_id_name' => "id",
			'id_table' => 42,
			'language' => "fr_FR",
			'key' => "1234",
			'domain' => "www.exemple.com",
		));
		$data = $admin->key_data($key_id);
		$this->assertEqual($data['key'], "1234");
		$this->assertEqual($data['name'], "toto");
		$this->assertEqual($data['table_name'], "client");
		$this->assertEqual($data['table_id_name'], "id");
		$this->assertEqual($data['id_table'], 42);
		$this->assertEqual($data['language'], "fr_FR");
		$this->assertEqual($data['date_creation'], $time);
		$this->assertEqual($data['active'], 1);
		$this->assertEqual($data['domain'], "www.exemple.com");
	}

	function test_update_key() {
		$admin = new API_Admin("api_");
		
		$key_id = $admin->add_key(array(
			'name' => 'toto',
			'table_name' => "client",
			'table_id_name' => "id",
			'id_table' => 42,
			'language' => "fr_FR",
		));
		$key_id = $admin->update_key($key_id, array(
			'name' => "titi",
			'language' => "en_UK",			
			'truc bidon' => "ne doit pas planter",
			'key' => "1234",
		));
		$data = $admin->key_data($key_id);
		$this->assertEqual($data['key'], "1234");
		$this->assertEqual($data['name'], "titi");
		$this->assertEqual($data['table_name'], "client");
		$this->assertEqual($data['table_id_name'], "id");
		$this->assertEqual($data['id_table'], 42);
		$this->assertEqual($data['language'], "en_UK");
	}

	function test_delete_key() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$role_id = $admin->add_role("rôle 1");
		$admin->assign_role($key_id, $role_id);
		$admin->add_key_rule($key_id, "GET", "*", "allow");

		$this->assertEqual(count($admin->key_roles($key_id)), 1);	
		$this->assertEqual(count($admin->key_rules($key_id)), 1);	

		$admin->delete_key($key_id);
		
		$this->assertEqual(count($admin->key_roles($key_id)), 0);	
		$this->assertEqual(count($admin->key_rules($key_id)), 0);	
	}

	function test_change_key() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key1 = $data['key'];
		$this->assertNotEqual($key1, "");

		$key2 = $admin->change_key($key_id);
		$this->assertNotEqual($key2, "");
		$this->assertNotEqual($key1, $key2);
	}

	function test_disable_enable_key() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$this->assertTrue($data['active']);
		
		$admin->disable_key($key_id);
		$data = $admin->key_data($key_id);
		$this->assertFalse($data['active']);

		$admin->enable_key($key_id);
		$data = $admin->key_data($key_id);
		$this->assertTrue($data['active']);
	}

	function test_add_role() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$role_id_1 = $admin->add_role("rôle 1");
		$role_id_2 = $admin->add_role("rôle 2");
		// On ne peut pas ajouter 2 fois le même rôle
		$role_id_2 = $admin->add_role("rôle 2");
		$role_id_3 = $admin->add_role("rôle 3");

		$roles = $admin->roles();

		$this->assertEqual(count($roles), 3);
		$this->assertTrue(in_array("rôle 1", $roles));
		$this->assertTrue(in_array("rôle 2", $roles));
		$this->assertTrue(in_array("rôle 3", $roles));
		$this->assertFalse(in_array("rôle 4", $roles));
	}

	function test_delete_role() {
		$admin = new API_Admin("api_");

		$admin->add_role("rôle 1");
		$role_id = $admin->add_role("rôle 2");
		$admin->add_role("rôle 3");
		$key_id = $admin->add_key();
		$admin->assign_role($key_id, $role_id);
		$admin->add_role_rule($role_id, "GET", "*", "allow");
		$this->assertEqual(count($admin->key_roles($key_id)), 1);	
		$this->assertEqual(count($admin->role_rules($role_id)), 1);	
	
		$roles = $admin->roles();
		$this->assertEqual(count($roles), 3);

		$admin->delete_role($role_id);

		$roles = $admin->roles();
		$this->assertEqual(count($roles), 2);
		$this->assertFalse(in_array("rôle 2", $roles));
		$this->assertEqual(count($admin->key_roles($key_id)), 0);	
		$this->assertEqual(count($admin->role_rules($role_id)), 0);	
	}

	function test_assign_role() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$role_id_1 = $admin->add_role("rôle 1");
		$role_id_2 = $admin->add_role("rôle 2");
		$role_id_3 = $admin->add_role("rôle 3");

		$admin->assign_role($key_id, $role_id_1);
		$admin->assign_role($key_id, $role_id_1);
		$admin->assign_role($key_id, $role_id_2);

		$roles = $admin->key_roles($key_id);

		$this->assertEqual(count($roles), 2);
		$this->assertTrue(in_array("rôle 1", $roles));
		$this->assertTrue(in_array("rôle 2", $roles));
		$this->assertFalse(in_array("rôle 3", $roles));
	}

	function test_unassign_role() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$role_id_1 = $admin->add_role("rôle 1");
		$role_id_2 = $admin->add_role("rôle 2");
		$role_id_3 = $admin->add_role("rôle 3");

		$admin->assign_role($key_id, $role_id_1);
		$admin->assign_role($key_id, $role_id_2);
		$admin->assign_role($key_id, $role_id_3);

		$admin->unassign_role($key_id, $role_id_1);
		$admin->unassign_role($key_id, $role_id_3);

		$roles = $admin->key_roles($key_id);

		$this->assertEqual(count($roles), 1);
		$this->assertTrue(in_array("rôle 2", $roles));
	}

	function test_add_key_rules() {
		$admin = new API_Admin("api_");

		$key_id_1 = $admin->add_key();
		$key_id_2 = $admin->add_key();

		$admin->add_key_rule($key_id_1, "GET", "foo/bar", "allow");
		$admin->add_key_rule($key_id_1, "GET", "foo/baz", "deny");
		$admin->add_key_rule($key_id_2, "GET", "foo/baz", "deny");

		$this->assertEqual(count($admin->key_rules($key_id_1)), 2);
		$this->assertEqual(count($admin->key_rules($key_id_2)), 1);
		$this->assertEqual(count($admin->key_rules("123")), 0);
	}

	function test_delete_key_rule() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();

		$id_1 = $admin->add_key_rule($key_id, "GET", "*", "allow");
		$id_2 = $admin->add_key_rule($key_id, "GET", "foo/bar", "allow");
		$id_3 = $admin->add_key_rule($key_id, "GET", "foo/baz", "deny");

		$this->assertEqual(count($admin->key_rules($key_id)), 3);

		$admin->delete_key_rule($id_2);
		$this->assertEqual(count($admin->key_rules($key_id)), 2);
	}

	function test_add_role_rules() {
		$admin = new API_Admin("api_");

		$key_id = $admin->add_key();
		$role_id = $admin->add_role("client");

		$admin->add_role_rule($role_id, "GET", "foo/bar", "allow");
		$admin->add_role_rule($role_id, "GET", "foo/baz", "deny");
		// Le rôle 123 n'existe pas
		$admin->add_role_rule(123, "GET", "foo/baz", "deny");

		$this->assertEqual(count($admin->role_rules($role_id)), 2);
		$this->assertEqual(count($admin->role_rules(123)), 0);
	}

	function test_delete_role_rule() {
		$admin = new API_Admin("api_");

		$key = $admin->add_key();
		$role_id = $admin->add_role("client");

		$id_1 = $admin->add_role_rule($role_id, "GET", "*", "allow");
		$id_2 = $admin->add_role_rule($role_id, "GET", "foo/bar", "allow");
		$id_3 = $admin->add_role_rule($role_id, "GET", "foo/baz", "deny");

		$this->assertEqual(count($admin->role_rules($role_id)), 3);

		$admin->delete_role_rule($id_2);
		$this->assertEqual(count($admin->role_rules($role_id)), 2);
	}

	public function test_roles() {
		$admin = new API_Admin("api_");

		$admin->add_role("rôle 1");
		$admin->add_role("rôle 2");
		$admin->add_role("rôle 3");

		$roles = $admin->roles();
		$this->assertEqual(count($roles), 3);
		$this->assertTrue(in_array("rôle 2", $roles));
	}

	public function test_keys() {
		$admin = new API_Admin("api_");

		$key1 = $admin->add_key();
		$key2 = $admin->add_key();
		$key3 = $admin->add_key();

		$keys = $admin->keys();
		$this->assertEqual(count($keys), 3);
	}
	
	public function test_role_rules() {
		$admin = new API_Admin("api_");

		$id_1 = $admin->add_role("rôle 1");
		$id_2 = $admin->add_role("rôle 2");

		$admin->add_role_rule($id_1, "GET", "*", "allow");
		$admin->add_role_rule($id_1, "POST", "*", "allow");
		$admin->add_role_rule($id_2, "GET", "*", "deny");

		$rules_role_1 = $admin->role_rules($id_1);
		$this->assertEqual(count($rules_role_1), 2);

		$rules_role_2 = $admin->role_rules($id_2);
		$this->assertEqual(count($rules_role_2), 1);
	}

}
