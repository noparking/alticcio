<?php

$config->core_include("database/tools", "outils/mysql", "outils/form", "outils/phrase", "outils/langue", "outils/filter", "outils/pager");
$config->core_include("blog/blogpost", "blog/blogtheme", "outils/url_redirection");
$config->base_include("functions/tree");

$sql = new Mysql($config->db());
$blogpost = new Blogpost($sql);
$produits = array(); 
if($id_billets = $url2->get('id')) {
	$blogpost->load($id_billets);
	$produits = $blogpost->produits(); 
}

$langue = new Langue($sql);
$id_langues = $langue->id($config->get("langue"));

$theme = new BlogTheme($sql);

$url_redirection = new UrlRedirection($sql);

$user_data = $user->data();


/*
 * Javascripts et CSS
 */
$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("jquery.form.js");
$page->javascript[] = $config->core_media("jquery.dteditor.js");
$page->javascript[] = $config->media("blog.js");
$page->javascript[] = $config->media("produit.js");
$page->javascript[] = $url->make("DTEditor");
$page->css[] = $config->media("dteditor.css");
$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('action' => 'edit', 'id' => "")),	
);

$page->css[] = $config->media("produit.css");

/*
 * Variables de navigation
 */
$menu->current('main/content/blogpost');

$action = $url2->get('action');
if ($action == "image-upload") {
	$path_upload = $config->get('base_path')."medias/images/blogpost/";
	$url_upload = $config->get('base_url')."medias/images/blogpost";
	exit ($blogpost->upload_image($path_upload, $url_upload));
}



/*
 * Fonctions diverses
 */
function formater_date($date, $format_date) {
	if ($format_date == "fr") {
		$format = date('d-m-Y',$date);
	}
	else {
		$format = date('Y-m-d',$date);
	}
	return $format;
}

/*
 * Création du formulaire
 */
$form = new Form(array(
	'id' => "form-blogpost",
	'class' => "form-blogpost",
	'actions' => array("save", "delete", "add-theme"),
	'check' => array(
		'blogpost[texte]' => array("validate_html"),
	),
));
$form->fields_error_messages = array(
	'blogpost[texte]' => array(
		'validate_html' => "Le contenu HTML du billet de blog n'est pas valide.",
	),
);

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

$template_checkbox = <<<TEMPLATE
<div class="ligne_form">#{field} : #{label} #{description} #{errors}</div>
TEMPLATE;

$template_date = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors} #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

$message = "";

$section = "contenu";
if ($form->value('section')) {
	$section = $form->value('section');
}
$traduction = $form->value("lang");

$filter_schema_produits = array(
	'id' => array(
		'title' => 'ID',
		'type' => 'between',
		'order' => 'DESC',
		'field' => 'pr.id',
	),
	'ref' => array(
		'title' => $dico->t('Reference'),
	),
	'nom' => array(
		'title' => $dico->t('Nom'),
		'type' => 'contain',
		'field' => 'ph.phrase',
	),
	'classement' => array(
		'title' => $dico->t('Classement'),
		'type' => 'between',
		'field' => 'bp.classement',
		'form' => array(
			'name' => 'produits[%id%][classement]',
			'method' => 'input',
			'type' => 'text',
			'template' => '#{field}',
		),
	),
);
$pager_produits = new Pager($sql, array(10, 30, 50, 100, 200), "pager_produits");
$filter_produits = new Filter($pager_produits, $filter_schema_produits, array_keys($produits), "filter_produits", true);

/*
 * Traitement du formulaire
 */
if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "translate":
		case "filter":
		case "pager":
		case "reload":
			break;
		case "add-theme":
			$data['theme']['affichage'] = 1;
		 	$theme->save($data);
			break;
		case "delete":
			$blogpost->delete();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default:
			if ($form->validate()) {
				$data['blogpost']['id_users'] = $_SESSION['extranet']['user']['id'];
				$filter_produits->clean_data($data, "produits");
				$id_billets = $url_redirection->save_object($blogpost, $data, array('titre_url' => "titre"));
				if ($id_billets === false) {
					$messages[] = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
				}
				else {
					switch($id_billets) {
						case Blogpost::UNCOMPLETED :
							$message = '<div class="message_error">'.$dico->t('VousDevezRenseignerTitre').'</div>';
							break;
						case Blogpost::UNDATED :
							$message = '<div class="message_error">'.$dico->t('VousDevezRenseignerDate').'</div>';
							break;
						default :
							$message = '<div class="message_succes">'.$dico->t('VosDonneesSauvegardees').'</div>';
							$form->reset();
							if ($action != "edit") {
								$url2->redirect("current", array('action' => "edit", 'id' => $id_billets));
							}
							break;
					};
				}
			}
			break;
	}
}
else {
	$form->reset();
}

if ($form->changed()) {
	$messages[] = '<p class="message">'.$dico->t('AttentionNonSauvergarde').'</p>';
}
else {
	$filter_produits->select(array_keys($blogpost->produits()));
}

/*
 * Rendu HTML
 */

if ($action == "create") {
	$titre_page =  $dico->t("CreerBillet");
}
else if ($action == "edit") {
	$titre_page =  $dico->t("EditerBillet");
	$form->default_values['blogpost'] = $blogpost->values;
	$form->default_values['themes'] = $blogpost->themes;
	$form->default_values['produits'] = $produits;
}
$buttons['new'] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create", 'id' => "")));

$blogs = $user->blogblogs();
$id_blog = isset($data['id_blog']) ? $data['id_blog'] : key($blogs);

if ($action == "create" or $action == "edit") {
	$buttons[] = $form->input(array('name' => "save", 'type' => "submit", 'value' => $dico->t("Enregistrer"), 'template' => "#{field}"));
	$buttons['list'] = $page->l($dico->t("VoirListe"), $url2->make("current", array('action' => "list", 'id' => "")));
	$sections = array(
		'contenu' => $dico->t('Contenu'),
		'referencement' => $dico->t('Referencement'),
		'themes' => $dico->t('Themes'),
		'produits' => $dico->t('Produits'),
	);

	// variable $hidden mise à jour dans ce snippet
	$left = $page->inc("snippets/produits-sections");

	$form_start = $form->form_start();
	$main = <<<HTML
{$message}
{$form->input(array('type' => "hidden", 'name' => "section", 'value' => $section))}
{$form->fieldset_start(array('legend' => $dico->t('VotreBillet:'), 'class' => "produit-section produit-section-contenu".$hidden['contenu'], 'id' => "produit-section-contenu"))}
{$form->input(array('name' => "blogpost[titre]", 'id' => 'titre', 'label' => $dico->t("Titre")))}
{$form->textarea(array('name' => "blogpost[texte]", 'id' => 'texte', 'class' => "dteditor", 'label' => $dico->t("Texte")))}
{$form->select(array('name' => "blogpost[id_langues]", 'id' => "id_langues", 'label' => $dico->t("Langue"), 'options' => $user->bloglangues(), 'template' => $template_date))}
{$form->input(array('type' => "checkbox", 'name' => "blogpost[affichage]", 'id' => 'checkbox', 'value' => 1, 'label' => $dico->t("Affichage"), 'template' => $template_checkbox))}
{$form->date(array('name' => "blogpost[date_affichage]", 'id' => 'date_affichage', 'label' => $dico->t("DateAffichage"), 'format' => $dico->d("FormatDate") ))}
{$form->fieldset_end()}
{$form->fieldset_start(array('legend' => $dico->t('Referencement:'), 'class' => "produit-section produit-section-referencement".$hidden['referencement'], 'id' => "produit-section-referencement"))}
{$form->input(array('name' => "blogpost[meta_title]", 'id' => 'meta_title', 'label' => $dico->t("MetaTitle")))}
{$form->textarea(array('name' => "blogpost[meta_description]", 'id' => 'meta_description', 'label' => $dico->t("MetaDescription")))}
{$form->textarea(array('name' => "blogpost[meta_keywords]", 'id' => 'meta_keywords', 'label' => $dico->t("MetaKeywords")))}
{$form->input(array('name' => "blogpost[titre_url]", 'id' => 'titre_url', 'label' => $dico->t("TitreURL")))}
{$form->fieldset_end()}
HTML;
	$themes_checkboxes = "<ul>";
	foreach ($blogs as $idblog => $blog) {
		$themes_checkboxes .= "<li>";
		$themes_checkboxes .= "<h3>$blog</h3>";
		$themes_checkboxes .= print_checkbox_tree(DBTools::tree($theme->all_themes($idblog)), $form, array(), "themes") ;
		$themes_checkboxes .= "</li>";
	}
	$themes_checkboxes .= "</ul>";
	$themes_options = options_select_tree(DBTools::tree($theme->all_themes($id_blog)), $form, "themes");
	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('NouveauTheme'), 'class' => "produit-section produit-section-themes".$hidden['themes'], 'id' => "produit-section-nouveau-theme"))}
{$form->input(array('name' => "theme[nom]", 'label' => $dico->t('Nom') ))}
{$form->select(array('name' => "id_blog", 'label' => $dico->t('Blog'), 'options' => $blogs))}
{$form->select(array('name' => "theme[id_parent]", 'label' => $dico->t('ThemeParent'), 'options' => $themes_options))}
{$form->input(array('name' => "add-theme", 'type' => "submit", 'value' => $dico->t('Ajouter'), 'template' => "#{field}"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => $dico->t('Themes'), 'class' => "produit-section produit-section-themes".$hidden['themes'], 'id' => "produit-section-themes"))}
{$themes_checkboxes}
{$form->fieldset_end()}
HTML;

	$main .= <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Produits'), 'class' => "produit-section produit-section-produits".$hidden['produits'], 'id' => "produit-section-produits"))}
HTML;
	$pager = $pager_produits;
	$filter = $filter_produits;
	$blogpost->all_produits($id_langues, $filter);
	$main .= $page->inc("snippets/filter-form");
	foreach ($filter->selected() as $selected_produit) {
		$main .= $form->hidden(array('name' => "produits[$selected_produit][classement]"));
	}

	$main .= <<<HTML
{$form->fieldset_end()}
HTML;
	if ($action == "edit") {
		$main .= $form->input(array('type' => "hidden", 'name' => "blogpost[id]"));
	}
	$form_end = $form->form_end();
}
else {
	$page->javascript[] = $config->core_media("filter-edit.js");
	$titre_page =  $dico->t("GestionBillets");
	$q = <<<SQL
SELECT DISTINCT(b.id), b.titre, u.login, lg.code_langue, b.affichage, b.date_affichage 
FROM dt_billets AS b
LEFT OUTER JOIN dt_billets_themes_blogs AS btb ON btb.id_billets = b.id
LEFT OUTER JOIN dt_langues AS lg ON lg.id = b.id_langues
LEFT OUTER JOIN dt_themes_blogs AS tb ON tb.id = btb.id_themes_blogs
LEFT OUTER JOIN dt_users AS u ON u.id = b.id_users
SQL;
	if ($user_data['id_groupes_users'] != 99) {
		$q .= "\n".<<<SQL
LEFT OUTER JOIN dt_blogs_themes_blogs AS bltb ON bltb.id_themes_blogs = tb.id 
LEFT OUTER JOIN dt_users_blogs AS ub ON ub.id_blogs = bltb.id_blogs
WHERE ub.id_users = {$user_data['id']} OR b.id_users = {$user_data['id']}
SQL;
	}
	$pager = new Pager($sql, array(20, 30, 50, 100, 200));
	$filter = new Filter($pager, array(
		'id' => array(
			'field' => 'b.id',
			'title' => 'ID',
			'type' => 'between',
			'order' => 'DESC',
		),
		'titre' => array(
			'field' => 'b.titre',
			'title' => $dico->t('Titre'),
			'type' => 'contain',
		),
		'login' => array(
			'field' => 'u.login',
			'title' => $dico->t('Auteur'),
			'type' => 'contain',
		),
		'code_langue' => array(
			'field' => 'lg.code_langue',
			'title' => $dico->t('Langue'),
			'type' => 'equal',
		),
		'affichage' => array(
			'field' => 'b.affichage',
			'title' => $dico->t('Affichage'),
		),
		'date_affichage' => array(
			'field' => 'b.date_affichage',
			'title' => $dico->t('Date'),
			'type' => "date",
		)
	), array(), "filter_blogpost");
	$res = $filter->query($q);
	$filter->fetchall($res);
	$main = <<<HTML
<form action="" method="post">
{$page->inc("snippets/filter")}
</form>
HTML;
}

foreach ($form->errors() as $error) { 
	$messages[] = <<<HTML
<p class="message">
	$error
</p>
HTML;
}

$main = $page->inc("snippets/messages").$main;
