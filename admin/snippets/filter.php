<?php
global $config, $page, $dico, $pager, $filter;

$page->javascript[] = $config->media("filter.js");

$html = <<<HTML
<div class="filter">
<div class="filter-pager">
	{$page->inc("snippets/pager")} |
	<div class="filter-buttons">
		{$filter->actionbutton("search", $dico->t("FiltreSearch"))}
		{$filter->actionbutton("reset", $dico->t("FiltreReset"))}
	</div>
</div>
HTML;

$html .= <<<HTML
<div class="filter-actions">
	{$dico->t("Selectionnes")} : {$filter->selectcount()}
	| {$filter->actionlink("unselectall", "Tout déselectionner")}
</div>
HTML;

$html .= <<<HTML
<table id="table_pager">
<thead>
<tr>
HTML;

	$html .= <<<HTML
<th>{$filter->allpagebox()}</th>
HTML;

$elements = $filter->visible_elements();
foreach ($elements as $element) {
	$html .= <<<HTML
<th>{$filter->column($element)}</th>
HTML;
}
$html .= <<<HTML
</tr>
HTML;

$html .= <<<HTML
<tr>
<td>
	{$filter->selection(array(0 => $dico->t("Tous"), 1 => $dico->t("Oui"), -1 => $dico->t("Non")))}
</td>
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
<td>
{$filter->selectbox($row['id'])}
</td>
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
</div>
HTML;
	
echo $html;
