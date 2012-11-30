<?php
class Pinterest_Pin_It_Button {
	public $url;
	public $media;
	public $description;
	public $id;
	
	function __construct(array $data) {
		$this->url = $data['url'];
		$this->media = $data['media'];
		$this->description = $data['description'];
		$this->id = isset($data['id']) ? $data['id'] : uniqid();
	}
	
	
	function generer_bouton() {
		global $page;
		
		if (!in_array("http://assets.pinterest.com/js/pinit.js", $page->javascript)) {
			$page->javascript[] = "http://assets.pinterest.com/js/pinit.js";
		}
		$link = "http://pinterest.com/pin/create/button/?";
		$link .= "url=".urlencode($this->url);
		$link .= "&media=".urlencode($this->media);
		$link .= "&description=".urlencode($this->description);
		return <<<HTML
<a href="{$link}" class="pin-it-button" count-layout="none" id="pinterest_{$data['id']}">
	<img border="0" src="http://assets.pinterest.com/images/PinExt.png" title="Pin It" />
</a>
HTML;
	}
}