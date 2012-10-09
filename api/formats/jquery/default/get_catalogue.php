<?php

$jq = array();

function _get_catalogue_categories($data, $level = 1) {
	global $jq;

	$html = '<ul class="level'.$level.'">';
	foreach ($data['categories'] as $categorie) {
		$id = "doublet-categorie-item-{$categorie['id']}";
		$html .= '<li class="collapse"><a id="'.$id.'" href="#doublet-catalogue-top">'.$categorie['name'].'</a>';
		if (count($categorie['categories'])) {
			$html .= _get_catalogue_categories($categorie, $level + 1);
		}
		$html .= '</li>';
		$jq['items']['show_categories'][] = array('selector' => "#$id", 'event' => "click", 'toggle' => "ul");
		$jq['items']['show_products'][] = array('selector' => "#$id", 'event' => "click", 'id' => $categorie['id']);
	}
	$html .= "</ul>";

	return $html;
}

$categories = _get_catalogue_categories($data);

$jq['html'] = <<<HTML
{$categories}
HTML;

$data['jq'] = $jq;
