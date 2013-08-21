<?php

require_once dirname(__FILE__)."/../inc/require.inc.php";

$config->core_include("outils/phrase", "produit/sku");

class tests_Sku extends TableTestCase {
	function __construct() {
		parent::__construct();
	}

	function setUp() {
		$this->truncateTables("dt_sku", "dt_prix", "dt_prix_degressifs");	
	}

	function test_save() {
		$phrase = new Phrase($this->sql);
		$sku = new SKU($this->sql, $phrase, 1);

		$data = array();
		$data['sku'] = array(
			'ref_ultralog' => "Ref1",
		);

		$id_sku = $sku->save($data);

		$this->assertEqual($id_sku, 1);
		$this->assertRecordExists("dt_sku", array('ref_ultralog' => "Ref1"));

		$sku->save();
		$this->assertTableHasSize("dt_sku", 1);

		$data['sku'] = array(
			'id' => $id_sku,
			'ref_ultralog' => "Ref_1",
		);
		$sku->save($data);
		$this->assertTableHasSize("dt_sku", 1);
		$this->assertRecordExists("dt_sku", array('ref_ultralog' => "Ref_1"));
		$this->assertRecordNotExists("dt_sku", array('ref_ultralog' => "Ref1"));
	}

	function test_save_prix() {
		$phrase = new Phrase($this->sql);
		$sku = new SKU($this->sql, $phrase, 1);

		$data = array();
		$data['sku'] = array(
			'ref_ultralog' => "Ref1",
		);
		$id_sku = $sku->save($data);

		$data['sku']['id'] = $id_sku;
		$data['prix'][0] = array(
			'montant_ht' => 50,
			'franco' => 1,
		);
		$sku->save($data);
		$this->assertRecordExists("dt_prix", array('id_sku' => $id_sku, 'id_catalogues' => 0, 'montant_ht' => 50, 'franco' => 1));

		$sku->duplicate_prix(42);

		$this->assertRecordExists("dt_prix", array('id_sku' => $id_sku, 'id_catalogues' => 42, 'montant_ht' => 50, 'franco' => 1));

		$data['sku']['id'] = $id_sku;
		$data['prix'][42] = array(
			'montant_ht' => 55,
			'franco' => 0,
		);
		$sku->save($data);
		$this->assertRecordExists("dt_prix", array('id_sku' => $id_sku, 'id_catalogues' => 42, 'montant_ht' => 55, 'franco' => 0));
	}

	function test_save_prix_degressifs() {
		$phrase = new Phrase($this->sql);
		$sku = new SKU($this->sql, $phrase, 1);

		$data = array();
		$data['sku'] = array(
			'ref_ultralog' => "Ref1",
		);
		$id_sku = $sku->save($data);

		$data['sku']['id'] = $id_sku;
		$data['prix'][0] = array(
			'montant_ht' => 50.50,
			'franco' => 1,
		);
		$sku->save($data);

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][0] = array(
			'prix' => 40.40,
			'quantite' => 10,
		);
		$sku->add_prix_degressif($data);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 0, 'montant_ht' => 40.40, 'pourcentage' => 20, 'quantite' => 10));

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][0] = array(
			'prix' => "",
			'quantite' => 10,
			'type' => "pourcentage",
			'reduction' => 10,
		);
		$sku->add_prix_degressif($data);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 0, 'montant_ht' => 45.45, 'pourcentage' => 10, 'quantite' => 10));

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][0] = array(
			'prix' => "",
			'quantite' => 10,
			'type' => "montant",
			'reduction' => 25.25,
		);
		$sku->add_prix_degressif($data);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 0, 'montant_ht' => 25.25, 'pourcentage' => 50, 'quantite' => 10));

		$sku->duplicate_prix(42);

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][42] = array(
			'prix' => 40.40,
			'quantite' => 10,
		);
		$sku->add_prix_degressif($data, 42);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 42, 'montant_ht' => 40.40, 'pourcentage' => 20, 'quantite' => 10));

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][42] = array(
			'prix' => "",
			'quantite' => 10,
			'type' => "pourcentage",
			'reduction' => 10,
		);
		$sku->add_prix_degressif($data, 42);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 42, 'montant_ht' => 45.45, 'pourcentage' => 10, 'quantite' => 10));

		$data = array();
		$data['sku']['id'] = $id_sku;
		$data['new_prix_degressif'][42] = array(
			'prix' => "",
			'quantite' => 10,
			'type' => "montant",
			'reduction' => 25.25,
		);
		$sku->add_prix_degressif($data, 42);

		$this->assertRecordExists("dt_prix_degressifs", array('id_sku' => $id_sku, 'id_catalogues' => 42, 'montant_ht' => 25.25, 'pourcentage' => 50, 'quantite' => 10));
	}
}
