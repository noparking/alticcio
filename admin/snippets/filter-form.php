<?php
global $config, $page, $dico, $pager, $filter, $form;

$page->javascript[] = $config->media("filter.js");

if (isset($form) and $form) {
	$is_permitted = $form->is_permitted("checkbox", array("name" => ""));
}
else {
	$is_permitted = true;
}

$html = <<<HTML
<div class="filter">
<div class="filter-pager">
	{$page->inc("snippets/pager")} |
	<div class="filter-buttons">
		{$filter->actionbutton("search", $dico->t("FiltreSearch"))}
		{$filter->actionbutton("reset", $dico->t("FiltreReset"))}
	</div>
</div>
<div class="filter-actions">
	{$dico->t("Selectionnes")} : {$filter->selectcount()}
	| {$filter->actionlink("unselectall", "Tout déselectionner")}
</div>
<table id="table_pager">
<thead>
<tr>
<th>{$filter->allpagebox($is_permitted)}</th>
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
{$filter->selectbox($row['id'], $is_permitted)}
</td>
HTML;
		foreach ($row as $cle => $valeur) {
			if (isset($elements[$cle])) {
				$replacements = array("%id%" => $row['id']);
				$html .= <<<HTML
<td class="filter-data">{$filter->value_or_input($form, $cle, $valeur, $replacements)}</td>
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
