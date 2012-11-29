<?php
class Facebook_Share_Button {
	public $app_id;
	public $redirect_uri;
	public $link;
	public $picture;
	public $name;
	public $caption;
	public $description;
	public $id;
	
	function __construct(array $data) {
		$this->app_id = $data['app_id'];
		$this->redirect_uri = $data['redirect_uri'];
		$this->link = $data['link'];
		$this->picture = $data['picture'];
		$this->name = $data['name'];
		$this->caption = $data['caption'];
		$this->description = $data['description'];
		$this->id = isset($data['id']) ? $data['id'] : uniqid();
	}
	
	function generer_bouton() {
		global $config, $page;
		$js = <<<Javascript
$(document).ready(function() {
	$("#fb-partage-{$this->id}").click(function() {
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
		if (!in_array("http://connect.facebook.net/en_US/all.js", $page->javascript)) {
			$page->javascript[] = "http://connect.facebook.net/en_US/all.js";
		}
		return <<<HTML
<img src="{$config->media("facebook.png")}" id="fb-partage-{$this->id}" style="cursor:pointer;"/>
HTML;
	}
}