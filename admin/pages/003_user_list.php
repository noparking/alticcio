<?php
$config->core_include("extranet/user", "outils/mysql");

$menu->current('main/params/users');

$sql = new Mysql($config->db());
$user = new User($sql);


/*
 * Si on delete comme action dans l'URL, on supprime l'enregistrement
 */
if ($url->get("action") == "delete") {
	$user->delete($url->get("id"));
	$message = $dico->t("UtilisateurSupprime");
}

/*
 * Tableau des utilisateurs
 */
function view_acces($acces) {
	$html = '<span class="acces_off">&nbsp;</span>';
	if ($acces == 1) {
		$html = '<span class="acces_on">&nbsp;</span>';
	}
	return $html;
}

$table = <<<HTML
<table>
	<tr>
		<th>{$dico->t("Utilisateurs")}</th>
		<th>{$dico->t("Emails")}</th>
		<th>{$dico->t("GroupesUsers")}</th>
		<th>{$dico->t("Acces")}</th>
		<th colspan="2">{$dico->t("Actions")}</th>
	</tr>
HTML;
foreach ($user->get_list() as $id => $login) {
	$actions = array(
		$page->l($dico->t("Editer"), $url->make("UserEdit", array('action' => "edit", 'id' => $id))),
		$page->l($dico->t("Supprimer"), $url->make("UserList", array('action' => "delete", 'id' => $id))),
	);
	$actions = implode('</td><td class="align_center">', $actions);
	$view_acces = view_acces($login['acces']);
	$table .= <<<HTML
	<tr>
		<td>{$login['login']}</td>
		<td>{$login['email']}</td>
		<td class="align_center">{$login['profil']}</td>
		<td class="align_center">{$view_acces}</td>
		<td class="align_center">{$actions}</td>
	</tr>
HTML;
}
$table .= <<<HTML
</table>
HTML;

if (isset($message)) {
	$message = '<div class="message">'.$message.'</div>';
}
else {
	$message = "";
}




/* 
 * Valeurs renvoyées dans le template
 */

$titre_page = $dico->t('ListeUtilisateurs');
$buttons['new'] = $page->l($dico->t("Nouveau"), $url->make("UserEdit", array('action' => "edit", 'id' => "")));

$main = <<<HTML
	$message
	$table
HTML;

$right = "";

?>
