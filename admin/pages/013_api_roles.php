<?php
$menu->current('main/params/api-roles');

$config->core_include("outils/form", "outils/mysql");
$config->core_include("api/admin");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("confirm.js");

$sql = new Mysql($config->db());
$admin = new API_Admin("api_");

$action = $url->get("action");

if ($action == "add-role") {
	$form = new Form(array(
		'id' => "form-add-api-role",
		'class' => "form-edit",
		'actions' => array("add-role"),
	));

	if ($form->is_submitted()) {
		$data = $form->escape_values();
		$admin->add_role($data['role']['name']);
		$form->reset();
		$url->redirect("current", array('action' => "", 'id' => ""));
	}

	$titre_page = $dico->t('AjouterRoleApi');

	$form_start = $form->form_start();

	$form->template = $page->inc("snippets/produits-form-template");

	$main = <<<HTML
{$form->fieldset_start($dico->t("AjouterUnRole"))}
{$form->input(array('name' => "role[name]", 'label' => $dico->t("Nom")))}
{$form->input(array('name' => "add-role", 'type' => "submit", 'value' => $dico->t("Ajouter")))}
{$form->fieldset_end()}
HTML;

	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));

	$form_end = $form->form_end();
}
else if ($action == "edit-rules") {
	$roles = $admin->roles();
	$id = $url->get('id');
	$role = $roles[$id];

	$form = new Form(array(
		'id' => "form-add-api-role-rule",
		'class' => "form-edit",
		'actions' => array("add-rule"),
	));

	if ($form->is_submitted()) {
		$data = $form->escape_values();
		$rule = $data['rule'];
		$admin->add_role_rule($id, $rule['method'], $rule['uri'], $rule['type'], $rule['log']);
		$form->reset();
	}

	$rules = $admin->role_rules($id);
	$main = <<<HTML
<table>
	<tr>
		<th>{$dico->t('Methode')}</th>
		<th>{$dico->t('Uri')}</th>
		<th>{$dico->t('Type')}</th>
		<th>{$dico->t('Logs')}</th>
		<th>{$dico->t('Actions')}</th>
	</tr>
HTML;
	foreach ($rules as $rule) {
		$actions = array(
			"<a class=\"confirm-delete\" href=\"{$url->make("current", array('action' => "delete-rule", 'id' => $rule['id']))}\">{$dico->t("Supprimer")}</a>",
		);
		$actions = implode('</td><td class="align_center">', $actions);
		$logs = $rule['log'] ? "Yes" : "No";
		$main .= <<<HTML
	<tr>
		<td>{$rule['method']}</td>
		<td>{$rule['uri']}</td>
		<td>{$rule['type']}</td>
		<td>{$logs}</td>
		<td class="align_center">$actions</td>
	</tr>
HTML;
	}
	$main .= <<<HTML
</table>
HTML;

	$titre_page = $dico->t('ReglesRole')." ".$role;

	$form_start = $form->form_start();

	$form->template = $page->inc("snippets/produits-form-template");

	$methods = array(
		"get" => "GET",		
		"post" => "POST",		
		"put" => "PUT",		
		"delete" => "DELETE",		
	);
	$acces = array(
		"deny" => "Deny",
		"allow" => "Allow",
	);
	$log = array(
		1 => "Yes",
		0 => "No",
	);
	$main .= <<<HTML
{$form->fieldset_start($dico->t('AjouterUneRegle'))}
{$form->select(array('name' => "rule[method]", 'label' => $dico->t('Methode'), 'options' => $methods))}
{$form->input(array('name' => "rule[uri]", 'label' => $dico->t('Uri') ))}
{$form->select(array('name' => "rule[type]", 'label' => $dico->t('Acces'), 'options' => $acces))}
{$form->select(array('name' => "rule[log]", 'label' => $dico->t('Logs'), 'options' => $log))}
{$form->input(array('name' => "add-rule", 'type' => "submit", 'value' => $dico->t("Ajouter")))}
{$form->fieldset_end()}
HTML;

	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));

	$form_end = $form->form_end();
}
else if ($action == "delete-rule") {
	$id_rule = $url->get('id');
	$id_role = $admin->delete_role_rule($id_rule);
	$url->redirect("current", array('action' => "edit-rules", 'id' => $id_role));
}
else {
	if ($action == "delete-role") {
		$admin->delete_role($url->get('id'));
		$url->redirect("current", array('action' => "", 'id' => ""));
	}
	$titre_page = $dico->t('ListeOfRoles');

	$buttons['new'] = $page->l($dico->t('NouveauRole'), $url->make("current", array('action' => "add-role", 'id' => "")));

	$table = <<<HTML
<table>
	<tr>
		<th>{$dico->t('Role')}</th>
		<th colspan="2">{$dico->t("Actions")}</th>
	</tr>
HTML;
	foreach ($admin->roles() as $id => $role) {
		$actions = array(
			$page->l($dico->t('EditerRegles'), $url->make("current", array('action' => "edit-rules", 'id' => $id))),
			"<a class=\"confirm-delete\" href=\"{$url->make("current", array('action' => "delete-role", 'id' => $id))}\">{$dico->t("Supprimer")}</a>",
		);
		$actions = implode('</td><td class="align_center">', $actions);
		$table .= <<<HTML
	<tr>
		<td>$role</td>
		<td class="align_center">$actions</td>
	</tr>
HTML;
	}
	$table .= <<<HTML
</table>
HTML;

	$main = <<<HTML
$table
HTML;
}
