<?php

$menus = array(
	'lang' => array(
		'separator' => '-',
		'items' => array(
			'lang-fr' => array(
				'label' => "Français",
				'url' => $url->make("current", array('langue' => "fr", 'pays' => "FR")),
				'protected' => true,
			),
			'lang-en' => array(
				'label' => "English",
				'url' => $url->make("current", array('langue' => "en", 'pays' => "UK")),
				'protected' => true,
			),
		),
	),

	'top' => array(
		'separator' => '|',
		'items' => array(
			'home' => array(
				'label' => $dico->t("Accueil"),
				'url' => $url->make("Accueil"),
				'protected' => true,
			),
			'account' => array(
				'label' => $dico->t("MonCompte"),
				'url' => $url->make("MonCompte"),
				'protected' => true,
			),
			'help' => array(
				'label' => $dico->t("Aide"),
				'url' => $url->make("Aide"),
				'protected' => true,
			),
			'logout' => array(
				'label' => $dico->t("Deconnexion"),
				'url' => $url->make("login", array('action' => "logout")),
				'protected' => true,
			),
			
			'lang' => 'lang',
		),
	),
	
	'main' => array(
		'items' => array(
//			'home' => array(
//				'label' => $dico->t("Accueil"),
//				'url' => $url->make("Accueil"),
//				'level' => 1,
//			),
			'products' => array(
				'label' => $dico->t("Produits"),
				//'url' => $url2->make("Produits", array('type' => "")),
				'level' => 70,
				'items' => array(
					'gammes' => array(
						'label' => $dico->t("Gammes"),
						'url' => $url2->make("Produits", array('type' => "gammes", 'action' => "", 'id' => "")),
						'level' => 70,
					),
					'products' => array(
						'label' => $dico->t("Produits"),
						'url' => $url2->make("Produits", array('type' => "produits", 'action' => "", 'id' => "")),
						'level' => 70,
					),
					'sku' => array(
						'label' => $dico->t("SKU"),
						'url' => $url2->make("Produits", array('type' => "sku", 'action' => "", 'id' => "")),
						'level' => 70,
					),
					'applications' => array(
						'label' => $dico->t("Applications"),
						'url' => $url2->make("Produits", array('type' => "applications", 'action' => "", 'id' => "")),
						'level' => 80,
					),
					'attributs' => array(
						'label' => $dico->t("Attributs"),
						'url' => $url2->make("Produits", array('type' => "attributs", 'action' => "", 'id' => "")),
						'level' => 80,
					),
					'matieres' => array(
						'label' => $dico->t("Matieres"),
						'url' => $url2->make("Produits", array('type' => "matieres", 'action' => "", 'id' => "")),
						'level' => 80,
					),
					'catalogs' => array(
						'label' => "Catalogues",
						'url' => $url2->make("Produits", array('type' => "catalogues", 'action' => "", 'id' => "")),
						'level' => 70,
					),
					'fiches' => array(
						'label' => $dico->t("FichesTechniques"),
						'url' => $url2->make("Produits", array('type' => "fiches", 'action' => "", 'id' => "")),
						'level' => 90,
					),
					'fiches-matieres' => array(
						'label' => $dico->t("FichesMatieres"),
						'url' => $url2->make("Produits", array('type' => "fiches_matieres", 'action' => "", 'id' => "")),
						'level' => 90,
					),
					'exportproduits' => array(
						'label' => $dico->t("Exports"),
						'url' => $url2->make("Exportproduits", array('type' => "")),
						'level' => 90,
					),
					'compare' => array(
						'label' => $dico->t("Comparatif"),
						'url' => $url->make("Comparatif"),
						'level' => 70,
					),
                         'degressifs' => array(
						'label' => $dico->t("Degressifs"),
						'url' => $url->make("Degressifs"),
						'level' => 70,
					),
				),
			),
			
			'content' => array(
				'separator' => '|',
				'label' => $dico->t("Contenus"),
				//'url' => $url2->make("Contenu", array('type' => "")),
				'level' => 50,
				'items' => array(
					'blogpost' => array(
						'level' => 50,
						'label' => $dico->t("BilletBlog"),
						'url' => $url2->make("Contenu", array('type' => "blogpost", 'action' => "", 'id' => "")),
					),
					'comments' => array(
						'level' => 50,
						'label' => $dico->t("Commentaires"),
						'url' => $url2->make("Contenu", array('type' => "comments", 'action' => "", 'id' => "")),
					),
					'themes' => array(
						'label' => "Themes",
						'url' => $url2->make("Blog", array('type' => "themes", 'action' => "", 'id' => "")),
						'level' => 55,
					),
					'blogs' => array(
						'label' => "Blogs",
						'url' => $url2->make("Blog", array('type' => "blogs", 'action' => "", 'id' => "")),
						'level' => 90,
					),
					'blocs' => array(
						'label' => "Blocs",
						'url' => $url0->make("Blocs", array('id' => "")),
						'level' => 60,
					),
					'pages' => array(
						'label' => "Pages",
						'url' => $url0->make("Pages", array('id' => "")),
						'level' => 60,
					),
					'diaporamas' => array(
						'label' => "Diaporamas",
						'url' => $url0->make("Diaporamas", array('id' => "")),
						'level' => 60,
					),
				),
			),
			
			'customers' => array(
				'label' => $dico->t("Clients"),
				//'url' => '';
				'level' => 30,
				'items' => array(
					'commandes' => array(
						'level' => 30,
						'label' => $dico->t('Commandes'),
						'url' => $url->make("Commandes", array('action' => "", 'id' => "")),
					),
				),
			),
			
			'stats' => array(
				'label' => $dico->t("Statistiques"),
				//'url' => $url->make("StatsEmailingList", array("action" => "list")),
				'level' => 30,
				'items' => array(
                        'commandes' => array(
						'level' => 30,
						'label' => $dico->t('Commandes'),
						'url' => $url2->make("StatsCommandes", array("action" => "")),
					),
					'messages' => array(
						'level' => 40,
						'label' => $dico->t('Messages'),
						'url' => $url2->make("StatsMessagesList"),
					),
					'api' => array(
						'level' => 30,
						'label' => $dico->t('APIshop'),
						'url' => $url2->make("StatsApi", array("action" => "")),
					),
					'satisfaction' => array(
						'level' => 30,
						'label' => $dico->t('Satisfaction'),
						'url' => $url2->make("StatsSatisfaction", array("action" => "")),
					),
					'visites-produits' => array(
						'level' => 30,
						'label' => $dico->t('Visites produits'),
						'url' => $url->make("StatsVisitesProduits", array("action" => "")),
					),
                        'shorturl' => array(
						'level' => 30,
						'label' => $dico->t('ShortUlr'),
						'url' => $url2->make("StatsShorturl", array("action" => "")),
					),
                        'emailing' => array(
						'level' => 60,
						'label' => $dico->t('Emailings'),
						'url' => $url->make("StatsEmailingList"),
					),
				),
			),
			
			'tools' => array(
				'label' => $dico->t("Outils"),
				'level' => 10,
				'items' => array(
					'tribune' => array(
						'label' => $dico->t("Tribune"),
						'url' => $url->make("tribune"),
						'level' => 20,
					),
					'flags' => array(
						'label' => $dico->t("EventFlags"),
						'url' => $url->make("CalculFlags"),
						'level' => 10,
					),
				),
			),
			
			'params' => array(
				'separator' => '|',
				'label' => $dico->t("Parametres"),
				//'url' => $url->make("Database"),
				'level' => 90,
				'items' => array(
					'boutiques' => array(
						'label' => "Boutiques",
						'url' => $url->make("Boutiques"),
						'level' => 99,
					),
					'users' => array(
						'label' => $dico->t("Utilisateurs"),
						'url' => $url->make("userlist"),
						'level' => 90,
					),
					'api-roles' => array(
						'label' => $dico->t("RolesAPI"),
						'url' => $url->make("apiroles"),
						'level' => 99,
					),
					'api-users' => array(
						'label' => $dico->t("UtilisateursAPI"),
						'url' => $url->make("apiusers"),
						'level' => 99,
					),
					'params' => array(
						'label' => $dico->t("Parametres"),
						'url' => $url->make("Database"),
						'level' => 99,
					),
					'schema' => array(
						'label' => $dico->t("SchemaDB"),
						'url' => $url->make("SchemaDb"),
						'level' => 90,

					),
					'trad' => array(
						'label' => $dico->t("Traductions"),
						'url' => $url->make("Trad"),
						'level' => 90,

					),
					'clean' => array(
						'label' => $dico->t("Maintenance"),
						'url' => $url->make("Cleandb"),
						'level' => 90,

					),
					'services' => array(
						'label' => $dico->t("Services"),
						'url' => $url->make("Services"),
						'level' => 90,

					),
					'menus' => array(
						'label' => $dico->t("Menus"),
						'url' => $url->make("Menus"),
						'level' => 99,
						'protected' => true,
					),
					'update' => array(
						'label' => $dico->t("Mises à jour"),
						'url' => $url->make("Update"),
						'level' => 99,
					),
				),
			),
			/*
			 * 
			 * 
			 * 
			'users' => array(
				'label' => $dico->t("Utilisateurs"),
				//'url' => $url->make("userlist"),
				'level' => 99,
				'items' => array(
					
				),
			),
			
			'help' => array(
				'label' => $dico->t("Aide"),
				'level' => 10,
			),
			'blog' => array(
				'label' => $dico->t("Blog"),
				'url' => $url->make("Blog"),
				'level' => 99,
				'items' => array(
					'blogs' => array(
						'label' => "Blogs",
						'url' => $url2->make("Blog", array('type' => "blogs", 'action' => "", 'id' => "")),
						'level' => 99,
					),
					'themes' => array(
						'label' => "Themes",
						'url' => $url2->make("Blog", array('type' => "themes", 'action' => "", 'id' => "")),
						'level' => 99,
					),
				),
			),
			*/
		),
	),
);
