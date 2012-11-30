<?php
class Pinterest_Pin_It_Button {
	public $url;
	public $media;
	public $description;
	public $id;
	public $js;
	
	function __construct(array $data) {
		$this->url = $data['url'];
		$this->media = $data['media'];
		$this->description = $data['description'];
		$this->id = isset($data['id']) ? $data['id'] : uniqid();
		$this->js = isset($data['js']) ? $data['js'] : "send_to_pinterest(pinterest_object_{$this->id});";
	}
	
	function getId() {
		return "pinterest-{$this->id}";
	}
	
	function pinterestJs() {
		return <<<Javascript
function send_to_pinterest(object) {
	var url = encodeURI(object.url);
	var media = encodeURI(object.media);
	var description = encodeURI(object.description);
	var newWindowUrl = "http://pinterest.com/pin/create/button/?url="+url+"&media="+media+"&description="+description;
	window.open(newWindowUrl,'name','height=350,width=600');
}		
Javascript;
	}
	
	function defaultJs() {
		return <<<Javascript
var pinterest_object_{$this->id} = {
	url: {$this->url},
	media: {$this->media},
	description: "{$this->description}"
};
Javascript;
	}
	
	function generer_bouton() {
		global $page;
		
		if (!in_array($this->pinterestJs(), $page->post_javascript)) {
			$page->post_javascript[] = $this->pinterestJs();
		}
		if (!in_array("http://assets.pinterest.com/js/pinit.js", $page->javascript)) {
			$page->javascript[] = "http://assets.pinterest.com/js/pinit.js";
		}
		$js = <<<Javascript
$(document).ready(function() {
	$("#{$this->getId()}").click(function() {
		{$this->defaultJs()}
		{$this->js}
	});
});
Javascript;
	$page->post_javascript[] = $js;
	return <<<HTML
<img border="0" src="http://assets.pinterest.com/images/PinExt.png" id="{$this->getId()}" style="cursor: pointer;" title="Pin It" />
HTML;
	}
}