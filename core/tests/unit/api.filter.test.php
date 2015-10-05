<?php

require_once('../simpletest/autorun.php');
require_once('../../api/admin.php');
require_once('../../api/api.php');

class TestOfApiFilter extends UnitTestCase {
	
	function test_tree() {
		$get = array(
			'toto' => "a",
			'titi.toto' => "b",
			'titi.tata' => "c",
			'tata.titi.toto' => "d",
		);

		$tree = array(
			'toto' => "a",
			'titi' => array(
				'toto' => "b",
				'tata' => "c",
			),
			'tata' => array(
				'titi' => array(
					'toto' => "d",
				),
			),
		);

		$this->assertEqual(API_Filter::tree($get), $tree);
	}

	function test_pass() {
		$get = array(
			'toto' => "a",
		);
		$tree = API_Filter::tree($get);

		$good = array(
			'toto' => 'a',
		);
		$this->assertTrue(API_Filter::pass($good, $tree));

		$wrong = array(
			'toto' => 'b',
		);
		$this->assertFalse(API_Filter::pass($wrong, $tree));

		$good = array(
			'toto' => array('a'),
		);
		$this->assertTrue(API_Filter::pass($good, $tree));

		$good = array(
			'toto' => array('a', 'b'),
		);
		$this->assertTrue(API_Filter::pass($good, $tree));
	}

	function test_filter() {
		$data = array(
			array(
				'toto' => "a",
			),
			array(
				'toto' => array("a"),
			),
			array(
				'toto' => array("a", "b"),
			),
			array(
				'toto' => array("b"),
			),
			array(
				'toto' => array("c"),
			),
		);

		$filter = array(
			'toto' => "a",
		);
		$filtered = array(
			array(
				'toto' => "a",
			),
			array(
				'toto' => array("a"),
			),
			array(
				'toto' => array("a", "b"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "a,b",
		);
		$filtered = array(
			2 => array(
				'toto' => array("a", "b"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "a|b",
		);
		$filtered = array(
			array(
				'toto' => "a",
			),
			array(
				'toto' => array("a"),
			),
			array(
				'toto' => array("a", "b"),
			),
			array(
				'toto' => array("b"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "a,c",
		);
		$filtered = array();
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "a|c",
		);
		$filtered = array(
			0 => array(
				'toto' => "a",
			),
			1 => array(
				'toto' => array("a"),
			),
			2 => array(
				'toto' => array("a", "b"),
			),
			4 => array(
				'toto' => array("c"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "(a,b)|c",
		);
		$filtered = array(
			2 => array(
				'toto' => array("a", "b"),
			),
			4 => array(
				'toto' => array("c"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "(a|b),c",
		);
		$filtered = array();
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_with_key() {
		$data = array(
			array(
				'toto' => array('a' => "A"),
			),
			array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			array(
				'toto' => array('b' => "B"),
			),
			array(
				'toto' => array('c' => "C"),
			),
		);

		$filter = array(
			'toto' => "{a}",
		);
		$filtered = array(
			array(
				'toto' => array('a' => "A"),
			),
			array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "{a,b}",
		);
		$filtered = array(
			1 => array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "{(a,b|c)}",
		);
		$filtered = array(
			1 => array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_with_apostrophe() {
		$data = array(
			array(
				'toto' => array('a' => "c'est ok"),
			),
		);
		$filter = array(
			'toto' => "c'est ok",
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $data);
	}

	function test_filter_not() {
		$data = array(
			array(
				'toto' => array('a' => "A"),
			),
			array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			array(
				'toto' => array('b' => "B"),
			),
			array(
				'toto' => array('c' => "C"),
			),
		);

		$filter = array(
			'toto' => "~A",
		);
		$filtered = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "~A,B",
		);
		$filtered = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "~A|B",
		);
		$filtered = array(
			1 => array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			2 => array(
				'toto' => array('b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "~A,~B",
		);
		$filtered = array(
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "~A|~B",
		);
		$filtered = array(
			0 => array(
				'toto' => array('a' => "A"),
			),
			2 => array(
				'toto' => array('b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "{~a|~b}",
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}
	function test_filter_only() {
		$data = array(
			array(
				'toto' => array('a' => "A"),
			),
			array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			array(
				'toto' => array('b' => "B"),
			),
			array(
				'toto' => array('c' => "C"),
			),
		);

		$filter = array(
			'toto' => "!B",
		);
		$filtered = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "{!b}",
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "{!a|!b}",
		);
		$filtered = array(
			0 => array(
				'toto' => array('a' => "A"),
			),
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_multi() {
		$data = array(
			array(
				'toto' => array('a' => "A"),
				'titi' => array('b' => "B"),
			),
			array(
				'toto' => array('a' => "A"),
				'titi' => array('c' => "C"),
			),
			array(
				'toto' => array('a' => "A"),
				'titi' => array('b' => ""),
			),
			array(
				'toto' => array('b' => "B"),
				'titi' => array('c' => "C"),
			),
		);

		$filter = array(
			'toto' => "A|B",
			'titi' => "C"
		);
		$filtered = array(
			1 => array(
				'toto' => array('a' => "A"),
				'titi' => array('c' => "C"),
			),
			3 => array(
				'toto' => array('b' => "B"),
				'titi' => array('c' => "C"),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_deep() {
		$data = array(
			array(
				'toto' => array(
					'titi' => array('a' => "A"),
				),
			),
			array(
				'toto' => array(
					'titi' => array('c' => "C"),
				),
			),
			array(
				'toto' => array(
					'titi' => array('b' => "B"),
				),
			),
		);

		$filter = array(
			'toto.titi' => "A|B",
		);
		$filtered = array(
			0 => array(
				'toto' => array(
					'titi' => array('a' => "A"),
				),
			),
			2 => array(
				'toto' => array(
					'titi' => array('b' => "B"),
				),
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_range() {
		$data = array(
			array(
				'toto' => 1,
			),
			array(
				'toto' => 3,
			),
			array(
				'toto' => 4,
			),
			array(
				'toto' => 2,
			),
		);

		$filter = array(
			'toto' => "[1,3]",
		);
		$filtered = array(
			0 => array(
				'toto' => 1,
			),
			1 => array(
				'toto' => 3,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "]1,3[",
		);
		$filtered = array(
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "]1,3]",
		);
		$filtered = array(
			1 => array(
				'toto' => 3,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "[1,3[",
		);
		$filtered = array(
			0 => array(
				'toto' => 1,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "]1.5,2.5[",
		);
		$filtered = array(
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "[2,*]",
		);
		$filtered = array(
			1 => array(
				'toto' => 3,
			),
			2 => array(
				'toto' => 4,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);

		$filter = array(
			'toto' => "[*,2]",
		);
		$filtered = array(
			0 => array(
				'toto' => 1,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$this->assertEqual(API_Filter::filter($data, $filter), $filtered);
	}

	function test_filter_error() {
		$data = array(
			array(
				'toto' => 1,
			),
		);
		$wrong_filter = array(
			'toto' => "something !} wrong",
		);
		try {
			API_Filter::filter($data, $wrong_filter);
		} catch (API_FilterException $e) {
			$this->pass("Caught exception");
		}
	}
# todo show
# todo ~! non seulement
# TODO distribuer les modificateur ~ ! ~!

}
