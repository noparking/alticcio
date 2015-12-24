<?php

require_once('../simpletest/autorun.php');
require_once('../../lib/form.class.php');

class TestHttpUrl extends UnitTestCase {
	function test_defaults() {
		$form = new Form("form-1");
		$form->defaults = array(
			'foo' => "FOO",
			'toto' => array(
				'titi' => "TITI",
				'tata' => "TATA",
			),
		);

		$form->control("foo");
		$this->assertEqual("FOO", $form->value);
		$this->assertEqual("FOO", $form->value("foo"));

		$form->control("bar");
		$this->assertEqual("", $form->value);
		$this->assertEqual("", $form->value("bar"));

		$form->control("toto[titi]");
		$this->assertEqual("TITI", $form->value);
		$this->assertEqual("TITI", $form->value("toto[titi]"));

		$form->control("toto[tata]");
		$this->assertEqual("TATA", $form->value);
		$this->assertEqual("TATA", $form->value("toto[tata]"));

		$form->control("toto[tutu]");
		$this->assertEqual("", $form->value);
		$this->assertEqual("", $form->value("toto[tutu]"));
	}

	function test_set_and_reset() {
		$form = new Form("form-1");

		$form->set(array(
			'bar' => "BAR",
			'toto' => array(
				'titi' => "TITITITI",
			),
		));
		
		$form->set(array(
			'foo' => "FOOFOO",
		));

		$form = new Form("form-1");
		$form->defaults = array(
			'foo' => "FOO",
			'toto' => array(
				'titi' => "TITI",
				'tata' => "TATA",
			),
		);

		$form->control("foo");
		$this->assertEqual("FOOFOO", $form->value);

		$form->control("bar");
		$this->assertEqual("BAR", $form->value);

		$form->control("toto[titi]");
		$this->assertEqual("TITITITI", $form->value);

		$form->control("toto[tata]");
		$this->assertEqual("TATA", $form->value);

		$form->reset();

		$form->control("foo");
		$this->assertEqual("FOO", $form->value);

		$form->control("bar");
		$this->assertEqual("", $form->value);

		$form->control("toto[titi]");
		$this->assertEqual("TITI", $form->value);

		$form->control("toto[tata]");
		$this->assertEqual("TATA", $form->value);

		$form->control("toto[tutu]");
		$this->assertEqual("", $form->value);
	}

	function test_label() {
		$form = new Form("form-1");
		$form->fields = array(
			'foo' => "Champ Foo",
			'toto[titi]' => array("Champ Toto > Titi"),
		);

		$form->control("foo");
		$this->assertEqual("Champ Foo", $form->label);
		$this->assertEqual("Champ Foo", $form->label("foo"));

		$form->control("toto[titi]");
		$this->assertEqual("Champ Toto > Titi", $form->label);
		$this->assertEqual("Champ Toto > Titi", $form->label("toto[titi]"));

		$form->control("bar");
		$this->assertEqual("", $form->label);
	}

	function test_name() {
		$form = new Form("form-1");

		$form->control("foo");
		$this->assertEqual("foo", $form->name);
		$this->assertEqual("foo", $form->name("foo"));

		$form->control("toto[titi]");
		$this->assertEqual("toto[titi]", $form->name);
		$this->assertEqual("toto[titi]", $form->name("toto[titi]"));
	}

	function test_cheked() {
		$form = new Form("form-1");
		$form->defaults = array(
			'oui' => true,
			'non' => false,
			'toto' => array(
				'oui' => true,
				'non' => false,
			),
		);

		$form->control("oui");
		$this->assertEqual("checked", $form->checked);
		$this->assertEqual("checked", $form->checked("oui"));

		$form->control("non");
		$this->assertEqual("", $form->checked);
		$this->assertEqual("", $form->checked("non"));

		$form->control("toto[oui]");
		$this->assertEqual("checked", $form->checked);
		$this->assertEqual("checked", $form->checked("toto[oui]"));

		$form->control("toto[non]");
		$this->assertEqual("", $form->checked);
		$this->assertEqual("", $form->checked("toto[non]"));
	}
}
