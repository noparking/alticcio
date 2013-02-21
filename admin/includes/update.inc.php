<?php

$update->maj[0] = function() {
};

$update->maj[1] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_commandes_produits_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `revision` int(11) NOT NULL,
  `date_revision` int(11) NOT NULL,
  `id_commandes_produits` int(11) NOT NULL,
  `id_commandes` int(11) NOT NULL,
  `id_produits` int(11) NOT NULL,
  `id_sku` int(11) NOT NULL,
  `ref` varchar(20) NOT NULL,
  `nom` varchar(256) NOT NULL,
  `prix_unitaire` float NOT NULL,
  `quantite` int(11) NOT NULL,
  `personnalisation_texte` text NOT NULL,
  `personnalisation_fichier` varchar(128) NOT NULL,
  `personnalisation_nom_fichier` varchar(128) NOT NULL,
  `personnalisation_objet` longblob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `revision` (`revision`,`id_commandes_produits`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;
	$update->sql->query($q);
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_commandes_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `revision` int(11) NOT NULL,
  `date_revision` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `id_commandes` int(11) NOT NULL,
  `shop` int(11) NOT NULL,
  `id_api_keys` int(11) NOT NULL,
  `token` varchar(16) NOT NULL,
  `etat` int(11) NOT NULL,
  `montant` float NOT NULL,
  `frais_de_port` float NOT NULL,
  `tva` float NOT NULL,
  `nom` varchar(128) NOT NULL,
  `prenom` varchar(128) NOT NULL,
  `profil` tinyint(4) NOT NULL,
  `societe` varchar(128) NOT NULL,
  `num_client` varchar(20) NOT NULL,
  `siret` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `livraison_societe` varchar(128) NOT NULL,
  `livraison_societe2` varchar(128) NOT NULL,
  `livraison_adresse` varchar(256) NOT NULL,
  `livraison_adresse2` varchar(256) NOT NULL,
  `livraison_adresse3` varchar(256) NOT NULL,
  `livraison_cp` varchar(16) NOT NULL,
  `livraison_ville` varchar(64) NOT NULL,
  `livraison_cedex` varchar(32) NOT NULL,
  `livraison_pays` int(11) NOT NULL,
  `facturation_societe` varchar(128) NOT NULL,
  `facturation_societe2` varchar(128) NOT NULL,
  `facturation_adresse` varchar(256) NOT NULL,
  `facturation_adresse2` varchar(256) NOT NULL,
  `facturation_adresse3` varchar(256) NOT NULL,
  `facturation_cp` varchar(16) NOT NULL,
  `facturation_ville` varchar(64) NOT NULL,
  `facturation_cedex` varchar(32) NOT NULL,
  `facturation_pays` int(11) NOT NULL,
  `date_commande` int(10) NOT NULL,
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `revision` (`revision`,`id_commandes`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;
	$update->sql->query($q);
};

$update->maj[2] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_factures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(256) NOT NULL,
  `id_commandes` int(11) NOT NULL,
  `shop` int(11) NOT NULL,
  `id_api_keys` int(11) NOT NULL,
  `token` varchar(16) NOT NULL,
  `etat` int(11) NOT NULL,
  `montant` float NOT NULL,
  `frais_de_port` float NOT NULL,
  `tva` float NOT NULL,
  `nom` varchar(128) NOT NULL,
  `prenom` varchar(128) NOT NULL,
  `profil` tinyint(4) NOT NULL,
  `societe` varchar(128) NOT NULL,
  `num_client` varchar(20) NOT NULL,
  `siret` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `livraison_societe` varchar(128) NOT NULL,
  `livraison_societe2` varchar(128) NOT NULL,
  `livraison_adresse` varchar(256) NOT NULL,
  `livraison_adresse2` varchar(256) NOT NULL,
  `livraison_adresse3` varchar(256) NOT NULL,
  `livraison_cp` varchar(16) NOT NULL,
  `livraison_ville` varchar(64) NOT NULL,
  `livraison_cedex` varchar(32) NOT NULL,
  `livraison_pays` int(11) NOT NULL,
  `facturation_societe` varchar(128) NOT NULL,
  `facturation_societe2` varchar(128) NOT NULL,
  `facturation_adresse` varchar(256) NOT NULL,
  `facturation_adresse2` varchar(256) NOT NULL,
  `facturation_adresse3` varchar(256) NOT NULL,
  `facturation_cp` varchar(16) NOT NULL,
  `facturation_ville` varchar(64) NOT NULL,
  `facturation_cedex` varchar(32) NOT NULL,
  `facturation_pays` int(11) NOT NULL,
  `date_commande` int(10) NOT NULL,
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `id_api_keys` (`id_api_keys`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SQL;
	$update->sql->query($q);
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_factures_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_factures` int(11) NOT NULL,
  `id_produits` int(11) NOT NULL,
  `id_sku` int(11) NOT NULL,
  `ref` varchar(20) NOT NULL,
  `nom` varchar(256) NOT NULL,
  `prix_unitaire` float NOT NULL,
  `quantite` int(11) NOT NULL,
  `personnalisation_texte` text NOT NULL,
  `personnalisation_fichier` varchar(128) NOT NULL,
  `personnalisation_nom_fichier` varchar(128) NOT NULL,
  `personnalisation_objet` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_factures` (`id_factures`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;	
SQL;
	$update->sql->query($q);
};
$update->maj[3] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_users_password` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_users` int(11) NOT NULL,
		`key` varchar(256) NOT NULL,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL;
	$update->sql->query($q);
};
$update->maj[4] = function($update) {
	$q = <<<SQL
ALTER TABLE  `dt_commandes` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
	$q = <<<SQL
ALTER TABLE  `dt_commandes_revisions` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[5] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `acces` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SQL;
	$update->sql->query($q);
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_clients_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_clients` int(11) NOT NULL,
  `key` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;	
SQL;
	$update->sql->query($q);
};

$update->maj[6] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `classement` INT NOT NULL;
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `classement` INT NOT NULL;
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `classement` INT NOT NULL;
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[7] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_gammes` ADD `phrase_description_courte` INT NOT NULL ,
ADD `phrase_url_key` INT NOT NULL ,
ADD `ref` VARCHAR( 20 ) NOT NULL 
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[8] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_gammes_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL,
  `id_gammes` int(11) NOT NULL,
  `valeur_numerique` float NOT NULL,
  `phrase_valeur` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_gammes` (`id_gammes`)
)
SQL;
	$update->sql->query($q);
};

$update->maj[9] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_images_gammes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gammes` int(11) NOT NULL,
  `ref` varchar(250) NOT NULL,
  `phrase_legende` int(11) NOT NULL,
  `affichage` tinyint(1) NOT NULL,
  `vignette` tinyint(1) NOT NULL,
  `diaporama` tinyint(1) NOT NULL,
  `classement` int(11) NOT NULL,
  `hd_extension` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_gammes`),
  KEY `phrase_legende` (`phrase_legende`)
)
SQL;
	$update->sql->query($q);
};

$update->maj[10] = function($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_attributs_valeurs` (
  `id_attributs` int(11) NOT NULL,
  `valeur_numerique` float NOT NULL,
  `phrase_valeur` int(11) NOT NULL,
  PRIMARY KEY (`id_attributs`)
)
SQL;
	$update->sql->query($q);
	$q = <<<SQL
ALTER TABLE `dt_attributs_valeurs` ADD `type_valeur` VARCHAR( 32 ) NOT NULL AFTER `id_attributs`
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[11] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_applications` ADD `ref` VARCHAR( 20 ) NOT NULL ,
ADD `phrase_description_courte` INT( 11 ) NOT NULL ,
ADD `phrase_description` INT( 11 ) NOT NULL ,
ADD `phrase_url_key` INT( 11 ) NOT NULL
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[12] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_users` ADD `id_langues` INT NOT NULL
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[13] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id` ) 
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}

	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id_langues` )
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};

$update->maj[14] = function($update) {
	$q = <<<SQL
ALTER TABLE `dt_groupes_users` ADD `perm` LONGTEXT NOT NULL
SQL;
	try {
		$update->sql->query($q);
	}
	catch (Exception $e) {
	}
};
