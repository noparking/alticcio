<?php

require_once('../simpletest/autorun.php');
require_once('../../lib/router.class.php');

class TestRouter extends UnitTestCase {
	
	function test_match() {
		$pattern = "/toto/titi";
		$value = "/toto/titi";
		$pattern = Router::get_pattern($pattern);
		$this->assertTrue(Router::match($pattern, $value));

		$pattern = "/toto/*";
		$value = "/toto/titi/tata";
		$pattern = Router::get_pattern($pattern);
		$this->assertTrue(Router::match($pattern, $value));

		$pattern = "/toto/{titi}/tata";
		$value = "/toto/TITI/tata";
		$pattern = Router::get_pattern($pattern);
		$this->assertTrue(Router::match($pattern, $value));

		$pattern = "/toto/{titi=TITI}/tata";
		$value = "/toto/TITI/tata";
		$pattern = Router::get_pattern($pattern);
		$this->assertTrue(Router::match($pattern, $value));

		$pattern = "/toto/{titi=TOTO}/tata";
		$value = "/toto/TITI/tata";
		$pattern = Router::get_pattern($pattern);
		$this->assertFalse(Router::match($pattern, $value));

		$pattern = "/toto/titi[/tata]";
		$value = "/toto/titi";
		$pattern = Router::get_pattern($pattern);
		$this->assertTrue(Router::match($pattern, $value));
	}

	function test_get_positions() {
		$pattern = "/(toto)/((titi)/(titi))/(tutu)";

		$positions = Router::get_positions("toto", $pattern);
		$this->assertEqual(array(1), $positions); 
		
		$positions = Router::get_positions("titi", $pattern);
		$this->assertEqual(array(3, 4), $positions); 

		$positions = Router::get_positions("tutu", $pattern);
		$this->assertEqual(array(5), $positions); 

		$positions = Router::get_positions("tata", $pattern);
		$this->assertEqual(array(), $positions);
	}

	function test_get_vars() {
		$pattern = "/toto/{foo}/titi/{bar}/tutu";
		$value = "/toto/FOO/titi/BAR/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_vars($pattern, $value);
		$expected = array(
			'foo' => "FOO",
			'bar' => "BAR",
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto/[{foo}/titi]/{bar}/tutu";
		$value = "/toto//BAR/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_vars($pattern, $value);
		$expected = array(
			'foo' => "",
			'bar' => "BAR",
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto/{foo=FOO}/titi/{bar}/tutu";
		$value = "/toto/FOO/titi/BAR/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_vars($pattern, $value);
		$expected = array(
			'foo' => "FOO",
			'bar' => "BAR",
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto[/titi]/{foo}/*";
		$value = "/toto/FOO/titi/BAR/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_vars($pattern, $value);
		$expected = array(
			'foo' => "FOO",
		);
		$this->assertEqual($expected, $vars);
	}

	function test_get_stars() {
		$pattern = "/toto/*";
		$value = "/toto/titi/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_stars($pattern, $value);
		$expected = array(
			'titi/tutu',
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto/*/*";
		$value = "/toto/titi/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_stars($pattern, $value);
		$expected = array(
			'titi',
			'tutu',
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto/*/{tutu}";
		$value = "/toto/titi/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_stars($pattern, $value);
		$expected = array(
			'titi',
		);
		$this->assertEqual($expected, $vars);

		$pattern = "/toto[/tata]/*/{tutu}";
		$value = "/toto/titi/tutu";
		$pattern = Router::get_pattern($pattern);
		$vars = Router::get_stars($pattern, $value);
		$expected = array(
			'titi',
		);
		$this->assertEqual($expected, $vars);
	}

	function test_route() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/bar",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/foo/bar",
				'target' => "route_3"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/bar";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);

		$router->data['REQUEST_URI'] = "/something-else";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__with_wildchars() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/*",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo*",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/bar.html",
				'target' => "route_3"
			),
			array(
				'target' => "default_route"
			),
		);
#TODO wildchar au milieu
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo/aze";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foobar";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);

		$router->data['REQUEST_URI'] = "/bar.html";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);

		$router->data['REQUEST_URI'] = "/barxhtml";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__with_alternatives() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo|bar/plop",
				'target' => "route_1"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo/plop";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/bar/plop";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/baz/plop";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__home_page() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/",
				'target' => "home_page"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/";
		$route = $router->route();
		$this->assertEqual("home_page", $route['target']);

		$router->data['REQUEST_URI'] = "/something-else";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__with_multiple_criteria() {
		$routes = array(
			array(
				'HTTP_HOST' => "example.com",
				'REQUEST_URI' => "/foo",
				'target' => "route_1"
			),
			array(
				'HTTP_HOST' => "example.com",
				'REQUEST_URI' => "/bar",
				'target' => "route_2"
			),
			array(
				'HTTP_HOST' => "other.example.com",
				'REQUEST_URI' => "/foo",
				'target' => "route_3"
			),
			array(
				'HTTP_HOST' => "other.example.com",
				'REQUEST_URI' => "/bar",
				'target' => "route_4"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['HTTP_HOST'] = "example.com";
		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['HTTP_HOST'] = "example.com";
		$router->data['REQUEST_URI'] = "/bar";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);

		$router->data['HTTP_HOST'] = "other.example.com";
		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);

		$router->data['HTTP_HOST'] = "other.example.com";
		$router->data['REQUEST_URI'] = "/bar";
		$route = $router->route();
		$this->assertEqual("route_4", $route['target']);

		$router->data['HTTP_HOST'] = "another.example.com";
		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__with_vars() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/bar",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo/{id}",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/foo/no",
				'target' => "no_route"
			),
			array(
				'REQUEST_URI' => "/bar/{action}/{id}",
				'target' => "route_3"
			),
			array(
				'REQUEST_URI' => "/asuivre/{suite=*}",
				'target' => "route_4"
			),
			array(
				'REQUEST_URI' => "/special/{action}/{id}",
				'target' => "special_{action}_{id}"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/42";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual(42, $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/foo/no";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual("no", $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/bar/edit/42";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("edit", $router->vars['REQUEST_URI']['action']);
		$this->assertEqual(42, $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/asuivre/voici/la/suite";
		$route = $router->route();
		$this->assertEqual("route_4", $route['target']);
		$this->assertEqual("voici/la/suite", $router->vars['REQUEST_URI']['suite']);

		$router->data['REQUEST_URI'] = "/special/edit/42";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("special_edit_42", $route['target']);
	}

	function test_route__with_particular_vars() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/bar",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo/{id=42}",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/bar/{action=edit|delete}/{id=42}",
				'target' => "route_3"
			),
			array(
				'REQUEST_URI' => "/etc/{suite=*}",
				'target' => "etc_{suite}"
			),
			array(
				'HTTP_HOST' => "{host}",
				'REQUEST_URI' => "/toto/{suite=*}",
				'target' => "toto_{suite}_{host}"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/42";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual(42, $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/foo/43";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/bar/edit/42";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("edit", $router->vars['REQUEST_URI']['action']);
		$this->assertEqual(42, $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/bar/delete/42";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("delete", $router->vars['REQUEST_URI']['action']);
		$this->assertEqual(42, $router->vars['REQUEST_URI']['id']);

		$router->data['REQUEST_URI'] = "/bar/discard/42";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/bar/edit/43";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/etc/blabla";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("etc_blabla", $route['target']);
		$this->assertEqual("blabla", $router->vars['target']['suite']);

		$router->data['REQUEST_URI'] = "/etc/bla/bla/bla";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("etc_bla/bla/bla", $route['target']);
		$this->assertEqual("bla/bla/bla", $router->vars['target']['suite']);

		$router->data['REQUEST_URI'] = "/etc/";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$route = $router->apply();
		$this->assertEqual("etc_", $route['target']);
		$this->assertEqual("", $router->vars['target']['suite']);

		$router->data['HTTP_HOST'] = "example.com";
		$router->data['REQUEST_URI'] = "/toto/titi";
		$route = $router->route();
		$router->associate_vars("REQUEST_URI", "target");
		$router->associate_vars("HTTP_HOST", "target");
		$route = $router->apply();
		$this->assertEqual("toto_titi_example.com", $route['target']);
		$this->assertEqual("example.com", $router->vars['target']['host']);
		$this->assertEqual("titi", $router->vars['target']['suite']);
	}

	function test_route__with_optional_part() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo[/bar]",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo[/{bar}]",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/foo[/bar][/bar]",
				'target' => "route_3"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/baz";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/bar/bar";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/bar/baz";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/baz/bar";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);
	}

	function test_route__with_vars_substitutions() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/{bar}/{baz}/*",
				'target' => "route_1"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router();
		$router->routes = $routes;

		$router->data['REQUEST_URI'] = "/foo/toto/titi/etc/etc";
		$route = $router->route();
		$route = $router->apply();
		$this->assertEqual("/foo/toto/titi/etc/etc", $route['REQUEST_URI']);

		$route = $router->apply(array('bar' => 'tata'));
		$this->assertEqual("/foo/tata/titi/etc/etc", $route['REQUEST_URI']);

		$route = $router->apply(array('bar' => 'tata', 'baz' => "tutu"));
		$this->assertEqual("/foo/tata/tutu/etc/etc", $route['REQUEST_URI']);
	}
}

