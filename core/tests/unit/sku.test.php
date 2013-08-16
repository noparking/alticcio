<?php

require_once dirname(__FILE__)."/../inc/require.inc.php";

$config->core_include("outils/phrase", "produit/sku");

class tests_Sku extends TableTestCase {
	function __construct() {
		parent::__construct();
		$this->resetDatabase();
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
}
