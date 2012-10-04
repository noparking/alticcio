-- Les différentes requêtes doivent être séparées par une ligne vide
-- Ne modifier que la fin du fichier
ALTER TABLE `dt_matieres` ADD `phrase_entretien` INT( 11 ) NOT NULL ,
ADD `phrase_marques_fournisseurs` INT( 11 ) NOT NULL ,
ADD `id_ecolabels` INT( 11 ) NOT NULL

CREATE TABLE IF NOT EXISTS `dt_images_matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_matieres` int(11) NOT NULL,
  `ref` varchar(250) NOT NULL,
  `phrase_legende` int(11) NOT NULL,
  `affichage` tinyint(1) NOT NULL,
  `vignette` tinyint(1) NOT NULL,
  `diaporama` tinyint(1) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)

CREATE TABLE IF NOT EXISTS `dt_matieres_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_matieres` int(11) NOT NULL,
  `id_applications` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_matieres` (`id_matieres`,`id_applications`)
)

ALTER TABLE `dt_applications_attributs` ADD `fiche_technique` BOOLEAN NOT NULL ,
ADD `pictos_vente` BOOLEAN NOT NULL

ALTER TABLE `dt_produits` ADD `offre` INT NOT NULL AFTER `id_gammes`

ALTER TABLE `dt_produits` ADD `phrase_entretien` INT( 11 ) NOT NULL ,
ADD `phrase_mode_emploi` INT( 11 ) NOT NULL ,
ADD `phrase_avantages_produit` INT( 11 ) NOT NULL

CREATE TABLE IF NOT EXISTS `dt_sku_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL,
  `id_sku` int(11) NOT NULL,
  `valeur_numerique` int(11) NOT NULL,
  `phrase_valeur` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_sku` (`id_sku`)
)

CREATE TABLE IF NOT EXISTS `dt_catalogues` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`nom` VARCHAR( 128 ) NOT NULL ,
`id_langues` INT( 11 ) NOT NULL ,
`type` INT( 4 ) NOT NULL ,
`statut` BOOLEAN NOT NULL ,
INDEX ( `id_langues` )
)

CREATE TABLE IF NOT EXISTS `dt_catalogues_categories` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_catalogues` INT( 11 ) NOT NULL ,
`nom` VARCHAR( 128 ) NOT NULL ,
`correspondance` INT( 11 ) NOT NULL ,
`statut` BOOLEAN NOT NULL ,
INDEX ( `id_catalogues` )
)

ALTER TABLE `dt_catalogues_categories` ADD `id_parent` INT( 11 ) NOT NULL AFTER `id` ,
ADD INDEX ( `id_parent` ) 

CREATE TABLE IF NOT EXISTS `dt_catalogues_categories_produits` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_catalogues_categories` INT( 11 ) NOT NULL ,
`id_produits` INT( 11 ) NOT NULL ,
INDEX ( `id_catalogues_categories` , `id_produits` )
)

CREATE TABLE IF NOT EXISTS `dt_recyclage` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`numero` TINYINT NOT NULL ,
`phrase_abreviations` INT NOT NULL ,
`phrase_nom` INT NOT NULL ,
`id_matieres` INT NOT NULL ,
`logo` VARCHAR( 120 ) NOT NULL ,
`phrase_recyclage` INT NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `dt_recyclage` DROP `id_matieres`

ALTER TABLE `dt_matieres` ADD `id_recyclage` INT NOT NULL

ALTER TABLE `dt_produits` ADD `id_recyclage` INT NOT NULL

ALTER TABLE `dt_sku` ADD `actif` BOOLEAN NOT NULL

ALTER TABLE `dt_catalogues_categories` ADD `date_modification` INT NOT NULL

ALTER TABLE `dt_familles_ventes` ADD `code` INT NOT NULL AFTER `id` 

ALTER TABLE `dt_sku` CHANGE `min_commande` `min_commande` INT( 11 ) NOT NULL DEFAULT '1'

UPDATE `dt_sku` SET `min_commande` = 1

ALTER TABLE `dt_fiches_produits` ADD `html` TEXT NOT NULL ,
ADD `xml` TEXT NOT NULL

ALTER TABLE `dt_fiches_produits` CHANGE `id_fiches_produits` `id` INT( 11 ) NOT NULL AUTO_INCREMENT

CREATE TABLE `dt_fiches_modeles` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`nom` VARCHAR( 128 ) NOT NULL ,
`html` TEXT NOT NULL ,
`css` TEXT NOT NULL
)

ALTER TABLE `dt_sites_tiers_synchro` ADD item VARCHAR( 32 ) NOT NULL AFTER `site`

ALTER TABLE `dt_fiches_modeles` CHANGE `nom` `phrase_nom` INT( 11 ) NOT NULL

ALTER TABLE dt_pays ADD phrase_nom_courant INT NOT NULL AFTER num_serie

ALTER TABLE dt_pays ADD phrase_adjectif INT NOT NULL AFTER phrase_nom_courant

CREATE TABLE dt_produits_complements (
`id_produits` INT NOT NULL ,
`id_pays` INT NOT NULL ,
`id_regions` INT NOT NULL ,
`id_administrations` INT NOT NULL ,
`id_matieres` INT NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE dt_filtre_emails (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`niveau` TINYINT NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE dt_duo_couleurs (
`id` INT NOT NULL AUTO_INCREMENT ,
`phrase_nom` INT NOT NULL ,
`image_duo` VARCHAR( 80 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `dt_sku` ADD `id_duo_couleurs` INT NOT NULL AFTER `id_ral` 

CREATE TABLE `dt_mats` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`forme` ENUM( 'conique', 'cylindrique' ) NOT NULL ,
`matiere` ENUM( 'acier', 'alu', 'fibre' ) NOT NULL ,
`hauteur` INT NOT NULL ,
`diametre_moyen` DECIMAL( 2, 1 ) NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `dt_mats` CHANGE `diametre_moyen` `diametre_moyen` INT NOT NULL 

ALTER TABLE `dt_mats` ADD `diametre_haut` INT NOT NULL AFTER `hauteur` ,
ADD `diametre_bas` INT NOT NULL AFTER `diametre_haut` 

ALTER TABLE `dt_mats` ADD `inertie` DECIMAL( 4, 2 ) NOT NULL 

ALTER TABLE `dt_stats_emailing` ADD `nb_desabonnements` INT NOT NULL AFTER `nb_emails_db` 

ALTER TABLE `dt_stats_emailing` ADD `pourcentage_desabonnements` INT NOT NULL 

ALTER TABLE `dt_stats_emailing` CHANGE `pourcentage_npai` `pourcentage_npai` DECIMAL( 5,2 ) NOT NULL ,
CHANGE `pourcentage_ouverture` `pourcentage_ouverture` DECIMAL( 5,2 ) NOT NULL ,
CHANGE `pourcentage_clic` `pourcentage_clic` DECIMAL( 5,2 ) NOT NULL ,
CHANGE `pourcentage_reactivite` `pourcentage_reactivite` DECIMAL( 5,2 ) NOT NULL ,
CHANGE `pourcentage_desabonnements` `pourcentage_desabonnements` DECIMAL( 5,2 ) NOT NULL 

CREATE TABLE `dt_zones_vents` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_pays` INT NOT NULL ,
`zone` INT NOT NULL ,
`vent_normal` DECIMAL( 4, 1 ) NOT NULL ,
`vent_fort` DECIMAL( 4, 1 ) NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `dt_zones_vents` (
`id` ,
`id_pays` ,
`zone` ,
`vent_normal` ,
`vent_fort`
)
VALUES (
NULL , '77', '1', '103', '136.1'
), (
NULL , '77', '2', '112,7', '149.1'
);

INSERT INTO `dt_zones_vents` (
`id` ,
`id_pays` ,
`zone` ,
`vent_normal` ,
`vent_fort`
)
VALUES (
NULL , '77', '3', '126', '166.6'
), (
NULL , '77', '4', '137.9', '182.5'
);

INSERT INTO `dt_zones_vents` (
`id` ,
`id_pays` ,
`zone` ,
`vent_normal` ,
`vent_fort`
)
VALUES (
NULL , '77', '5', '159.2', '210.6'
);

ALTER TABLE `dt_dimensions` ADD `pavillon` ENUM( '','horizontal', 'vertical' ) NOT NULL 

ALTER TABLE `dt_dimensions` ADD `trainee` DECIMAL( 3, 2 ) NOT NULL 

CREATE TABLE IF NOT EXISTS `dt_tribunes_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salle` enum('spectacle','sports','pleinair') NOT NULL DEFAULT 'spectacle',
  `emplacement` enum('interieur','exterieur') NOT NULL DEFAULT 'interieur',
  `sieges_par_bloc` int(11) NOT NULL,
  `type` enum('telescopique','fixe','demontable') NOT NULL DEFAULT 'telescopique',
  `gradin_hauteur` int(11) NOT NULL,
  `siege_type` enum('banc','coque','coque_dossier','banquette','fauteuil_nez','fauteuil_sur','fauteuil_fond') NOT NULL DEFAULT 'banc',
  `gradin_profondeur` int(11) NOT NULL,
  `siege_largeur` int(11) NOT NULL,
  `siege_profondeur` int(11) NOT NULL,
  `siege_hauteur` int(11) NOT NULL,
  `vue_haut` varchar(150) NOT NULL,
  `vue_coupe_1` varchar(150) NOT NULL,
  `vue_coupe_2` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;


INSERT INTO `dt_tribunes_configurations` (`id`, `salle`, `emplacement`, `sieges_par_bloc`, `type`, `gradin_hauteur`, `siege_type`, `gradin_profondeur`, `siege_largeur`, `siege_profondeur`, `siege_hauteur`, `vue_haut`, `vue_coupe_1`, `vue_coupe_2`) VALUES
(1, 'spectacle', 'interieur', 16, 'telescopique', 320, 'banquette', 750, 450, 250, 450, '', '', ''),
(2, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_nez', 900, 500, 500, 450, '', '', ''),
(3, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_sur', 900, 500, 500, 450, '', '', ''),
(4, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_fond', 900, 500, 500, 450, '', '', ''),
(5, 'spectacle', 'interieur', 16, 'fixe', 200, 'fauteuil_nez', 900, 500, 500, 450, '', '', ''),
(6, 'spectacle', 'interieur', 16, 'fixe', 400, 'fauteuil_nez', 900, 500, 500, 450, '', '', ''),
(7, 'spectacle', 'interieur', 16, 'fixe', 200, 'fauteuil_sur', 900, 500, 500, 450, '', '', ''),
(8, 'spectacle', 'interieur', 16, 'fixe', 400, 'fauteuil_sur', 900, 500, 500, 450, '', '', ''),
(9, 'spectacle', 'interieur', 16, 'fixe', 200, 'fauteuil_fond', 900, 500, 500, 450, '', '', ''),
(10, 'spectacle', 'interieur', 16, 'fixe', 400, 'fauteuil_fond', 900, 500, 500, 450, '', '', ''),
(11, 'sports', 'interieur', 22, 'telescopique', 320, 'banc', 750, 400, 250, 450, '', '', ''),
(12, 'sports', 'interieur', 22, 'telescopique', 320, 'coque', 750, 420, 350, 450, '', '', ''),
(36, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_nez', 1800, 500, 500, 450, '', '', ''),
(14, 'sports', 'interieur', 22, 'telescopique', 320, 'banquette', 750, 450, 250, 450, '', '', ''),
(15, 'sports', 'interieur', 22, 'fixe', 200, 'banc', 750, 400, 250, 450, '', '', ''),
(16, 'sports', 'interieur', 22, 'fixe', 400, 'banc', 750, 400, 250, 450, '', '', ''),
(17, 'sports', 'interieur', 22, 'fixe', 200, 'coque', 750, 420, 350, 450, '', '', ''),
(18, 'sports', 'interieur', 22, 'fixe', 400, 'coque', 750, 420, 350, 450, '', '', ''),
(19, 'sports', 'interieur', 22, 'fixe', 200, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(20, 'sports', 'interieur', 22, 'fixe', 400, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(21, 'sports', 'interieur', 22, 'fixe', 200, 'banquette', 750, 450, 250, 450, '', '', ''),
(22, 'sports', 'interieur', 22, 'fixe', 400, 'banquette', 750, 450, 250, 450, '', '', ''),
(23, 'sports', 'interieur', 22, 'demontable', 200, 'banc', 750, 400, 250, 450, '', '', ''),
(24, 'sports', 'interieur', 22, 'demontable', 200, 'coque', 750, 420, 350, 450, '', '', ''),
(25, 'sports', 'interieur', 22, 'demontable', 200, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(26, 'sports', 'interieur', 22, 'demontable', 200, 'banquette', 750, 450, 250, 450, '', '', ''),
(27, 'pleinair', 'exterieur', 44, 'fixe', 200, 'banc', 750, 400, 250, 450, '', '', ''),
(28, 'pleinair', 'exterieur', 44, 'fixe', 400, 'banc', 750, 400, 250, 450, '', '', ''),
(29, 'pleinair', 'exterieur', 44, 'fixe', 200, 'coque', 750, 420, 350, 450, '', '', ''),
(30, 'pleinair', 'exterieur', 44, 'fixe', 400, 'coque', 750, 420, 350, 450, '', '', ''),
(31, 'pleinair', 'exterieur', 44, 'fixe', 200, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(32, 'pleinair', 'exterieur', 44, 'fixe', 400, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(33, 'pleinair', 'exterieur', 44, 'demontable', 200, 'banc', 750, 400, 250, 450, '', '', ''),
(34, 'pleinair', 'exterieur', 44, 'demontable', 200, 'coque', 750, 420, 350, 450, '', '', ''),
(35, 'pleinair', 'exterieur', 44, 'demontable', 200, 'coque_dossier', 750, 430, 400, 450, '', '', ''),
(37, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_sur', 1800, 500, 500, 450, '', '', ''),
(38, 'spectacle', 'interieur', 16, 'telescopique', 320, 'fauteuil_fond', 1800, 500, 500, 450, '', '', '');

CREATE TABLE `dt_blocs` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`nom` VARCHAR( 128 ) NOT NULL ,
UNIQUE (
`nom`
)
)

CREATE TABLE `dt_blocs_contenus` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_blocs` INT NOT NULL ,
`id_langues` INT NOT NULL ,
`contenu` TEXT NOT NULL
)

ALTER TABLE `dt_familles_ventes` ADD `ref` CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL

ALTER TABLE `dt_tribunes_configurations` ADD `niveau` TINYINT NOT NULL DEFAULT '0'

CREATE TABLE dt_sondage_satisfaction (
`id` INT NOT NULL AUTO_INCREMENT ,
`date_reponse` INT( 10 ) NOT NULL ,
`num_facture` INT NOT NULL ,
`q1` INT NOT NULL ,
`q2` INT NOT NULL ,
`q3` INT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `dt_sondage_satisfaction` ADD `q4` INT NOT NULL

ALTER TABLE `dt_sondage_satisfaction` ADD `q5` INT NOT NULL

ALTER TABLE `dt_sondage_satisfaction` ADD `q6` INT NOT NULL 

ALTER TABLE `dt_sondage_satisfaction` ADD `scoring` INT NOT NULL 

ALTER TABLE `dt_sondage_satisfaction` ADD `commentaires` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 

CREATE TABLE `dt_documents` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`titre` VARCHAR( 250 ) NOT NULL ,
`id_langues` INT NOT NULL ,
`fichier` VARCHAR( 150 ) NOT NULL ,
`vignette` VARCHAR( 150 ) NOT NULL ,
`actif` TINYINT( 1 ) NOT NULL ,
`public` TINYINT( 1 ) NOT NULL ,
`type_documents` ENUM( 'guide', 'book', 'catalogue', 'film' ) NOT NULL DEFAULT 'guide' ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;