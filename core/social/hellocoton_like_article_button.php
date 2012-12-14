<?php
class Hellocoton_Billet_Article_Button {
	function generer_bouton() {
		return <<<HTML
<span style="display:block;width:147px;height:26px;position:relative;padding:0;border:10px 0px;margin:0;clear:both;">
	<a href="http://www.hellocoton.fr/vote" target="_blank" style="display:block;width:121px;height:26px;position:absolute;top:0;left:0;">
		<img src="http://widget.hellocoton.fr/img/action-on.gif" border="0" style="background:transparent;padding:0;border:0;margin:0;float:none;" />
	</a>
	<a href="http://www.hellocoton.fr" target="_blank" style="display:block;width:27px;height:26px;position:absolute;top:0;left:120px;">
		<img src="http://widget.hellocoton.fr/img/hellocoton.gif" border="0" alt="Rendez-vous sur Hellocoton !" style="background:transparent;padding:0;border:0;margin:0;float:none;" />
	</a>
</span>
HTML;
	}
}