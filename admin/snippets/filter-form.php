<?php
global $config, $page, $dico, $pager, $filter, $form;

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
<div class="filter-actions">
	{$dico->t("Selectionnes")} : {$filter->selectcount()}
	| {$filter->actionlink("unselectall", "Tout déselectionner")}
</div>
<table id="table_pager">
<thead>
<tr>
<th>{$filter->allpagebox()}</th>
HTML;
	foreach ($filter->elements() as $element) {
		$html .= <<<HTML
<th>{$filter->column($element)}</th>
HTML;
	}
	$html .= <<<HTML
</tr>
<tr>
<td>
	{$filter->selection(array(0 => $dico->t("Tous"), 1 => $dico->t("Oui"), -1 => $dico->t("Non")))}
</td>
HTML;
	foreach ($filter->elements() as $element) {
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
			$replacements = array("%id%" => $row['id']);
			$html .= <<<HTML
<td class="filter-data">{$filter->value_or_input($form, $cle, $valeur, $replacements)}</td>
HTML;
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
