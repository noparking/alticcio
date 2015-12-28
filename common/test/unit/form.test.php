<?php

require_once('../simpletest/autorun.php');
require_once('../../lib/form.class.php');

class TestHttpUrl extends UnitTestCase {
	function test_defaults() {
		$form = new Form("form-1");
		$form->defaults(array(
			'foo' => "FOO",
			'toto' => array(
				'titi' => "TITI",
				'tata' => "TATA",
			),
		));

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

		$this->assertEqual("TITI", $form->value("toto[titi]"));
		$this->assertEqual(array('titi' => "TITI", 'tata' => "TATA"), $form->value("toto"));
		$this->assertEqual("", $form->value("rien"));
		$this->assertEqual("", $form->value("toto[rien]"));
	}

	function test_set_get_and_reset() {
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
		$form->fields(array(
			'non' => "Non coché",
			'toto[non]' => "Toto non coché",
		));
		$form->defaults(array(
			'foo' => "FOO",
		));
		$form->defaults(array(
			'toto' => array(
				'titi' => "TITI",
				'tata' => "TATA",
			),
		));

		$values = array(
			'non' => false,
			'foo' => "FOOFOO",
			'toto' => array(
				'non' => false,
				'titi' => "TITITITI",
				'tata' => "TATA",
			),
			'bar' => "BAR",
		);
		$this->assertEqual($values, $form->get());

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

		$values = array(
			'non' => false,
			'titi' => "TITI",
			'tata' => "TATA",
		);
		$this->assertEqual($values, $form->val("toto"));
	}

	function test_label() {
		$form = new Form("form-1");
		$form->fields(array(
			'foo' => "Champ Foo",
			'toto[titi]' => array("Champ Toto > Titi"),
		));

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

	function test_checked() {
		$form = new Form("form-1");
		$form->defaults(array(
			'oui' => true,
			'non' => false,
			'toto' => array(
				'oui' => true,
				'non' => false,
			),
		));

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

	function test_selected() {
		$form = new Form("form-1");
		$form->defaults(array(
			'option' => "a",
		));

		$form->control("option");
		$this->assertEqual("selected", $form->selected("a"));
		$this->assertEqual("a", $form->option);
		$this->assertEqual("", $form->selected("b"));
		$this->assertEqual("b", $form->option);
		$this->assertEqual("a", $form->option("a"));
		$this->assertEqual("selected", $form->selected);

		$form = new Form("form-1");
		$form->defaults(array(
			'option' => array("a", "c"),
		));

		$form->control("option");
		$this->assertEqual("selected", $form->selected("a"));
		$this->assertEqual("a", $form->option);
		$this->assertEqual("", $form->selected("b"));
		$this->assertEqual("b", $form->option);
		$this->assertEqual("selected", $form->selected("c"));
		$this->assertEqual("c", $form->option);
	}

	function test_key() {
		$form = new Form("form-1");
		$form->defaults(array(
			'action' => array('save' => "Sauvegarder"),
		));
		
		$this->assertEqual("save", $form->key("action"));

		$form = new Form("form-1");
		$form->defaults(array(
			'action' => array('save' => "Sauvegarder", 'delete' => "Delete"),
		));
		
		$this->assertEqual("save", $form->key("action"));
	}

	function test_check() {
		$form = new Form("form-1");
		$form->defaults(array(
			'nom' => "TOTO",
		));
		$form->set(array(
			'prenom' => "Toto",
			'adresse' => array(
				'rue' => "rue des prés",
			),
		));
		$form->fields(array(
			'nom' => array("Nom", "upcase"),
			'cgv' => array("CGV", "required"),
			'adresse[rue]' => array("Adresse", "required", "rue"),
			'adresse[aussi_facturation]' => array("Adresse", "required"),
		));

		$form->checks(array(
			'required' => function($value) {
				return $value != "";
			},
			'upcase' =>  function($value) {
				return $value == strtoupper($value);
			},
			'downcase' =>  function($value) {
				return $value == strtolower($value);
			},
		));
		$form->checks(array(
			'rue' => function($value) {
				return strpos($value, "rue") === 0;
			}
		));

		$report = $form->validate();
		$this->assertEqual(false, $report['ok']);
		
		$this->assertEqual(true, $report['fields']['nom']['ok']);
		$this->assertEqual(false, $report['fields']['cgv']['ok']);
		$this->assertEqual(false, isset($report['fields']['prenom']));
		$this->assertEqual(true, $report['fields']['adresse[rue]']['ok']);
		$this->assertEqual(false, $report['fields']['adresse[aussi_facturation]']['ok']);

		$ok_fields = array("nom", "adresse[rue]");
		$ko_fields = array("cgv", "adresse[aussi_facturation]");
		$this->assertEqual($ok_fields, $report['ok_fields']);
		$this->assertEqual($ko_fields, $report['ko_fields']);

		$this->assertEqual(false, $report['checks']['required']['ok']);
		$this->assertEqual(true, $report['checks']['upcase']['ok']);
		$this->assertEqual(true, $report['checks']['downcase']['ok']);
		$this->assertEqual(true, $report['checks']['rue']['ok']);

		$ok_checks = array("upcase", "downcase", "rue");
		$ko_checks = array("required");
		$this->assertEqual($ok_checks, $report['ok_checks']);
		$this->assertEqual($ko_checks, $report['ko_checks']);
	}

	function test_check_data() {

	}
}
