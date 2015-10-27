<?php

require_once('../simpletest/autorun.php');
require_once('../../routing.class.php');

class TestRouting extends UnitTestCase {

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
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/bar";
		$this->assertEqual("route_2", $routing->target());

		$routing->data['REQUEST_URI'] = "/foo/bar";
		$this->assertEqual("route_3", $routing->target());

		$routing->data['REQUEST_URI'] = "/something-else";
		$this->assertEqual("default_route", $routing->target());
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
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo/aze";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/foobar";
		$this->assertEqual("route_2", $routing->target());

		$routing->data['REQUEST_URI'] = "/bar.html";
		$this->assertEqual("route_3", $routing->target());

		$routing->data['REQUEST_URI'] = "/barxhtml";
		$this->assertEqual("default_route", $routing->target());
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
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo/plop";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/bar/plop";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/baz/plop";
		$this->assertEqual("default_route", $routing->target());
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
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/";
		$this->assertEqual("home_page", $routing->target());

		$routing->data['REQUEST_URI'] = "/something-else";
		$this->assertEqual("default_route", $routing->target());
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
		$routing = new Routing($routes);

		$routing->data['HTTP_HOST'] = "example.com";
		$routing->data['REQUEST_URI'] = "/foo";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['HTTP_HOST'] = "example.com";
		$routing->data['REQUEST_URI'] = "/bar";
		$this->assertEqual("route_2", $routing->target());

		$routing->data['HTTP_HOST'] = "other.example.com";
		$routing->data['REQUEST_URI'] = "/foo";
		$this->assertEqual("route_3", $routing->target());

		$routing->data['HTTP_HOST'] = "other.example.com";
		$routing->data['REQUEST_URI'] = "/bar";
		$this->assertEqual("route_4", $routing->target());

		$routing->data['HTTP_HOST'] = "another.example.com";
		$routing->data['REQUEST_URI'] = "/foo";
		$this->assertEqual("default_route", $routing->target());
	}

	function test_target__with_vars() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/bar",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo/[id]",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/foo/no",
				'target' => "no_route"
			),
			array(
				'REQUEST_URI' => "/bar/[action]/[id]",
				'target' => "route_3"
			),
			array(
				'REQUEST_URI' => "/special/[action]/[id]",
				'target' => "special_[action]_[id]"
			),
		);
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo/bar";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/foo/42";
		$this->assertEqual("route_2", $routing->target());
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/foo/no";
		$this->assertEqual("route_2", $routing->target());
		$this->assertEqual("no", $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/bar/edit/42";
		$this->assertEqual("route_3", $routing->target());
		$this->assertEqual("edit", $routing->vars['action']);
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/special/edit/42";
		$this->assertEqual("special_edit_42", $routing->target());
	}

	function test_target__with_particular_vars() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/bar",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/foo/[id=42]",
				'target' => "route_2"
			),
			array(
				'REQUEST_URI' => "/bar/[action=edit|delete]/[id=42]",
				'target' => "route_3"
			),
			array(
				'REQUEST_URI' => "/etc/[suite=*]",
				'target' => "etc_[suite]"
			),
			array(
				'HTTP_HOST' => "[host]",
				'REQUEST_URI' => "/toto/[suite=*]",
				'target' => "toto_[suite]_[host]"
			),
			array(
				'target' => "default_route"
			),
		);
		$routing = new Routing($routes);

		$routing->data['REQUEST_URI'] = "/foo/bar";
		$this->assertEqual("route_1", $routing->target());

		$routing->data['REQUEST_URI'] = "/foo/42";
		$this->assertEqual("route_2", $routing->target());
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/foo/43";
		$this->assertEqual("default_route", $routing->target());

		$routing->data['REQUEST_URI'] = "/bar/edit/42";
		$this->assertEqual("route_3", $routing->target());
		$this->assertEqual("edit", $routing->vars['action']);
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/bar/delete/42";
		$this->assertEqual("route_3", $routing->target());
		$this->assertEqual("delete", $routing->vars['action']);
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/bar/discard/42";
		$this->assertEqual("default_route", $routing->target());

		$routing->data['REQUEST_URI'] = "/bar/edit/43";
		$this->assertEqual("default_route", $routing->target());

		$routing->data['REQUEST_URI'] = "/etc/blabla";
		$this->assertEqual("etc_blabla", $routing->target());
		$this->assertEqual("blabla", $routing->vars['suite']);

		$routing->data['REQUEST_URI'] = "/etc/bla/bla/bla";
		$this->assertEqual("etc_bla/bla/bla", $routing->target());
		$this->assertEqual("bla/bla/bla", $routing->vars['suite']);

		$routing->data['REQUEST_URI'] = "/etc/";
		$this->assertEqual("etc_", $routing->target());
		$this->assertEqual("", $routing->vars['suite']);

		$routing->data['HTTP_HOST'] = "example.com";
		$routing->data['REQUEST_URI'] = "/toto/titi";
		$this->assertEqual("toto_titi_example.com", $routing->target());
		$this->assertEqual("example.com", $routing->vars['host']);
		$this->assertEqual("titi", $routing->vars['suite']);
	}

	function test_target__with_prefix() {
		$routes = array(
			array(
				'REQUEST_URI' => "/foo/[id]",
				'target' => "route_1"
			),
			array(
				'REQUEST_URI' => "/bar/[action]/[id]",
				'target' => "route_2"
			),
		);
		$routing = new Routing($routes);
		$routing->prefixes['REQUEST_URI'] = "/mon/prefix";

		$routing->data['REQUEST_URI'] = "/mon/prefix/foo/42";
		$this->assertEqual("route_1", $routing->target());
		$this->assertEqual(42, $routing->vars['id']);

		$routing->data['REQUEST_URI'] = "/mon/prefix/bar/edit/42";
		$this->assertEqual("route_2", $routing->target());
		$this->assertEqual("edit", $routing->vars['action']);
		$this->assertEqual(42, $routing->vars['id']);
	}
}

