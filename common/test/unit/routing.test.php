<?php

require_once('../simpletest/autorun.php');
require_once('../../php/router.class.php');

class TestRouter extends UnitTestCase {

	function test_target() {
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
		$router = new Router($routes);

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

	function test_target__with_wildchars() {
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
		$router = new Router($routes);

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

	function test_target__with_alternatives() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo|bar/plop",
				'target' => "route_1"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router($routes);

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

	function test_target__home_page() {
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
		$router = new Router($routes);

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

	function test_target__with_multiple_criteria() {
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
		$router = new Router($routes);

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

	function test_target__with_vars() {
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
				'REQUEST_URI' => "/special/{action}/{id}",
				'target' => "special_{action}_{id}"
			),
		);
		$router = new Router($routes);

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/42";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/foo/no";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual("no", $router->vars['id']);

		$router->data['REQUEST_URI'] = "/bar/edit/42";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("edit", $router->vars['action']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/special/edit/42";
		$route = $router->route();
		$this->assertEqual("special_edit_42", $route['target']);
	}

	function test_target__with_particular_vars() {
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
		$router = new Router($routes);

		$router->data['REQUEST_URI'] = "/foo/bar";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);

		$router->data['REQUEST_URI'] = "/foo/42";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/foo/43";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/bar/edit/42";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("edit", $router->vars['action']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/bar/delete/42";
		$route = $router->route();
		$this->assertEqual("route_3", $route['target']);
		$this->assertEqual("delete", $router->vars['action']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/bar/discard/42";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/bar/edit/43";
		$route = $router->route();
		$this->assertEqual("default_route", $route['target']);

		$router->data['REQUEST_URI'] = "/etc/blabla";
		$route = $router->route();
		$this->assertEqual("etc_blabla", $route['target']);
		$this->assertEqual("blabla", $router->vars['suite']);

		$router->data['REQUEST_URI'] = "/etc/bla/bla/bla";
		$route = $router->route();
		$this->assertEqual("etc_bla/bla/bla", $route['target']);
		$this->assertEqual("bla/bla/bla", $router->vars['suite']);

		$router->data['REQUEST_URI'] = "/etc/";
		$route = $router->route();
		$this->assertEqual("etc_", $route['target']);
		$this->assertEqual("", $router->vars['suite']);

		$router->data['HTTP_HOST'] = "example.com";
		$router->data['REQUEST_URI'] = "/toto/titi";
		$route = $router->route();
		$this->assertEqual("toto_titi_example.com", $route['target']);
		$this->assertEqual("example.com", $router->vars['host']);
		$this->assertEqual("titi", $router->vars['suite']);
	}

	function test_target__with_prefix() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/{id}",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/bar/{action}/{id}",
				'target' => "route_2"
			),
		);
		$router = new Router($routes);
		$router->prefixes['REQUEST_URI'] = "/mon/prefix";

		$router->data['REQUEST_URI'] = "/mon/prefix/foo/42";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);
		$this->assertEqual(42, $router->vars['id']);

		$router->data['REQUEST_URI'] = "/mon/prefix/bar/edit/42";
		$route = $router->route();
		$this->assertEqual("route_2", $route['target']);
		$this->assertEqual("edit", $router->vars['action']);
		$this->assertEqual(42, $router->vars['id']);
	}

	function test_target__with_optional_part() {
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
		$router = new Router($routes);

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

	function test_target__with_multiple_slashes() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/bar/baz",
				'target' => "route_1"
			),
			array(
				'target' => "default_route"
			),
		);
		$router = new Router($routes);

		$router->data['REQUEST_URI'] = "/foo//bar///baz";
		$route = $router->route();
		$this->assertEqual("route_1", $route['target']);
	}

	function test_change() {
		$route = array(
			'REQUEST_URI' => "/foo/{bar}/baz/*",
			'target' => "route_1"
		),
		$router = new Router($routes);

		$new_route = $router->change(array('REQUEST_URI' => "/foo/toto/baz/etc"), array('bar' => "titi"));
		$this->assertEqual(array('REQUEST_URI' => "/foo/titi/baz/etc"), $new_route);
	}
}

