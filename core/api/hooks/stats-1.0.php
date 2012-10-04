<?php

function get_stats_product($api, $annee, $id) {
	$stats = new API_Stats($api, $annee);
	$ret = $stats->produit($id);
	return $ret;
}

function get_stats_general($api, $annee) {
	$stats = new API_Stats($api, $annee);
	$ret = $stats->general();
	return $ret;
}

function get_stats_clients($api, $annee) {
	$stats = new API_Stats($api, $annee);
	$ret = $stats->clients();
	return $ret;
}
