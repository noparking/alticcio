<?php

require_once('../simpletest/autorun.php');
require_once('../../outils/dico.php');

class TestOfDico extends UnitTestCase {
	
	var $dir;
	var $dir2;
	
	function __construct() {
		$this->dir = dirname(__FILE__)."/tmp/traductions";
		$this->dir2 = dirname(__FILE__)."/tmp/traductions2";
		mkdir($this->dir);
		mkdir($this->dir2);
		$french = '<?php
$t["HelloWorld"] = "Bonjour le monde";
$t["Test"] = "Ceci est un test";
$t["QueFrancais"] = "Que en français";
?>';
		file_put_contents($this->dir."/fr_FR.php", $french);
		
		$english = '<?php
$t["HelloWorld"] = "Hello world";
$t["Test"] = "This is a test";
$t["QueAnglais"] = "Que en anglais";
?>';
		file_put_contents($this->dir."/en_UK.php", $english);
		
		$french2 = '<?php
$t["HelloWorld"] = "Bonjour le monde !!!";
$t["Test"] = "Ceci est un test";
$t["Test2"] = "Ceci est un second test";
?>';
		file_put_contents($this->dir2."/fr_FR.php", $french2);
		
		parent::__construct();
	}
	
	function __destruct() {
		unlink($this->dir."/fr_FR.php");
		unlink($this->dir."/en_UK.php");
		rmdir($this->dir);
		unlink($this->dir2."/fr_FR.php");
		rmdir($this->dir2);
	}
	
	function testAddEnglish() {
		$dico = new Dico("en_UK");
		$dico->add($this->dir);

		$this->assertEqual($dico->t("HelloWorld"), "Hello world");
		$this->assertEqual($dico->t("Bonjour le monde"), "Hello world");
		$this->assertEqual($dico->t("Test"), "This is a test");
		$this->assertEqual($dico->t("Ceci est un test"), "This is a test");
		$this->assertEqual($dico->t("NonTraduit"), '<span class="untranslated-term">NonTraduit</span>');
		$this->assertEqual($dico->t("QueFrancais"), '<span class="default-term">Que en français</span>');
	}
	
	function testAddEnglishWithCustomTemplates() {
		$dico = new Dico("en_UK");
		$dico->add($this->dir);
		$dico->template_normal = "#{term}*";
		$dico->template_untranslated = "#{term}**";
		$dico->template_default = "#{term}***";
		
		$this->assertEqual($dico->t("HelloWorld"), "Hello world*");
		$this->assertEqual($dico->t("Bonjour le monde"), "Hello world*");
		$this->assertEqual($dico->t("Test"), "This is a test*");
		$this->assertEqual($dico->t("Ceci est un test"), "This is a test*");
		$this->assertEqual($dico->t("NonTraduit"), 'NonTraduit**');
		$this->assertEqual($dico->t("Hello world"), "Hello world**");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français***');
	}
	
	function testAddFrench() {
		$dico = new Dico("fr_FR");
		$dico->add($this->dir);
		
		$this->assertEqual($dico->t("HelloWorld"), "Bonjour le monde");
		$this->assertEqual($dico->t("Bonjour le monde"), "Bonjour le monde");
		$this->assertEqual($dico->t("Test"), "Ceci est un test");
		$this->assertEqual($dico->t("Ceci est un test"), "Ceci est un test");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français');
		$this->assertEqual($dico->t("QueAnglais"), '<span class="untranslated-term">QueAnglais</span>');
	}
	
	function testAddFrenchWithEnglishAsDefault() {
		$dico = new Dico("fr_FR", "en_UK");
		$dico->add($this->dir);
		
		$this->assertEqual($dico->t("HelloWorld"), "Bonjour le monde");
		$this->assertEqual($dico->t("Hello world"), "Bonjour le monde");
		$this->assertEqual($dico->t("Test"), "Ceci est un test");
		$this->assertEqual($dico->t("This is a test"), "Ceci est un test");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français');
		$this->assertEqual($dico->t("QueAnglais"), '<span class="default-term">Que en anglais</span>');
	}
	
	function testAddMultiple() {
		$dico = new Dico("fr_FR");
		$dico->add($this->dir);
		$dico->add($this->dir2);
		
		$this->assertEqual($dico->t("HelloWorld"), "Bonjour le monde !!!");
		$this->assertEqual($dico->t("Test"), "Ceci est un test");
		$this->assertEqual($dico->t("Test2"), "Ceci est un second test");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français');
	}
	
	function testAddfile() {
		$dico = new Dico("fr_FR");
		$dico->addfile($this->dir."/en_UK.php");
		
		$this->assertEqual($dico->t("HelloWorld"), "Hello world");
		$this->assertEqual($dico->t("Bonjour le monde"), '<span class="untranslated-term">Bonjour le monde</span>');
		$this->assertEqual($dico->t("Test"), "This is a test");
		$this->assertEqual($dico->t("QueFrancais"), '<span class="untranslated-term">QueFrancais</span>');
		$this->assertEqual($dico->t("QueAnglais"), 'Que en anglais');
	}
	
	function testAddAndAddfile() {
		$dico = new Dico("fr_FR");
		$dico->add($this->dir);
		$dico->addfile($this->dir."/en_UK.php");
		
		$this->assertEqual($dico->t("HelloWorld"), "Hello world");
		$this->assertEqual($dico->t("Bonjour le monde"), "Hello world");
		$this->assertEqual($dico->t("Test"), "This is a test");
		$this->assertEqual($dico->t("Ceci est un test"), "This is a test");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français');
		$this->assertEqual($dico->t("QueAnglais"), 'Que en anglais');
	}
	
	function testAddfileAndAdd() {
		$dico = new Dico("fr_FR");
		$dico->addfile($this->dir."/en_UK.php");
		$dico->add($this->dir);
		
		$this->assertEqual($dico->t("HelloWorld"), "Bonjour le monde");
		$this->assertEqual($dico->t("Test"), "Ceci est un test");
		$this->assertEqual($dico->t("Ceci est un test"), "Ceci est un test");
		$this->assertEqual($dico->t("QueFrancais"), 'Que en français');
		$this->assertEqual($dico->t("QueAnglais"), 'Que en anglais');
	}
}

?>