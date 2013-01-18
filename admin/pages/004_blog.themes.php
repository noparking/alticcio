<?php

$menu->current("main/content/themes");
$config->core_include("database/tools", "blog/blogtheme", "outils/url_redirection");
$config->core_include("outils/mysql", "outils/form", "outils/langue");
$config->core_include("outils/filter", "outils/pager");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("confirm.js");
$page->javascript[] = $config->media("blog.js");
$page->jsvars[] = array(
	"edit_url" => $url2->make("current", array('type' => "themes", 'action' => 'edit', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$sql = new Mysql($config->db());
$theme = new BlogTheme($sql);
$url_redirection = new UrlRedirection($sql);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$theme->load($id);
}

$form = new Form(array(
	'id' => "form-blog-themes",
	'class' => "form-blog-themes",
	'actions' => array("delete", "reload"),
));

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

$template_checkbox = <<<TEMPLATE
<div class="ligne_form">#{field} : #{label} #{description} #{errors}</div>
TEMPLATE;

$message = "";

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case 'reload' : break;
		case 'delete':
			$theme->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default:
			if ($action == "edit" or $action == "create") {
				if ($form->validate()) {
					$id_saved = $url_redirection->save_object($theme, $data, array('titre_url' => "nom"));
					if ($id_saved === false) {
						$message = '<p class="message">'."Le code URL est déjà utilisé !".'</p>';
					}
					else if ($id_saved > 0) {
						$form->reset();
					}
				}
			}
			break;
	}
}

$main = $message;

$form_start = $form->form_start();

if ($action == "edit") {
	$form->default_values['theme'] = $theme;
	$main .= $form->input(array('type' => "hidden", 'name' => "theme[id]"));
}

if ($action == "create" or $action == "edit") {
	$blogs = $theme->blogs();
	$id_blog = isset($data['id_blog']) ? $data['id_blog'] : (isset($theme->id_blogs) ? $theme->id_blogs : key($blogs));
	$form->default_values['id_blog'] = $id_blog;
	$themes = options_select_tree(DBTools::tree($theme->all_themes($id_blog)), $form, "themes");
	$main  .= <<<HTML
{$form->fieldset_start($dico->t('ThemeBlog'))}
{$form->input(array('name' => "theme[nom]", 'label' => $dico->t('Nom') ))}
{$form->input(array('type' => "checkbox", 'name' => "theme[affichage]", 'value' => 1, 'label' => $dico->t('Affichage'), 'template' => $template_checkbox))}
{$form->select(array('name' => "id_blog", 'label' => $dico->t('Blog'), 'options' => $blogs))}
{$form->select(array('name' => "theme[id_parent]", 'label' => $dico->t('ThemeParent'), 'options' => $themes))}
{$form->input(array('name' => "theme[titre_url]", 'label' => "Titre URL" ))}
{$form->fieldset_end()}
HTML;
}

switch ($action) {
	case "create" :
		$titre_page = $dico->t('CreerThemeBlog');
		$main .= <<<HTML
{$form->fieldset_start($dico->t('Validation:'))}
{$form->input(array('name' => "create", 'type' => "submit", 'value' => $dico->t("Creer"), 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;
		$buttons['new'] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create", 'id' => "")));
		$buttons['list'] = $page->l($dico->t("VoirListe"), $url2->make("current", array('action' => "list", 'id' => "")));
		break;

	case "edit" :
		$titre_page = $dico->t('EditerThemeBlog')." #".$id;
		$main .= <<<HTML
{$form->fieldset_start($dico->t('Validation:'))}
{$form->input(array('name' => "save", 'type' => "submit", 'value' => $dico->t('Sauvegarder'), 'template' => "#{field}"))}
{$form->input(array('name' => "delete", 'class' => "confirm-delete", 'type' => "submit", 'value' => $dico->t('Supprimer'), 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;
		$buttons['new'] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create")));
		$buttons['list'] = $page->l($dico->t("VoirListe"), $url2->make("current", array('action' => "list")));
		break;

	case "list" :
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$titre_page = $dico->t('ThemesBlog');

		$q = <<<SQL
SELECT tb.id, tb.nom, tb2.nom AS parent, b.nom AS blog, tb.affichage
FROM dt_themes_blogs AS tb
LEFT OUTER JOIN dt_themes_blogs AS tb2 ON tb2.id = tb.id_parent
LEFT OUTER JOIN dt_blogs_themes_blogs AS btb ON btb.id_themes_blogs = tb.id
LEFT OUTER JOIN dt_blogs AS b ON b.id = btb.id_blogs
SQL;
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'field' => 'tb.id',
				'title' => 'ID',
				'type' => 'between',
				'order' => 'ASC',
			),
			'nom' => array(
				'field' => 'tb.nom',
				'title' => $dico->t('Nom'),
				'type' => 'contain',
			),
			'parent' => array(
				'field' => 'tb2.nom',
				'title' => $dico->t('Parent'),
				'type' => 'contain',
			),
			'blog' => array(
				'field' => 'b.nom',
				'title' => $dico->t('Blog'),
				'type' => 'contain',
			),
			'affichage' => array(
				'field' => 'tb.affichage',
				'title' => $dico->t('Affichage'),
				'type' => 'select',
				'options' => array(1 => 'oui', 0 => 'non'),
			),
		), array(), "filter_blog_themes");
		$res = $filter->query($q);
		$filter->fetchall($res);
		$main = <<<HTML
{$page->inc("snippets/filter")}
HTML;
		$buttons['new'] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create")));
		break;
}

$form_end = $form->form_end();
