<?php

function api_get() {
	global $api;
	$url = implode("/", func_get_args());
	$res = $api->get($url);
	if (is_array($res) and isset($res['error'])) {
		$message = "API error {$res['error']}";
		if ($res['message']) {
			$message .= ": ".$res['message'];
		}
		throw new Exception($message);
	}
	return $res;
}

function api_post() {
	global $api;
	$args = func_get_args();
	$url = implode("/", $args);
	$res = $api->post($url);
	if (is_array($res) and isset($res['error'])) {
		$message = "API error {$res['error']}";
		if ($res['message']) {
			$message .= ": ".$res['message'];
		}
		throw new Exception($message);
	}
	return $res;
}

function api_postdata() {
	global $api;
	$args = func_get_args();
	$data = array_pop($args);
	$url = implode("/", $args);
	$res = $api->post($url, $data);
	if (is_array($res) and isset($res['error'])) {
		$message = "API error {$res['error']}";
		if ($res['message']) {
			$message .= ": ".$res['message'];
		}
		throw new Exception($message);
	}
	return $res;
}
