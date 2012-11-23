<?php
class Twitter_Tweet_Link_Button {
	public $href;
	public $lang = "fr";
	public $hashtags;
	public $text;
	
	function __construct(array $data) {
		$this->href = urlencode($data['href']);
		$this->hashtags = urlencode(implode(",", $data['hashtags']));
		$this->text = urlencode($data['text']);
	}
	
	function generer_bouton() {
		global $config;
		$referer = urlencode($_SERVER['REQUEST_URI']);
		$link = "https://twitter.com/intent/tweet?";
		$link .= "hashtags={$this->hashtags}";
		$link .= "&amp;original_referer={$referer}";
		$link .= "&amp;source=tweetbutton";
		$link .= "&amp;text={$this->text}";
		if ($this->href != "") {
			$link .= "&amp;url={$this->href}";
		}
		return <<<HTML
<a href="{$link}" target="_blank" id="twitter-partage">
	<img src="{$config->media("twitter.png")}" />
</a>
HTML;
	}
}