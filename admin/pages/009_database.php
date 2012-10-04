<?php

$config->core_include("database/database", "outils/mysql");

$menu->current('main/params/params');

$sql = new Mysql($config->db());
$database = new Database($sql);

$table = $url->get("action");
$id = $url->get("id");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery-ui.datepicker.min.js");
if ($config->get("langue") != "en_UK") {
	$lang = substr($config->get("langue"), 0, 2);
	$page->javascript[] = $config->core_media("ui.datepicker-".$lang.".js");
}
$page->javascript[] = $config->media("database.js");

$page->css[] = $config->core_media("jquery-ui.custom.css");

$message = "";
if (isset($_POST['item'])) {
	$url->redirect("current", array('id' => $_POST['item']));
}

if (isset($_POST['db-save'])) {
	$record = $_POST['field'];
	if ($id) {
		$message = '<div class="message_succes">'.$dico->t("EnregistrementSauvegarde").'</div>';
		$record['id'] = $id;
	}
	else {
		$message = '<div class="message_succes">'.$dico->t("EnregistrementAjoute").'</div>';
	}
	$database->save($table, $record, isset($_POST['phrase']) ? $_POST['phrase'] : array());
}

if (isset($_POST['db-delete'])) {
	$database->delete($table, $id, $_POST['phrase']);
	$url->redirect("current", array('id' => false));
}

$tables = "";
foreach ($database->tables(array('exclude' => array("dt_phrases"))) as $table_name) {
	$tables .= "<li>{$page->l($table_name, $url->make('current', array('action' => $table_name, 'id' => false)))}</li>"; 
}

$titre_page = $dico->t("GestionBDD");

$select_item = "";
$table_lines = "";

if ($table) {
	$titre_page .= " - ".$table;
	
	if ($id) {
		$database->load($table, $id);
	}
	
	$select_item .= "<select name='item' >";
	$select_item .= "<option value=''>-- {$dico->t("Nouveau")} --</option>";
	foreach ($database->records($table) as $id_record => $label) {
		$selected = $id_record == $id ? "selected='selected'" : "";
		$select_item .= "<option value='$id_record' $selected>($id_record) $label</option>";
	}
	$select_item .= "</select>";
	
	foreach ($database->table($table) as $field) {
		$table_lines .= <<<HTML
<tr>
	<td><label>{$field->name()}</label></td>
	<td>{$field->input()}</td>
</tr>
HTML;
	}
	
}

/* 
 * Valeurs renvoyées dans le template
 */
$right = <<<HTML
<h3>{$dico->t("ListeOfTables")}</h3>
<ul id="liste_tables">
{$tables}
</ul>
HTML;

$bouton_supprimer = $id ? "<input name='db-delete' type='submit' value='{$dico->t("Supprimer")}' />" : "";

$main = <<<HTML
$message
<form id="db-form-load" action="" method="post">
{$select_item}
<input name="db-load" class="db_load" type="submit" value="{$dico->t('Charger')}" />
</form>
<br/>
<form id="db-form-edit" action="" method="post">
<table>
<tr>
<th>{$dico->t("Champs")}</th>
<th>{$dico->t("Valeurs")}</th>
</tr>
{$table_lines}
</table>
<br/>
<input name="db-save" class="db-save" type="submit" value="{$dico->t('Enregistrer')}" />
$bouton_supprimer
</form>
HTML;

if (!$table) {
	$main = <<<HTML
{$dico->t('SelectionnezTable')}
HTML;
}
?>
