<?php

require_once '../simpletest/autorun.php';
require_once '../../outils/url.php';
require_once '../../tribune/tribune.php';

class TestOfTribune extends UnitTestCase {
	function test_parameters() {
		$tribune = new Tribune();
		$default = array(
			'largeur' => 10000,
			'profondeur' => 2000,
			'plafond' => 8000,
			'plafond_marge_hauteur' => 3000,
			'siege_largeur' => 600,
			'siege_profondeur' => 500,
			'siege_hauteur' => 300,
			'siege_largeur_ecart' => 100,
			'sieges_par_bloc' => 8,
			'sieges_nombre_avant_2_degagement' => 50,
			'gradin_hauteur' => 400,
			'gradin_profondeur' => 900,
			'gardecorps_largeur_gauche' => 0,
			'gardecorps_largeur_droite' => 0,
			'gardecorps_hauteur' => 0,
			'bardage_largeur_gauche' => 0,
			'bardage_largeur_droite' => 0,
			'rangement_hauteur' => 0,
			'rangement_marge_hauteur' => 0,
			'repartition_espaces' => 0,
		);

		$this->assertEqual($tribune->parametres(), $default);
	}
	
	function test_preparer_rang__meme_taille_que_le_bloc() {
		$tribune = new Tribune();
		$tribune->largeur = 8400;
		$tribune->profondeur = 4000;
		$tribune->plafond = 8000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->sieges_par_bloc = 10;
		$tribune->gardecorps_largeur_gauche = 0;
		$tribune->gardecorps_largeur_droite = 0;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 4);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 23);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 12);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 11);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur_sans_espaces(0), 7200);
		$this->assertEqual($tribune->rang_largeur(0), 8300);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 12);
		$this->assertEqual($tribune->nombre_de_sieges_a_gauche(1), 1);
		$this->assertEqual($tribune->nombre_de_sieges_a_droite(1), 1);
		$this->assertEqual($tribune->nombre_de_blocs_au_rang(1), 1);
		$this->assertEqual(count($tribune->rangs(1)), 23);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 12);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 9);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(1, 21));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 12);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 9700);
		
		$this->assertTrue($tribune->retirer_siege_au_rang(1));
		$this->assertEqual(count($tribune->rangs(1)), 21);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 11);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 9);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 1);
		$this->assertEqual($tribune->position_des_degagements(1), array(1));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 23);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 8300);
	}

	function test_preparer_rang__plus_petit_que_le_bloc() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 4000;
		$tribune->plafond = 8000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_profondeur_ecart = 400;
		$tribune->sieges_par_bloc = 16;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 4);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur(0), 9700);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual($tribune->nombre_de_blocs_au_rang(1), 0);
		$this->assertEqual(count($tribune->rangs(1)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 12);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 1);
		$this->assertEqual($tribune->position_des_degagements(1), array(13));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 28);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 10400);
	}
	
	function test_preparer_rang__avec_bardage() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 2000;
		$tribune->plafond = 8000;
		$tribune->plafond_marge_hauteur = 3000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_hauteur = 400;
		$tribune->gradin_hauteur = 400;
		$tribune->bardage_largeur_gauche = 100;
		$tribune->bardage_largeur_droite = 150;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 2);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 29);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 12);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->nombre_de_gardecorps_au_rang(0), 0);
		$this->assertEqual($tribune->nombre_de_bardages_au_rang(0), 2);
		$this->assertEqual($tribune->nombre_de_passagesbardage_au_rang(0), 2);
		$this->assertEqual($tribune->rang_largeur(0), 9500);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 29);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 13);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 10);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->nombre_de_gardecorps_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_de_bardages_au_rang(1), 2);
		$this->assertEqual($tribune->nombre_de_passagesbardage_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(7, 23));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 12);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 10900);

		$this->assertTrue($tribune->affiner_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 25);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 11);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 8);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->nombre_de_gardecorps_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_de_bardages_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(5, 21));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 11);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 9500);
	}
	
	function test_preparer_rang__avec_garde_corps() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 2000;
		$tribune->plafond = 8000;
		$tribune->plafond_marge_hauteur = 3000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_hauteur = 400;
		$tribune->gradin_hauteur = 400;
		$tribune->gardecorps_largeur_gauche = 200;
		$tribune->gardecorps_largeur_droite = 300;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 2);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 12);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur(0), 9500);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 13);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 10);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->nombre_de_gardecorps_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(6, 22));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 12);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 10900);

		$this->assertTrue($tribune->affiner_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 23);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 11);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 8);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->nombre_de_gardecorps_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(4, 20));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 11);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 9500);
	}

	function test_preparer_rang__avec_rangement_pour_un_rang_uniquement() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 4000;
		$tribune->plafond = 40000;
		$tribune->plafond_marge_hauteur = 3000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_hauteur = 400;
		$tribune->gradin_hauteur = 400;
		$tribune->rangement_hauteur = 1000;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 1);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur(0), 9700);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 0);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 0);
		$this->assertEqual($tribune->position_des_degagements(1), array());
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 0);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 0);
		$this->assertEqual($tribune->rang_largeur(1), 0);
		$this->assertTrue($tribune->affiner_rang_normal(1));
	}

	function test_preparer_rang__avec_plafond_pour_un_rang_uniquement() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 4000;
		$tribune->plafond = 4000;
		$tribune->plafond_marge_hauteur = 3000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_hauteur = 400;
		$tribune->gradin_hauteur = 400;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 1);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur(0), 9700);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 0);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 0);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 0);
		$this->assertEqual($tribune->position_des_degagements(1), array());
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 0);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 0);
		$this->assertEqual($tribune->rang_largeur(1), 0);
		$this->assertTrue($tribune->affiner_rang_normal(1));
	}
	
	function test_preparer_rang() {
		$tribune = new Tribune();
		$tribune->largeur = 10000;
		$tribune->profondeur = 4000;
		$tribune->plafond = 8000;
		$tribune->siege_largeur = 600;
		$tribune->siege_profondeur = 500;
		$tribune->siege_largeur_ecart = 100;
		$tribune->siege_profondeur_ecart = 400;
		
		$this->assertEqual($tribune->nombre_de_rangs(), 4);
		
		$this->assertTrue($tribune->preparer_rang(0));
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
		$this->assertEqual($tribune->rang_largeur(0), 9700);
		
		$this->assertTrue($tribune->preparer_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 11);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(5, 21));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 14);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 11100);
		
		$this->assertTrue($tribune->affiner_rang_normal(1));
		$this->assertEqual(count($tribune->rangs(1)), 23);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(1), 12);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(1), 9);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(1), 2);
		$this->assertEqual($tribune->position_des_degagements(1), array(3, 19));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(1), 13);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(1), 1);
		$this->assertEqual($tribune->rang_largeur(1), 9700);

		$this->assertTrue($tribune->preparer_rang_normal(2));
		$this->assertEqual(count($tribune->rangs(2)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(2), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(2), 11);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(2), 2);
		$this->assertEqual($tribune->position_des_degagements(2), array(5, 21));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(2), 20);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(2), 1);
		$this->assertEqual($tribune->rang_largeur(2), 11100);
		
		$this->assertTrue($tribune->affiner_rang_normal(2));
		$this->assertEqual(count($tribune->rangs(2)), 23);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(2), 12);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(2), 9);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(2), 2);
		$this->assertEqual($tribune->position_des_degagements(2), array(3, 19));
		$this->assertEqual($tribune->nombre_d_effectif_par_degagement(2), 19);
		$this->assertEqual($tribune->nombre_d_up_par_degagement(2), 1);
		$this->assertEqual($tribune->rang_largeur(2), 9700);
	}
	
	function test_preparer_dernier_rang__avec_plafond_bas() {
		$tribune = new Tribune();
		$tribune->plafond = 1;
		$this->assertTrue($tribune->preparer_dernier_rang());
		$this->assertEqual(count($tribune->rangs(0)), 0);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 0);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 0);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
	}
	
	function test_preparer_dernier_rang() {
		$tribune = new Tribune();
		$this->assertTrue($tribune->preparer_dernier_rang());
		$this->assertEqual(count($tribune->rangs(0)), 27);
		$this->assertEqual($tribune->nombre_de_sieges_au_rang(0), 14);
		$this->assertEqual($tribune->nombre_d_espaces_au_rang(0), 13);
		$this->assertEqual($tribune->nombre_de_degagements_au_rang(0), 0);
	}
	
	function test_degagement_largeur() {
		$tribune = new Tribune();
		$this->assertEqual($tribune->degagement_largeur(-1), false);
		$this->assertEqual($tribune->degagement_largeur(1), 800);
		$this->assertEqual($tribune->degagement_largeur(2), 1200);
		$this->assertEqual($tribune->degagement_largeur(3), 1600);
		$this->assertEqual($tribune->degagement_largeur(4), 2200);
	}
	
	function test_up() {
		$tribune = new Tribune();
		$this->assertEqual($tribune->up(-10), false);
		$this->assertEqual($tribune->up(0), false);
		$this->assertEqual($tribune->up(10), 1);
		$this->assertEqual($tribune->up(50), 1);
		$this->assertEqual($tribune->up(51), 1);
		$this->assertEqual($tribune->up(100), 1);
		$this->assertEqual($tribune->up(101), 2);
		$this->assertEqual($tribune->up(200), 2);
		$this->assertEqual($tribune->up(201), 3);
		$this->assertEqual($tribune->up(300), 3);
		$this->assertEqual($tribune->up(301), 4);
		$this->assertEqual($tribune->up(400), 4);
		$this->assertEqual($tribune->up(401), 5);
		$this->assertEqual($tribune->up(600), 5);
		$this->assertEqual($tribune->up(601), 6);
		$this->assertEqual($tribune->up(700), 6);
		$this->assertEqual($tribune->up(701), 7);
		$this->assertEqual($tribune->up(800), 7);
		$this->assertEqual($tribune->up(801), 8);
		$this->assertEqual($tribune->up(900), 8);
		$this->assertEqual($tribune->up(901), 9);
		$this->assertEqual($tribune->up(1000), 9);
		$this->assertEqual($tribune->up(1001), 10);
		$this->assertEqual($tribune->up(1100), 10);
		$this->assertEqual($tribune->up(1101), 11);
		$this->assertEqual($tribune->up(1200), 11);
		$this->assertEqual($tribune->up(1201), 12);
		$this->assertEqual($tribune->up(1300), 12);
		$this->assertEqual($tribune->up(5501), 55);
		$this->assertEqual($tribune->up(5590), 55);
		$this->assertEqual($tribune->up(5600), 55);
	}
	
	function test_x__y() {
		$tribune = new Tribune();
		$this->assertEqual($tribune->x(), 10000);
		$this->assertEqual($tribune->y(), 2000);
	}
	
	function test_nombre_de_rangs__profondeur_entre_les_sieges() {
		$tribune = new Tribune();
		$tribune->profondeur = 1;
		$this->assertEqual($tribune->nombre_de_rangs(), 0);
		$this->assertEqual($tribune->profondeur_entre_les_sieges(), 0);
		
		$tribune->profondeur = 500;
		$this->assertEqual($tribune->nombre_de_rangs(), 1);
		$this->assertEqual($tribune->profondeur_entre_les_sieges(), 0);
		
		$tribune->profondeur = 1000;
		$this->assertEqual($tribune->nombre_de_rangs(), 1);
		$this->assertEqual($tribune->profondeur_entre_les_sieges(), 0);
		
		$tribune->profondeur = 1500;
		$this->assertEqual($tribune->nombre_de_rangs(), 2);
		$this->assertEqual($tribune->profondeur_entre_les_sieges(), 500);

		$tribune->profondeur = 3000;
		$this->assertEqual($tribune->nombre_de_rangs(), 3);
		$this->assertEqual($tribune->profondeur_entre_les_sieges(), 750);
	}
}
