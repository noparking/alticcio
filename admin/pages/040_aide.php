<?php

$titre_page = $dico->t("Aide");

$config->core_include("blog/blog", "blog/blogtheme", "database/tools");
$config->base_include("functions/tree");

$blog = new Blog($sql);

$blogtheme = new BlogTheme($sql);

$id_blogs = $config->get("blog_aide");
$blog->load($id_blogs);

$billets = $blog->billets($id_blogs, 10);

$all_themes = DBTools::tree($blog->all_themes($id_blogs));

$aide_themes = print_callback_tree($all_themes, "print_theme_or_billet");
$left = '<section class="aide_themes">'.$aide_themes.'</section>';

function print_theme_or_billet($element) {
	global $blogtheme, $url0;

	if (!count($element['children'])) { // on n'affiche pas les billets dans des thèmes ayant des sous-thèmes
		$blogtheme->load($element['id']);
		$links = "{$element['nom']}";
		$links .= "<ul class='tree'>";
		foreach ($blogtheme->billets() as $billet) {
			$links .= "<li class='tree'>";
			$links .= "<a href='{$url0->make("current", array('id' => $billet['id']))}'>{$billet['titre']}</a>";
			$links .= "</li>";
		}
		$links .= "</ul>";
		
		return $links;
	}
	else {
		return "{$element['nom']}";
	}
}

$main = '<section class="aide_texte"></section>';

if ($id = $url0->get('id')) {
	$config->core_include("blog/blogpost");
	$blogpost = new Blogpost($sql);
	$blogpost->load($id);
	$values = $blogpost->values;

	$titre_page = $values['titre'];

	$main = '<section class="aide_texte">'.$values['texte'].'</section>';
}

