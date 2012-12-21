<?php
class Atom {
	public $id = "";
	public $title = "";
	public $subtitle = "";
	public $updated = "";
	public $link = "";
	public $website = "";
	
	function __construct(array $array) {
		$this->id = $array['id'];
		$this->title = $array['title'];
		$this->subtitle = $array['subtitle'];
		$this->updated = $this->date($array['updated']);
		$this->link = $array['link'];
		$this->website = $array['website'];
	}
	
	function date($timestamp) {
		return date("Y-m-d\TH:i:s\Z", (int) $timestamp);
	}
	
	function header() {
		return <<<HTML
<title>{$this->title}</title>
<subtitle>{$this->subtitle}</subtitle>
<link href="{$this->link}" rel="self" />
<link href="{$this->website}" />
<id>{$this->id}</id>
<updated>{$this->updated}</updated>		
HTML;
	}
	
	function entry(array $array) {
		$title = $array['title'];
		$link = $array['link'];
		$id = $array['id'];
		$updated = $this->date($array['updated']);
		$summary = $array['summary'];
		$author['name'] = $array['name'];
		$author['email'] = $array['email'];
		return <<<HTML
<entry>
	<title>{$title}</title>
	<link href="{$link}" />
	<id>{$id}</id>
	<updated>{$updated}</updated>
	<summary>{$summary}</summary>
	<author>
		<name>{$author['name']}</name>
		<email>{$author['email']}</email>
	</author>
</entry>		
HTML;
	}
}