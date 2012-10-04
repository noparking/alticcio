<?php

require_once '../simpletest/autorun.php';
require_once '../../outils/url.php';
require_once '../../tribune/tribune2.php';

class TestSituationsReelles extends UnitTestCase {

	function test_1 () {
		$tribune = new Tribune2(array(
			'largeur' => 9500,
			'profondeur' => 10000,
			'plafond' => 4100,
			'plafond_marge' => 2000,
			'rangement_hauteur' => 2000,
			'rangement_marge' => 1,
			'gradin_hauteur' => 320,
			'gradin_profondeur' => 900,
			'siege_largeur' => 500,
			'siege_profondeur' => 500,
			'siege_hauteur' => 450,
			'sieges_par_bloc' => 16,
			'telescopique' => 0,
			'garde_corps_gauche' => 0,
			'garde_corps_droite' => 0,
			'bardage_gauche' => 0,
			'bardage_droite' => 0,
		));
		$this->assertEqual($tribune->nb_gradins(), 6);
		$structure = $tribune->structure();
		$this->assertEqual($structure[1], array(1000, array(15, 500), 1000));
	}

}

class TestOfGradin extends UnitTestCase {

	function test_nb_rangees() {
		$rang = new Gradin(0, array(
			'gradin_profondeur' => 20,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($rang->nb_rangees(), 1);
		
		$rang = new Gradin(1, array(
			'gradin_profondeur' => 20,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($rang->nb_rangees(), 1);

		$rang = new Gradin(1, array(
			'gradin_profondeur' => 20,
			'siege_profondeur' => 10,
			'siege_passage' => 10,
		));
		$this->assertEqual($rang->nb_rangees(), 1);
		
		$rang = new Gradin(1, array(
			'gradin_profondeur' => 30,
			'siege_profondeur' => 10,
			'siege_passage' => 10,
		));
		$this->assertEqual($rang->nb_rangees(), 1);

		$rang = new Gradin(1, array(
			'gradin_profondeur' => 40,
			'siege_profondeur' => 10,
			'siege_passage' => 10,
		));
		$this->assertEqual($rang->nb_rangees(), 2);

		$rang = new Gradin(2, array(
			'gradin_profondeur' => 50,
			'siege_profondeur' => 10,
			'siege_passage' => 10,
		));
		$this->assertEqual($rang->nb_rangees(), 2);

		$rang = new Gradin(3, array(
			'gradin_profondeur' => 60,
			'siege_profondeur' => 10,
			'siege_passage' => 10,
		));
		$this->assertEqual($rang->nb_rangees(), 3);
	}

	function test_nb_degagements() {
		// largeur inférieure à 1 up
		$rang = new Gradin(0, array(
			'largeur' => Gradin::degagement_largeur(1) - 1,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), false);

		// au rang 0, pas de dégagements
		$rang = new Gradin(0, array(
			'largeur' => 1800,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 0);

		// au rang 1, moins d'un bloc
		$rang = new Gradin(1, array(
			'largeur' => 1500,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 1);

		// au rang 1, nombre de siège d'un bloc réparti de chaque côté
		$rang = new Gradin(1, array(
			'largeur' => 2100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 1);

		// au rang 1, un bloc avec dégagement de chaque côté
		$rang = new Gradin(1, array(
			'largeur' => 3000,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 2);

		// au rang 1, un bloc + 1 siège
		$rang = new Gradin(1, array(
			'largeur' => 3100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 2);

		// au rang 1, 2 blocs - 1 siège
		$rang = new Gradin(1, array(
			'largeur' => 3550,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 2);

		// au rang 1, 2 blocs dont 1 réparti de chaque côté
		$rang = new Gradin(1, array(
			'largeur' => 3600,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 2);

		// au rang 1, 2 blocs avec dégagement de chaque côté
		$rang = new Gradin(1, array(
			'largeur' => 5100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 3);

		// au rang 1, 2 blocs + un siège
		$rang = new Gradin(1, array(
			'largeur' => 5110,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_degagements(), 3);
	}

	function test_nb_up() {
		// au rang 0
		$rang = new Gradin(0, array(
			'largeur' => 1000,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		));
		$this->assertEqual($rang->nb_up(), 0);

		// au rang 1, un seul dégagement
		$rang = new Gradin(1, array(
			'largeur' => 1800,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		), 30);
		$this->assertEqual($rang->nb_up(), 1);

		// au rang 1, deux degagements
		$rang = new Gradin(1, array(
			'largeur' => 2800,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 10,
		), 30);
		$this->assertEqual($rang->nb_up(), 2);

		// Règles sur les UP
		// Tribune en salle
		$rang = new Gradin(1, array(
			'largeur' => 10100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 100,
		), 101);
		$this->assertEqual($rang->nb_up(), 2);

		$rang = new Gradin(1, array(
			'largeur' => 20100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 200,
		), 201);
		$this->assertEqual($rang->nb_up(), 3);

		// Tribune en plein air
		$rang = new Gradin(1, array(
			'largeur' => 15000,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 150,
			'emplacement' => "exterieur",
		), 150);
		$this->assertEqual($rang->nb_up(), 1);

		$rang = new Gradin(1, array(
			'largeur' => 15100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 150,
			'emplacement' => "exterieur",
		), 151);
		$this->assertEqual($rang->nb_up(), 2);

		$rang = new Gradin(1, array(
			'largeur' => 300100,
			'siege_largeur' => 100,
			'sieges_par_bloc' => 3000,
			'emplacement' => "exterieur",
		), 3001);
		$this->assertEqual($rang->nb_up(), 21);
	}

	function test_degagements() {
		$rang = new Gradin(1, array(
			'largeur' => 900,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		));
		$this->assertEqual($rang->degagements(), array(1));

		$rang = new Gradin(1, array(
			'largeur' => 1300,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 101);
		$this->assertEqual($rang->degagements(), array(2));

		$rang = new Gradin(1, array(
			'largeur' => 1920,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		));
		$this->assertEqual($rang->degagements(), array(1, 1));

		$rang = new Gradin(1, array(
			'largeur' => 1920,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 201);
		$this->assertEqual($rang->degagements(), array(2, 1));

		$rang = new Gradin(1, array(
			'largeur' => 1920,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 301);
		$this->assertEqual($rang->degagements(), array(2, 2));

		$rang = new Gradin(1, array(
			'largeur' => 2940,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		));
		$this->assertEqual($rang->degagements(), array(1, 1, 1));

		$rang = new Gradin(1, array(
			'largeur' => 2940,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 301);
		$this->assertEqual($rang->degagements(), array(1, 2, 1));

		$rang = new Gradin(1, array(
			'largeur' => 2940,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 401);
		$this->assertEqual($rang->degagements(), array(2, 2, 1));

		$rang = new Gradin(1, array(
			'largeur' => 2940,
			'siege_largeur' => 10,
			'sieges_par_bloc' => 12,
		), 601);
		$this->assertEqual($rang->degagements(), array(2, 2, 2));
	}

	function test_structure_rang_0() {
		// cas limite largeur plus petite que largeur d'un siège
		$rang = new Gradin(0, array(
			'profondeur' => 20,
			'siege_profondeur' => 20,
			'largeur' => 10,
			'siege_largeur' => 20,
		));
		$this->assertEqual($rang->structure(), array());

		// cas limite largeur égale à la largeur d'un siège
		$rang = new Gradin(0, array(
			'profondeur' => 20,
			'siege_profondeur' => 20,
			'largeur' => 20,
			'siege_largeur' => 20,
		));
		$this->assertEqual($rang->structure(), array(array(1, 20)));

		// cas limite largeur égale à la largeur de deux sièges
		$rang = new Gradin(0, array(
			'profondeur' => 20,
			'siege_profondeur' => 20,
			'largeur' => 40,
			'siege_largeur' => 20,
		));
		$this->assertEqual($rang->structure(), array(array(2, 20)));

		// cas limite largeur égale à la largeur de deux sièges plus un petit reste
		$rang = new Gradin(0, array(
			'profondeur' => 20,
			'siege_profondeur' => 20,
			'largeur' => 42,
			'siege_largeur' => 20,
		));
		$this->assertEqual($rang->structure(), array(array(2, 21)));
	}

	function test_structure_rang_1() {
		// cas de moins de sieges qu'un demi bloc
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 960,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(6, 10), 900));

		// cas d'un nombre de sieges entre un demi bloc et un bloc
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 990,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(5, 10), 900, array(4, 10)));

		// cas d'un dégagement un peu plus grand à cause d'un reste
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 985,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 905, array(4, 10)));

		// cas de 2 dégagements
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1920,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(900, array(12, 10), 900));

		// cas de 2 dégagements
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1920,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(900, array(12, 10), 900));

		// cas de 2 dégagements avec petit reste réparti dans les dégagements
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1926,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(903, array(12, 10), 903));

		// cas de 2 dégagements avec plus d'un bloc
		// cas ou le nombre de sièges % 4 = 0
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1960,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(8, 10), 900, array(4, 10)));
		// cas ou le nombre de sièges % 4 = 1
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1970,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(9, 10), 900, array(4, 10)));
		// cas ou le nombre de sièges % 4 = 2
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1980,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(10, 10), 900, array(4, 10)));
		// cas ou le nombre de sièges % 4 = 3
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 1990,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(11, 10), 900, array(4, 10)));
		// cas ou le nombre de sièges % 4 redevient = 0
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2000,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(5, 10), 900, array(10, 10), 900, array(5, 10)));
		// cas limite de 2 blocs (24 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2040,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(6, 10), 900, array(12, 10), 900, array(6, 10)));

		// Cas de trois dégagements
		// cas limite de 2 blocs (24 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2940,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(8, 10), 900, array(8, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 1 (25 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2950,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(9, 10), 900, array(8, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 2 (26 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2960,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(9, 10), 900, array(9, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 3 (27 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2970,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(10, 10), 900, array(9, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 4 (28 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2980,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(10, 10), 900, array(10, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 5 (29 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2990,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(4, 10), 900, array(11, 10), 900, array(10, 10), 900, array(4, 10)));
		// cas nombre de sièges % 6 = 0 (30 sièges)
		$rang = new Gradin(1, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 3000,
			'siege_largeur' => 10,
		));
		$this->assertEqual($rang->structure(), array(array(5, 10), 900, array(10, 10), 900, array(10, 10), 900, array(5, 10)));
	}

	function test_structure_rang_quelconque() {
		// Cas d'un seul dégagement avec tous les sièges d'un côté
		// Pas de changement d'UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 3300,
			'siege_largeur' => 400,
		), 6, array(array(6, 400), 900));
		$this->assertEqual($rang->structure(), array(array(6, 400), 900));
		// Changement d'UP : besoin d'enlever un siège
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 3400,
			'siege_largeur' => 400,
		), 101, array(array(6, 400), 1000));
		$this->assertEqual($rang->structure(), array(array(5, 400), 1400));
		// Changement d'UP : besoin d'enlever deux sièges
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 2700,
			'siege_largeur' => 300,
		), 101, array(array(6, 300), 900));
		$this->assertEqual($rang->structure(), array(array(4, 300), 1500));

		// Cas d'un seul dégagement avec des sièges de chaque côté
		// Pas de changement d'UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 4500,
			'siege_largeur' => 400,
		), 10, array(array(5, 400), 900, array(4, 400)));
		$this->assertEqual($rang->structure(), array(array(5, 400), 900, array(4, 400)));
		// Changement d'UP : besoin d'enlever 1 siège
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 4600,
			'siege_largeur' => 400,
		), 101, array(array(5, 400), 1000, array(4, 400)));
		$this->assertEqual($rang->structure(), array(array(4, 400), 1400, array(4, 400)));

		// Cas de deux dégagements sans sièges sur les côtés
		// Pas de changement d'UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 6600,
			'siege_largeur' => 400,
		), 10, array(900, array(12, 400), 900));
		$this->assertEqual($rang->structure(), array(900, array(12, 400), 900));
		// Changement d'UP : besoin d'enlever un siège
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 7800,
			'siege_largeur' => 500,
		), 201, array(900, array(12, 500), 900));
		$this->assertEqual($rang->structure(), array(1400, array(11, 500), 900));
		
		// Cas de deux dégagements avec sièges sur les côtés
		// Augmentation d'une UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 13800,
			'siege_largeur' => 500,
		), 201, array(array(6, 500), 900, array(12, 500), 900, array(6, 500)));
		$this->assertEqual($rang->structure(), array(array(6, 500), 1400, array(11, 500), 900, array(6, 500)));
		// Augmentation de 2 UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 13800,
			'siege_largeur' => 500,
		), 301, array(array(6, 500), 900, array(12, 500), 900, array(6, 500)));
		$this->assertEqual($rang->structure(), array(array(6, 500), 1400, array(10, 500), 1400, array(6, 500)));
		// Augmentation de 3 UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 13800,
			'siege_largeur' => 500,
		), 401, array(array(6, 500), 900, array(12, 500), 900, array(6, 500)));
		$this->assertEqual($rang->structure(), array(array(6, 500), 1900, array(9, 500), 1400, array(6, 500)));

		// Cas de trois dégagements
		// Augmentation d'une UP
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 14700,
			'siege_largeur' => 500,
		), 301, array(array(4, 500), 900, array(8, 500), 900, array(8, 500), 900, array(4, 500)));
		$this->assertEqual($rang->structure(), array(array(4, 500), 900, array(8, 500), 1400, array(7, 500), 900, array(4, 500)));

		// Augmentation de 2 UPs
		$rang = new Gradin(42, array(
			'profondeur' => 10,
			'siege_profondeur' => 10,
			'sieges_par_bloc' => 12,
			'largeur' => 14700,
			'siege_largeur' => 500,
		), 401, array(array(4, 500), 900, array(8, 500), 900, array(8, 500), 900, array(4, 500)));
		$this->assertEqual($rang->structure(), array(array(4, 500), 1400, array(7, 500), 1400, array(7, 500), 900, array(4, 500)));
	}
}

class TestOfTribune2 extends UnitTestCase {
	
	function test_hauteur_max() {
		// pas de limite de plafond ni de hauteur de rangement
		$tribune = new Tribune2(array(
		));
		$this->assertEqual($tribune->hauteur_max(), false);

		$tribune = new Tribune2(array(
			'plafond' => 0,
			'rangement_hauteur' => 0,
		));
		$this->assertEqual($tribune->hauteur_max(), false);

		// limite de plafond
		$tribune = new Tribune2(array(
			'plafond' => 2000,
			'plafond_marge' => 100,
		));
		$this->assertEqual($tribune->hauteur_max(), 1900);

		// limite de hauteur de rangement
		$tribune = new Tribune2(array(
			'rangement_hauteur' => 1000,
		));
		$this->assertEqual($tribune->hauteur_max(), 1000);

		$tribune = new Tribune2(array(
			'rangement_hauteur' => 1000,
			'rangement_marge_hauteur' => 100,
		));
		$this->assertEqual($tribune->hauteur_max(), 900);

		// limite de plafond et de hauteur de rangement
		$tribune = new Tribune2(array(
			'plafond' => 2000,
			'plafond_marge' => 100,
			'rangement_hauteur' => 1000,
			'rangement_marge_hauteur' => 100,
		));
		$this->assertEqual($tribune->hauteur_max(), 900);

		$tribune = new Tribune2(array(
			'plafond' => 1000,
			'plafond_marge' => 100,
			'rangement_hauteur' => 2000,
			'rangement_marge_hauteur' => 100,
		));
		$this->assertEqual($tribune->hauteur_max(), 900);
	}

	function test_gradin_profondeur() {
		$tribune = new Tribune2(array(
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'siege_type' => "fauteuil_nez",
		));
		$this->assertEqual($tribune->gradin_profondeur(0), 10);
		$this->assertEqual($tribune->gradin_profondeur(1), 40);
		$this->assertEqual($tribune->gradin_profondeur(2), 40);

		$tribune = new Tribune2(array(
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'siege_type' => "fauteuil_sur",
		));
		$this->assertEqual($tribune->gradin_profondeur(0), 20);
		$this->assertEqual($tribune->gradin_profondeur(1), 40);
		$this->assertEqual($tribune->gradin_profondeur(2), 40);

		$tribune = new Tribune2(array(
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'siege_type' => "fauteuil_fond",
		));
		$this->assertEqual($tribune->gradin_profondeur(0), 40);
		$this->assertEqual($tribune->gradin_profondeur(1), 40);
		$this->assertEqual($tribune->gradin_profondeur(2), 40);

		$tribune = new Tribune2(array(
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'siege_type' => "fauteuil_sur",
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->gradin_profondeur(0), 40);
		$this->assertEqual($tribune->gradin_profondeur(1), 40);
		$this->assertEqual($tribune->gradin_profondeur(2), 40);
		
	}

	function test_profondeur_reelle() {
		// Toute la profondeur disponible utilisée
		$tribune = new Tribune2(array(
			'profondeur' => 100,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->profondeur_reelle(), 100);

		// Version téléscopique
		$tribune = new Tribune2(array(
			'profondeur' => 100,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->profondeur_reelle(), 80);

		// Toute la profondeur disponible n'est pas utilisée
		$tribune = new Tribune2(array(
			'profondeur' => 120,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->profondeur_reelle(), 100);

		// En version téléscopique, si
		$tribune = new Tribune2(array(
			'profondeur' => 120,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->profondeur_reelle(), 120);
	}

	function test_nb_gradins_limites_par_profondeur() {
		// cas limite : profondeur plus petite que profondeur d'un siège
		$tribune = new Tribune2(array(
			'profondeur' => 10,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 0);
		
		// cas limite : profondeur égale à la profondeur d'un siège
		$tribune = new Tribune2(array(
			'profondeur' => 20,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 1);

		$tribune = new Tribune2(array(
			'profondeur' => 30,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 1);

		$tribune = new Tribune2(array(
			'profondeur' => 60,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 2);

		// version téléscopique
		$tribune = new Tribune2(array(
			'profondeur' => 60,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->nb_gradins(), 1);

		$tribune = new Tribune2(array(
			'profondeur' => 200,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 5);
		
		// Version téléscopique
		$tribune = new Tribune2(array(
			'profondeur' => 200,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->nb_gradins(), 5);

		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 5);

		// Version téléscopique
		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->nb_gradins(), 4);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 4);

		// Version téléscopique
		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'type' => "telescopique",
		));
		$this->assertEqual($tribune->nb_gradins(), 4);
	}

	function test_nb_gradins_limites_par_hauteur() {
		// hauteur de salle suffisante
		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'gradin_hauteur' => 20,
			'plafond' => 130,
			'plafond_marge' => 30,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 5);

		// hauteur de salle insuffisante : suppression d'un gradin
		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'gradin_hauteur' => 20,
			'plafond' => 120,
			'plafond_marge' => 30,
			'siege_profondeur' => 20,
		));
		$this->assertEqual($tribune->nb_gradins(), 4);
	}

	function test_nb_gradins_limites_par_hauteur_rangement() {
		// hauteur de rangement suffisante
		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'gradin_hauteur' => 20,
			'plafond' => 130,
			'plafond_marge' => 30,
			'siege_profondeur' => 20,
			'rangement_hauteur' => 110,
			'rangement_marge' => 10,
		));
		$this->assertEqual($tribune->nb_gradins(), 5);

		// hauteur de rangement insuffisante
		$tribune = new Tribune2(array(
			'profondeur' => 180,
			'gradin_profondeur' => 40,
			'gradin_hauteur' => 20,
			'plafond' => 130,
			'plafond_marge' => 30,
			'siege_profondeur' => 20,
			'rangement_hauteur' => 105,
			'rangement_marge' => 10,
		));
		$this->assertEqual($tribune->nb_gradins(), 4);
	}

	function test_largeur_exploitable() {
		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => false,
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000);
		
		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => false,
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => true,
			'bardage_droite' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => false,
			'garde_corps_gauche' => true,
			'garde_corps_droite' => false,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 100);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => false,
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*100);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => false,
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'bardage_gauche' => true,
			'bardage_droite' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*100);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'telescopique' => true,
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'fixe',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => false,
			'bardage_droite' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'telescopique',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => false,
			'bardage_droite' => true,
			'bardage_telescopique' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 30*4);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'fixe',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => true,
			'bardage_droite' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'telescopique',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => false,
			'bardage_gauche' => true,
			'bardage_droite' => true,
			'bardage_telescopique' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*30*4);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'telescopique',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => true,
			'garde_corps_telescopique' => true,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 250);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'fixe',
			'garde_corps_gauche' => false,
			'garde_corps_droite' => true,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 100);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'telescopique',
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'garde_corps_telescopique' => true,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*250);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'fixe',
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'bardage_gauche' => false,
			'bardage_droite' => false,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*100);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'telescopique',
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'garde_corps_telescopique' => true,	
			'bardage_gauche' => true,
			'bardage_droite' => true,
			'bardage_telescopique' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*250 - 2*30*4);

		$tribune = new Tribune2(array(
			'profondeur' => 170,
			'gradin_profondeur' => 40,
			'siege_profondeur' => 20,
			'largeur' => 1000,
			'type' => 'fixe',
			'garde_corps_gauche' => true,
			'garde_corps_droite' => true,
			'bardage_gauche' => true,
			'bardage_droite' => true,
		));
		$this->assertEqual($tribune->largeur_exploitable(), 1000 - 2*100);
	}

	function test_nb_degagements_obligatoires() {
		$tribune = new Tribune2(array());
		$this->assertEqual($tribune->nb_degagements_obligatoires(20), 1);
		$this->assertEqual($tribune->nb_degagements_obligatoires(21), 2);
		$this->assertEqual($tribune->nb_degagements_obligatoires(500), 2);
		$this->assertEqual($tribune->nb_degagements_obligatoires(501), 3);
		$this->assertEqual($tribune->nb_degagements_obligatoires(1001), 4);
		$this->assertEqual($tribune->nb_degagements_obligatoires(1501), 5);
		$this->assertEqual($tribune->nb_degagements_obligatoires(2001), 6);
		$this->assertEqual($tribune->nb_degagements_obligatoires(5001), 12);
	}

	function test_ajout_degagements_obligatoires() {
		$tribune = new Tribune2(array(
			'largeur' => 2400,
			'profondeur' => 400,
			'plafond' => 10000,
			'rangement_hauteur' => 10000,
			'gradin_hauteur' => 100,
			'gradin_profondeur' => 200,
			'siege_largeur' => 500,
			'siege_profondeur' => 100,
			'sieges_par_bloc' => 20,
		));
		$structure = $tribune->structure();
		$this->assertEqual($structure[0], array(array(4, 600)));
		$this->assertEqual($structure[1], array(array(3, 500), 900));

		$tribune = new Tribune2(array(
			'largeur' => 2300,
			'profondeur' => 2000,
			'plafond' => 10000,
			'rangement_hauteur' => 10000,
			'gradin_hauteur' => 100,
			'gradin_profondeur' => 200,
			'siege_largeur' => 500,
			'siege_profondeur' => 100,
			'sieges_par_bloc' => 20,
		));
		$structure = $tribune->structure();
		$this->assertEqual($structure[0], array(array(4, 575)));
		$this->assertEqual($structure[1], array(900, array(1, 500), 900));
	}
}

