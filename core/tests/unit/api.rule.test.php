<?php

require_once('../simpletest/autorun.php');
require_once('../../api/admin.php');
require_once('../../api/api.php');

mysql_connect("localhost", "root", "");
mysql_set_charset("utf8");
mysql_select_db("doublet_api_test");

class TestOfApiRule extends UnitTestCase {

	function test_over() {
		$this->assertFalse(API_Rule::over("foo", "bar"));
		$this->assertFalse(API_Rule::over("foo", "foo"));
		$this->assertFalse(API_Rule::over("*", "*"));
		$this->assertFalse(API_Rule::over("test/foo", "test/bar"));
		$this->assertFalse(API_Rule::over("test/foo", "test/foo"));
		$this->assertFalse(API_Rule::over("test/*", "test/*"));
		$this->assertFalse(API_Rule::over("test/*/foo", "test/*/bar"));
		$this->assertFalse(API_Rule::over("test/*/foo", "test/*/foo"));
		$this->assertFalse(API_Rule::over("test/foo/bar", "test/*/baz"));
		$this->assertFalse(API_Rule::over("test/foo/bar", "*/bar/*"));

		$this->assertTrue(API_Rule::over("test", "*"));
		$this->assertTrue(API_Rule::over("*/test", "*"));
		$this->assertTrue(API_Rule::over("test/*", "*"));
		$this->assertTrue(API_Rule::over("test/foo", "test/*"));
		$this->assertTrue(API_Rule::over("test/foo/bar", "test/*"));
		$this->assertTrue(API_Rule::over("test/foo/*", "test/*"));
		$this->assertTrue(API_Rule::over("test/foo/bar", "test/*/bar"));
		$this->assertTrue(API_Rule::over("test/foo/bar", "*/bar"));
		$this->assertTrue(API_Rule::over("test/foo/bar", "*/foo/bar"));
		$this->assertTrue(API_Rule::over("test/foo/bar", "*/foo/*"));
	}

	function test_apply() {
		$this->assertFalse(API_Rule::apply("test", "foo"));
		$this->assertTrue(API_Rule::apply("test", "test"));
		$this->assertTrue(API_Rule::apply("test", "*"));
		$this->assertTrue(API_Rule::apply("foo/bar", "foo/bar"));
		$this->assertTrue(API_Rule::apply("foo/bar", "foo/*"));
		$this->assertTrue(API_Rule::apply("foo/bar", "*"));
		$this->assertFalse(API_Rule::apply("foo/bar", "foo/baz"));
	}
}
