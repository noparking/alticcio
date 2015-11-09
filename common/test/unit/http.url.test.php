<?php

require_once('../simpletest/autorun.php');
require_once('../../php/http.class.php');

class TestHttpUrl extends UnitTestCase {
	function test_url() {
		$http = new Http("");
		
		$url = $http->url();
		$expected = "";
		$this->assertEqual($expected, $url);

		$url = $http->url("/toto/titi");
		$expected = "/toto/titi";
		$this->assertEqual($expected, $url);

		$url = $http->url("http://example.com/toto");
		$expected = "http://example.com/toto";
		$this->assertEqual($expected, $url);
	}

	function test_url_base_url() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array();
		
		$url = $http->url();
		$expected = "/www";
		$this->assertEqual($expected, $url);

		$url = $http->url("/toto/titi");
		$expected = "/www/toto/titi";
		$this->assertEqual($expected, $url);
	}

	function test_url_vars() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		
		$url = $http->url("/toto/{foo}");
		$expected = "/www/toto/bar";
		$this->assertEqual($expected, $url);

		$url = $http->url("/toto/{foo}", array('foo' => "baz"));
		$expected = "/www/toto/baz";
		$this->assertEqual($expected, $url);
	}

	function test_url_current() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		$http->route = array('path' => "/toto/{foo}");
		
		$url = $http->url($http);
		$expected = "/www/toto/bar";
		$this->assertEqual($expected, $url);

		$url = $http->url($http, array('foo' => "baz"));
		$expected = "/www/toto/baz";
		$this->assertEqual($expected, $url);
	}
}
