<?php
/* configuration */
$config->core_include("extranet/user", "outils/mysql", "outils/form");
$config->core_include("blog/blog");

$menu->current('main/params/users');

$sql = new Mysql($config->db());
$user = new User($sql);

$blog = new Blog($sql);

$id_user = $url->get("id");

/*
 * On initialise le formulaire
 */
$form = new Form(array(
	'id' => "form-edit",
	'class' => "form-edit",
	'required' => array('login', 'email'),
));

$form->required_mark = ' <span class="required">('.$dico->t('obligatoire').')</span>';

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

$template_profils = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors} #{field} <br/> #{description} #{errors}</div>
TEMPLATE;

$template_checkbox = <<<TEMPLATE
<div class="ligne_form">#{field} : #{label} #{description} #{errors}</div>
TEMPLATE;


/*
 * Quand le formulaire est soumis, on vérifie si on a un id pour update, sinon on créé.
 */
if ($form->is_submitted() and $form->validate()) {
	if ($id_user > 0) {
		switch ($user->update($id_user, $form->values())) {
			case User::UPDATED :
				$message = '<div class="message_succes">'.$dico->t("UtilisateurSauvegarde").'</div>';
				$form->reset();
				break;
			case User::ALLREADYEXISTS :
				$message = '<div class="message_error">'.$dico->t("UtilisateurExiste").'</div>';
				break;
		}
	}
	else {
		switch ($user->create($form->values())) {
			case User::CREATED :
				$message = '<div class="message_succes">'.$dico->t("UtilisateurCree").'</div>';
				$form->reset();
				break;
			case User::ALLREADYEXISTS :
				$message = '<div class="message_error">'.$dico->t("UtilisateurExiste").'</div>';
				break;
		}
	}
}
else {
	$form->reset();
}


/*
 * on récupère les données de l'utilisateur
 */
if ($id_user > 0) {
	$user_data = $user->load(array('id' => $id_user));
}
else {
	$user_data = array('login'=>"","password"=>"","email"=>"","acces"=>"","id_groupes_users"=>"");
	$id_user = 0;
}
$user_profils = $user->list_profils();
$user_langues = $user->list_langues();


/*
 * On détermine le message à afficher
 */
if (isset($message)) {
	$message = $message;
}
else {
	$message = "";
}



/* 
 * Valeurs renvoyées dans le template
 */

$titre_page = $dico->t("ModifierUtilisateur");
$buttons['list'] = $page->l($dico->t("VoirListeUtilisateurs"), $url->make("UserList", array('action' => "list")));

$form_start = $form->form_start();

$main = <<<HTML
$message
{$form->fieldset_start($dico->t("SonIdentite"))}
{$form->input(array('name' => "login", 'label' => $dico->t("Login"), 'value' => $user_data['login']))}
{$form->input(array('name' => "password", 'type' => "password", 'label' => $dico->t("Password"), 'description' => $dico->t("VidePasDeChangements")))}
{$form->input(array('name' => "email", 'label' => "Email", 'value' => $user_data['email']))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("SesDroits"))}
{$form->input(array('name' => "acces", 'type' => "checkbox", 'label' => $dico->t("Acces"), 'value' => "1", 'template' => $template_checkbox, 'checked' => ($user_data['acces'] == 1)))}
{$form->select(array('name' => 'id_groupes_users', 'label' => $dico->t("Profils"), 'id' => 'id_groupes_users', 'options' => $user_profils, 'value' => $user_data['id_groupes_users'], 'template' => $template_profils))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("SesPreferences"))}
{$form->select(array('name' => 'id_langues', 'label' => $dico->t("Langue"), 'options' => $user_langues, 'value' => $user_data['id_langues'], 'template' => $template_profils))}
{$form->fieldset_end()}
{$form->fieldset_start($dico->t("Validation"))}
{$form->input(array('name' => "update", 'type' => "submit", 'value' => $dico->t("Sauvegarder"), 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;

$form->default_values['blogs'] = array_fill_keys($user->blogs($id_user), 1);
$blogs_checkboxes = "";
$blogs = array(0 => $dico->t('TousBlogs'));
foreach ($blog->liste() as $cle => $valeur) {
	$blogs[$cle] = $valeur;
}
if (count($blogs)) {
	$blogs_checkboxes .= '<ul class="blogs">';
	foreach ($blogs as $id_blog => $blog) {
		$blogs_checkboxes .= '<li class="blogs">';
		if (isset($form->default_values['blogs'][0]) and $id_blog != 0) {
			$blogs_checkboxes .= $blog;
		}
		else {
			$blogs_checkboxes .= $form->input(array(
				'type' => "checkbox",
				'name' => "blogs[$id_blog]",
				'id' => "blogs-{$id_blog}",
				'label' => $blog,
				'template' => "#{field}#{label}",
				'value' => 1,
			));
		}
		$blogs_checkboxes .= '</li>';
	}
	$blogs_checkboxes .= '</ul>';
}

$right = <<<HTML
{$form->fieldset_start("Blogs")}
{$blogs_checkboxes}
{$form->fieldset_end()}
HTML;

$form_end = $form->form_end();
