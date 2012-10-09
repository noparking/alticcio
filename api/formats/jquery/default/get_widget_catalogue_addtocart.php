<?php

$jq = array();

$product = $data;

$id_texte = md5($product['texte_perso']['value']); 
$id = "doublet-sku-item-{$product['sku']['id']}-".$id_texte;
$id_qty = "doublet-sku-qty-{$product['sku']['id']}-".$id_texte;
$jq['items']['qty'][] = array('selector' => "#$id_qty");
$id_remove = "doublet-sku-removefromcart-{$product['sku']['id']}-".$id_texte;
$jq['items']['removefromcart'][] = array('selector' => "#$id_remove");
$id_price = "doublet-sku-price-{$product['sku']['id']}-".$id_texte;
$src = $config->core_media("produits/".$product['thumbnail']);

$item = $product['name']."\n<br />{$product['sku']['name']}";

$texte_perso_label = htmlspecialchars($product['texte_perso']['label']);
$texte_perso_value = nl2br(htmlspecialchars($product['texte_perso']['value']));
$jq['html'] = <<<HTML
<tr id="{$id}" class="doublet-product-item">
	<td><img src="{$src}" alt="{$item}"></td>
	<td><p>{$item}</p></td>
	<td><p><label>Texte à insérer</label></p><p>{$texte_perso_value}</p></td>
	<td><p>{$product['sku']['price']}&nbsp;€</p></td>
	<td><p><input type="text" class="doublet-sku-qty" id="{$id_qty}" value="1" size="4" /></p></td>
	<td><p><strong id="{$id_price}"></strong><strong>&nbsp;€</strong></p></td>
	<td><button type="button" class="doublet-sku-removefromcart" id="{$id_remove}" title="Supprimer"></button></td>
</tr>
HTML;
$jq['items']['cart_item'][] = array('selector' => "#$id", 'price_selector' => "#$id_price", 'unit_price' => $product['sku']['price'], 'item_id' => $product['item_id']);

$data['jq'] = $jq;
