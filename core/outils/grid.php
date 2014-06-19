<?php

class Grid {

	private $internal_vars = array();

	public function __construct($nb, $structure) {
		$this->internal_vars['nb'] = $nb;
		$this->internal_vars['structure'] = $structure;
		foreach ($structure as $element => $space) {
			if (is_numeric($space)) {
				$this->$element = "";
			}
		}
	}

	public function afficher($tab = "") {
		$nb = $this->internal_vars['nb'];
		$structure = array();
		foreach ($this->internal_vars['structure'] as $element) {
			$sub_structure = $this->normalize_structure($element);
			foreach ($sub_structure as $sub_element) {
				$structure[] = $sub_element;
			}
		}

		$html = '<div class="container_'.$nb.'">'."\n";
		$html .= $this->afficher_grid(0, "$tab\t", $structure, $nb); 
		$html .= $tab.'</div>'."\n";
		
		return $html;
	}

	private function normalize_structure($structure) {
		if (is_array($structure)) {
			$normalized = array();
			$normalized[] = array_shift($structure);
			foreach ($structure as $element) {
				$sub_structure = $this->normalize_structure($element);
				foreach ($sub_structure as $sub_element) {
					$normalized[] = $sub_element;
				}
			}
			return array($normalized);
		}
		else {
			if ($structure != "" and $structure[0] == ":") {
				$table = explode(':', ltrim($structure, ":"));
				$label = $table[0]; 
				$space = isset($table[1]) ? $table[1] : null;
			}
			else {
				$label = $structure;
				$space = null;
			}
			if ($space !== null) {
				return array($structure);
			}
			else {
				if (isset($this->$label)) {
					$normalized = array();
					foreach (explode("\n", $this->$label) as $element) {
						$sub_structure = $this->normalize_structure(trim($element));
						foreach ($sub_structure as $sub_element) {
							$normalized[] = $sub_element;
						}
					}
					return $normalized;
				}
				else {
					return array($label);
				}
			}
		}
	}

	private function afficher_grid($level, $tab, $structure, $nb, $default_container = "div", $default_class_container = "") {
		$html = "";
		$count = 0;
		$inline = "";
		foreach ($structure as $grid) {
			$class_container = $default_class_container ? $default_class_container : "";
			$class_element = "";
			if (is_array($grid)) {	
				$table = explode(':', array_shift($grid));
				$space = $table[0];
				$container = isset($table[1]) ? $table[1] : $default_container;
				$element = isset($table[2]) ? $table[2] : "div";
				if (strpos($container, " ") !== false) {
					list($container, $class_container) = explode(" ", $container, 2);
				}
				if (strpos($element, " ") !== false) {
					list($element, $class_element) = explode(" ", $element, 2);
				}
			}
			else {
				if ($grid == "" or $grid[0] != ":") {
					$content = $grid;
					$space = null;
				}
				else {
					$table = explode(':', ltrim($grid, ':'));
					$label = $table[0]; 
					$space = isset($table[1]) ? $table[1] : null;
					$container =  isset($table[2]) ? $table[2] : $default_container;
					if (strpos($container, " ") !== false) {
						list($container, $class_container) = explode(" ", $container, 2);
					}
					if (preg_match("/([^\[]+)\[([^\]]+)\]/", $label, $matches)) {
						$array = $this->$matches[1];
						$item = trim($this->$matches[2]);
						$content = array();
						foreach ($array as $key => $value) {
							$line = $item;
							if (is_array($value)) {
								foreach ($value as $cle => $valeur) {
									$line = str_replace("[$cle]", $valeur, $line);
								}
							}
							else {
								$line = str_replace('[key]', $key, $line);
								$line = str_replace('[value]', $value, $line);
							}
							$content[] = $line;
						}
						if ($space) {
							$spaces = explode("-", $space);
							if (count($spaces) > 1) {
								$space = 0;
								for ($i = 0; $i < count($array); $i++) {
									$space += $spaces[$i % count($spaces)];
								}
							}
							else {
								$spaces = array($space); 
								$space *= count($array);
							}
						}
					}
					else {
						$content = isset($this->$label) ? trim($this->$label) : $label;
					}
				}
			}
				
			if (($space && ($count + $space > $nb)) or (!$space && $count >= $nb)) {
				if ($level == 0) {
					$html .= $tab.'<div class="clear"></div>'."\n";
				}
				$count = 0;
			}

			if ($inline) {
				if (strpos($inline, "</") === 0) {
					$tab = preg_replace("/^\t/", "", $tab);
					$html .= $tab.$inline."\n";
				}
				else if (strpos($inline, "<") === 0) {
					$html .= $tab.$inline."\n";
					if (strpos($inline, "<!--") !== 0 and strpos($inline, "</") === false and strpos($inline, "/>") === false) {
						$tab .= "\t";
					}
				}
				else {
					$html .= $tab.$inline."\n";
				}
				$inline = "";
			}

			if ($space !== null) {
				$alphaomega = "";
				if ($level) {
					if ($count == 0) {
						$alphaomega .= " alpha";
					}
					if ($count + $space >= $nb) {
						$alphaomega .= " omega";
					}
				}
				
				if ($class_container) {
					$class_container = " $class_container";
				}
				if (is_array($grid)) {
					$grid_class = $space ? "grid_$space".$alphaomega : "";
					$grid_class .= $class_container;
					$grid_class = trim($grid_class);
					if ($grid_class) {
						$grid_class = ' class="'.$grid_class.'"';
					}
					$html .= $tab.'<'.$container.$grid_class.'>'."\n";
					$html .= $this->afficher_grid($level + 1, "$tab\t", $grid, $space, $element, $class_element);
					$html .= $tab.'</'.$container.'>'."\n";
				}
				else {
					if (is_array($content)) {
						foreach ($content as $i => $line) {
							$alpha = "";
							$omega = "";
							$piece_of_space = $spaces[$i % count($spaces)];
							$nb2 = $nb / $piece_of_space;
							if (strpos($alphaomega, "alpha") !== false and ($i + $count) % $nb2 == 0) {
								$alpha = " alpha";
							}
							if (strpos($alphaomega, "omega") !== false and ($i + $count) % $nb2 == $nb2 - 1) {
								$omega = " omega";
							}
							$grid_class = $piece_of_space ? "grid_$piece_of_space".$alpha.$omega : "";
							$grid_class .= $class_container;
							$grid_class = trim($grid_class);
							if ($grid_class) {
								$grid_class = ' class="'.$grid_class.'"';
							}
							$html .= $tab.'<'.$container.$grid_class.'>'."\n";
							$html .= "$tab\t".str_replace("\n", "\n$tab\t", $line)."\n";
							$html .= $tab.'</'.$container.'>'."\n";
						}
					}
					else {
						$grid_class = $space ? "grid_$space".$alphaomega : "";
						$grid_class .= $class_container;
						$grid_class = trim($grid_class);
						if ($grid_class) {
							$grid_class = ' class="'.$grid_class.'"';
						}
						$html .= $tab.'<'.$container.$grid_class.'>'."\n";
						$html .= "$tab\t".str_replace("\n", "\n$tab\t", $content)."\n";
						$html .= $tab.'</'.$container.'>'."\n";
					}
				}

				$count += $space;
			}
			else if (isset($content)) {
				$inline = $content;
			}
		}
		if ($inline) {
			if (strpos($inline, "</") === 0) {
				$tab = preg_replace("/^\t/", "", $tab);
				$html .= $tab.$inline."\n";
			}
			else if (strpos($inline, "<") === 0) {
				$html .= $tab.$inline."\n";
				if (strpos($inline, "</") === false and strpos($inline, "/>") === false) {
					$tab .= "\t";
				}
			}
			else {
				$html .= $tab.$inline."\n";
			}
			$inline = "";
		}

		return $html;
	}
}
