<?php

$menu->current('main/content/blogs');

$config->core_include("database/tools", "blog/blog");
$config->core_include("outils/mysql", "outils/form", "outils/langue");
$config->core_include("outils/filter", "outils/pager");
$config->base_include("functions/tree");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("confirm.js");
$page->javascript[] = $config->media("blog.js");
$page->jsvars[] = array(
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$sql = new Mysql($config->db());
$blog = new Blog($sql);

$langue = new Langue($sql);

$action = $url2->get('action');
if ($id = $url2->get('id')) {
	$blog->load($id);
}

$form = new Form(array(
	'id' => "form-blog",
	'class' => "form-blog",
	'actions' => array("save", "delete"),
));

$form->template = <<<TEMPLATE
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
TEMPLATE;

$template_checkbox = <<<TEMPLATE
<div class="ligne_form">#{field} : #{label} #{description} #{errors}</div>
TEMPLATE;

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case 'delete':
			$blog->delete($data);
			$form->reset();
			$url2->redirect("current", array('action' => "", 'id' => ""));
			break;
		default:
			if ($action == "edit" or $action == "create") {
				$id = $blog->save($data);
				$form->reset();
				if ($action != "edit") {
					$url2->redirect("current", array('action' => "edit", 'id' => $id));
				}
				$blog->load($id);
			}
			break;
	}
}

$main = "";

$form_start = $form->form_start();

if ($action == "edit") {
	$form->default_values['blog'] = $blog;
	$form->default_values['themes'] = array_fill_keys($blog->themes($user->blogs()), 1);
	$form->default_values['langues'] = array_fill_keys($blog->langues($user->blogs()), 1);

	$main .= $form->input(array('type' => "hidden", 'name' => "blog[id]"));
}

if ($action == "create" or $action == "edit") {
	$main .= <<<HTML
{$form->fieldset_start("Blog")}
{$form->input(array('name' => "blog[nom]", 'label' => "Nom"))}
{$form->input(array('type' => "checkbox", 'name' => "blog[access]", 'value' => 1, 'label' => $dico->t('Acces'), 'template' => $template_checkbox))}
{$form->fieldset_end()}
HTML;
	$langues_checkboxes = "";
	$langues = $langue->liste();
	if (count($langues)) {
		$langues_checkboxes .= '<ul class="langues">';
		foreach ($langues as $id_lang => $lang) {
			$langues_checkboxes .= '<li class="langues">';
			$langues_checkboxes .= $form->input(array(
				'type' => "checkbox",
				'name' => "langues[$id_lang]",
				'id' => "langues-{$id_lang}",
				'label' => $lang,
				'template' => "#{field}#{label}",
				'value' => 1,
			));
			$langues_checkboxes .= '</li>';
		}
		$langues_checkboxes .= "</ul>";
	}
	$right = <<<HTML
{$form->fieldset_start("Langues")}
{$langues_checkboxes}
{$form->fieldset_end()}
HTML;
}

if ($action == "edit") {
	$themes = print_link_tree(DBTools::tree($blog->all_themes($id)), $url2->make("blog", array('type' => "themes", 'action' => "edit")), "themes");
	$right .= <<<HTML
{$form->fieldset_start($dico->t("Themes"))}
{$themes}
{$form->fieldset_end()}
HTML;
}

switch ($action) {
	case "create" :
		$titre_page = $dico->t('CreerBlog');
		$main .= <<<HTML
{$form->fieldset_start($dico->t('Validation:'))}
{$form->input(array('name' => "create", 'type' => "submit", 'value' => $dico->t("Creer"), 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;
		$buttons[] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create")));
		$buttons[] = $page->l($dico->t("VoirListe"), $url2->make("current", array('action' => "list")));
		break;
	case "edit" :
		$titre_page = $dico->t('EditerBlog')." #".$id;
		$main .= <<<HTML
{$form->fieldset_start($dico->t('Validation:'))}
{$form->input(array('name' => "save", 'type' => "submit", 'value' => "Sauvegarder", 'template' => "#{field}"))}
{$form->input(array('name' => "delete", 'class' => "confirm-delete", 'type' => "submit", 'value' => "Supprimer", 'template' => "#{field}"))}
{$form->fieldset_end()}
HTML;
		$buttons[] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create")));
		$buttons[] = $page->l($dico->t('BilletdeBlog'), $url2->make("current", array('action' => "posts")));
		$buttons[] = $page->l($dico->t("VoirListe"), $url2->make("current", array('action' => "list")));
		break;
	case "posts" :
		$titre_page = $dico->t('BilletsduBlog')." #".$id;
		$langues_list = implode(",", $blog->langues());
		$themes_list = implode(",", $blog->themes());
		$q = <<<SQL
SELECT DISTINCT(b.id), b.titre, lg.code_langue, b.affichage, b.date_affichage
FROM dt_billets AS b
INNER JOIN dt_langues AS lg ON lg.id = b.id_langues
INNER JOIN dt_billets_themes_blogs AS btb ON btb.id_billets = b.id
WHERE 1
SQL;
		$q .= $langues_list ? " AND lg.id IN ($langues_list)" : " AND 0";
		$q .= $themes_list ? " AND btb.id_themes_blogs IN ($themes_list)" : " AND 0";

		$page->javascript[] = $config->core_media("filter-edit.js");
		$page->jsvars[] = array(
			"edit_url" => $url2->make("contenu", array('type' => "blogpost", 'action' => 'edit', 'id' => "")),
		);

		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'field' => 'b.id',
				'title' => 'ID',
				'type' => 'between',
				'order' => 'ASC',
			),
			'titre' => array(
				'field' => 'b.titre',
				'title' => $dico->t('Titre'),
				'type' => 'contain',
			),
			'id_langues' => array(
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
			)
		), array(), "filter_blog_billets");
		$res = $filter->query($q);
		$filter->fetchall($res);
		$main = <<<HTML
{$page->inc("snippets/filter")}
HTML;
		$buttons[] = $page->l($dico->t('RetourBlog'), $url2->make("current", array('action' => "edit")));
		break;
	case "list" :
	default :
		$page->javascript[] = $config->core_media("filter-edit.js");
		$page->jsvars[] = array(
			"edit_url" => $url2->make("current", array('type' => "blogs", 'action' => 'edit', 'id' => "")),
		);
		$titre_page = $dico->t('ListeOfBlogs');
		$q = <<<SQL
SELECT id, nom, `access` FROM dt_blogs
SQL;
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'title' => 'ID',
				'type' => 'between',
				'order' => 'ASC',
			),
			'nom' => array(
				'title' => $dico->t('Nom'),
				'type' => 'contain',
			),
			'access' => array(
				'title' => $dico->t('Acces'),
				'type' => 'select',
				'options' => array(1 => 'oui', 0 => 'non'),
			),
		), array(), "filter_blogs");
		$res = $filter->query($q);
		$filter->fetchall($res);
		$main = <<<HTML
{$page->inc("snippets/filter")}
HTML;
		$buttons[] = $page->l($dico->t("Nouveau"), $url2->make("current", array('action' => "create")));
		break;
}

$form_end = $form->form_end();
