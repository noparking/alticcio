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
		$this->js = isset($data['js']) ? $data['js'] : "send_to_pinterest_{$this->id}()";
	}
	
	function getId() {
		return "pinterest-{$this->id}";
	}
	
	function generer_bouton() {
		global $page;
		
		if (!in_array("http://assets.pinterest.com/js/pinit.js", $page->javascript)) {
			$page->javascript[] = "http://assets.pinterest.com/js/pinit.js";
		}
		$js = <<<Javascript
function send_to_pinterest_{$this->id}() {
	var url = encodeURI({$this->url});
	var media = encodeURI({$this->media});
	var description = encodeURI("{$this->description}");
	var newWindowUrl = "http://pinterest.com/pin/create/button/?url="+url+"&media="+media+"&description="+description;
	window.open(newWindowUrl,'name','height=350,width=600');
}
$(document).ready(function() {
	$("#{$this->getId()}").click(function() {
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