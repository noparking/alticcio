<?php

require_once '../simpletest/autorun.php';
require_once '../../outils/menu.php';

class TestOfMenu extends UnitTestCase {
	
	function test_access() {
		$data = array(
			'n1' => array(
				'url' => "url-1/ok",
				'level' => 5,
				'items' => array(
					'n1-n1' => array(
						'level' => 5,
						'url' => "url-1/ok/ok",
					),
					'n1-n2' => array(
						'actif' => 0,
						'level' => 5,
						'url' => "url-1/ok/ko",
					),
				),
			),
			'n2' => array(
				'url' => "url-2/ok",
				'actif' => 1,
				'level' => 4,
			),
			'n3' => array(
				'url' => "url-3/ko",
				'actif' => 1,
				'level' => 6,
			),
			'n4' => array(
				'url' => "url-4/ko",
				'actif' => 0,
				'level' => 5,
				'items' => array(
					'n4-n1'  => array(
						'actif' => 1,
						'level' => 5,
						'url' => "url-4/ko/ok",
					),
				),
			),
			'n5' => array(
				'url' => "url-5/ok",
				'actif' => 1,
			),
			'n6' => array(
				'url' => "url-6/ko",
				'actif' => 0,
			),
			'n6bis' => array(
				'url' => "url-6/ko",
				'actif' => 1,
			),
			'n7' => array(
				'actif' => 0,
				'level' => 5,
				'items' => array(
					'n7-n1'  => array(
						'actif' => 1,
						'level' => 5,
						'url' => "url-7/ko",
					),
				),
			),
		);

		$menu = new Menu(null, $data, 5);
		$this->assertTrue($menu->can_access("url-1/ok"));
		$this->assertTrue($menu->can_access("url-1/ok/un-autre-truc"));
		$this->assertTrue($menu->can_access("url-1/ok/un-autre-truc/encore-un-autre-truc"));
		$this->assertTrue($menu->can_access("url-1/ok/ok"));
		$this->assertFalse($menu->can_access("url-1/ok/ko"));
		$this->assertTrue($menu->can_access("url-2/ok"));
		$this->assertFalse($menu->can_access("url-3/ko"));
		$this->assertFalse($menu->can_access("url-3/ko/un-autre-truc"));
		$this->assertFalse($menu->can_access("url-4/ko"));
		$this->assertFalse($menu->can_access("url-4/ko/ok"));
		$this->assertTrue($menu->can_access("url-5/ok"));
		$this->assertFalse($menu->can_access("url-6/ko"));
		$this->assertFalse($menu->can_access("url-7/ko"));
	}

	function test_protected() {
		$data = array(
			'n1' => array(
				'items' => array(
					'n1-1' => array(
						'protected' => false,
					),
					'n1-2' => array(
						'protected' => false,
					),
				),
			),
			'n2' => array(
				'items' => array(
					'n2-1' => array(
						'protected' => false,
					),
					'n2-2' => array(
						'protected' => true,
					),
				),
			),
		);
		$menu = new Menu(null, $data, 5);
		$this->assertFalse($menu->is_protected($data));
		$this->assertFalse($menu->is_protected($data['n1']));
		$this->assertFalse($menu->is_protected($data['n1']['items']['n1-1']));
		$this->assertTrue($menu->is_protected($data['n2']));
		$this->assertFalse($menu->is_protected($data['n2']['items']['n2-1']));
		$this->assertTrue($menu->is_protected($data['n2']['items']['n2-2']));
	}
}

