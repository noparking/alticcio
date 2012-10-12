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

	function test_save_object() {
		$url_redirection = new UrlRedirectionMock();
		$object = new ObjectMock(42);
		$other_object = new ObjectMock(43);
		
		$url_fields = array('url' => '');

		$data = array(
			'object' => array(
				'nom' => "Ceci est un Test",
				'url' => "mon-url",
			),
		);
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($result, 42);
		$this->assertEqual($object->values['url'], "mon-url");

		// on peut sauvegarder deux fois de suite
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->values['url'], "mon-url");
		// ce n'est pas le même object : sauvegarde impossible
		$result = $url_redirection->save_object($other_object, $data, $url_fields);
		$this->assertEqual($result, false);

		// l'url est vide
		$data = array(
			'object' => array(
				'nom' => "Ceci est un Test",
				'url' => "",
			),
		);
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->values['url'], "");

		// l'url est vide mais déduite du nom
		$url_fields = array('url' => 'nom');
		$data = array(
			'object' => array(
				'nom' => "Ceci est un Test",
				'url' => "",
			),
		);
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->values['url'], "ceci-est-un-test");

		// enregistrement impossible car url déjà réservée
		$data = array(
			'object' => array(
				'nom' => "Ceci est un Test",
				'url' => "mon-url",
			),
		);
		$url_fields = array('url' => 'nom');
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($result, false);
		$this->assertEqual($object->values['url'], "ceci-est-un-test");

		// Url vide. Celle déduite du nom est déjà prise, donc on rajoute "-1" (puis "-2", etc.)
		$data = array(
			'object' => array(
				'nom' => "Ceci est un Test",
				'url' => "",
			),
		);
		$url_fields = array('url' => 'nom');
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->values['url'], "ceci-est-un-test-1");

		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->values['url'], "ceci-est-un-test-2");

		// Url gérée dans les phrases
		$data = array(
			'object' => array(
				'phrase_nom' => 0,
				'phrase_url' => 0,
			),
			'phrases' => array(
				'phrase_nom' => array(
					'fr_FR' => "Nom Français",
					'en_UK' => "English name",
				),
				'phrase_url' => array(
					'fr_FR' => "url-fr",
					'en_UK' => "url-en",
				),
			),
		);
		$url_fields = array('phrase_url' => '');
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->phrases['phrase_url']['fr_FR'], "url-fr");
		$this->assertEqual($object->phrases['phrase_url']['en_UK'], "url-en");
		
		// Url vide gérée dans les phrases
		$data = array(
			'object' => array(
				'phrase_nom' => 0,
				'phrase_url' => 0,
			),
			'phrases' => array(
				'phrase_nom' => array(
					'fr_FR' => "Nom Français",
					'en_UK' => "English name",
				),
				'phrase_url' => array(
					'fr_FR' => "",
					'en_UK' => "",
				),
			),
		);
		$url_fields = array('phrase_url' => '');
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->phrases['phrase_url']['fr_FR'], "");
		$this->assertEqual($object->phrases['phrase_url']['en_UK'], "");

		// Url automatique gérée dans les phrases
		$data = array(
			'object' => array(
				'phrase_nom' => 0,
				'phrase_url' => 0,
			),
			'phrases' => array(
				'phrase_nom' => array(
					'fr_FR' => "Nom Francais",
					'en_UK' => "English name",
				),
				'phrase_url' => array(
					'fr_FR' => "",
					'en_UK' => "",
				),
			),
		);
		$url_fields = array('phrase_url' => 'phrase_nom');
		$result = $url_redirection->save_object($object, $data, $url_fields);
		$this->assertEqual($object->phrases['phrase_url']['fr_FR'], "nom-francais");
		$this->assertEqual($object->phrases['phrase_url']['en_UK'], "english-name");
	}

}

class UrlRedirectionMock extends UrlRedirection {
	public $records = array();
	
	function __construct() {
	}

	function load($code_url) {
		foreach ($this->records as $record) {
			if ($record['code_url'] == $code_url) {
				return $record;
			}
		}
	
		return false;
	}

	function save($code_url, $data) {
		if (!$code_url || !$this->is_free($code_url)) {
			return false;
		}

		$data['code_url'] = $code_url;
		$this->records[] = $data;

		return true;
	}

}

class ObjectMock {
	public $type = "object";
	public $table = "table_object";
	public $values = array('id_langues' => 1);
	public $phrases = array();
	public $id;

	function __construct($id) {
		$this->id = $id;
	}

	function load() {
	}

	function save($data) {
		if (isset($data['object'])) {
			$this->values = array_merge($this->values, $data['object']);
		}
		if (isset($data['phrases'])) {
			$this->phrases = array_merge($this->phrases, $data['phrases']);
		}
		
		return $this->id;
	}

	function get_id_langues($code_langue) {
		$ids = array(
			'fr_FR' => 1,
			'en_UK' => 2,
		);

		return $ids[$code_langue];
	}
}
