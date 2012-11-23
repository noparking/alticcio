<?php
class Facebook_Share_Button {
	public $href;
	
	function __construct(array $data) {
		$this->href = $data['href'];
	}
	
	function generer_bouton() {
		return <<<HTML
<a name="fb_share" id="fb-partage" type="button" share_url="{$this->href}" href="http://www.facebook.com/sharer.php">
	Partager
</a>
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
HTML;
	}
}