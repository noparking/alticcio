<?php
$menu->current('main/params/api-users');

$config->core_include("outils/form", "outils/mysql");
$config->core_include("api/admin");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("confirm.js");
$page->jsdico[] = array('ConfirmerChangement');

$sql = new Mysql($config->db());
$admin = new API_Admin("api_", $sql);

$action = $url->get("action");

if ($action == "add-user") {
	$form = new Form(array(
		'id' => "form-add-api-user",
		'class' => "form-edit",
		'actions' => array("add-user"),
	));

	if ($form->is_submitted()) {
		$data = $form->escape_values();
		$data['user']['active'] = 1;
		$key_id = $admin->add_key($data['user']);
		foreach ($data['roles'] as $role_id => $value) {
			if ($value) {
				$admin->assign_role($key_id, $role_id);
			}
		}
		$form->reset();
		$url->redirect("current", array('action' => "", 'id' => ""));
	}

	$titre_page = $dico->t('AjouterUnUtilisateurApi');

	$form_start = $form->form_start();

	$form->template = $page->inc("snippets/produits-form-template");

	$roles_checkboxes = "<ul>";
	foreach ($admin->roles() as $id => $role) {
		$roles_checkboxes .= $form->input(array('type' => "checkbox", 'name' => "roles[$id]", 'label' => $role, 'template' => "<li>#{field} #{label}</li>"));
	}
	$roles_checkboxes .= "</ul>";

	$main = <<<HTML
{$form->fieldset_start($dico->t('AjouterUnUtilisateur'))}
{$form->input(array('name' => "user[name]", 'label' => $dico->t('Nom')))}
{$form->input(array('name' => "user[domain]", 'label' => $dico->t('Domaine')))}
{$form->fieldset_start($dico->t('Roles'))}
{$roles_checkboxes}
{$form->fieldset_end()}
{$form->input(array('name' => "add-user", 'type' => "submit", 'value' => $dico->t("Ajouter")))}
{$form->fieldset_end()}
HTML;

	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));

	$form_end = $form->form_end();
}
else if ($action == "edit-user") {
	$key_id = $url->get('id');

	$form = new Form(array(
		'id' => "form-edit-api-user",
		'class' => "form-edit",
		'actions' => array("save-user", "change-key"),
	));

	if ($form->is_submitted()) {
		if ($form->action() == "change-key") {
			$admin->change_key($key_id);
		}
		else {
			$data = $form->escape_values();
			foreach ($admin->roles() as $role_id => $role) {
				$admin->unassign_role($key_id, $role_id);
			}
			if (isset($data['roles']) and is_array($data['roles'])) {
				foreach ($data['roles'] as $role_id => $value) {
					if ($value) {
						$admin->assign_role($key_id, $role_id);
					}
				}
			}
			$admin->update_key($key_id, $data['user']);
			$form->reset();
			$url->redirect("current", array('action' => "", 'id' => ""));
		}
	}
	$user = $admin->key_data($key_id);

	$form->default_values['user'] = $user;
	$form->default_values['roles'] = $admin->key_roles($key_id);

	$titre_page = $dico->t('EditerUtilisateur')." ".$user['name'];

	$form_start = $form->form_start();

	$form->template = $page->inc("snippets/produits-form-template");

	$roles_checkboxes = "<ul>";
	foreach ($admin->roles() as $id => $role) {
		$roles_checkboxes .= $form->input(array('type' => "checkbox", 'name' => "roles[$id]", 'label' => $role, 'template' => "<li>#{field} #{label}</li>"));
	}
	$roles_checkboxes .= "</ul>";

	$main = <<<HTML
{$form->fieldset_start($dico->t('ParametresUtilisateur'))}
{$form->input(array('type' => "submit", 'class' => "confirm-change", 'name' => "change-key", 'value' => $dico->t('Changer'), 'label' => $dico->t('Key'), 'template' => "<span>#{label} :</span> {$user['key']} #{field}"))}
{$form->input(array('name' => "user[name]", 'label' => $dico->t('Nom') ))}
{$form->input(array('name' => "user[domain]", 'label' => $dico->t('Domaine') ))}
{$form->input(array('name' => "user[language]", 'label' => $dico->t('Langue') ))}
{$form->textarea(array('name' => "user[emails]", 'label' => $dico->t('Emails') ))}
{$form->input(array('type' => "checkbox", 'name' => "user[active]", 'label' => $dico->t('Active'), 'template' => "#{field} #{label}"))}
{$form->fieldset_start($dico->t('Roles') )}
{$roles_checkboxes}
{$form->fieldset_end()}
{$form->input(array('name' => "save-user", 'type' => "submit", 'value' => $dico->t("Sauvegarder")))}
{$form->fieldset_end()}
HTML;

	$buttons['back'] = $page->l($dico->t('Retour'), $url->make("current", array('action' => "", 'id' => "")));

	$form_end = $form->form_end();
}
else if ($action == "edit-rules") {
	$key_id = $url->get('id');
	$user = $admin->key_data($key_id);
	$key = $user['key'];

	$form = new Form(array(
		'id' => "form-add-api-user-rule",
		'class' => "form-edit",
		'actions' => array("add-rule"),
	));

	if ($form->is_submitted()) {
		$data = $form->escape_values();
		$rule = $data['rule'];
		$admin->add_key_rule($key_id, $rule['method'], $rule['uri'], $rule['type'], $rule['log']);
		$form->reset();
	}

	$rules = $admin->key_rules($key_id);
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

	$titre_page = $dico->t('ReglesUtilisateur')." ".$user['name'];

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
{$form->input(array('name' => "rule[uri]", 'label' => $dico->t('Uri')))}
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
	$id_key = $admin->delete_key_rule($id_rule);
	$url->redirect("current", array('action' => "edit-rules", 'id' => $id_key));
}
else {
	if ($action == "delete-user") {
		$admin->delete_key($url->get('id'));
		$url->redirect("current", array('action' => "", 'id' => ""));
	}
	$titre_page = $dico->t('ListeUtilisateursApi');

	$buttons['new'] = $page->l($dico->t('NouvelUtilisateur'), $url->make("current", array('action' => "add-user", 'id' => "")));

	$table = <<<HTML
<table>
	<tr>
		<th>{$dico->t('Utilisateur')}</th>
		<th>{$dico->t('Key')}</th>
		<th>{$dico->t('Roles')}</th>
		<th>{$dico->t('Statut')}</th>
		<th>{$dico->t('Domaine')}</th>
		<th colspan="3">{$dico->t("Actions")}</th>
	</tr>
HTML;
	foreach ($admin->keys() as $id => $user) {
		$roles = $admin->key_roles($id);
		switch (count($roles)) {
			case 0 : $roles = ""; break;
			case 1 : $roles = array_pop($roles); break;
			default :
				$roles = "<ul><li>".implode("</li><li>", $roles)."</li></ul>";
				break;
		}
		$actions = array(
			$page->l($dico->t("Editer"), $url->make("current", array('action' => "edit-user", 'id' => $id))),
			$page->l($dico->t("Règles"), $url->make("current", array('action' => "edit-rules", 'id' => $id))),
			"<a class=\"confirm-delete\" href=\"{$url->make("current", array('action' => "delete-user", 'id' => $id))}\">{$dico->t("Supprimer")}</a>",
		);
		$actions = implode('</td><td class="align_center">', $actions);
		$status = $user['active'] ? $dico->t('Active') : $dico->t('Desactive');
		$table .= <<<HTML
	<tr>
		<td>{$user['name']}</td>
		<td>{$user['key']}</td>
		<td>{$roles}</td>
		<td>{$status}</td>
		<td>{$user['domain']}</td>
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
