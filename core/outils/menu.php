<?php

class Menu { 

	public $data;
	private $currents = array();
	private $current = array();
	private $level;
	private $sql;
	
	public function __construct($sql, $data, $level, $data_edited = array()) {
		$this->data = $this->merge_values($this->flat_values($data), $data_edited);
		$this->level = $level;
		$this->sql = $sql;
	}

	private function flat_values($values) {
		$flat_values = array();
		foreach	($values as $key => $value) {
			$flat_values[$key] = $value;
			if (isset($value['items']) and is_array($value['items'])) {
				foreach ($value['items'] as $key2 => $value2) {
					if (!is_string($value2)) {
						$flat_values[$key]['items'][$key2] = $this->flat_values($value2);
					}
				}
				foreach ($value['items'] as $key2 => $value2) {
					if (is_string($value2)) {
						$flat_values[$key]['items'][$key2] = $flat_values[$value2];
					}
				}
			}
		}

		return $flat_values;
	}

	private function merge_values($values, $values_edited) {
		foreach ($values_edited as $key => $value) {
			if (isset($values[$key])) {
				if (isset($values[$key]) and is_array($values[$key])) {
					$values[$key] = $this->merge_values($values[$key], $value);
				}
				else {
					$values[$key] = $value;
				}
			}
			else {
				$values[$key] = $value;
			}
		}
		return $values;
	}
	
	public function current() {
		foreach (func_get_args() as $path) {
			$items = explode("/", $path);
			$menu = array_shift($items);
			$this->currents[$menu] = $items;
		}
	}
	
	public function get($element, $i = 0) {
		if (isset($this->currents[$element])) {
			$this->current = $this->currents[$element];
		}
		$class = $element;
		$html = '<ul class="menu menu-'.$i.' menu-'.$element.'-'.$i.'" id="menu-'.$element.'">';
		$separator = isset($this->data[$element]['separator']) ? $this->data[$element]['separator'] : null;
		$j = 1;
		foreach ($this->data[$element]['items'] as $id => $item) {
			if ($j == count($this->data[$element]['items'])) {
				$separator = null;
			}
			$html .= $this->get_item($class, $element, $id, $item, $i, $separator);
			$j++;
		}
		$html .= '</ul>';
		return $html;
	}
	
	private function get_item($class, $element, $id, $item, $i, $separator) {

		if (is_array($item) and isset($item['level']) and $item['level'] > $this->level) {
			return "";
		}
		if (is_array($item) and isset($item['actif']) and !$item['actif']) {
			return "";
		}
		$class_current = "";
		if (isset($this->current[$i]) and $this->current[$i] == $id) {
			$class_current = "menu-current";
		}
		$html = '<li class="menu menu-'.$i.' menu-'.$class.'-'.$i.' '.$class_current.'" id="menu-'.$element.'-'.$id.'">';


		if (is_string($item)) {
			$html .= $this->get($item, $i + 1);
		}
		else {
			if (isset($item['label'])) {
				if (isset($item['url'])) {
					$html .= '<a class="menu menu-'.$i.' menu-'.$class.'-'.$i.'" href="'.$item['url'].'">'.$item['label'].'</a>';
				}
				else {
					$html .= '<span class="menu menu-'.$i.' menu-'.$class.'-'.$i.'">'.$item['label'].'</span>';
				}
			}
			if (isset($item['items'])) {
				$element = $element.'-menu-'.$id;
				$html .= '<ul class="menu menu-'.($i + 1).' menu-'.$class.'-'.($i + 1).'" id="menu-'.$element.'">';
				$separator = isset($item['separator']) ? $item['separator'] : null;
				$j = 1;
				foreach ($item['items'] as $id => $item2) {
					if ($j == count($item['items'])) {
						$separator = null;
					}
					$html .= $this->get_item($class, $element, $id, $item2, $i + 1, $separator);
					$j++;
				}
				$html .= '</ul>';
			}
		}
		$html .= '</li>';

		if ($separator !== null) {
			//$html .= '<li class="menu-separator menu-separator-'.$i.' menu-separator-'.$class.'-'.$i.'">'.$separator."</li>";
		}

		return $html;
	}

	function get_groupes() {
		$q = <<<SQL
SELECT id, nom FROM dt_groupes_users
ORDER BY id DESC
SQL;
		$res = $this->sql->query($q);
		$groupes = array();
		while ($row = $this->sql->fetch($res)) {
			$groupes[$row['id']] = $row['nom'];
		}

		return $groupes;
	}

	function get_level($groupe_id) {
		$q = <<<SQL
SELECT id FROM dt_groupes_users
WHERE id <= $groupe_id
ORDER BY id DESC
LIMIT 0, 1
SQL;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		
		return $row['id'];
	}

	function write($data) {
		$menus_edited = var_export($data, true);
		return <<<PHP
<?php

\$menus_edited = $menus_edited;
PHP;
	}

	function can_access($url, $data = null) {
		if ($data === null) {
			$data = $this->data;
		}
		$return = true;
		foreach ($data as $cle => $valeur) {
			if (!isset($valeur['url'])) {
				if (isset($valeur['items']) and is_array($valeur['items'])) {
					if ($this->match($url, $valeur['items'])) {
						$return = $return && $this->can_access($url, $valeur['items']);
						if ((isset($valeur['actif']) and $valeur['actif'] == 0) or (isset($valeur['level']) and $this->level < $valeur['level'])) {
							$return = false;
						}
					}
				}
			}
			else if (strpos($url, $valeur['url']) !== false) {
				if ((isset($valeur['actif']) and $valeur['actif'] == 0) or (isset($valeur['level']) and $this->level < $valeur['level'])) {
					$return = false;
				}
				if (isset($valeur['items']) and is_array($valeur['items']) and !$this->can_access($url, $valeur['items'])) {
					$return = false;
				}
			}
		}

		return $return;
	}

	private function match($url, $data) {
		foreach ($data as $cle => $valeur) {
			if (isset($valeur['url']) and strpos($url, $valeur['url']) !== false) {
				return true;
			}
			else if (isset($valeur['items']) and is_array($valeur['items']) and $this->match($url, $valeur['items'])) {
				return true;
			}
		}
		return false;
	}

	function is_protected($data) {
		if (isset($data['protected']) and $data['protected']) {
			return true;
		}
		if (isset($data['items']) and is_array($data['items']))  {
			foreach ($data['items'] as $item) {
				if ($this->is_protected($item)) {
					return true;
				}
			}
		}

		return false;
	}
}
