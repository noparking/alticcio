<?php

require_once('../simpletest/autorun.php');
require_once('../../outils/config.php');

class TestOfConfig extends UnitTestCase {
	
	function testBaseUrl() {
		$GLOBALS['config']['base_url'] = "/plop/";
		$config = new Config();
		$this->assertEqual($config->base_url(), $GLOBALS['config']['base_url']);
	}
	
	function testCoreMedia() {
		$GLOBALS['config']['medias_url'] = "http://plop/";
		$config = new Config();
		
		$path = $config->core_media("file.css");
		$this->assertEqual($path, "http://plop/medias/css/file.css");
		
		$path = $config->core_media("file.js");
		$this->assertEqual($path, "http://plop/medias/js/file.js");
		
		$path = $config->core_media("file.jpeg");
		$this->assertEqual($path, "http://plop/medias/images/file.jpeg");
		
		$path = $config->core_media("file.png");
		$this->assertEqual($path, "http://plop/medias/images/file.png");
	}
	
	function testMedia() {
		$GLOBALS['config']['base_path'] = dirname(__FILE__)."/../../";
		$GLOBALS['config']['base_url'] = "/plop/";
		$config = new Config();
		
		$path = $config->media("file.css");
		$this->assertEqual($path, "/plop/medias/css/file.css");
		
		$path = $config->media("file.js");
		$this->assertEqual($path, "/plop/medias/js/file.js");
		
		$path = $config->media("file.jpeg");
		$this->assertEqual($path, "/plop/medias/images/file.jpeg");
		
		$path = $config->media("file.png");
		$this->assertEqual($path, "/plop/medias/images/file.png");
		
		$GLOBALS['config']['base_url'] = "/";
		$config = new Config();
		$path = $config->media("file.png");
		$this->assertEqual($path, "/medias/images/file.png");
	}
	
	function testSetGet() {
		$config = new Config();
		$this->assertNull($config->get("plop"));
		$config->set("plop", "foo");
		$this->assertEqual($config->get("plop"), "foo");
	}
	
	function testDb() {
		$config = new Config();
		$data = array();
		$this->assertEqual($config->db(), $data);
		
		$GLOBALS['config']['db_user'] = "my_user";
		$config = new Config();
		$data = array('user' => "my_user");
		$this->assertEqual($config->db(), $data);
		
		$GLOBALS['config']['db_password'] = "my_password";
		$GLOBALS['config']['db_server'] = "my_server";
		$GLOBALS['config']['db_database'] = "my_database";
		$config = new Config();
		$data = array('user' => "my_user", 'server' => "my_server", 'password' => "my_password", 'database' => "my_database");
		$this->assertEqual($config->db(), $data);
	}
}


?>
