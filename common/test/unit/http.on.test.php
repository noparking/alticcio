<?php

require_once('../simpletest/autorun.php');
require_once('../../lib/http.class.php');

class TestHttpOn extends UnitTestCase {
	function test_on() {
		$http = new Http("");
		$this->assertEqual("ok", $http->on("a" == "a", "ok"));
		$this->assertEqual("", $http->on("a" == "b", "ok"));
		$this->assertEqual("ko", $http->on("a" == "b", "ok", "ko"));

		$this->assertEqual("b", $http->on(42, [41 => "a", 42 => "b", 43 => "c", 44 => "d"]));
		$this->assertEqual("AbD", $http->on(42, [41 => "a", 42 => "b", 43 => "c", 44 => "d"], [41 => "A", 42 => "B", 44 => "D"]));
	}
}
