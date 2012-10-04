<?php

require_once '../simpletest/autorun.php';
require_once '../../outils/url_redirection.php';

class TestOfUrlRedirection extends UnitTestCase {
	
	function test_short_encode_and_decode() {
		$ur = new UrlRedirection(null, 1);
		$this->assertEqual($ur->short_encode(1, 1), "1");
		$this->assertEqual($ur->short_encode(1, 2), "2");
		$this->assertEqual($ur->short_encode(1, 3), "3");
		$this->assertEqual($ur->short_encode(1, 4), "4");
		$this->assertEqual($ur->short_encode(1, 10), "a");
		$this->assertEqual($ur->short_encode(1, 36), "10");

		$this->assertEqual($ur->short_decode("1"), array(1, 1));
		$this->assertEqual($ur->short_decode("2"), array(1, 2));
		$this->assertEqual($ur->short_decode("3"), array(1, 3));
		$this->assertEqual($ur->short_decode("4"), array(1, 4));
		$this->assertEqual($ur->short_decode("a"), array(1, 10));
		$this->assertEqual($ur->short_decode("10"), array(1, 36));

		$ur = new UrlRedirection(null, 2);
		$this->assertEqual($ur->short_encode(1, 1), "1");
		$this->assertEqual($ur->short_encode(1, 2), "3");
		$this->assertEqual($ur->short_encode(1, 3), "5");
		$this->assertEqual($ur->short_encode(1, 4), "7");
		$this->assertEqual($ur->short_encode(2, 1), "2");
		$this->assertEqual($ur->short_encode(2, 2), "4");
		$this->assertEqual($ur->short_encode(2, 3), "6");
		$this->assertEqual($ur->short_encode(2, 4), "8");

		$this->assertEqual($ur->short_decode("1"), array(1, 1));
		$this->assertEqual($ur->short_decode("2"), array(2, 1));
		$this->assertEqual($ur->short_decode("3"), array(1, 2));
		$this->assertEqual($ur->short_decode("4"), array(2, 2));
		$this->assertEqual($ur->short_decode("5"), array(1, 3));
		$this->assertEqual($ur->short_decode("6"), array(2, 3));
		$this->assertEqual($ur->short_decode("7"), array(1, 4));
		$this->assertEqual($ur->short_decode("8"), array(2, 4));

		$ur = new UrlRedirection(null, 3);
		$this->assertEqual($ur->short_encode(1, 1), "1");
		$this->assertEqual($ur->short_encode(1, 2), "4");
		$this->assertEqual($ur->short_encode(1, 3), "7");
		$this->assertEqual($ur->short_encode(1, 4), "a");
		$this->assertEqual($ur->short_encode(2, 1), "2");
		$this->assertEqual($ur->short_encode(2, 2), "5");
		$this->assertEqual($ur->short_encode(2, 3), "8");
		$this->assertEqual($ur->short_encode(2, 4), "b");
		$this->assertEqual($ur->short_encode(3, 1), "3");
		$this->assertEqual($ur->short_encode(3, 2), "6");
		$this->assertEqual($ur->short_encode(3, 3), "9");
		$this->assertEqual($ur->short_encode(3, 4), "c");

		$this->assertEqual($ur->short_decode("1"), array(1, 1));
		$this->assertEqual($ur->short_decode("2"), array(2, 1));
		$this->assertEqual($ur->short_decode("3"), array(3, 1));
		$this->assertEqual($ur->short_decode("4"), array(1, 2));
		$this->assertEqual($ur->short_decode("5"), array(2, 2));
		$this->assertEqual($ur->short_decode("6"), array(3, 2));
		$this->assertEqual($ur->short_decode("7"), array(1, 3));
		$this->assertEqual($ur->short_decode("8"), array(2, 3));
		$this->assertEqual($ur->short_decode("9"), array(3, 3));
		$this->assertEqual($ur->short_decode("a"), array(1, 4));
		$this->assertEqual($ur->short_decode("b"), array(2, 4));
		$this->assertEqual($ur->short_decode("c"), array(3, 4));

		$ur = new UrlRedirection(null, 5);
		$this->assertEqual($ur->short_encode(1, 1), "1");
		$this->assertEqual($ur->short_encode(1, 2), "6");
		$this->assertEqual($ur->short_encode(2, 1), "2");
		$this->assertEqual($ur->short_encode(2, 2), "7");
		$this->assertEqual($ur->short_encode(3, 1), "3");
		$this->assertEqual($ur->short_encode(3, 2), "8");
		$this->assertEqual($ur->short_encode(4, 1), "4");
		$this->assertEqual($ur->short_encode(4, 2), "9");
		$this->assertEqual($ur->short_encode(5, 1), "5");
		$this->assertEqual($ur->short_encode(5, 2), "a");

		$this->assertEqual($ur->short_decode("1"), array(1, 1));
		$this->assertEqual($ur->short_decode("2"), array(2, 1));
		$this->assertEqual($ur->short_decode("3"), array(3, 1));
		$this->assertEqual($ur->short_decode("4"), array(4, 1));
		$this->assertEqual($ur->short_decode("5"), array(5, 1));
		$this->assertEqual($ur->short_decode("6"), array(1, 2));
		$this->assertEqual($ur->short_decode("7"), array(2, 2));
		$this->assertEqual($ur->short_decode("8"), array(3, 2));
		$this->assertEqual($ur->short_decode("9"), array(4, 2));
		$this->assertEqual($ur->short_decode("a"), array(5, 2));
	}
}
