<?php
global $config, $page, $dico, $pager, $filter;

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter.js");

$html = <<<HTML
<div class="filter-pager">
	{$page->inc("snippets/pager")} |
	<div class="filter-buttons">
		{$filter->actionbutton("reset", $dico->t("FiltreReset"))}
		{$filter->actionbutton("search", $dico->t("FiltreSearch"))}
	</div>
</div>
<table id="table_pager">
<thead>
<tr>
HTML;

$elements = $filter->visible_elements();
foreach ($elements as $element) {
	$html .= <<<HTML
<th>{$filter->column($element)}</th>
HTML;
}

$html .= <<<HTML
</tr>
<tr>
HTML;
	foreach ($elements as $element) {
		$html .= <<<HTML
<td>
	{$filter->field($element)}
</td>
HTML;
	}
	$html .= <<<HTML
</tr>
</thead>
<tbody>
HTML;
	$items = array();
	foreach ($filter->rows() as $row) {
		$html .= <<<HTML
<tr class="filter-data-row">
HTML;
		foreach ($row as $cle => $valeur) {
			if (isset($elements[$cle])) {
				$html .= <<<HTML
<td class="filter-data">{$filter->value($cle, $valeur)}</td>
HTML;
			}
		}
		$html .= <<<HTML
</tr>
HTML;
	}
	$html .= <<<HTML
</tbody>
</table>
{$filter->hidden()}
HTML;
	
echo $html;
