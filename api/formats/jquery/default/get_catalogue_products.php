<?php

$jq = array();

function _get_catalogue_products($data, $level = 1) {
	global $jq;

	$html = '<ul class="level'.$level.'">';
	foreach ($data['categories'] as $categorie) {
		$id = "doublet-categorie-item-{$categorie['id']}";
		$html .= '<li class="collapse"><a id="'.$id.'" href="#doublet-catalogue-top">'.$categorie['name'].'</a>';
		if (count($categorie['categories'])) {
			$html .= _get_catalogue_categories($categorie, $level + 1);
		}
		$html .= '<ul class="level'.($level + 1).'">';
		foreach ($categorie['products'] as $product) {
			$id_p = "doublet-product-item-{$product['id']}";
			$html .= '<li class="collapse"><a id="'.$id_p.'" href="#doublet-catalogue-top">'.$product['name'].'</a>';
			$html .= '</li>';
			$jq['items']['show_product'][] = array('selector' => "#$id_p", 'event' => "click", 'id' => $product['id']);
		}
		$html .= "</ul>";
		$html .= '</li>';
		$jq['items']['show_categories'][] = array('selector' => "#$id", 'event' => "click", 'toggle' => "ul");
	}
	$html .= "</ul>";

	return $html;
}

$products = _get_catalogue_products($data);

$jq['html'] = <<<HTML
{$products}
HTML;

$data['jq'] = $jq;
