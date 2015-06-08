<?php

require_once '../simpletest/autorun.php';
require_once '../../database/tools.php';

class TestOfDBTools extends UnitTestCase {

	public function test_tree() {
		$flat_array = array(
			array('id' => 1, 'id_parent' => 0),
			array('id' => 2, 'id_parent' => 0),
			array('id' => 3, 'id_parent' => 1),
			array('id' => 4, 'id_parent' => 3),
			array('id' => 5, 'id_parent' => 3),
		);

		$tree = array(
			array('id' => 1, 'id_parent' => 0, 'children' => array(
				array('id' => 3, 'id_parent' => 1, 'children' => array(
					array('id' => 4, 'id_parent' => 3, 'children' => array(
					)),	
					array('id' => 5, 'id_parent' => 3, 'children' => array(
					)),	
				)),
			)),
			array('id' => 2, 'id_parent' => 0, 'children' => array(	
			)),
		);

		$this->assertEqual(DBTools::tree($flat_array), $tree);
	}
	
	public function test_tree__with_exclude() {
		$flat_array = array(
			array('id' => 1, 'id_parent' => 0),
			array('id' => 2, 'id_parent' => 0),
			array('id' => 3, 'id_parent' => 1),
			array('id' => 4, 'id_parent' => 3),
			array('id' => 5, 'id_parent' => 3),
		);

		$tree = array(
			array('id' => 1, 'id_parent' => 0, 'children' => array(
				array('id' => 3, 'id_parent' => 1, 'children' => array(
					array('id' => 5, 'id_parent' => 3, 'children' => array(
					)),	
				)),
			)),
			array('id' => 2, 'id_parent' => 0, 'children' => array(	
			)),
		);

		$this->assertEqual(DBTools::tree($flat_array, 4), $tree);

		$tree = array(
			array('id' => 1, 'id_parent' => 0, 'children' => array(
			)),
			array('id' => 2, 'id_parent' => 0, 'children' => array(	
			)),
		);

		$this->assertEqual(DBTools::tree($flat_array, 3), $tree);
	}
}
