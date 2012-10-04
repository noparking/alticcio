<?php

global $messages;

if (count($messages)) {
	echo '<ul class="messages"><li>';
	echo implode("</li>\n<li>", $messages); 
	echo '</li></ul>';
}
