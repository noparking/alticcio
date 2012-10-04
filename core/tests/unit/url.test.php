<?php

require_once('../simpletest/autorun.php');
require_once('../../outils/url.php');

class MyUrl extends Url {
	public function build() {
		list($page, $params) = func_get_args();
		return array(
			'page' => $page,
			'action' => $params['action'],
			'id' => $params['id'],
		);
	}
}

class TestOfUrl extends UnitTestCase {
	
	function testMakeUrl() {
		$url = new MyUrl();
		$url->elements("page", "action", "id");
		
		$this->assertEqual($url->make("user", array('id' => 42, 'action' => "edit")), "user/edit/42");
	}
	
	function testMakeUrlWithBaseUrl() {
		$url = new MyUrl();
		$url->elements("page", "action", "id");
		$url->set_base("/toto/");
		
		$this->assertEqual($url->make("user", array('id' => 42, 'action' => "edit")), "/toto/user/edit/42");
	}
	
	function testGetElements() {
		$url = new MyUrl();
		$url->elements("page", "action", "id");
		$url->set_base("/toto/");
		$_SERVER['REQUEST_URI'] = "/toto/user/edit/42";
		
		$this->assertEqual($url->get("page"), "user");
		$this->assertEqual($url->get("action"), "edit");
		$this->assertEqual($url->get("id"), "42");
		$this->assertEqual($url->get("rien"), "");
	}
}


?>