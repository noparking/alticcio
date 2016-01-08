<?php

require_once('../simpletest/autorun.php');
require_once('../../api/admin.php');
require_once('../../api/api.php');

class Mysql extends Mysqli {
	
	public function __construct() {
		parent::__construct("localhost", "root", "", "doublet_api_test");
		$this->set_charset("utf8");
	}

	public function fetch($result) {
		return $result->fetch_assoc();
	}
}

class TestOfApi extends UnitTestCase {

	function tearDown() {
		$tables = array(
			"api_keys",
			"api_keys_rules",
			"api_roles",
			"api_keys_roles",
			"api_roles_rules",
			"api_logs",
		);
		$sql = new Mysql();
		foreach ($tables as $table) {
			$sql->query("TRUNCATE TABLE $table");	
		}
		$_GET = array();
		$_POST = array();
		$_SERVER['REQUEST_METHOD'] = "";
		$_SERVER['REQUEST_URI'] = "";
		$_SERVER['REQUEST_TIME'] = time();
	}

	function test_errors() {
		$sql = new Mysql();
		$api = new API("api_", $sql);
		$api->errors(array(
			1 => "error 1",
			2 => "error 2",
		));

		$error = $api->error(1);
		$this->assertEqual($error['error'], 1);
		$this->assertEqual($error['message'], "error 1");

		$error = $api->error(2);
		$this->assertEqual($error['error'], 2);
		$this->assertEqual($error['message'], "error 2");

		$error = $api->error(42);
		$this->assertEqual($error['error'], 42);
		$this->assertEqual($error['message'], "");

		$api->errors(array(
			2 => "new error 2",
			42 => "error 42",
		));

		$error = $api->error(1);
		$this->assertEqual($error['error'], 1);
		$this->assertEqual($error['message'], "error 1");

		$error = $api->error(2);
		$this->assertEqual($error['error'], 2);
		$this->assertEqual($error['message'], "new error 2");

		$error = $api->error(42);
		$this->assertEqual($error['error'], 42);
		$this->assertEqual($error['message'], "error 42");
	}

	function test_execute_errors() {

		function get_testapi_existant($api) {
			return "toto";
		}
		function get_testapi_existant_avec_parametres($api, $a, $b) {
			return "titi";
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 101);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi/inexistant";
		$_GET = array('key' => $key);
		$admin->disable_key($key_id);
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 102);

		$admin->enable_key($key_id);
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 103);

		$_SERVER['REQUEST_URI'] = "testapi/existant";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 104);

		$admin->add_key_rule($key_id, "GET", "testapi/*", "allow");
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse(is_array($response) and isset($response['error']));

		$_SERVER['REQUEST_URI'] = "testapi/existant/avec/parametres";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 105);

		$_SERVER['REQUEST_URI'] = "testapi/existant/avec/parametres/a";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response['error'], 105);

		$_SERVER['REQUEST_URI'] = "testapi/existant/avec/parametres/a/b";
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse(is_array($response) and isset($response['error']));

		$_SERVER['REQUEST_URI'] = "testapi/existant/avec/parametres/a/b/c";
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse(is_array($response) and isset($response['error']));
	}

	function test_execute_select_function() {
		function get($api) {
			return "default";
		}

		function get_testapi($api) {
			return 42;
		}

		function get_testapi_foo($api) {
			return "foo";
		}

		function get_testapi_foo_bar($api) {
			return "foo and bar";
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, 42);

		$_SERVER['REQUEST_URI'] = "testapi/foo";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, "foo");

		$_SERVER['REQUEST_URI'] = "testapi/foo/bar";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, "foo and bar");

		$_SERVER['REQUEST_URI'] = "";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, "default");
	}

	function test_execute_function_with_parameters() {
		function get_testapi_params($api, $a, $b) {
			return $a + $b;
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi/params/25/32/rien/toto";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, 57);

		$this->assertEqual($api->func(), "get_testapi_params");

		$this->assertEqual($api->args(), array(25, 32, "rien", "toto"));
	}

	function test_log() {
		function get_testapi_justforlog($api) {
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$logs = $admin->watch();
		$this->assertEqual(count($logs), 0);
		
		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi/justforlog";
		$_GET = array('key' => $key);
		$api->prepare();
		$api->execute();
		$logs = $admin->watch();
		$this->assertEqual(count($logs), 1);
		$this->assertEqual($logs[0], array('key' => $key, 'method' => "get", 'uri' => "testapi/justforlog", 'status' => 104, 'date' => $_SERVER['REQUEST_TIME']));
		
		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$api->prepare();
		$api->execute();
		$logs = $admin->watch();
		$this->assertEqual(count($logs), 2);
		$this->assertEqual($logs[1], array('key' => $key, 'method' => "get", 'uri' => "testapi/justforlog", 'status' => 0, 'date' => $_SERVER['REQUEST_TIME']));
	}

	function test_role_permission() {
		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];

		$role_id = $admin->add_role("client");
		$admin->assign_role($key_id, $role_id);

		$admin->add_role_rule($role_id, "GET", "test/ok", "allow");
		$admin->add_role_rule($role_id, "GET", "test/ko", "deny");
		$admin->add_role_rule($role_id, "GET", "test/ko/ok", "allow");
		$admin->add_role_rule($role_id, "GET", "test/ok/ko", "deny");

		$_SERVER['REQUEST_METHOD'] = "GET";
		$_GET = array('key' => $key);

		// deny si rien n'est précisé
		$_SERVER['REQUEST_URI'] = "test";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ok";
		$api->prepare();
		$this->assertTrue($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ok/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ko/ok";
		$api->prepare();
		$this->assertTrue($api->check_permission());

		// en cas de conflit, c'est le deny qui l'emporte quelque soit l'ordre des rules
		$admin->add_role_rule("client", "GET", "test/conflit1", "allow");
		$admin->add_role_rule("client", "GET", "test/conflit1", "deny");
		$admin->add_role_rule("client", "GET", "test/conflit2", "deny");
		$admin->add_role_rule("client", "GET", "test/conflit2", "allow");
		$_SERVER['REQUEST_URI'] = "test/conflit1";
		$api->prepare();
		$this->assertFalse($api->check_permission());
		$_SERVER['REQUEST_URI'] = "test/conflit2";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "autre";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$admin->add_role_rule($role_id, "GET", "*", "allow");
		$admin->add_role_rule($role_id, "GET", "*/ko", "deny");

		$_SERVER['REQUEST_URI'] = "autre";
		$api->prepare();
		$this->assertTrue($api->check_permission());

		$_SERVER['REQUEST_URI'] = "autre/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());

	}

	function test_key_permission() {
		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];

		$admin->add_key_rule($key_id, "GET", "test/ok/*", "allow");
		$admin->add_key_rule($key_id, "GET", "test/ko/*", "deny");
		$admin->add_key_rule($key_id, "GET", "test/ko/ok", "allow");
		$admin->add_key_rule($key_id, "GET", "test/ok/ko", "deny");

		$_SERVER['REQUEST_METHOD'] = "GET";
		$_GET = array('key' => $key);

		// deny si rien n'est précisé
		$_SERVER['REQUEST_URI'] = "test";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ok";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ok/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "test/ko/ok";
		$api->prepare();
		$this->assertTrue($api->check_permission());

		// en cas de conflit, c'est le deny qui l'emporte quelque soit l'ordre des rules
		$admin->add_key_rule($key_id, "GET", "test/conflit1", "allow");
		$admin->add_key_rule($key_id, "GET", "test/conflit1", "deny");
		$admin->add_key_rule($key_id, "GET", "test/conflit2", "deny");
		$admin->add_key_rule($key_id, "GET", "test/conflit2", "allow");
		$_SERVER['REQUEST_URI'] = "test/conflit1";
		$api->prepare();
		$this->assertFalse($api->check_permission());
		$_SERVER['REQUEST_URI'] = "test/conflit2";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$_SERVER['REQUEST_URI'] = "autre";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$admin->add_key_rule($key_id, "GET", "*/ko", "deny");

		$_SERVER['REQUEST_URI'] = "autre";
		$api->prepare();
		$this->assertTrue($api->check_permission());

		$_SERVER['REQUEST_URI'] = "autre/ko";
		$api->prepare();
		$this->assertFalse($api->check_permission());
	}

	function test_role_and_key_permission() {
		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$role_id = $admin->add_role("client");
		$admin->assign_role($key_id, $role_id);

		$_SERVER['REQUEST_METHOD'] = "GET";
		$_GET = array('key' => $key);

		// deny si rien n'est précisé
		$_SERVER['REQUEST_URI'] = "test/ok";
		$api->prepare();
		$this->assertFalse($api->check_permission());

		$admin->add_role_rule($role_id, "GET", "test/ok", "allow");
		$api->prepare();
		$this->assertTrue($api->check_permission());

		// Les permissions par clé sont prioritaires sur les permissions par rôle
		$admin->add_key_rule($key_id, "GET", "test/ok", "deny");
		$api->prepare();
		$this->assertFalse($api->check_permission());
		
		// Et ce même si le pattern pour le rôle est plus spécifique
		$admin->add_key_rule($key_id, "GET", "foo", "allow");
		$admin->add_role_rule($role_id, "GET", "foo/bar", "deny");
		$_SERVER['REQUEST_URI'] = "foo/bar";
		$api->prepare();
		$this->assertFalse($api->check_permission());
		$admin->add_key_rule($key_id, "GET", "foo/*", "allow");
		$api->prepare();
		$this->assertTrue($api->check_permission());

	}

	function test_ip() {
		function get_testapi_justforip($api) {

		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi/justforlog";
		$_SERVER['REMOTE_ADDR'] = "37.59.55.124";
		$_GET = array('key' => $key);
		$api->prepare();
		$this->assertTrue($api->check_ip());

		$admin->update_key($key_id, array('ip' => "111.222.333.444"));
		$api->prepare();
		$this->assertFalse($api->check_ip());
		$response = $api->execute();
		$this->assertEqual($response['error'], 106);

		$admin->update_key($key_id, array('ip' => "37.59.55.124"));
		$api->prepare();
		$this->assertTrue($api->check_ip());
	}

	function test_domain() {
		function get_testapi_justfordomain($api) {

		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "*", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "testapi/justforlog";
		$_SERVER['REMOTE_ADDR'] = "37.59.55.124";
		$_GET = array('key' => $key);
		$api->prepare();
		$this->assertTrue($api->check_domain());
		$api->prepare();
		$this->assertEqual($api->info("ip"), "");

		$admin->update_key($key_id, array('domain' => "noparking.net"));
		$api->prepare();
		$this->assertTrue($api->check_domain());
		$api->prepare();
		$this->assertEqual($api->info("ip"), "37.59.55.124");

		$admin->update_key($key_id, array('domain' => "google.fr"));
		$api->prepare();
		$this->assertFalse($api->check_domain());
		$response = $api->execute();
		$this->assertEqual($response['error'], 107);
		$api->prepare();
		$this->assertEqual($api->info("ip"), "");

		$admin->update_key($key_id, array('domain' => "noparking.net"));
		$admin->update_key($key_id, array('ip' => "111.222.333.444"));
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse(is_array($response) and isset($response['error']));
	}

	function test_last_access() {

		function get_test_last_access($api) {
			return $api->last_access();
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "test/last/access", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "test/last/access";
		$_SERVER['REQUEST_TIME'] = "123456";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse($response);

		// En appel en erreur n'est pas comptabilisé
		$_SERVER['REQUEST_URI'] = "test/error";
		$_SERVER['REQUEST_TIME'] = "123457";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();

		$_SERVER['REQUEST_URI'] = "test/last/access";
		$_SERVER['REQUEST_TIME'] = "1234568";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, 123456);
	}

	function test_last_call() {

		function get_test_last_call($api) {
			if ($_SERVER['REQUEST_TIME'] == "123458") {
				return $api->error(42);	
			}
			return $api->last_call();
		}

		function get_test_other_call($api) {
			return 42;
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "test/last/call", "allow");
		$admin->add_key_rule($key_id, "GET", "test/other/call", "allow");
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "test/last/call";
		$_SERVER['REQUEST_TIME'] = "123456";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();
		$this->assertFalse($response);

		$_SERVER['REQUEST_TIME'] = "123457";
		$_SERVER['REQUEST_URI'] = "test/other/call";
		$api->prepare();
		$response = $api->execute();

		// En appel en erreur n'est pas comptabilisé
		$_SERVER['REQUEST_URI'] = "test/last/call";
		$_SERVER['REQUEST_TIME'] = "123458";
		$_GET = array('key' => $key);
		$api->prepare();
		$response = $api->execute();
	
		$_SERVER['REQUEST_TIME'] = "123459";
		$_SERVER['REQUEST_URI'] = "test/last/call";
		$api->prepare();
		$response = $api->execute();
		$this->assertEqual($response, 123456);
	}

	function test_post_request() {
		function post_testapi_post($api, $post, $key) {
			return $post[$key];
		}

		$sql = new Mysql();
		$api = new API("api_", $sql);
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "POST", "*", "allow");
		$_SERVER['REQUEST_METHOD'] = "POST";
		$_SERVER['REQUEST_URI'] = "testapi/post/toto";
		$_GET = array('key' => $key);
		$_POST = array('toto' => "TOTO", 'titi' => "TITI");
		$api->prepare();
		$response = $api->execute();

		$this->assertEqual($response, "TOTO");

		$this->assertEqual($api->func(), "post_testapi_post");

		$this->assertEqual($api->args(), array("toto"));
	}

	function test_post_with_php() {
		$sql = new Mysql();
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "POST", "*", "allow");

		$sql = new Mysql();
		$api = new API("api_", $sql, array('key' => $key));
		$uri = "testapi/post/titi";
		$post = array('toto' => "TOTO", 'titi' => "TITI");
		$response = $api->post($uri, $post);

		$this->assertEqual($response, "TITI");

		$this->assertEqual($api->func(), "post_testapi_post");

		$this->assertEqual($api->args(), array("titi"));
	}

	function test_get_with_php() {
		$sql = new Mysql();
		$admin = new API_Admin("api_", $sql);

		$key_id = $admin->add_key();
		$data = $admin->key_data($key_id);
		$key = $data['key'];
		$admin->add_key_rule($key_id, "GET", "*", "allow");

		$sql = new Mysql();
		$api = new API("api_", $sql, array('key' => $key));
		$uri = "testapi";
		$response = $api->get($uri);

		$this->assertEqual($response, 42);

		$this->assertEqual($api->func(), "get_testapi");

		$this->assertEqual($api->args(), array());
	}
}

