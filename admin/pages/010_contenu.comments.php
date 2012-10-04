<?php

$menu->current('main/content/comments');

$config->core_include("outils/form", "outils/mysql");
$config->core_include("blog/commentaire");
$config->core_include("outils/filter", "outils/pager");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("confirm.js");
$page->inc("snippets/date-input");
$page->jsvars[] = array(
	"edit_url" => $url4->make("current", array('action' => "edit", 'id' => "")),
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);

$titre_page = $dico->t('Commentaires');

$sql = new Mysql($config->db());
$commentaire = new Commentaire($sql);

$action = $url4->get('action');
$nature = $url4->get('nature');
$id = $url4->get('id');

$form_id = "form-$action-$nature-$id"; 
$form = new Form(array(
	'id' => $form_id,
	'class' => "form-edit",
	'actions' => array("save", "delete"),
	'date_format' => $dico->d('FormatDate'),
));
$status_options = array('' => "", 'approved' => $dico->t('Approved'), 'disapproved' => $dico->t('Disapproved'), 'spam' => $dico->t('Spam'));

$form->template = <<<HTML
<div class="ligne_form">#{label} : #{errors}<br/> #{field} <br/><span class="help_form">#{description}</span></div>
HTML;

$messages = array();

if ($form->is_submitted()) {
	$data = $form->escaped_values();
	switch ($form->action()) {
		case "delete" :
			$commentaire->delete($data);
			$form->reset();
			$url4->redirect("current", array('action' => "", 'id' => ""));
			break;
		default :
			$id = $commentaire->save($data);
			$form->reset();
			if ($action != "edit") {
				$url4->redirect("current", array('action' => "edit", 'id' => $id));
			}
			$commentaire->load($id);
			break;
	}
}

if ($id and $action == "edit") {
	$commentaire->load($id);
	$form->default_values['commentaire'] = $commentaire;
}

if ($action == "create" or $action == "edit") {
	$form_start = $form->form_start();
	$main = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Commentaire'), 'class' => "comment", 'id' => "comment"))}
{$form->input(array('type' => "hidden", 'name' => "commentaire[nature]", 'value' => $nature))}
{$form->textarea(array('name' => "commentaire[texte]", 'label' => $dico->t('Texte') ))}
{$form->select(array('name' => "commentaire[status]", 'label' => $dico->t('Statut'), 'options' => $status_options))}
{$form->input(array('type' => "checkbox", 'name' => "commentaire[affichage]", 'label' => $dico->t('Affichage'), 'template' => "#{field} #{label}"))}
{$form->date(array('name' => "commentaire[date_creation]", 'label' => $dico->t('DateCreation') ))}
{$form->input(array('name' => "commentaire[nom_auteur]", 'label' => $dico->t('Auteur') ))}
{$form->input(array('name' => "commentaire[ip_auteur]", 'label' => $dico->t('AdresseIP') ))}
{$form->input(array('name' => "commentaire[email_auteur]", 'label' => $dico->t('Email') ))}
{$form->fieldset_end()}
HTML;
	$buttons[] = $form->input(array('type' => "submit", 'name' => "save", 'value' => $dico->t('Enregistrer') ));
	$buttons[] = $page->l($dico->t('ListeOfCommentaires'), $url4->make("current", array('action' => "list", "id" => $commentaire->item)));
	$form_end = $form->form_end();
}

if ($action == "create") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "commentaire[item]", 'value' => $id))}
HTML;
}

if ($action == "edit") {
	$main .= <<<HTML
{$form->input(array('type' => "hidden", 'name' => "commentaire[item]"))}
{$form->input(array('type' => "hidden", 'name' => "commentaire[id]", 'value' => $id))}
HTML;
	$buttons[] = $form->input(array('type' => "submit", 'name' => "delete", 'value' => $dico->t('Supprimer')));
}

switch ($nature) {
	case 'blog' :
		if ($id) {
			$titre_page = $dico->t('CommentairesDuBlog')." #".$id;
		}
		else {
			$titre_page = $dico->t('CommentairesDeBlog');
		}
		break;
	case 'produit' :
		if ($id) {
			$titre_page = $dico->t('CommentairesDuProduit')." #".$id;
		}
		else {
			$titre_page = $dico->t('CommentairesDesProduits');
		}
		break;
}

switch ($action) {
	case "create" :
		switch ($nature) {
			case "blog" :
				$titre_page = $dico->t('CreerNouveauCommentaireBlog')." #".$id;
				break;
			case "produit" :
				$titre_page = $dico->t('CreerNouveauCommentaireProduit')." #".$id;
				break;
		}
		break;
	case "edit" :
		$titre_page = $dico->t('EditionCommentaire')." #".$id;
		break;
	case "list" :
	default :
		$q = <<<SQL
SELECT id, nom_auteur, date_creation, status
FROM dt_commentaires
WHERE 1
SQL;
		if ($id) {
			$q .= " AND item = $id";
		}
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'title' => 'ID',
				'type' => 'between',
				'order' => 'DESC',
			),
			'nom_auteur' => array(
				'title' => $dico->t('Auteur'),
				'type' => 'contain',
			),
			'date_creation' => array(
				'title' => $dico->t('DateCreation'),
				'type' => "date",
			),
			'status' => array(
				'title' => $dico->t('Statut'),
				'type' => 'select',
				'options' => array('approved' => $dico->t('Approved'), 'disapproved' => $dico->t('Disapproved'), 'spam' => $dico->t('Spam') ),
			),
		), array(), "filter_contenu_comments");
		$display_filter = true;
		switch ($nature) {
			case "blog" :
				if ($id) {
					$buttons[] = $page->l($dico->t('NouveauCommentaire'), $url4->make("current", array('action' => "create")));
				}
				$q .= " AND nature='blog'";
				break;
			case "produit" :
				$q .= " AND nature='produit'";
				break;
			default :
				$display_filter = false;
				$main = <<<HTML
<ul class="liens_niveau2">
<li>{$page->l($dico->t('CommentairesDesBlogs'), $url4->make("current", array('type' => "comments", 'action' => "list", 'nature' => "blog")))}</li>
<li>{$page->l($dico->t('CommentairesDesProduits'), $url4->make("current", array('type' => "comments", 'action' => "list", 'nature' => "produit")))}</li>
</ul>
HTML;
				break;
		}
		if ($display_filter) {
			$res = $filter->query($q);
			$filter->fetchall($res);
			$main = <<<HTML
{$page->inc("snippets/filter")}
HTML;
		}
		break;
}
