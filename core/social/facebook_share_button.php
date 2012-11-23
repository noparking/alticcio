<?php
class Facebook_Share_Button {
	public $href;
	public $app_id;
	public $redirect_uri;
	public $link;
	public $picture;
	public $name;
	public $caption;
	public $description;
	
	function __construct(array $data) {
		$this->href = $data['href'];
		$this->app_id = $data['app_id'];
		$this->redirect_uri = $data['redirect_uri'];
		$this->link = $data['link'];
		$this->picture = $data['picture'];
		$this->name = $data['name'];
		$this->caption = $data['caption'];
		$this->description = $data['description'];
	}
	
	function generer_bouton() {
		global $config, $page;
		$js = <<<Javascript
$(document).ready(function() {
	$("#fb-partage").click(function() {
		FB.init({appId: "{$this->app_id}", status: true, cookie: true});
		var obj = {
			method: 'feed',
			redirect_uri: {$this->redirect_uri},
			link: {$this->link},
			picture: '{$this->picture}',
			name: '{$this->name}',
			caption: '{$this->caption}',
			description: "{$this->description}",
		};
		function callback(reponse) { }
		FB.ui(obj, callback);
	});
});
Javascript;
		$page->post_javascript[] = $js;
		return <<<HTML
<img src="{$config->media("facebook.png")}" id="fb-partage" style="cursor:pointer;"/>
HTML;
	}
}