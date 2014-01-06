<?php

function get_cart() {
	$args = func_get_args();
	$api = array_shift($args);
	$method = array_shift($args);

	$cart = new API_Cart($api);
	return call_user_func_array(array($cart, $method) ,$args);
}

function post_cart() {
	$args = func_get_args();
	$api = array_shift($args);
	$method = array_shift($args);

	$cart = new API_Cart($api);
	return call_user_func_array(array($cart, $method) ,$args);
}

function post_cart_add() {
	$args = func_get_args();
	$api = array_shift($args);

	$cart = new API_Cart($api);
	call_user_func_array(array($cart, "add") ,$args);

	$cart_number = 0;
	foreach ($cart->items() as $item) {
		$cart_number += $item['qte'];
	}

	$ret = array(
		'cart_number' => $cart_number
	);

	return $ret;
}


function get_cart_content() {
	$args = func_get_args();
	$api = array_shift($args);

	$cart = new API_Cart($api);

	return $cart->personnalisations();
}

function get_cart_livraison() {
	$args = func_get_args();
	$api = array_shift($args);
	$method = array_shift($args);

	$livraison = new API_Livraison($api);
	return call_user_func_array(array($livraison, $method) ,$args);
}

function post_cart_livraison() {
	$args = func_get_args();
	$api = array_shift($args);
	$method = array_shift($args);

	$livraison = new API_Livraison($api);
	return call_user_func_array(array($livraison, $method) ,$args);
}

