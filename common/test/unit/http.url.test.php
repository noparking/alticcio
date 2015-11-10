<?php

require_once('../simpletest/autorun.php');
require_once('../../php/http.class.php');

class TestHttpUrl extends UnitTestCase {
	function test_url() {
		$http = new Http("");
		
		$url = $http->url("/toto/titi");
		$expected = "/toto/titi";
		$this->assertEqual($expected, $url);

		$url = $http->url("/");
		$expected = "/";
		$this->assertEqual($expected, $url);

		$url = $http->url("http://example.com/toto");
		$expected = "http://example.com/toto";
		$this->assertEqual($expected, $url);
	}

	function test_url_with_base_url() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array();
		
		$url = $http->url("/toto/titi");
		$expected = "/www/toto/titi";
		$this->assertEqual($expected, $url);

		$url = $http->url("/");
		$expected = "/www/";
		$this->assertEqual($expected, $url);

		$url = $http->url("http://example.com/toto");
		$expected = "http://example.com/toto";
		$this->assertEqual($expected, $url);
	}

	function test_url_with_vars() {
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
		$http->url_vars = array('foo' => "bar");
		$http->route = array('path' => "/toto/{foo}");
		
		$url = $http->url();
		$expected = "/toto/bar";
		$this->assertEqual($expected, $url);
		
		$url = $http->url("");
		$expected = "/toto/bar";
		$this->assertEqual($expected, $url);

		$url = $http->url(array('foo' => "baz"));
		$expected = "/toto/baz";
		$this->assertEqual($expected, $url);
	}

	function test_url_current_with_base_url() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		$http->route = array('path' => "/toto/{foo}");
		
		$url = $http->url();
		$expected = "/www/toto/bar";
		$this->assertEqual($expected, $url);
		
		$url = $http->url("");
		$expected = "/www/toto/bar";
		$this->assertEqual($expected, $url);

		$url = $http->url("", array('foo' => "baz"));
		$expected = "/www/toto/baz";
		$this->assertEqual($expected, $url);

		$url = $http->url(array('foo' => "baz"));
		$expected = "/www/toto/baz";
		$this->assertEqual($expected, $url);
	}

	function test_url_named_route() {
		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		$http->routes = array(
			'url_toto' => array('path' => "/toto/{foo}"),
		);
		
		$url = $http->url("url_toto");
		$expected = "/www/toto/bar";
		$this->assertEqual($expected, $url);

		$url = $http->url("url_toto", array('foo' => "baz"));
		$expected = "/www/toto/baz";
		$this->assertEqual($expected, $url);
	}

	function test_url_plus() {
		$http = new Http("");
		$http->url_vars = array('foo' => "bar");
		
		$url = $http->url("+/titi/{foo}");
		$expected = "/titi/bar";
		$this->assertEqual($expected, $url);

		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		$http->route = array('path' => "");

		$url = $http->url("+/titi/{foo}");
		$expected = "/www/titi/bar";
		$this->assertEqual($expected, $url);

		$http = new Http("");
		$http->base_url = "/www";
		$http->url_vars = array('foo' => "bar");
		$http->route = array('path' => "/toto/{foo}");

		$url = $http->url("+/titi/{foo}");
		$expected = "/www/toto/bar/titi/bar";
		$this->assertEqual($expected, $url);
	}

	function test_url_get_vars() {
		$http = new Http("");
		$http->base_url = "/www";
		
		$url = $http->url("/toto/titi", array(), array('foo' => "FOO", 'bar' => "BAR"));
		$expected = "/www/toto/titi?foo=FOO&bar=BAR";
		$this->assertEqual($expected, $url);

		$url = $http->url("/", array(), array('foo' => "FOO", 'bar' => "BAR"));
		$expected = "/www/?foo=FOO&bar=BAR";
		$this->assertEqual($expected, $url);

		$_GET = array('foo' => "truc", 'bar' => "BAR");
		$url = $http->url("/toto/titi");
		$expected = "/www/toto/titi";
		$this->assertEqual($expected, $url);
		
		$url = $http->url("/toto/titi", array(), array('foo' => "FOO", 'baz' => "BAZ"), $_GET);
		$expected = "/www/toto/titi?foo=FOO&baz=BAZ&bar=BAR";
		$this->assertEqual($expected, $url);

		$http->route = array('path' => "/toto/titi/{tata}");
		$http->url_vars = array('tata' => "TATA");
		$_GET = array('foo' => "truc", 'bar' => "BAR");

		$url = $http->url("");
		$expected = "/www/toto/titi/TATA";
		$this->assertEqual($expected, $url);

		$url = $http->url();
		$expected = "/www/toto/titi/TATA?foo=truc&bar=BAR";
		$this->assertEqual($expected, $url);
		
		$url = $http->url(array('tata' => 'TATATA'));
		$expected = "/www/toto/titi/TATATA?foo=truc&bar=BAR";
		$this->assertEqual($expected, $url);
		
		$url = $http->url(array(), array('foo' => "FOO", 'baz' => "BAZ"));
		$expected = "/www/toto/titi/TATA?foo=FOO&baz=BAZ&bar=BAR";
		$this->assertEqual($expected, $url);

		$url = $http->url("", array(), array('foo' => "FOO", 'baz' => "BAZ"));
		$expected = "/www/toto/titi/TATA?foo=FOO&baz=BAZ";
		$this->assertEqual($expected, $url);
	}

	function test_url_var() {
		$http = new Http("");
		$http->url_vars = array(
			'foo' => "FOO",
			'bar' => "BAR",
		);

		$var = $http->url_var('foo');
		$this->assertEqual("FOO", $var);

		$var = $http->url_var('bar');
		$this->assertEqual("BAR", $var);

		$var = $http->url_var('baz');
		$this->assertNull($var);
	}

	function test_url_vars() {
		$http = new Http("");
		$http->url_vars = array(
			'foo' => "FOO",
			'bar' => "BAR",
		);

		list($foo) = $http->url_vars('foo');
		$this->assertEqual("FOO", $foo);

		list($foo, $bar) = $http->url_vars('foo', 'bar');
		$this->assertEqual("FOO", $foo);
		$this->assertEqual("BAR", $bar);

		list($bar, $foo) = $http->url_vars('bar', 'foo');
		$this->assertEqual("FOO", $foo);
		$this->assertEqual("BAR", $bar);

		list($bar, $baz) = $http->url_vars('bar', 'baz');
		$this->assertEqual("BAR", $bar);
		$this->assertNull($baz);
	}
}
