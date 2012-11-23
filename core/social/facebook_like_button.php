<?php
class Facebook_Like_Button {
	public $action = "like";
	public $width = 400;
	public $href;
	public $send = "false";
	public $font = "arial";
	public $show_faces = "false";

	function __construct(array $data) {
		foreach ($data as $cle => $valeur) {
			$method = "set".ucfirst($cle);
			if (method_exists($this, $method)) {
				$this->$method($valeur);
			}
		}
	}
	
	function setAction($valeur) {
		if ($valeur == "recommend") {
			$this->action = "recommend";
		} else {
			$this->action = "like";
		}
	}
	
	function setWidth($valeur) {
		$this->width = (int) $valeur;
	}
	
	function setHref($valeur) {
		$this->href = $valeur;
	}
	
	function setFaces($valeur) {
		if ($valeur == true) {
			$this->show_faces = "true";
		} else {
			$this->show_faces = "false";
		}
	}
	
	function setSend($valeur) {
		if ($valeur == true) {
			$this->send = "true";
		} else {
			$this->send = "false";
		}
	}
	
	function setFont($valeur) {
		if (in_array($valeur, array("arial", "lucida grande", "segoe ui", "tahoma", "trebuchet ms", "verdana"))) {
			$this->font = $valeur;
		} else {
			$this->font = "arial";
		}
	}
	
	function generer_bouton() {
		return <<<HTML
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div id="fb-partage" class="fb-like" data-href="{$this->href}" data-send="{$this->send}" data-width="{$this->width}"
data-show-faces="{$this->show_faces}" data-font="{$this->font}" data-action="{$this->action}"></div>		
HTML;
	}
}