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
	public $js;
	
	function __construct(array $data) {
		$this->app_id = $data['app_id'];
		$this->redirect_uri = $data['redirect_uri'];
		$this->link = $data['link'];
		$this->picture = $data['picture'];
		$this->name = $data['name'];
		$this->caption = $data['caption'];
		$this->description = $data['description'];
		$this->id = isset($data['id']) ? $data['id'] : uniqid();
		$this->js = isset($data['js']) ? $data['js'] : "send_to_facebook(facebook_object_{$this->id});";
	}
	
	function getId() {
		return "fb-partage-{$this->id}";
	}
	
	function facebookJs() {
		return <<<Javascript
function send_to_facebook(object) {		
	FB.init({appId: object.app_id, status: true, cookie: true});
	var obj = {
		method: 'feed',
		redirect_uri: object.redirect_uri,
		link: object.link,
		picture: object.picture,
		name: object.name,
		caption: object.caption,
		description: object.description,
	};
	function callback(reponse) { }
	FB.ui(obj, callback);
}
Javascript;
	}
	
	function defaultJs() {
		return <<<Javascript
var facebook_object_{$this->id} = {
	app_id: "{$this->app_id}",
	redirect_uri: {$this->redirect_uri},
	link: {$this->link},
	picture: {$this->picture},
	name: "{$this->name}",
	caption: "{$this->caption}",
	description: "{$this->description}"
};
Javascript;
	}
	
	function generer_bouton() {
		global $config, $page;
		if (!in_array($this->facebookJs(), $page->post_javascript)) {
			$page->post_javascript[] = $this->facebookJs();
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
		if (!in_array("http://connect.facebook.net/en_US/all.js", $page->javascript)) {
			$page->javascript[] = "http://connect.facebook.net/en_US/all.js";
		}
		return <<<HTML
<img src="{$config->media("facebook.png")}" id="{$this->getId()}" style="cursor:pointer;"/>
HTML;
	}
}