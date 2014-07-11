<?php

require_once('../simpletest/autorun.php');
require_once('../../outils/form.php');

class TestOfForm extends UnitTestCase {

	function test_is_submitted() {
		$form = new Form();
		$this->assertFalse($form->is_submitted());

		$_POST['form-id'] = "my-form";
		$form = new Form(array('id' => "my-form"));
		$this->assertTrue($form->is_submitted());

		$form->reset();
	}

	function test_reset() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
		);

		$form = new Form(array('id' => "my-form"));
		$form->reset();

		$this->assertEqual($form->values(), array());
	}

	function test_session() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertEqual($form->value('foo'), "bar");
		
		$_POST = array(
			'form-id' => "my-form",
			'toto' => "titi",
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertEqual($form->value('foo'), "bar");
		$this->assertEqual($form->value('toto'), "titi");

		$form->reset();
	}
	
	function test_unregistered() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'toto' => array(
				'nom' => "TOTO",
				'age' => 42,
			),
		);
	
		$form = new Form(array('id' => "my-form", 'unregistered' => array('foo', 'toto[age]')));
	
		$this->assertEqual($form->value('foo'), "bar");
		$this->assertEqual($form->value('toto[nom]'), "TOTO");
		$this->assertEqual($form->value('toto[age]'), 42);
	
		$_POST = array(
			'form-id' => "my-form",
		);
	
		$form = new Form(array('id' => "my-form", 'unregistered' => array('foo', 'toto[age]')));
	
		$this->assertEqual($form->value('foo'), "");
		$this->assertEqual($form->value('toto[nom]'), "TOTO");
		$this->assertEqual($form->value('toto[age]'), "");
	
		$form->reset();
	}

	function test_unregistered_steps() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'toto' => "titi",
		);
	
		$form = new Form(array('id' => "my-form", 'unregistered' => array('foo'), 'steps' => 2));
	
		$this->assertEqual($form->value('foo'), "bar");
		$this->assertEqual($form->value('toto'), "titi");

		$form->next();

		$_POST = array(
			'form-id' => "my-form",
		);
	
		$form = new Form(array('id' => "my-form", 'unregistered' => array('foo'), 'steps' => 2));
		
		$this->assertEqual($form->value('foo'), "");
		$this->assertEqual($form->value('toto'), "titi");

		$form->reset();
	}

	function test_values() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k2' => 112,
					),
					'j2' => 12,
				),
				'i2' => 2,
			),
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertNull($form->value('rien'));
		$this->assertEqual($form->value('foo'), "bar");
		$this->assertEqual($form->value('tab[i1][j1][k1]'), 111);
		$this->assertEqual($form->value('tab[i1][j1][k2]'), 112);
		$this->assertEqual($form->value('tab[i1][j2]'), 12);
		$this->assertEqual($form->value('tab[i2]'), 2);
		$this->assertEqual($form->value('tab[i1][j1]'), array('k1' => 111, 'k2' => 112));
		$this->assertEqual($form->value('tab[i1]'), array('j1' => array('k1' => 111, 'k2' => 112), 'j2' => 12));
		$this->assertEqual($form->value('tab'), array('i1' => array('j1' => array('k1' => 111, 'k2' => 112), 'j2' => 12), 'i2' => 2));
		$this->assertEqual($form->values(), $_POST);

		$form->reset();
	}

	function test_default_values() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k2' => 112,
					),
				),
			),
		);

		$form = new Form(array('id' => "my-form"));

		$form->default_values = array(
			'foo' => "foo",
			'bar' => "bar",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k2' => 0,
					),
				),
			),
		);

		$this->assertNull($form->value('rien'));
		$this->assertEqual($form->value('foo'), "bar");
		$this->assertEqual($form->value('tab[i1][j1][k1]'), 111);
		$this->assertEqual($form->value('tab[i1][j1][k2]'), 112);
		$this->assertEqual($form->value('tab[i1][j1]'), array('k1' => 111, 'k2' => 112));
		$this->assertEqual($form->values(), $_POST);

		$form->reset();
	}

	function test_checkboxes_values() {
		$_POST = array(
			'form-id' => "my-form",
			'box-on' => 1,
			'boxes' => array(
				'on' => 1,
			),
			'checkboxes' => array('box-on', 'box-off', 'boxes[on]', 'boxes[off]', 'empty[0]', 'empty[1]'),
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertEqual($form->value('box-on'), 1);
		$this->assertEqual($form->value('box-off'), 0);
		$this->assertEqual($form->value('boxes'), array('on' => 1, 'off' => 0));
		$this->assertEqual($form->value('empty'), array(0 => 0, 1 => 0));

		$form->reset();
	}

	function test_value_with_two_forms() {
		$_POST = array(
			'form-id' => "form1",
			'foo' => "bar",
		);

		$form1 = new Form(array('id' => "form1"));
		$form2 = new Form(array('id' => "form2"));

		$this->assertEqual($form1->value('foo'), "bar");
		$this->assertNull($form2->value('foo'));

		$form1->reset();
		$form2->reset();
	}

	function test_changed() {
		$form = new Form(array('id' => "my-form"));

		$this->assertFalse($form->changed());

		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
		);

		$form = new Form(array('id' => "my-form"));
		$this->assertTrue($form->changed());

		$form->reset();
		$this->assertFalse($form->changed());
	}

	function test_set_value() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'toto' => array('titi' => array('tata' => 42)),
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertEqual($form->value('foo'), "bar");

		$form->set_value('foo', "baz");
		$this->assertEqual($form->value('foo'), "baz");

		$this->assertEqual($form->value('toto[titi][tata]'), 42);

		$form->set_value('toto[titi][tata]', 51);
		$this->assertEqual($form->value('toto[titi][tata]'), 51);

		$form->reset();
	}

	function test_forget_value() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k2' => 112,
					),
					'j2' => 12,
				),
				'i2' => 2,
			),
			'toto' => array(
				'nom' => "TOTO",
				'age' => 42,
			),
		);

		$form = new Form(array('id' => "my-form"));

		$this->assertEqual($form->value('foo'), "bar");
		$form->forget_value('foo');
		$this->assertNull($form->value('foo'));

		$this->assertEqual($form->value('tab[i1][j1][k1]'), 111);
		$form->forget_value('tab[i1][j1][k1]');
		$this->assertNull($form->value('tab[i1][j1][k1]'));
		$this->assertEqual($form->value('tab[i1][j1]'), array('k1' => null, 'k2' => 112));

		$form->forget_value('tab');
		$this->assertNull($form->value('tab'));

		$form->forget_value('toto[age]');
		$this->assertEqual($form->value('toto[nom]'), "TOTO");
		$this->assertEqual($form->value('toto[age]'), null);

		$form->reset();
	}

	function test_action_and_action_arg() {
		$_POST = array(
			'form-id' => "my-form",
		);

		$form = new Form(array('id' => "my-form"));
		$this->assertEqual($form->action(), "");

		$_POST['delete'] = "Supprimer";
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'delete');
		$this->assertEqual($form->action_arg(), null);

		$_POST['delete'] = array(42 => "Supprimer");
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'delete');
		$this->assertEqual($form->action_arg(), 42);

		$_POST['delete'] = array(42 => "Supprimer");
		$_POST['action'] = "add";
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'add');
		$this->assertEqual($form->action_arg(), null);

		$_POST['delete'] = array(42 => "Supprimer");
		$_POST['action'] = "add[plop]";
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'add');
		$this->assertEqual($form->action_arg(), "plop");

		$_POST['delete'] = array(42 => "Supprimer");
		$_POST['action'] = array('add' => "plop");
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'add');
		$this->assertEqual($form->action_arg(), "plop");

		$_POST['delete'] = array(42 => "Supprimer");
		$_POST['action'] = array('add' => array("plop" => 42));
		$form = new Form(array('id' => "my-form", 'actions' => array('save', 'delete')));
		$this->assertEqual($form->action(), 'add');
		$this->assertEqual($form->action_arg(), "plop");

		$form->reset();
	}

	function test_is_valid() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'foo2' => "bar",
			'foo3' => "baz",
			'required' => "foo,plop",
			'good_email' => "titi@toto.com",
			'bad_email' => "azeaz.sqdqs@ppp",
			'good_length' => "azerty",
			'too_short' => "aze",
			'too_long' => "azertyui",
		);

		$form = new Form(array(
			'id' => "my-form",
			'required' => array('foo', 'plop'),
			'confirm' => array('foo' => 'foo2', 'foo2' => 'foo3'), 
			'validate' => array(
				'good_email' => array('validate_email'),
				'bad_email' => array('validate_email'),
				'good_length' => array('validate_length', 5, 7),
				'too_short' => array('validate_length', 5, 7),
				'too_long' => array('validate_length', 5, 7),
			),
		));

		$this->assertTrue($form->is_valid('foo'));
		$this->assertTrue($form->is_valid('plop'));

		$form->validate();

		$this->assertTrue($form->is_valid('foo'));
		$this->assertFalse($form->is_valid('plop'));

		$this->assertFalse($form->is_valid('foo2'));
		
		$this->assertTrue($form->is_valid('good_email'));
		$this->assertFalse($form->is_valid('bad_email'));

		$this->assertTrue($form->is_valid('good_length'));
		$this->assertFalse($form->is_valid('too_short'));
		$this->assertFalse($form->is_valid('too_long'));

		$form->reset();
	}

	function test_is_valid_post_array() {
		$_POST = array(
			'form-id' => "my-form",
			'a1' => array(
				'a2' => array(
					'foo' => "bar",
					'foo2' => "bar",
					'foo3' => "baz",
					'good_email' => "titi@toto.com",
					'bad_email' => "azeaz.sqdqs@ppp",
					'good_length' => "azerty",
					'too_short' => "aze",
					'too_long' => "azertyui",
				),
			),
			'required' => "a1[a2][foo],a1[a2][plop]",
		);

		$form = new Form(array(
			'id' => "my-form",
			'required' => array('a1[a2][foo]', 'a1[a2][plop]'),
			'confirm' => array('a1[a2][foo]' => 'a1[a2][foo2]', 'a1[a2][foo2]' => 'a1[a2][foo3]'), 
			'validate' => array(
				'a1[a2][good_email]' => array('validate_email'),
				'a1[a2][bad_email]' => array('validate_email'),
				'a1[a2][good_length]' => array('validate_length', 5, 7),
				'a1[a2][too_short]' => array('validate_length', 5, 7),
				'a1[a2][too_long]' => array('validate_length', 5, 7),
			),
		));

		$this->assertTrue($form->is_valid('a1[a2][foo]'));
		$this->assertTrue($form->is_valid('a1[a2][plop]'));

		$form->validate();

		$this->assertTrue($form->is_valid('a1[a2][foo]'));
		$this->assertFalse($form->is_valid('a1[a2][plop]'));

		$this->assertFalse($form->is_valid('a1[a2][foo2]'));
		
		$this->assertTrue($form->is_valid('a1[a2][good_email]'));
		$this->assertFalse($form->is_valid('a1[a2][bad_email]'));

		$this->assertTrue($form->is_valid('a1[a2][good_length]'));
		$this->assertFalse($form->is_valid('a1[a2][too_short]'));
		$this->assertFalse($form->is_valid('a1[a2][too_long]'));

		$form->reset();
	}

	function test_invalid_field() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
		);
			
		$form = new Form(array(
			'id' => "my-form",
		));

		$this->assertTrue($form->is_valid('foo'));

		$form->invalid_field('foo');
		$this->assertFalse($form->is_valid('foo'));

		$form->reset();
	}

	function test_next_previous_step() {
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		
		$this->assertEqual($form->step(), 1);
		$this->assertFalse($form->previous());
		$this->assertEqual($form->step(), 1);

		$this->assertTrue($form->next());
		$this->assertEqual($form->step(), 2);

		$this->assertTrue($form->next());
		$this->assertEqual($form->step(), 3);

		$this->assertTrue($form->previous());
		$this->assertEqual($form->step(), 2);

		$this->assertTrue($form->next());
		$this->assertTrue($form->next());
		$this->assertFalse($form->next());
		$this->assertEqual($form->step(), 4);

		$form->reset();
	}

	function test_next_or_previous() {
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		$form->next_or_previous();
		$this->assertEqual($form->step(), 1);

		$_POST = array(
			'form-id' => "my-form",
			'action' => "next",
			'step' => 1,
		);
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		$form->next_or_previous();
		$this->assertEqual($form->step(), 2);

		$_POST = array(
			'form-id' => "my-form",
			'action' => "next",
			'step' => 1,
		);
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		$form->next_or_previous();
		$this->assertEqual($form->step(), 2);

		$_POST = array(
			'form-id' => "my-form",
			'action' => "next",
			'step' => 2,
		);
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		$form->next_or_previous();
		$this->assertEqual($form->step(), 3);

		$_POST = array(
			'form-id' => "my-form",
			'action' => "previous",
			'step' => 3,
		);
		$form = new Form(array(
			'id' => "my-form",
			'steps' => 3,
		));
		$form->next_or_previous();
		$this->assertEqual($form->step(), 2);
	}

	function test_escape_values() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "test avec l'apostrophe",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => "aaa\"bbb",
						'k2' => "112'0",
					),
					'j2' => "aaa\\bbb",
				),
				'i2' => "aaa'bbb\"ccc",
			),
		);

		$form = new Form(array(
			'id' => "my-form",
		));

		$values = $form->escape_values();
		$this->assertEqual($values['foo'], "test avec l\\'apostrophe");

		$this->assertEqual($values['tab']['i1']['j1']['k1'], "aaa\\\"bbb");
		$this->assertEqual($values['tab']['i1']['j2'], "aaa\\\\bbb");
		$this->assertEqual($values['tab']['i2'], "aaa\\'bbb\\\"ccc");

		$form->reset();
	}

	function test_hidden() {
		$form = new Form(array(
			'id' => "my-form",
		));

		$hidden = $form->hidden(array('name' => "foo"));
		$this->assertNotEqual($hidden, "");

		$form->input(array('name' => "foo"));
		
		$hidden = $form->hidden(array('name' => "foo"));
		$this->assertEqual($hidden, "");

		$hidden = $form->hidden(array('name' => "bar"));
		$this->assertNotEqual($hidden, "");
	}

	function test_merge_values() {
		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k2' => 112,
					),
					'j2' => 12,
				),
				'i2' => 2,
			),
		);

		$form = new Form(array('id' => "my-form"));

		$_POST = array(
			'form-id' => "my-form",
			'foo' => "bar2",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k3' => 113,
					),
					'j3' => 130,
				),
				'i3' => 3,
			),
		);

		$form = new Form(array('id' => "my-form"));

		$values = $form->values();
		$expected_values = array(
			'form-id' => "my-form",
			'foo' => "bar2",
			'tab' => array(
				'i1' => array(
					'j1' => array(
						'k1' => 111,
						'k2' => 112,
						'k3' => 113,
					),
					'j2' => 12,
					'j3' => 130,
				),
				'i2' => 2,
				'i3' => 3,
			),
		);

		$this->assertEqual($values, $expected_values);

		$form->reset();
	}

	function test_permissions() {
		// pas de permissions
		$form = new Form(array(
			'id' => "my-form",
		));
		$params = array(
			'name' => "save",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		// permissions pour sauvegarder un objet
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "save",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		$params = array(
			'name' => "delete",
		);
		$this->assertFalse($form->is_permitted("submit", $params));

		// permissions pour sauvegarder n'importe quoi
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "save",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		$params = array(
			'name' => "delete",
		);
		$this->assertFalse($form->is_permitted("submit", $params));

		// permissions de faire n'importe quoi sur les objets
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('all objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "save",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		$params = array(
			'name' => "delete",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		// permissions de faire n'importe quoi sur n'importe quoi
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('all'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "save",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		$params = array(
			'name' => "delete",
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		// permissions forcée pour une action
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "reset",
			'permitted' => true,
		);
		$this->assertTrue($form->is_permitted("submit", $params));

		// permissions ou non de sauvegarder un champ texte
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertTrue($form->is_permitted("text", $params));

		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array(),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertFalse($form->is_permitted("text", $params));

		// permissions ou non de sauvegarder un champ textarea
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertTrue($form->is_permitted("textarea", $params));

		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array(),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertFalse($form->is_permitted("textarea", $params));

		// permissions ou non de sauvegarder un champ select
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('save objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertTrue($form->is_permitted("select", $params));

		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array(),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "nom",
		);
		$this->assertFalse($form->is_permitted("select", $params));

		// permissions ou non de traduire un champ texte
		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('translate objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "phrases[phrase_nom]",
		);
		$this->assertTrue($form->is_permitted("text", $params));

		$params = array(
			'name' => "prix",
		);
		$this->assertFalse($form->is_permitted("text", $params));

		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('translate other_objet'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "phrases[phrase_nom]",
		);
		$this->assertFalse($form->is_permitted("text", $params));

		$form = new Form(array(
			'id' => "my-form",
			'permissions' => array('translate all'),
			'permissions_object' => "objet", 
		));

		$params = array(
			'name' => "phrases[phrase_nom]",
		);
		$this->assertTrue($form->is_permitted("text", $params));
	}

	function test_trim() {
		$_POST = array(
		'form-id' => "my-form",
			'a' => array(
				3 => "  aze  ",
				4 => "  qsd  ",
				),
			42 => "  qsdqsd  ",
		);
		$form = new Form(array('id' => "my-form"));
		$values = $form->values();
		$this->assertEqual($values['a'][3], "aze");
		$this->assertEqual($values['a'][4], "qsd");
		$this->assertEqual($values[42], "qsdqsd");

		$form = new Form(array('id' => "my-form", 'trim' => false));
		$values = $form->values();
		$this->assertEqual($values['a'][3], "  aze  ");
		$this->assertEqual($values['a'][4], "  qsd  ");
		$this->assertEqual($values[42], "  qsdqsd  ");

		$form = new Form(array('id' => "my-form", 'trim' => true));
		$values = $form->values();
		$this->assertEqual($values['a'][3], "aze");
		$this->assertEqual($values['a'][4], "qsd");
		$this->assertEqual($values[42], "qsdqsd");
	}

	function test_flat() {
		$form = new Form(array('id' => "my-form"));

		$array = array(
			'toto' => 1,
			'titi' => 2,
			'foo' => 'bar',
		);

		$this->assertEqual($form->flat($array), $array);

		$array = array(
			'toto' => array(
				'titi' => 1,
				'tata' => 2,
			),
			'foo' => 'bar',
		);

		$flat_array= array(
			'toto[titi]' => 1,
			'toto[tata]' => 2,
			'foo' => "bar",
		);
	
		$this->assertEqual($form->flat($array), $flat_array);
		$this->assertEqual($form->flat($flat_array), $flat_array);

		$array = array(
			'toto' => array(
				'titi' => array(
					'tata' => 1,
					'tutu' => 2
				),
				'foo' => 'bar'),
			'foo' => 'bar',
		);

		$flat_array= array(
			'toto[titi][tata]' => 1,
			'toto[titi][tutu]' => 2,
			'toto[foo]' => 'bar',
			'foo' => "bar",
		);
	
		$this->assertEqual($form->flat($array), $flat_array);
		$this->assertEqual($form->flat($flat_array), $flat_array);

		$array = array(
			'toto' => array(
				'titi' => array(
					'tata' => array(
						'truc' => 1,
					),
					'tutu' => 2
				),
				'foo' => 'bar'),
			'foo' => 'bar',
		);

		$flat_array= array(
			'toto[titi][tata][truc]' => 1,
			'toto[titi][tutu]' => 2,
			'toto[foo]' => 'bar',
			'foo' => "bar",
		);
	
		$this->assertEqual($form->flat($array), $flat_array);
		$this->assertEqual($form->flat($flat_array), $flat_array);
	}
}
