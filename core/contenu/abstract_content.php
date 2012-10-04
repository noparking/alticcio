<?php

abstract class AbstractContent {
	
	protected function validate_html($html) {
		$doc = new DOMDocument();
		$htmls = array(
			$html,
			utf8_encode($html),
			utf8_decode($html),
		);
		foreach ($htmls as $html) {
			if (@$doc->loadXML("<html>".$html."</html>")) {
				return true;
			}
		}

		return false;

	}
}
