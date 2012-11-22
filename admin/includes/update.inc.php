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
	$q = <<<SQL
ALTER TABLE  `dt_commandes_revisions` ADD  `date_revision` INT NOT NULL AFTER  `revision`
SQL;
	$update->sql->query($q);
	$q = <<<SQL
ALTER TABLE  `dt_commandes_produits_revisions` ADD  `date_revision` INT NOT NULL AFTER  `revision`
SQL;
	$update->sql->query($q);
};
