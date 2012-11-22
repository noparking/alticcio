<?php

class Filter {
	
	private $sql; // $sql can be a pager
	private $filter;
	private $elements;
	private $name;
	private $total;
	private $rows = array();
	private $items = array();
	private $selected = array();
	private $inverted = 0;
	private $md5 = "";
	private $inputs = array();

	public function __construct($sql, $elements, $selected = array(), $name = "filter", $selected_only = null) {
		$this->sql = $sql;
		$this->name = $name;
		$this->elements = array();
		if (isset($_POST[$this->name])) {
			$filter = $_POST[$this->name];
			$_SESSION['filters'][$this->name] = $filter;
		}
		else {
			if (isset($_SESSION['filters'][$this->name])) {
				$filter = $_SESSION['filters'][$this->name];
			}
			else {
				$filter = array();
			}
			if ((count($selected) and $selected_only !== false) or $selected_only === true) {
				$filter['selection'] = 1;
			}
		}
		
		if (isset($filter['inverted'])) {
			$this->inverted = $filter['inverted'];
		}
		
		if (isset($filter['total'])) {
			$this->total = $filter['total'];
		}
		
		if (isset($filter['md5'])) {
			$this->md5 = $filter['md5'];
		}
		else {
			// on initialise la sélection que si le filter n'a pas été soumis
			$this->selected = $selected;
		}
		
		if ($this->action('selectall')) {
			$this->inverted = 1;
			$filter['values'] = array();
			foreach (explode(",", $filter['items']) as $item) {
				$filter['selected'][$item] = 1;
			}
			$filter['pselected'] = "";
		}
		
		if ($this->action('unselectall')) {
			$this->inverted = 0;
			$filter['values'] = array();
			foreach (explode(",", $filter['items']) as $item) {
				unset($filter['selected'][$item]);
			}
			$filter['pselected'] = "";
		}
		
		if ($this->action('invertselect')) {
			$this->inverted = $this->inverted ? 0 : 1;
		}
		
		if (isset($filter['pselected']) and $filter['pselected']) {
			$this->selected = array();
			foreach (explode(",", $filter['pselected']) as $id) {
				$items = explode(",", $filter['items']);
				if ($this->inverted) {
					if (!in_array($id, $items) or !isset($filter['selected'][$id])) {
						$this->selected[] = $id;
					}
				}
				else {
					if (!in_array($id, $items) or isset($filter['selected'][$id])) {
						$this->selected[] = $id;
					}
				}
			}
		}

		if ($this->inverted) {
			if (isset($filter['items'])) {
				$items = explode(",", $filter['items']);
				foreach ($items as $item) {
					if (!isset($filter['selected'][$item]) and !in_array($item, $this->selected)) {
						$this->selected[] = $item;
					}
				}
			}
		}
		else {
			if (isset($filter['selected']) and is_array($filter['selected'])) {
				foreach ($filter['selected'] as $id => $value) {
					if (!in_array($id, $this->selected)) {
						$this->selected[] = $id;
					}
				}
			}
		}

		// On a traité la sélection avant le reset car le reset n'affecte pas la sélection
		if ($this->action('reset')) {
			$filter = array();
			$_SESSION['filters'][$this->name] = array();
			if (method_exists($sql, "reset")) {
				$sql->reset();
			}
		}

		foreach ($elements as $cle => $element) {
			$element['id'] = $cle;
			if (!isset($element['field'])) {
				$element['field'] = "`".$cle."`";
			}
			$this->elements[$cle] = $element;
			if (!isset($filter['sort']['column']) and isset($element['order'])) {
				$filter['sort']['column'] = $cle;
				$filter['sort']['order'] = $element['order'];
			}
		}
		
		$this->filter = $filter;
		
		if ($this->has_changed()) {
			if (method_exists($sql, "reset")) {
				$sql->reset();
			}
		}
	}
	
	public function action($action) {
		$filter = isset($_POST[$this->name]) ? $_POST[$this->name] : array();
		return isset($filter['action'][$action]) or (isset($filter['action']['action']) and $filter['action']['action'] == $action);
	}
	
	public function elements() {
		return $this->elements;
	}
	
	public function selected() {
		return $this->selected;
	}

	public function select($items) {
		$this->selected = $items;
	}

	public function total() {
		return $this->total;
	}
	
	public function sort() {
		return isset($this->filter['sort']) ? $this->filter['sort'] : array('column' => "", 'order' => "");
	}
	
	public function selectcount() {
		if ($this->inverted) {
			$count_selected = $this->total() - count($this->selected());
		}
		else {
			$count_selected = count($this->selected());
		}
		return <<<HTML
<span class="filter-selectcount">$count_selected</span>
HTML;
	}
	
	public function actionbutton($action, $label) {
		return <<<HTML
<input type="submit" name="{$this->name}[action][{$action}]" class="filter-action" value="{$label}" />
HTML;
	}
	
	public function actionlink($action, $label) {
		return <<<HTML
<a href="#" class="filter-action" id="{$this->name}-action-$action">$label</a>
HTML;
	}
	
	public function actionselect($action, $label, $options) {
		$options_html = '<option value=""></option>';
		foreach ($options as $value => $option) {
			$options_html .= '<option value="'.$value.'">'.$option.'</option>';
		}
		return <<<HTML
<select class="filter-action" name="{$this->name}[action][{$action}]">
$options_html
</select>
<input type="submit" class="filter-action" value="{$label}" />
HTML;
	}
	
	public function selection($selection) {
		$html = '<select name="'.$this->name.'[selection]" class="filter-in">';
		foreach ($selection as $value => $label) {
			$html .= '<option value="'.$value.'"';
			if (isset($this->filter['selection']) and $this->filter['selection'] == $value) {
				$html .= ' selected="selected"';
			}
			$html .= ">$label</option>";
		}
		$html .= "</select>";
		
		return $html;
	}
	
	public function field($element) {
		$value = isset($this->filter['values'][$element['id']]) ? $this->filter['values'][$element['id']] : "";

		if (!isset($element['type'])) {
			$element['type'] = "equal";
		}
		switch ($element['type']) {
			case "equal" :
			case "contain" :
				return <<<HTML
<input name="{$this->name}[values][{$element['id']}]" type="text" class="filter-element filter-{$element['type']}" value="{$value}" />
HTML;
				break;
			case "between" :
				$start = isset($value['start']) ? $value['start'] : "";
				$end = isset($value['end']) ? $value['end'] : "";
				$html = '<span class="filter-element filter-'.$element['type'].'">';
				$html .= '[<input name="'.$this->name.'[values]['.$element['id'].'][start]" type="text" class="filter-element filter-'.$element['type'].'" value="'.$start.'" />';
				$html .= '-<input name="'.$this->name.'[values]['.$element['id'].'][end]" type="text" class="filter-element filter-'.$element['type'].'" value="'.$end.'" />]';
				$html .= '</span>';
				return $html;
				break;
			case "select" :
				$options = "";
				foreach ($element['options'] AS $id => $label) {
					$selected = ($value !== "" and $value == $id) ? 'selected="selected"' : "";
					$options .= '<option value="'.$id.'" '.$selected.'>'.$label.'</option>';
				}
				return <<<HTML
<select name="{$this->name}[values][{$element['id']}]" class="filter-element filter-select">
<option value=""></option>
{$options}
</select>
HTML;
				break;
			case "date" :
			case "date_from" :
			case "date_to" :
				$id = "{$this->name}[values][{$element['id']}]";
				$format = isset($element['format']) ? $element['format'] : "d/m/Y"; 
				$date = $value ? date($format, $value) : "";
				return <<<HTML
<input type="hidden" name="{$id}" id="{$id}" value="{$value}" />
<input name="" id="{$id}-visible" value="{$date}" class="date-input filter-date" />
HTML;
				break;
			case "date_between" :
				$start = isset($value['start']) ? $value['start'] : "";
				$end = isset($value['end']) ? $value['end'] : "";
				$id = "{$this->name}[values][{$element['id']}]";
				$format = isset($element['format']) ? $element['format'] : "d/m/Y"; 
				$date_start = $start ? date($format, $start) : "";
				$date_end = $end ? date($format, $end) : "";
				return <<<HTML
<span class="filter-element filter-{$element['type']}">
<input type="hidden" name="{$id}[start]}" id="{$id}[start]" value="{$start}" />
<input type="hidden" name="{$id}[end]}" id="{$id}[end]" value="{$end}" />
[<input name="" id="{$id}[start]-visible" type="text" class="date-input filter-date" value="{$date_start}" />-<input name="" id="{$id}[end]-visible" type="text" class="date-input filter-date" value="{$date_end}" />]
</span>
HTML;
				break;
		}
	}
	
	public function selectbox($id, $enabled = true) {
		
		$checked = "";
		if ((in_array($id, $this->selected()) and !$this->inverted) or (!in_array($id, $this->selected()) and $this->inverted)) {
			$checked = 'checked="checked"';
		}
		$this->items[] = $id;

		if ($enabled) {
			return <<<HTML
<input class="filter-selected" name="{$this->name}[selected][{$id}]" type="checkbox" value="1" $checked />
HTML;
		}
		else {
			$value = $checked ? 1 : 0;
			return <<<HTML
<input name="{$this->name}[selected][{$id}]" type="hidden" value="{$value}" />
<input class="filter-selected" name="{$this->name}[selected-disabled][{$id}]" type="checkbox" disabled="disabled" value="1" $checked />
HTML;
		}
	}
	
	public function allpagebox($enabled = true) {
		$disabled = $enabled ? '' : ' disabled="disabled"';
		return <<<HTML
<input class="filter-allpage" name="{$this->name}[allpage]" type="checkbox" value="1" {$disabled} />
HTML;
	}
	
	public function column($element) {
		$class = "filter-column";
		$sort = $this->sort();
		if ($sort['column'] == $element['id']) {
			$class .= " filter-sorted-column";
			if ($sort['order'] == "DESC") {
				$class .= " filter-sorted-column-desc";
			}
			else {
				$class .= " filter-sorted-column-asc";
			}
		}
		return <<<HTML
<a class="$class" id="{$this->name}-column-{$element['id']}" href="#">{$element['title']}</a>
HTML;
	}

	public function value_or_input($form, $cle, $valeur, $replacements) {
		if (isset($this->elements[$cle]['form'])) {
			$params = array();
			foreach ($this->elements[$cle]['form'] as $key => $value) {
				if ($key == "method") {
					$method = $value;
				}
				else {
					foreach ($replacements as $search => $replace) {
						$params[$key] = str_replace($search, $replace, $value);
					}
				}
			}
			return $form->$method($params);
		}
		else {
			return $valeur;
		}
	}

	public function value($cle, $valeur) {
		if (isset($this->elements[$cle]['options'][$valeur])) {
			$valeur = $this->elements[$cle]['options'][$valeur];
		}
		if (isset($this->elements[$cle]['type']) and substr($this->elements[$cle]['type'], 0, 4) == "date") {
			$format = isset($this->elements[$cle]['format']) ? $this->elements[$cle]['format'] : "d/m/Y";
			$valeur = date($format, $valeur);
		}
		
		return $valeur;
	}
	
	public function query($q) {
		$filter = $this->filter;
		
		$where = "";
		$having = "";
		if (isset($filter['selection'])) {
			$elements = $this->elements();
			$element = array_shift($elements);
			$field = $element['field'];
			if ($filter['selection'] == 1) {
				if ($this->inverted) {
					if (count($this->selected)) {
						$where .= " AND $field NOT IN (".implode(",", $this->selected).")";
					}
				}
				else {
					if (count($this->selected)) {
						$where .= " AND $field IN (".implode(",", $this->selected).")";
					}
					else {
						$where .= " AND 0";
					}
				}
			}
			if ($filter['selection'] == -1) {
				if ($this->inverted) {
					if (count($this->selected)) {
						$where .= " AND $field IN (".implode(",", $this->selected).")";
					}
					else {
						$where .= " AND 0";
					}
				}
				else {
					if (count($this->selected)) {
						$where .= " AND $field NOT IN (".implode(",", $this->selected).")";
					}
				}
			}
		}
		
		if (isset($filter['values'])) {
			foreach ($filter['values'] as $cle => $valeur) {
				if ($valeur !== "") {
					$cond = "";
					if (!isset($this->elements[$cle]['type']) or in_array($this->elements[$cle]['type'], array('equal', 'select'))) {
						$valeur = mysql_real_escape_string($valeur);
						$cond .= " AND {$this->elements[$cle]['field']} = '$valeur'";
					}
					else if ($this->elements[$cle]['type'] == 'contain') {
						$valeur = mysql_real_escape_string($valeur);
						$cond .= " AND {$this->elements[$cle]['field']} LIKE '%$valeur%'";
					}
					else if ($this->elements[$cle]['type'] == 'between') {
						if ($valeur['start']) {
							$start = mysql_real_escape_string($valeur['start']);
							$cond .= " AND {$this->elements[$cle]['field']} >= '{$start}'";
						}
						if ($valeur['end']) {
							$end = mysql_real_escape_string($valeur['end']);
							$cond .= " AND {$this->elements[$cle]['field']} <= '{$end}'";
						}
					}
					else if ($this->elements[$cle]['type'] == 'date') {
						$valeur = mysql_real_escape_string($valeur);
						$cond .= " AND {$this->elements[$cle]['field']} = {$valeur}";
					}
					else if ($this->elements[$cle]['type'] == 'date_from') {
						$valeur = mysql_real_escape_string($valeur);
						$cond .= " AND {$this->elements[$cle]['field']} >= {$valeur}";
					}
					else if ($this->elements[$cle]['type'] == 'date_to') {
						$valeur = mysql_real_escape_string($valeur);
						$cond .= " AND {$this->elements[$cle]['field']} <= {$valeur}";
					}
					else if($this->elements[$cle]['type'] == 'date_between') {
						if ($valeur['start']) {
							$start = mysql_real_escape_string($valeur['start']);
							$cond .= " AND {$this->elements[$cle]['field']} >= {$start}";
						}
						if ($valeur['end']) {
							$end = mysql_real_escape_string($valeur['end']);
							$cond .= " AND {$this->elements[$cle]['field']} <= {$end}";
						}
					}
					if (isset($this->elements[$cle]['group']) and $this->elements[$cle]['group']) {
						$having .= $cond;
					}
					else {
						$where .= $cond;
					}
				}
			}
		}

		foreach ($this->elements() as $element) {
			if (isset($element['where_in'])) {
				$where .= " AND {$element['field']} IN ('".implode("','", $element['where_in'])."')";
			}
		}
		
		if ($where) {
			if (preg_match("/\Wwhere\W/i", $q)) {
				$q = preg_replace("/(\Wwhere\W[^\n]*)(\n|$)/i", '$1'.$where.'$2', $q);
			}
			else {
				$q .= " WHERE 1".$where;
			}
		}

		$group_by = array();
		foreach ($this->elements() as $element) {
			if (isset($element['group_by']) and $element['group_by']) {
				$group_by[] = $element['field'];
			}
		}
		if (count($group_by)) {
			if (preg_match("/\Wgroup by\W/i", $q)) {
				$q = preg_replace("/(\Wgroup by\W[^\n]*)(\n|$)/i", '$1, '.implode(", ", $group_by).'$2', $q);
			}
			else {
				$q .= " GROUP BY ".implode(", ", $group_by);
			}
		}
		if ($having) {
			if (preg_match("/\Whaving\W/i", $q)) {
				$q = preg_replace("/(\Whaving\W[^\n]*)(\n|$)/i", '$1'.$having.'$2', $q);
			}
			else {
				$q .= " HAVING 1".$having;
			}
		}
		
		$sort = $this->sort();
		if ($sort['column'] and $sort['order']) {
			$elements = $this->elements();
			$q .= " ORDER BY {$elements[$sort['column']]['field']} {$sort['order']}";
		}
		
		if (!preg_match("/^SELECT SQL_CALC_FOUND_ROWS/i", $q)) {
			$q = preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS", $q);
		}
		$res = $this->sql->query($q);

		if (!isset($this->total)) {
			$this->total = $this->sql->found_rows();
		}
		
		return $res;
	}
	
	public function fetch($res) {
		if ($row = $this->sql->fetch($res)) {
			$ordered_row = array();
			foreach ($this->elements as $key => $element) {
				$ordered_row[$key] = $row[$key];
			}
			$this->add($ordered_row);
			
			return $ordered_row;
		}
		else {
			return false;
		}
	}
	
	public function found_rows() {
		return $this->total;
	}
	
	public function fetchall($res) {
		while ($this->fetch($res)) {}
	}
	
	public function add($row) {
		$this->rows[] = $row;
	}
	
	public function rows() {
		return $this->rows;
	}
	
	public function hidden() {
		$item_list = implode(",", $this->items);
		$selected = implode(",", $this->selected);
		$sort = $this->sort();
		return <<<HTML
		<input type="hidden" name="{$this->name}[pselected]" value="{$selected}" />
		<input type="hidden" name="{$this->name}[inverted]" value="{$this->inverted}" />
		<input type="hidden" name="{$this->name}[items]" value="{$item_list}" />
		<input type="hidden" name="{$this->name}[md5]" value="{$this->md5()}" />
		<input type="hidden" name="{$this->name}[action][action]" id="{$this->name}-action-action" value="" />
		<input type="hidden" name="{$this->name}[total]" id="{$this->name}-total" value="{$this->total()}" />
		<input type="hidden" name="{$this->name}[sort][column]" id="{$this->name}-sort-column" value="{$sort['column']}" />
		<input type="hidden" name="{$this->name}[sort][order]" id="{$this->name}-sort-order" value="{$sort['order']}" />
HTML;
	}
	
	public function has_changed() {
		if (isset($this->filter['values'])) {
			return $this->md5() != $this->md5;
		}
		else {
			return false;
		}
	}
	
	private function md5() {
		if (isset($this->filter['values'])) {
			$md5 = md5(serialize($this->filter['values']));
			if (!$this->md5) {
				$this->md5 = $md5;
			}
			return $md5;
		}
		else {
			return "";
		}
	}
}
