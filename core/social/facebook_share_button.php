<?php
class Facebook_Share_Button {
	public $href;
	public $redirect_uri;
	public $picture;
	public $name;
	public $caption;
	public $description;
	
	function __construct(array $data) {
		$this->href = $data['href'];
	}
	
	function generer_bouton() {
		global $config, $page;
		$js = <<<Javascript
$(document).ready(function() {
	$("#fb-partage").click(function() {
		FB.init({appId: "486457814727883", status: true, cookie: true});
		var obj = {
			method: 'feed',
			redirect_uri: facebook_wall_url,
			link: 'https://developers.facebook.com/docs/reference/dialogs/',
			picture: '{$config->get("site_url")}{$config->media("logo.png")}',
			name: 'Mon tapis de jeu Aberlaas',
			caption: 'Aberlaas',
			description: "Comment trouvez-vous le tapis que j'ai choisi ?",
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