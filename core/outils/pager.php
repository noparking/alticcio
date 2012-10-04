<?php

class Pager {
	
	private $sql;
	private $numbers;
	private $page;
	private $name;
	private $number;
	private $total = null;
	
	public function __construct($sql, $numbers, $name = "pager", $number = 0, $page = 1) {
		$this->sql = $sql;
		$this->numbers = $numbers;
		$this->name = $name;
		$this->page = isset($_POST[$this->name]['page']) ? $_POST[$this->name]['page'] : $page;
		$this->number = isset($_POST[$this->name]['number']) ? $_POST[$this->name]['number'] : ($number ? $number : $numbers[0]);
		if ($this->page < 1) {
			$this->page = 1;
		}
	}
	
	public function reset() {
		$this->page = 1;
	}
	
	public function query($q) {
		$query = $q." LIMIT ".(($this->page() - 1) * $this->number()).",".$this->number();
		
		if (!preg_match("/^SELECT SQL_CALC_FOUND_ROWS/i", $q)) {
			$q = preg_replace("/^select/i", "SELECT SQL_CALC_FOUND_ROWS", $q);
		}
		$res = $this->sql->query($query);
		
		$this->total = $this->sql->found_rows();
		$pages = $this->pages();
		if ($this->page > $pages) {
			$this->page = count($pages);
			return $this->query($q);
		}
		
		return $res;
	}
	
	public function fetch($res) {
		return $this->sql->fetch($res);
	}
	
	public function found_rows() {
		return $this->total;
	}
	
	public function numbers() {
		return $this->numbers;
	}
	
	public function page() {
		return $this->page;
	}
	
	public function number() {
		return $this->number;
	}
	
	public function total() {
		return $this->total;
	}
	
	public function returned() {
		if ($this->number() * $this->page() <  $this->total() ) {
			return $this->number();
		}
		else {
			$this->total() % $this->number();
		}
	}
	
	public function pages() {
		$pages = ceil($this->total() / $this->number());
		return $pages ? $pages : 1;
	}
	
	public function previous($label) {
		if ($this->page() == 1) {
			return <<<HTML
<span class="pager-previous-disabled">$label</span>
HTML;
		}
		return <<<HTML
<a class="pager-previous" href="#">$label</a>
HTML;
	}
	
	public function next($label) {
		if ($this->page() == $this->pages()) {
			return <<<HTML
<span class="pager-next-disabled">$label</span>
HTML;
		}
		else {
			return <<<HTML
<a class="pager-next" href="#">$label</a>
HTML;
		}
	}
	
	public function numberselect() {
		$page_number_options = "";
		foreach ($this->numbers() as $number) {
			$selected = ($this->number() == $number) ? 'selected="selected"' : '';
			$page_number_options .= "<option value=\"$number\" $selected>$number</option>";
		}
		
		return <<<HTML
<select class="pager-number" name="{$this->name}[number]">
{$page_number_options}
</select>
HTML;
	}

	public function pageinput() {
		return <<<HTML
<input class="pager-page" name="{$this->name}[page]" size="1" type="text" value="{$this->page()}" />
HTML;
	}
}
