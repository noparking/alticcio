<?php

require_once('../simpletest/autorun.php');
require_once('../../webservices/ws_message.php');

class TestOfWSMessage extends UnitTestCase {
	
	function test_get_num_voie() {
		$this->assertEqual(WSMessage::get_num_voie("pas de numéro"), null);
		$this->assertEqual(WSMessage::get_num_voie("165 avenue de Bretagne"), 165);
		$this->assertEqual(WSMessage::get_num_voie("9 rue du 8 Mai"), 9);
		$this->assertEqual(WSMessage::get_num_voie("221bis Baker Street"), "221bis");
		$this->assertEqual(WSMessage::get_num_voie("221 bis Baker Street"), "221 bis");
		$this->assertEqual(WSMessage::get_num_voie("221 Bis Baker Street"), "221 Bis");
		$this->assertEqual(WSMessage::get_num_voie("33 ter rue machin"), "33 ter");
	}
	
	function test_get_type_voie() {
		$this->assertEqual(WSMessage::get_type_voie("35 rien du tout"), null);
		
		$this->assertEqual(WSMessage::get_type_voie("35 cours des miracles"), 28);
		$this->assertEqual(WSMessage::get_type_voie("35 cour des miracles"), 28);
		
		$this->assertEqual(WSMessage::get_type_voie("42 grande rue principale"), 29);
		$this->assertEqual(WSMessage::get_type_voie("42 grde rue principale"), 29);
		$this->assertEqual(WSMessage::get_type_voie("42 grd rue principale"), 29);
		
		$this->assertEqual(WSMessage::get_type_voie("42 rue Truc"), 1);
		$this->assertEqual(WSMessage::get_type_voie("42 ru Machin"), 1);
		
		$this->assertEqual(WSMessage::get_type_voie("42 chemin du sud"), 2);
		$this->assertEqual(WSMessage::get_type_voie("42 ch du sud"), 2);
		$this->assertEqual(WSMessage::get_type_voie("42 ch. du sud"), 2);
		$this->assertEqual(WSMessage::get_type_voie("42 chmin du sud"), 2);
		
		$this->assertEqual(WSMessage::get_type_voie("42 route du sud"), 3);
		$this->assertEqual(WSMessage::get_type_voie("42 rte du sud"), 3);
		$this->assertEqual(WSMessage::get_type_voie("42 rt. du sud"), 3);
		$this->assertEqual(WSMessage::get_type_voie("42 rt du sud"), 3);
		$this->assertEqual(WSMessage::get_type_voie("42 rte. du sud"), 3);
		
		$this->assertEqual(WSMessage::get_type_voie("42 avenue "), 4);
		$this->assertEqual(WSMessage::get_type_voie("42 av du sud"), 4);
		$this->assertEqual(WSMessage::get_type_voie("42 ave du sud"), 4);
		$this->assertEqual(WSMessage::get_type_voie("42 av. du sud"), 4);
		$this->assertEqual(WSMessage::get_type_voie("42 ave. du sud"), 4);
		$this->assertEqual(WSMessage::get_type_voie("42 avn du sud"), 4);
		
		$this->assertEqual(WSMessage::get_type_voie("42 street du sud"), 5);
		$this->assertEqual(WSMessage::get_type_voie("42 str du sud"), 5);
		
		$this->assertEqual(WSMessage::get_type_voie("42 allée du sud"), 6);
		$this->assertEqual(WSMessage::get_type_voie("42 allee du sud"), 6);
		$this->assertEqual(WSMessage::get_type_voie("42 alle du sud"), 6);
		$this->assertEqual(WSMessage::get_type_voie("42 alee du sud"), 6);
		$this->assertEqual(WSMessage::get_type_voie("42 all du sud"), 6);
		
		$this->assertEqual(WSMessage::get_type_voie("42 boulevard du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 bd du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 bld du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 blvd du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 bd. du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 bvd. du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 bld. du sud"), 7);
		$this->assertEqual(WSMessage::get_type_voie("42 blvd. du sud"), 7);
		
		$this->assertEqual(WSMessage::get_type_voie("42 impasse du sud"), 11);
		$this->assertEqual(WSMessage::get_type_voie("42 imp du sud"), 11);
		$this->assertEqual(WSMessage::get_type_voie("42 impase du sud"), 11);
		$this->assertEqual(WSMessage::get_type_voie("42 imp. du sud"), 11);
		
		$this->assertEqual(WSMessage::get_type_voie("42 passage du sud"), 14);
		$this->assertEqual(WSMessage::get_type_voie("42 pass du sud"), 14);
		$this->assertEqual(WSMessage::get_type_voie("42 psg du sud"), 14);
		$this->assertEqual(WSMessage::get_type_voie("42 pas du sud"), 14);
		$this->assertEqual(WSMessage::get_type_voie("42 pas. du sud"), 14);
		
		$this->assertEqual(WSMessage::get_type_voie("42 place du sud"), 15);
		$this->assertEqual(WSMessage::get_type_voie("42 pl du sud"), 15);
		$this->assertEqual(WSMessage::get_type_voie("42 pl. du sud"), 15);
		$this->assertEqual(WSMessage::get_type_voie("42 plce du sud"), 15);
		$this->assertEqual(WSMessage::get_type_voie("42 ple du sud"), 15);
		
		$this->assertEqual(WSMessage::get_type_voie("42 rond-point du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rond-pt du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rond pt du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rd pt du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rond point du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rpt du sud"), 17);
		$this->assertEqual(WSMessage::get_type_voie("42 rpt. du sud"), 17);
		
		$this->assertEqual(WSMessage::get_type_voie("42 square du sud"), 18);
		$this->assertEqual(WSMessage::get_type_voie("42 sq du sud"), 18);
		$this->assertEqual(WSMessage::get_type_voie("42 sqre du sud"), 18);
		$this->assertEqual(WSMessage::get_type_voie("42 sq. du sud"), 18);
		
		$this->assertEqual(WSMessage::get_type_voie("42 sentier du sud"), 24);
		
		$this->assertEqual(WSMessage::get_type_voie("42 faubourg du sud"), 25);
		$this->assertEqual(WSMessage::get_type_voie("42 fbg du sud"), 25);
		$this->assertEqual(WSMessage::get_type_voie("42 fbrg du sud"), 25);
		$this->assertEqual(WSMessage::get_type_voie("42 fb. du sud"), 25);
		$this->assertEqual(WSMessage::get_type_voie("42 fbg. du sud"), 25);
		
		$this->assertEqual(WSMessage::get_type_voie("42 quai du sud"), 26);
		$this->assertEqual(WSMessage::get_type_voie("42 q. du sud"), 26);
		
		$this->assertEqual(WSMessage::get_type_voie("42 chaussée du sud"), 27);
		$this->assertEqual(WSMessage::get_type_voie("42 chauss du sud"), 27);
		$this->assertEqual(WSMessage::get_type_voie("42 chaus du sud"), 27);
		$this->assertEqual(WSMessage::get_type_voie("42 chaussee du sud"), 27);
	}
	
	function test_get_nom_voie() {
		$this->assertEqual(WSMessage::get_nom_voie("42 chaussée du sud"), "du sud");
		$this->assertEqual(WSMessage::get_nom_voie("165 avenue de Bretagne"), "de Bretagne");
		$this->assertEqual(WSMessage::get_nom_voie("33 ter rue machin"), "machin");
		$this->assertEqual(WSMessage::get_nom_voie("  33 ter   rue   machin  "), "machin");
		$this->assertEqual(WSMessage::get_nom_voie("42 rue  "), "");
	}
	
	function test_get_bp() {
		$this->assertEqual(WSMessage::get_bp("BP 432"), "BP 432");
		$this->assertEqual(WSMessage::get_bp("Le-Lieu-dit"), "");
	}
	
function test_get_lieu_dit() {
		$this->assertEqual(WSMessage::get_lieu_dit("BP 432"), "");
		$this->assertEqual(WSMessage::get_lieu_dit("Le-Lieu-dit"), "Le-Lieu-dit");
	}
}