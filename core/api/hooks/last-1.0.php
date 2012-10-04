<?php

function get_last_sku($api) {
	$last_call = $api->last_call();

	$sku = new API_Sku();
	return $sku->updated_since($last_call);
}
