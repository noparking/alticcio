<?php

class Blogpages {
	
	
	public function __construct($sql, $dico, $id_blog) {
		$this->sql = $sql;
		$this->dico = $dico;
		$this->id_blog = $id_blog;
		// nb_posts correspond au nombre de billets maximum par page.
		$this->nb_posts = 1;
		// format de la date Fr ou En
		$this->format_date = "fr";
		
		$q = "SELECT id_langues FROM dt_blogs_langues WHERE id_blogs =".$this->id_blog;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		$this->id_langues = $row['id_langues'];
	}
	
	
	public function themes() {
		$q = "SELECT t.id, t.nom, t.id_parent 
				FROM dt_themes_blogs AS t
				INNER JOIN dt_blogs_themes_blogs AS bltbl
				ON bltbl.id_themes_blogs = t.id
				AND bltbl.id_blogs = ".$this->id_blog;
		$res = $this->sql->query($q);
		$themes = array();
		while ($row = $this->sql->fetch($res)) {
			$themes[] = $row;
		}
		return $themes;
	}
	
	
	private function clean_text($chaine) {
		$string = str_replace(" ","-",$chaine);
		return strtr(	$string, 
						'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
						'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	}
	
	
	private function afficher_menu($parent, $niveau, $array, $lien, $num_page = 0) {
		$html = "";
		$niveau_precedent = 0;
		if (!$niveau && !$niveau_precedent) {
			$html .= "<ul id=\"menu_blog\">";
		}
		foreach ($array AS $noeud) {
			if ($parent == $noeud['id_parent']) {
				if ($niveau_precedent < $niveau) {
					$html .= "<ul>";
				}
		 		$html .= "<li><a href=".$lien."/".$num_page."/".$noeud['id']."-".$this->clean_text($noeud['nom']).">".$noeud['nom']."</a>";
				$niveau_precedent = $niveau;
				$html .= $this->afficher_menu($noeud['id'], ($niveau + 1), $array, $lien, $num_page = 0);
		 	}
		}
		if (($niveau_precedent == $niveau) && ($niveau_precedent != 0)) {
			$html .= "</ul></li>";
		}
		else if ($niveau_precedent == $niveau) {
			$html .= "</ul>";
		}
		else {
			$html .= "</li>";	
		}
		return $html;
	}
	
	
	public function liste_themes($lien, $num_page) {
		$themes = $this->themes();
		$html = $this->afficher_menu(0,0,$themes,$lien,$num_page);
		return $html;
	}
	
	
	public function posts($id_themes = 0) {
		$themes = $this->themes();
		$q = "SELECT b.id, b.titre, b.texte, b.date_affichage, b.titre_url 
				FROM dt_billets AS b
				INNER JOIN dt_billets_themes_blogs AS t 
				ON t.id_billets = b.id 
				AND b.affichage = '1' 
				INNER JOIN dt_blogs_themes_blogs AS bt
				ON t.id_themes_blogs = bt.id_themes_blogs ";	
		if ((int)$id_themes > 0) {
			$q .= " AND bt.id_themes_blogs = ".(int)$id_themes;
		}
		else {
			if (count($themes) == 1) {
				$q .= " AND bt.id_themes_blogs = ".$themes[0]['id'];
			}
			else {
				$q .= "AND bt.id_themes_blogs IN (";
				$i=0;
				foreach($themes as $key => $theme) {
					if ($i>0) {
						$q .= ', ';
					}
					$q .= $theme['id'];
					$i++;
				}
				$q .= ")";
			}
//			$q .= " AND bt.id_blogs = ".$this->id_blog;
//			$q .= "	LEFT JOIN dt_blogs_langues AS lg
//				ON lg.id_blogs = ".$this->id_blog."
//				AND lg.id_langues = ".$this->id_langues." ";
		}


// LEFT JOIN dt_blogs_themes_blogs AS bt
// ON t.id_themes_blogs = bt.id_themes_blogs
// AND bt.id_blogs = $this->id_blog
		
// LEFT JOIN dt_blogs_langues AS lg
// ON lg.id_blogs = $this->id_blog
// AND lg.id_langues = $this->id_langues		
		
		
//AND b.id_langues = ".$this->id_langues." "			
//		if ($id_themes > 0) {
//			$q .= " AND t.id_themes_blogs = ".(int)$id_themes;
//		}
//		else {
//			if (count($themes) > 1) {
//				$q .= " AND t.id_themes_blogs IN (";
//				foreach($themes as $key => $theme) {
//					$q .= $theme['id'].', ';
//				}
//				$q .= ") ";
//			}
//			else {
//				$q .= " AND t.id_themes_blogs =".$themes[0]['id'];
//			}
//		}

		$res = $this->sql->query($q);
	
		$liste = array();
		while ($row = $this->sql->fetch($res)) {	
			$liste[$row['id']] = $row;
		}	
		return $liste;
	}
	
	
	private function formater_date($date) {
		if ($this->format_date == "fr") {
			$format = date('d-m-Y',$date);
		}
		else {
			$format = date('m-d-Y',$date);
		}
		return $format;
	}
	
	
	private function formater_key_url($chaine) {
		$chaine_bis = strtr($chaine,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
		$chaine_bis = quotemeta($chaine_bis);
		$chaine_bis = str_replace(" ", "-", $chaine_bis);
		return $chaine_bis;
	}
	
	
	public function liste_posts($id_themes = 0, $num_page, $lien_post) {
		$posts = $this->posts($id_themes,$num_page);
		$html = "";
		if (count($posts) > 0) {
			foreach($posts as $key => $values) {
				if (empty($values['titre_url'])) {
					$values['titre_url'] = $this->formater_key_url($values['titre']);
				}
				$html .= '<div class="blog_posts">';
				$html .= '<h3><a href="'.$lien_post.'/'.$values['id'].'/'.$values['titre_url'].'">'.stripslashes($values['titre']).'</a></h3>';
				$html .= '<div class="content_post">'.stripslashes($values['texte']).'</div>';
				$html .= '<div class="date_post">'.$this->dico->t('PublieLe').' '.$this->formater_date($values['date_affichage']).'</div>';
				$html .= '</div>';
			}
		}
		else {
			$html .= '<p>'.$this->dico->t('AucunBillet').'</p>';
		}
		return $html;
	}
	
	
	public function details_post($id_post = 0) {
		$q = "SELECT * FROM dt_billets WHERE id=".$id_post;
		$res = $this->sql->query($q);
		$row = $this->sql->fetch($res);
		return $row;
	}
	
	
	public function afficher_post($values) {
		$html = '<div class="page_post">';
		$html .= '<h2>'.stripslashes($values['titre']).'</h2>';
		$html .= '<div class="content_post">'.stripslashes($values['texte']).'</div>';
		$html .= '<div class="date_post">'.$this->dico->t('PublieLe').' '.$this->formater_date($values['date_affichage']).'</div>';
		$html .= '</div>';
		return $html;
	}
}