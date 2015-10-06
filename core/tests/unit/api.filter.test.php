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

		$expected = array(
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

		$tree = API_Filter::tree($get);
		$this->assertEqual($tree, $expected);

		$get = array(
  			'toto' => true,
			'toto.titi' => "a", 
			'toto.tata' => "b",
		);

		$expected = array(
			'toto' => array(
				'titi' => "a",
				'tata' => "b",
			),
		);

		$tree = API_Filter::tree($get);
		$this->assertEqual($tree, $expected);
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
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "a,b",
		);
		$expected = array(
			2 => array(
				'toto' => array("a", "b"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "a|b",
		);
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "a,c",
		);
		$expected = array();
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "a|c",
		);
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "(a,b)|c",
		);
		$expected = array(
			2 => array(
				'toto' => array("a", "b"),
			),
			4 => array(
				'toto' => array("c"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "(a|b),c",
		);
		$expected = array();
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$expected = array(
			array(
				'toto' => array('a' => "A"),
			),
			array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "{a,b}",
		);
		$expected = array(
			1 => array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "{(a,b|c)}",
		);
		$expected = array(
			1 => array(
				'toto' => array('a' => "A", 'b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $data);
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
		$expected = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "~A,B",
		);
		$expected = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "~A|B",
		);
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "~A,~B",
		);
		$expected = array(
			3 => array(
				'toto' => array('c' => "C"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "~A|~B",
		);
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "{~a|~b}",
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$expected = array(
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "{!b}",
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "{!a|!b}",
		);
		$expected = array(
			0 => array(
				'toto' => array('a' => "A"),
			),
			2 => array(
				'toto' => array('b' => "B"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$expected = array(
			1 => array(
				'toto' => array('a' => "A"),
				'titi' => array('c' => "C"),
			),
			3 => array(
				'toto' => array('b' => "B"),
				'titi' => array('c' => "C"),
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "]1,3[",
		);
		$expected = array(
			3 => array(
				'toto' => 2,
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "]1,3]",
		);
		$expected = array(
			1 => array(
				'toto' => 3,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "[1,3[",
		);
		$expected = array(
			0 => array(
				'toto' => 1,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "]1.5,2.5[",
		);
		$expected = array(
			3 => array(
				'toto' => 2,
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "[2,*]",
		);
		$expected = array(
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
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);

		$filter = array(
			'toto' => "[*,2]",
		);
		$expected = array(
			0 => array(
				'toto' => 1,
			),
			3 => array(
				'toto' => 2,
			),
		);
		$filtered = API_Filter::filter($data, $filter);
		$this->assertEqual($filtered, $expected);
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

	function test_show_tree() {
		$show = "";
		$expected = array('*' => true);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "*";
		$expected = array('*' => true);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "toto,titi.a";
		$expected = array(
			'toto' => array(
				'*' => true,
			),
			'titi' => array(
				'a' => array(
					'*' => true,
				),
			),
		);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "*,~tata,~titi.b";
		$expected = array(
			'*' => true,
			'tata' => false,
			'titi' => array(
				'b' => false,
			),
		);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "*,~titi";
		$expected = array(
			'*' => true,
			'titi' => false,
		);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "titi,~titi.b";
		$expected = array(
			'titi' => array(
				'*' => true,
				'b' => false,
			),
		);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);

		$show = "toto.tata,~toto.tata.titi.b";
		$expected = array(
			'toto' => array(
				'tata' => array(
					'*' => true,
					'titi' => array(
						'b' => false,
					),
				),
			),
		);
		$show_tree = API_Filter::show_tree($show);
		$this->assertEqual($show_tree, $expected);
	}

	function test_show() {
		$data = array(
			'toto' => 1,
			'tata' => 11,
			'titi' => array('a' => "A", 'b' => "B")
		);

		$expected = $data;

		$show = "";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$show = "*";
		$expected = $data;
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$expected = array(
			'toto' => 1,
			'titi' => array('a' => "A")
		);

		$show = "toto,titi.a";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$show = "*,~tata,~titi.b";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$expected = array(
			'toto' => 1,
			'tata' => 11,
		);

		$show = "*,~titi";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$expected = array(
			'titi' => array('a' => "A")
		);

		$show = "titi,~titi.b";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);

		$data = array(
			'toto' => array(
				'tata' => array(
					'titi' => array('a' => "A", 'b' => "B"),
				),
				'tutu' => "c",
			),
		);

		$expected = array(
			'toto' => array(
				'tata' => array(
					'titi' => array('a' => "A"),
				),
			),
		);

		$show = "toto.tata,~toto.tata.titi.b";
		$shown = API_Filter::show($data, API_Filter::show_tree($show));
		$this->assertEqual($shown, $expected);
	}

# todo ~! non seulement
# TODO distribuer les modificateur ~ ! ~!

}
