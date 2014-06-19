<?php

require_once '../simpletest/autorun.php';
require_once '../../outils/grid.php';

class TestOfGrid extends UnitTestCase {
	function test_basic() {
		$grid = new Grid(3, array(
			':toto:2', ':toto:1',
			':toto:1', ':toto:2',
		));
		$display = <<<HTML
<div class="container_3">
	<div class="grid_2">
		toto
	</div>
	<div class="grid_1">
		toto
	</div>
	<div class="clear"></div>
	<div class="grid_1">
		toto
	</div>
	<div class="grid_2">
		toto
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_substitution() {
		$grid = new Grid(3, array(
			':toto:2', ':titi:1',
			':toto:1', ':titi:2',
		));
		$grid->titi = "toto";
		$display = <<<HTML
<div class="container_3">
	<div class="grid_2">
		toto
	</div>
	<div class="grid_1">
		toto
	</div>
	<div class="clear"></div>
	<div class="grid_1">
		toto
	</div>
	<div class="grid_2">
		toto
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_imbrication() {
		$grid = new Grid(5, array(
			':toto:3', ':toto:2',
			array(5, array(5, ':toto:5')),
			array(3, ':toto:1', ':toto:1', ':toto:1', ':toto:1', ':toto:1', ':toto:1'), ':toto:2'
		));
		$display = <<<HTML
<div class="container_5">
	<div class="grid_3">
		toto
	</div>
	<div class="grid_2">
		toto
	</div>
	<div class="clear"></div>
	<div class="grid_5">
		<div class="grid_5 alpha omega">
			<div class="grid_5 alpha omega">
				toto
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="grid_3">
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_1">
			toto
		</div>
		<div class="grid_1 omega">
			toto
		</div>
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_1">
			toto
		</div>
		<div class="grid_1 omega">
			toto
		</div>
	</div>
	<div class="grid_2">
		toto
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_elements_hors_grille() {
		$grid = new Grid(5, array(
			':toto:3', ':toto:2',
			':toto', ':toto:0',
			':toto:4',
			array(3, ':toto', ':toto:0:section toto', ':toto:1', ':toto:2'),
		));
		$display = <<<HTML
<div class="container_5">
	<div class="grid_3">
		toto
	</div>
	<div class="grid_2">
		toto
	</div>
	<div class="clear"></div>
	toto
	<div>
		toto
	</div>
	<div class="grid_4">
		toto
	</div>
	<div class="clear"></div>
	<div class="grid_3">
		toto
		<section class="toto">
			toto
		</section>
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_2 omega">
			toto
		</div>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_tabulation() {
		$grid = new Grid(3, array(
			':toto:2', ':titi:1',
		));
		$grid->titi = "toto";
		$display = <<<HTML
		<div class="container_3">
			<div class="grid_2">
				toto
			</div>
			<div class="grid_1">
				toto
			</div>
		</div>

HTML;
		$this->assertEqual("\t\t".$grid->afficher("\t\t"), $display);

	}

	function test_tags() {
		$grid = new Grid(3, array(
			array('3:ul:li', ':toto:2', ':toto:1'),
			array('3:section', ':toto:1', ':toto:2'),
			array('3:ul foo:li foo bar', ':toto:2', ':toto:1'),
			array('3:section foo bar', ':toto:1', ':toto:2'),
		));
		$display = <<<HTML
<div class="container_3">
	<ul class="grid_3">
		<li class="grid_2 alpha">
			toto
		</li>
		<li class="grid_1 omega">
			toto
		</li>
	</ul>
	<div class="clear"></div>
	<section class="grid_3">
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_2 omega">
			toto
		</div>
	</section>
	<div class="clear"></div>
	<ul class="grid_3 foo">
		<li class="grid_2 alpha foo bar">
			toto
		</li>
		<li class="grid_1 omega foo bar">
			toto
		</li>
	</ul>
	<div class="clear"></div>
	<section class="grid_3 foo bar">
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_2 omega">
			toto
		</div>
	</section>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_inline() {
		$grid = new Grid(3, array(
			':toto:2', ':toto:1',
			'<div class="toto">',
				':toto:1', ':toto:2',
			'</div>',
			'<div><h1>Titre</h1></div>',
			'<br />',
			'<div style="width:42px;"></div>',
			'blabla',
			'titi',
			'I say : OK',
			'tutu',
		));
		$grid->titi = '<div style="width:42px;"></div>';
		$grid->tutu = <<<HTML
aa:aaa
bb:bbb
cc:ccc
HTML;
		$display = <<<HTML
<div class="container_3">
	<div class="grid_2">
		toto
	</div>
	<div class="grid_1">
		toto
	</div>
	<div class="clear"></div>
	<div class="toto">
		<div class="grid_1">
			toto
		</div>
		<div class="grid_2">
			toto
		</div>
		<div class="clear"></div>
	</div>
	<div><h1>Titre</h1></div>
	<br />
	<div style="width:42px;"></div>
	blabla
	<div style="width:42px;"></div>
	I say : OK
	aa:aaa
	bb:bbb
	cc:ccc
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_change_tags() {
		$grid = new Grid(3, array(
			':toto:2', ':toto:1:p',
			'<ul>',
				':toto:1:li', ':toto:2:li',
			'</ul>',
		));
		$display = <<<HTML
<div class="container_3">
	<div class="grid_2">
		toto
	</div>
	<p class="grid_1">
		toto
	</p>
	<div class="clear"></div>
	<ul>
		<li class="grid_1">
			toto
		</li>
		<li class="grid_2">
			toto
		</li>
		<div class="clear"></div>
	</ul>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_classes() {
		$grid = new Grid(3, array(
			':toto:2:p', ':toto:1:p truc machin',
			'<ul>',
				':toto:1:li bidule', ':toto:2:li',
			'</ul>',
		));
		$display = <<<HTML
<div class="container_3">
	<p class="grid_2">
		toto
	</p>
	<p class="grid_1 truc machin">
		toto
	</p>
	<div class="clear"></div>
	<ul>
		<li class="grid_1 bidule">
			toto
		</li>
		<li class="grid_2">
			toto
		</li>
		<div class="clear"></div>
	</ul>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array() {
		$grid = new Grid(3, array(
			':items[item]:1',
		));
		$grid->items = array('toto', 'titi', 'tata');
		$grid->item = "<span>[key]:[value]</span>";
		$display = <<<HTML
<div class="container_3">
	<div class="grid_1">
		<span>0:toto</span>
	</div>
	<div class="grid_1">
		<span>1:titi</span>
	</div>
	<div class="grid_1">
		<span>2:tata</span>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_variable_array() {
		$grid = new Grid(3, array(
			':items[item]:2-1-3',
		));
		$grid->items = array('toto_0', 'toto_1', 'toto_2', 'toto_3', 'toto_4', 'toto_5', 'toto_6');
		$grid->item = "<span>[key]:[value]</span>";
		// TODO voi pour virer le clear du début a priori inutile
		$display = <<<HTML
<div class="container_3">
	<div class="clear"></div>
	<div class="grid_2">
		<span>0:toto_0</span>
	</div>
	<div class="grid_1">
		<span>1:toto_1</span>
	</div>
	<div class="grid_3">
		<span>2:toto_2</span>
	</div>
	<div class="grid_2">
		<span>3:toto_3</span>
	</div>
	<div class="grid_1">
		<span>4:toto_4</span>
	</div>
	<div class="grid_3">
		<span>5:toto_5</span>
	</div>
	<div class="grid_2">
		<span>6:toto_6</span>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array_imbrication() {
		$grid = new Grid(3, array(
			array(3, ':items3[item]:1'),
			array(3, ':toto:1', ':items2[item]:1'),
			array(3, ':items2[item]:1', ':toto:1'),
		));
		$grid->items2 = array('toto', 'titi');
		$grid->items3 = array('toto', 'titi', 'tata');
		$grid->item = "<span>[key]:[value]</span>";
		$display = <<<HTML
<div class="container_3">
	<div class="grid_3">
		<div class="grid_1 alpha">
			<span>0:toto</span>
		</div>
		<div class="grid_1">
			<span>1:titi</span>
		</div>
		<div class="grid_1 omega">
			<span>2:tata</span>
		</div>
	</div>
	<div class="clear"></div>
	<div class="grid_3">
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_1">
			<span>0:toto</span>
		</div>
		<div class="grid_1 omega">
			<span>1:titi</span>
		</div>
	</div>
	<div class="clear"></div>
	<div class="grid_3">
		<div class="grid_1 alpha">
			<span>0:toto</span>
		</div>
		<div class="grid_1">
			<span>1:titi</span>
		</div>
		<div class="grid_1 omega">
			toto
		</div>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array_plusieurs_alpha_omega() {
		$grid = new Grid(3, array(
			array(3, ':items[item]:1'),
		));
		$grid->items = array('toto', 'titi', 'tata', 'toto', 'titi', 'tata');
		$grid->item = "[value]";
		$display = <<<HTML
<div class="container_3">
	<div class="grid_3">
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_1">
			titi
		</div>
		<div class="grid_1 omega">
			tata
		</div>
		<div class="grid_1 alpha">
			toto
		</div>
		<div class="grid_1">
			titi
		</div>
		<div class="grid_1 omega">
			tata
		</div>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array_plusieurs_alpha_omega_largeur2() {
		$grid = new Grid(4, array(
			array(4, ':items[item]:2'),
		));
		$grid->items = array('toto', 'titi', 'tata', 'toto');
		$grid->item = "[value]";
		$display = <<<HTML
<div class="container_4">
	<div class="grid_4">
		<div class="grid_2 alpha">
			toto
		</div>
		<div class="grid_2 omega">
			titi
		</div>
		<div class="grid_2 alpha">
			tata
		</div>
		<div class="grid_2 omega">
			toto
		</div>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array_of_array() {
		$grid = new Grid(3, array(
			':items[item]:1',
		));
		$grid->items = array(
			array('login' => 'toto', 'password' => 'otot'),
			array('login' => 'titi', 'password' => 'itit'),
			array('login' => 'tata', 'password' => 'atat'),
		);
		$grid->item = "<span>[login]:[password]</span>";
		$display = <<<HTML
<div class="container_3">
	<div class="grid_1">
		<span>toto:otot</span>
	</div>
	<div class="grid_1">
		<span>titi:itit</span>
	</div>
	<div class="grid_1">
		<span>tata:atat</span>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_array_of_array_hors_grille() {
		$grid = new Grid(3, array(
			':table',
		));
		$grid->table = <<<HTML
<div class="truc">
	:items[item]:1
</div>
HTML;
		$grid->items = array(
			array('login' => 'toto', 'password' => 'otot'),
			array('login' => 'titi', 'password' => 'itit'),
			array('login' => 'tata', 'password' => 'atat'),
		);
		$grid->item = "<span>[login]:[password]</span>";
		$display = <<<HTML
<div class="container_3">
	<div class="truc">
		<div class="grid_1">
			<span>toto:otot</span>
		</div>
		<div class="grid_1">
			<span>titi:itit</span>
		</div>
		<div class="grid_1">
			<span>tata:atat</span>
		</div>
		<div class="clear"></div>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

	function test_grid_in_content() {
		$grid = new Grid(3, array(
			':toto',
			array(3, ':toto'),
		));
		$grid->toto = <<<HTML
<ul>
	:titi:1:li
	:tata:1:li
	:tutu:1:li
</ul>
HTML;
		$grid->tata = <<<HTML
<span>tata</span>
HTML;
		$display = <<<HTML
<div class="container_3">
	<ul>
		<li class="grid_1">
			titi
		</li>
		<li class="grid_1">
			<span>tata</span>
		</li>
		<li class="grid_1">
			tutu
		</li>
		<div class="clear"></div>
	</ul>
	<div class="grid_3">
		<ul>
			<li class="grid_1 alpha">
				titi
			</li>
			<li class="grid_1">
				<span>tata</span>
			</li>
			<li class="grid_1 omega">
				tutu
			</li>
		</ul>
	</div>
</div>

HTML;
		$this->assertEqual($grid->afficher(), $display);
	}

}
