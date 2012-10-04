<?php

$config->core_include("outils/menu", "outils/form");

$menu->current('main/params/menus');

$titre_page = $dico->t("Gestion des menus");

$form = new Form(array(
	'id' => "form-edit-menus",
	'class' => "form-edit",
	'actions' => array("save"),
));

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	file_put_contents($config->get("base_path")."/includes/menu_edited.php", $menu->write($data['menus']));
	$url->redirect("current");
}
$form->reset();

$form_start = $form->form_start();

$groupes = $menu->get_groupes();
$main = get_submenu($menu->data);

if (is_writable($config->get("base_path")."/includes/") or is_writable($config->get("base_path")."/includes/menu_edited.php")) {
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
}
else {
	$main = <<<HTML
<p class="message">Impossible d'éditer le menu. Vérifier les droits d'écriture.</p>
$main
HTML;
}

$form_end = $form->form_end();

function get_submenu($items, $name = "menus") {
	global $menu, $form, $groupes;

	$submenu = "<ul>";
	foreach ($items as $entry => $data) {
		$label = isset($data['label']) ? $data['label'] : $entry;
		$checked = (!isset($data['actif']) or $data['actif'] != '');
		if ($menu->is_protected($data)) {
			$submenu .= <<<HTML
	<li>
	<div style="display: inline-block; width: 150px;">
	<span style="margin-left: 25px;">{$label}</span>
	</div>
HTML;
		}
		else {
			$submenu .= <<<HTML
	<li>
	<div style="display: inline-block; width: 150px;">
	{$form->input(array('name' => "{$name}[{$entry}][actif]", 'type' =>	"checkbox", 'label' => $label, 'checked' => "{$checked}", 'template' => "#{field} #{label}"))}
	</div>
HTML;
		}
		if (isset($data['level'])) {
				$submenu .= <<<HTML
	{$form->select(array('name' => "{$name}[{$entry}][level]", 'options' => $groupes, 'label' => "Niveau d'accès :", 'forced_value' => $menu->get_level($data['level'])))}
HTML;
		}
		if (isset($data['items'])) {
			$subname = $name."[{$entry}][items]";
			$submenu .= get_submenu($data['items'], $subname);
		}
		$submenu .= <<<HTML
	</li>
HTML;
	}
	$submenu .= "</ul>";

	return $submenu;
}
