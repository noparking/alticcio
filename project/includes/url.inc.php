<?php
class StandardUrl extends Url {

	public function __construct($url_redirection) {
		$this->url_redirection = $url_redirection;
	}

	public function build() {
		$args = func_get_args();
		$code_url = $args[0];
		$section = "";
		if ($code_url == "current") {
			$code_url = $this->get('code_url');
			$section = $this->get('section');
		}
		else {
			if ($primary = $this->url_redirection->primary($code_url)) {
				$code_url = $primary;
			}
		}
		if (isset($args[1])) {
			list($langue, $pays) = explode('_', $args[1]);
		}
		else {
			$langue = $this->get('langue');
			$pays = $this->get('pays');
		}
		if (strpos($code_url, "/")) {
			list($section, $code_url) = explode("/", $code_url, 2);
		}
		return array(
			'langue' => $langue,
			'pays' => $pays,
			'section' => $section,
			'code_url' => $code_url,
		);
	}
}

class PageUrl extends Url {

	public function __construct($url_redirection) {
		$this->url_redirection = $url_redirection;
	}

	public function build() {
		$args = func_get_args();
		$code_url = $args[0];
		$section = "";
		if ($code_url == "current") {
			$code_url = $this->get('code_url');
			$section = $this->get('section');
		}
		else {
			if ($primary = $this->url_redirection->primary($code_url)) {
				$code_url = $primary;
			}
		}
		$nb = isset($args[1]) ? $args[1] : $this->get('nb');
		$page = isset($args[2]) ? $args[2] : $this->get('page');
		
		$langue = $this->get('langue');
		$pays = $this->get('pays');
		
		if (strpos($code_url, "/")) {
			list($section, $code_url) = explode("/", $code_url, 2);
		}
		return array(
			'langue' => $langue,
			'pays' => $pays,
			'section' => $section,
			'code_url' => $code_url,
			'nb' => $nb,
			'page' => $page,
		);
	}
}

class AjaxUrl extends Url {

	public function build() {
		$args = func_get_args();
		$format = $args[0];
		$page = $args[1];
		$id = isset($args[2]) ? $args[2] : "";
		$extra = isset($args[3]) ? $args[3] : "";
		$langue = $this->get('langue');
		$pays = $this->get('pays');
		return array(
			'langue' => $langue,
			'pays' => $pays,
			'format' => $format,
			'page' => $page,
			'id' => $id,
			'extra' => $extra,
		);
	}
}

class ItemUrl extends Url {

	public function build() {
		$args = func_get_args();
		$item = $args[0];
		$id = $args[1];
		$langue = $this->get('langue');
		$pays = $this->get('pays');
		return array(
			'langue' => $langue,
			'pays' => $pays,
			'item' => $format,
			'id' => $id, 
		);
	}
}

class PaymentUrl extends Url {

	public function build() {
		$args = func_get_args();
		$type = $args[0];
		return array(
			'payment' => "payment",
			'type' => $type, 
		);
	}
}
