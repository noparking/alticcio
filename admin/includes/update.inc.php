<?php

function update_0() {
};

function update_1($update) {
	$q = <<<SQL
CREATE TABLE `dt_commandes_produits_revisions` (
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
	$update->query($q);

	$q = <<<SQL
CREATE TABLE `dt_commandes_revisions` (
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
	$update->query($q);
};

function update_2($update) {
	$q = <<<SQL
CREATE TABLE `dt_factures` (
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
	$update->query($q);

	$q = <<<SQL
CREATE TABLE `dt_factures_produits` (
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
	$update->query($q);
};

function update_3($update) {
	$q = <<<SQL
CREATE TABLE `dt_users_password` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_users` int(11) NOT NULL,
		`key` varchar(256) NOT NULL,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQL;
	$update->query($q);
};

function update_4($update) {
	$q = <<<SQL
ALTER TABLE  `dt_commandes` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE  `dt_commandes_revisions` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	$update->query($q);
};

function update_5($update) {
	$q = <<<SQL
CREATE TABLE `dt_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `acces` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE `dt_clients_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_clients` int(11) NOT NULL,
  `key` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;	
SQL;
	$update->query($q);
};

function update_6($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `classement` INT NOT NULL;
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `classement` INT NOT NULL;
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `classement` INT NOT NULL;
SQL;

	$update->query($q);
};

function update_7($update) {
	$q = <<<SQL
ALTER TABLE `dt_gammes` ADD `phrase_description_courte` INT NOT NULL ,
ADD `phrase_url_key` INT NOT NULL ,
ADD `ref` VARCHAR( 20 ) NOT NULL 
SQL;
	$update->query($q);
};

function update_8($update) {
	$q = <<<SQL
CREATE TABLE `dt_gammes_attributs` (
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
	$update->query($q);
};

function update_9($update) {
	$q = <<<SQL
CREATE TABLE `dt_images_gammes` (
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
	$update->query($q);
};

function update_10($update) {
	$q = <<<SQL
CREATE TABLE `dt_attributs_valeurs` (
  `id_attributs` int(11) NOT NULL,
  `valeur_numerique` float NOT NULL,
  `phrase_valeur` int(11) NOT NULL,
  PRIMARY KEY (`id_attributs`)
)
SQL;
	$update->query($q);
	$q = <<<SQL
ALTER TABLE `dt_attributs_valeurs` ADD `type_valeur` VARCHAR( 32 ) NOT NULL AFTER `id_attributs`
SQL;
	$update->query($q);
};

function update_11($update) {
	$q = <<<SQL
ALTER TABLE `dt_applications` ADD `ref` VARCHAR( 20 ) NOT NULL ,
ADD `phrase_description_courte` INT( 11 ) NOT NULL ,
ADD `phrase_description` INT( 11 ) NOT NULL ,
ADD `phrase_url_key` INT( 11 ) NOT NULL
SQL;
	$update->query($q);
};

function update_12($update) {
	$q = <<<SQL
ALTER TABLE `dt_users` ADD `id_langues` INT NOT NULL
SQL;
	$update->query($q);
};

function update_13($update) {
	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id` ) 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id_langues` )
SQL;
	$update->query($q);
};

function update_14($update) {
	$q = <<<SQL
ALTER TABLE `dt_groupes_users` ADD `perm` LONGTEXT NOT NULL
SQL;
	$update->query($q);
};

function update_15($update) {
	$q = <<<SQL
ALTER TABLE `dt_attributs_valeurs` ADD `valeur_libre` TEXT NOT NULL
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->query($q);
}

function update_16($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_produits`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_sku`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_gammes`
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_produits_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_sku_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_gammes_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE `dt_groupes_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);
}

function update_17($update) {
	$tables = array(
		"id_gammes" => "dt_gammes_attributs",
		"id_produits" => "dt_produits_attributs",
		"id_sku" => "dt_sku_attributs",
	);
	foreach ($tables as $id_field => $table) {

		$q = <<<SQL
CREATE TABLE `{$table}_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL,
  `{$id_field}` int(11) NOT NULL,
  `groupe` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `{$id_field}` (`{$id_field}`)
)
SQL;
		$update->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'valeur_numerique'
SQL;
		$update->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> ''
SQL;
		$update->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'valeur_libre' WHERE valeur_libre <> ''
SQL;
		$update->query($q);
		
		$q = <<<SQL
SELECT * FROM $table 
SQL;
		$res = $update->query($q);
	
		$i = 1;
		$values = array();
		while ($row = $update->sql->fetch($res)) {
			$values[] = "({$row['id_attributs']}, {$row[$id_field]}, {$row['classement']})";
			if ($i % 33000 == 0) { // on insère par paquet de 33000
				$values = implode (",", $values);
				$q = <<<SQL
INSERT INTO {$table}_management (id_attributs, $id_field, classement) VALUES $values 
SQL;
				$update->query($q);
				$values = array();
			}
			$i++;
		}
		if ($values = implode (",", $values)) {
			$q = <<<SQL
INSERT INTO {$table}_management (id_attributs, $id_field, classement) VALUES $values 
SQL;
			$update->query($q); // insertion de ce qui reste
		}
	}
}

function update_18($update) {
	$q = <<<SQL
ALTER TABLE `dt_catalogues` ADD `export_frequency` INT NOT NULL 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_exports_catalogues` ADD `auto` INT NOT NULL 
SQL;
	$update->query($q);
}

function update_19($update) {
	$q = <<<SQL
CREATE TABLE `dt_devis_pose` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_commande` int(11) NOT NULL,
  `num_devis` int(11) NOT NULL,
  `type_pose` varchar(32) NOT NULL,
  `champ` varchar(128) NOT NULL,
  `valeur` text NOT NULL,
  `date_creation` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);
}

function update_20($update) {
	$q = <<<SQL
CREATE TABLE `dt_ecotaxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL,
  `id_pays` int(11) NOT NULL,
  `id_familles_taxes` int(11) NOT NULL,
  `id_catalogues` int(11) NOT NULL,
  `montant` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`,`id_pays`,`id_familles_taxes`, `id_catalogues`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE `dt_familles_taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_taxe` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `ecotaxe` FLOAT NOT NULL AFTER `tva` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `ecotaxe` FLOAT NOT NULL AFTER `tva` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` ADD `ecotaxe` FLOAT NOT NULL AFTER `prix_unitaire` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` ADD `ecotaxe` FLOAT NOT NULL AFTER `prix_unitaire` 
SQL;
	$update->query($q);
}

function update_21($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` CHANGE `ref_ultralog` `ref_ultralog` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_produits` CHANGE `ref` `ref` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes` CHANGE `ref` `ref` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->query($q);
}

function update_22($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0'
SQL;
	$update->query($q);
}

function update_23($update) {
	$q = <<<SQL
SHOW TABLES
SQL;
	$res = $update->query($q);
	while ($row = $update->sql->fetch($res)) {
		$table = array_pop($row);
		$q = <<<SQL
DESCRIBE $table
SQL;
		$res2 = $update->query($q);
		while ($row2 = $update->sql->fetch($res2)) {
			if ($row2['Null'] == "NO" and $row2['Default'] == null and $row2['Extra'] != "auto_increment") {
				$default_value = null;
				$null = false;
				preg_match("/([^(]*)/", $row2['Type'], $matches);
				switch ($matches[1]) {
					case 'int': 
					case 'tinyint': 
					case 'decimal':
					case 'float':
					case 'year':
						$default_value = 0;
						break;
					case 'varchar':
					case 'char':
						$default_value = "''";
						break;
					case 'text':
					case 'longtext':
					case 'tinytext':
					case 'longblob':
						$null = true;
						break;
				}
				if ($default_value !== null) {
					$q = <<<SQL
ALTER TABLE `$table` CHANGE `{$row2['Field']}` `{$row2['Field']}` {$row2['Type']} NOT NULL DEFAULT $default_value
SQL;
					$update->query($q);
				}
				else if ($null) {
					$q = <<<SQL
ALTER TABLE `$table` CHANGE `{$row2['Field']}` `{$row2['Field']}` {$row2['Type']} NULL
SQL;
					$update->query($q);
				}
			}
		}
	}
}

function update_24($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` DROP `echantillon` 
SQL;
	$update->query($q);
	$q = <<<SQL
ALTER TABLE `dt_produits` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0'
SQL;
	$update->query($q);
}

function update_25($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_types_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
INSERT INTO dt_types_documents (code)
VALUES ('guide'), ('book'), ('catalogue'), ('film')
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_documents` ADD `id_types_documents` INT NOT NULL ,
ADD INDEX ( `id_types_documents` )
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_documents` SET id_types_documents = 1 WHERE type_documents = 'guide'
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_documents` SET id_types_documents = 2 WHERE type_documents = 'book'
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_documents` SET id_types_documents = 3 WHERE type_documents = 'catalogue'
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_documents` SET id_types_documents = 4 WHERE type_documents = 'film'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_documents` DROP `type_documents`
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_documents_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_sku` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_sku` (`id_sku`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_documents_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_produits` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_produits` (`id_produits`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_documents_gammes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_gammes` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_gammes` (`id_gammes`)
)
SQL;
	$update->query($q);
}

function update_26($update) {
	$q = <<<SQL
ALTER TABLE `dt_applications` ADD `phrase_produit_description` INT NOT NULL DEFAULT '0',
ADD `phrase_produit_description_courte` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);
}

function update_27($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_exports_produits` (
  `id_produits` int(11) NOT NULL,
  `date_export` int(11) NOT NULL,
  PRIMARY KEY (`id_produits`)
)
SQL;
	$update->query($q);
}

function update_28($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `quantite` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` ADD `echantillon` TINYINT( 1 )  NOT NULL DEFAULT '0' AFTER `quantite` 
SQL;
	$update->query($q);
}

function update_29($update) {
	$q = <<<SQL
ALTER TABLE `dt_attributs` CHANGE `ref` `ref` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT ''
SQL;
	$update->query($q);
}

function update_30($update) {
	$q = <<<SQL
ALTER TABLE `dt_types_documents` CHANGE `code` `code` VARCHAR( 60 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT ''
SQL;
	$update->query($q);
}

function update_31($update) {
	$q = <<<SQL
ALTER TABLE `dt_factures_produits` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `quantite` 
SQL;
	$update->query($q);
	$q = <<<SQL
ALTER TABLE `dt_factures` ADD `ecotaxe` FLOAT NOT NULL DEFAULT '0' AFTER `tva` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_factures_produits` ADD `ecotaxe` FLOAT NOT NULL DEFAULT '0' AFTER `prix_unitaire` 
SQL;
	$update->query($q);
}

function update_32($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_boutiques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) NOT NULL,
  `id_catalogues` int(11) NOT NULL,
  `id_api_keys` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_catalogues` (`id_catalogues`,`id_api_keys`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_boutiques_data` (
  `id_boutiques` int(11) NOT NULL DEFAULT '0',
  `data_key` varchar(64) NOT NULL DEFAULT '',
  `data_value` text,
  PRIMARY KEY (`id_boutiques`,`data_key`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_frais_port` ADD `id_boutiques` INT( 11 ) NOT NULL DEFAULT '0' AFTER id
SQL;
	$update->query($q);
}

function update_33($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `notification` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `id_langues` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `notification` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);
	
	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `id_langues` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_commandes` SET notification = 1, id_langues = 1
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_commandes_revisions` SET notification = 1, id_langues = 1
SQL;
	$update->query($q);
}

function update_34($update) {
	$q = <<<SQL
ALTER TABLE `api_keys_rules` ADD `log` TINYINT NOT NULL DEFAULT '1'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `api_roles_rules` ADD `log` TINYINT NOT NULL DEFAULT '1'
SQL;
	$update->query($q);
}

function update_35($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` CHANGE `quantite` `quantite` FLOAT( 11 ) NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` CHANGE `quantite` `quantite` FLOAT( 11 ) NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku` CHANGE `min_commande` `min_commande` FLOAT NOT NULL DEFAULT '1'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku` CHANGE `colisage` `colisage` FLOAT NOT NULL DEFAULT '1'
SQL;
	$update->query($q);
}

function update_36($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits` ADD `date_creation` INT( 11 ) NOT NULL DEFAULT '0' AFTER `phrase_meta_keywords`
SQL;
	$update->query($q);
}

function update_37($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `id_boutiques` INT NOT NULL DEFAULT '0' AFTER `id_api_keys` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `id_boutiques` INT NOT NULL DEFAULT '0' AFTER `id_api_keys` 
SQL;
	$update->query($q);
}

function update_38($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_references_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL,
  `id_catalogues` int(11) NOT NULL,
  `reference` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`,`id_catalogues`)
)
SQL;
	$update->query($q);
}

function update_39($update) {
	$q = <<<SQL
ALTER TABLE `dt_matieres` CHANGE `nom_matiere` `ref_matiere` VARCHAR( 70 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_attributs_references` SET `field_label`='ref_matiere' WHERE `table_name`='dt_matieres' AND `field_label`='nom_matiere'
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres` ADD `phrase_nom` INT( 11 ) NOT NULL DEFAULT '0' AFTER `ref_matiere`
SQL;
	$update->query($q);
}

function update_40($update) {
	$q = <<<SQL
ALTER TABLE `dt_matieres` ADD `date_modification` INT( 11 ) NOT NULL DEFAULT '0'
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_exports_matieres` (
  `id_matieres` int(11) NOT NULL,
  `date_export` int(11) NOT NULL,
  PRIMARY KEY (`id_matieres`)
)
SQL;
	$update->query($q);
}

function update_41($update) {
	$q = <<<SQL
UPDATE `dt_matieres_attributs` SET phrase_valeur = valeur_numerique, valeur_numerique = 0
WHERE id_attributs IN (SELECT id FROM dt_attributs WHERE id_types_attributs IN (5, 3))
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_matieres`
SQL;
	$update->query($q);

	$q = <<<SQL
UPDATE `dt_matieres_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->query($q);

}

function update_42($update) {
	$q = <<<SQL
ALTER TABLE `dt_exports_catalogues` ADD `error` TEXT NOT NULL
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_exports_catalogues` CHANGE `etat` `etat` ENUM( 'tobuild', 'building', 'built', 'error' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
SQL;
	$update->query($q);
}

function update_43($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `infos` TEXT NOT NULL 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `infos` TEXT NOT NULL 
SQL;
	$update->query($q);
}

function update_44($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits` ADD `delai_livraison` VARCHAR( 8 ) NOT NULL DEFAULT ''
SQL;
	$update->query($q);
}

function update_45($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_catalogues_categories_blocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_catalogues_categories` int(11) NOT NULL DEFAULT '0',
  `id_blocs` int(11) NOT NULL DEFAULT '0',
  `utilisation` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_catalogues_categories` (`id_catalogues_categories`),
  KEY `id_blocs` (`id_blocs`)
)
SQL;
	$update->query($q);
}

function update_46($update) {
	$q = <<<SQL
ALTER TABLE `dt_blogs` ADD `titre` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `nom` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_billets` ADD `vignette` VARCHAR( 100 ) NOT NULL DEFAULT '' AFTER `texte` 
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_billets_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_billets` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_billets` (`id_billets`),
  KEY `id_produits` (`id_produits`)
)
SQL;
	$update->query($q);
}

function update_47($update) {
	$q = <<<SQL
SElECT id, id_blocs FROM dt_catalogues_categories WHERE id_blocs <> 0
SQL;
	$res = $update->query($q);

	while ($row = $update->sql->fetch($res)) {
		$q = <<<SQL
INSERT INTO `dt_catalogues_categories_blocs` (id_catalogues_categories, id_blocs, utilisation)
VALUES ({$row['id']}, {$row['id_blocs']}, 'header')
SQL;
		$update->query($q);
	}

	$q = <<<SQL
ALTER TABLE  dt_catalogues_categories DROP id_blocs
SQL;
	$update->query($q);
}

function update_48($update) {
	$q = <<<SQL
ALTER TABLE dt_diaporamas ADD `id_langues` int(11) NOT NULL DEFAULT 0 AFTER `ref` 
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE dt_images_diaporamas ADD `legende` VARCHAR ( 128 ) NOT NULL DEFAULT '' AFTER `phrase_legende` 
SQL;
	$update->query($q);

	$q = <<<SQL
SELECT * FROM dt_diaporamas
SQL;
	$res = $update->query($q);

	$last_row = null;
	$is_first_row = true;
	while ($row = $update->sql->fetch($res)) {
		if ($is_first_row) {
			foreach ($row as $key => $value) {
				if (strpos($key, "phrase_") === 0) {
					$field = str_replace("phrase_", "", $key);
					if ($field == "description") {
						$type = "TEXT NOT NULL";
					}
					else {
						$type = "VARCHAR( 128 ) NOT NULL DEFAULT ''";
					}
					$q = <<<SQL
ALTER TABLE dt_diaporamas ADD `$field` $type AFTER `$key` 
SQL;
					$update->query($q);
				}
			}
			$is_first_row = false;
		}

		$images = array();
		$q = <<<SQL
SELECT * FROM dt_images_diaporamas WHERE id_diaporamas = {$row['id']}
SQL;
		$res2 = $update->query($q);
		while ($row2 = $update->sql->fetch($res2)) {
			$images[] = $row2;
		}

		$new_fields = array();
		foreach ($row as $key => $value) {
			if (strpos($key, "phrase_") === 0) {
				$q = <<<SQL
SELECT phrase, id_langues FROM dt_phrases WHERE id = $value 
SQL;
				$res2 = $update->query($q);
				while ($row2 = $update->sql->fetch($res2)) {
					if ($row2['phrase']) {
						$new_fields[$row2['id_langues']][str_replace("phrase_", "", $key)] = $row2['phrase'];
					}
				}
			}
		}
		$first = true;
		foreach ($new_fields as $id_langues => $data) {
			$fields = array("id_langues");
			$values = array($id_langues);
			if (!isset($data['description'])) {
				$fields[] = "description";
				$values[] = "''";
			}
			$sets = array("id_langues = $id_langues");
			foreach ($row as $key => $value) {
				$value = addslashes($value);
				if ($key != "id" and $key != "id_langues") {
					$fields[] = $key;
					$values[] = "'$value'";
				}
			}
			foreach ($data as $key => $value) {
				$value = addslashes($value);
				$fields[] = $key;
				$values[] = "'$value'";
				$sets[] = "$key = '$value'";
			}
			$list_fields = implode(",", $fields);
			$list_values = implode(",", $values);
			$list_sets = implode(",", $sets);
			if ($first) {
				$q = <<<SQL
UPDATE dt_diaporamas SET $list_sets WHERE id = {$row['id']}
SQL;
				$update->query($q);
				$first = false;
			}
			else {
				$q = <<<SQL
INSERT INTO dt_diaporamas ($list_fields) VALUES ($list_values) 
SQL;
				$update->query($q);
				$id = $update->sql->insert_id();
				foreach ($images as $image) {
					$fields = array("id_diaporamas");
					$values = array($id);
					foreach ($image as $key => $value) {
						if ($key != "id" and $key != "id_diaporamas" and $key != "legende") {
							if ($key == "phrase_legende") {
								$q = <<<SQL
SELECT phrase FROM dt_phrases WHERE id = $value AND id_langues = $id_langues 
SQL;
								$res2 = $update->query($q);
								$row2 = $update->sql->fetch($res2);
								
								$fields[] = "legende";
								$values[] = $row2 ? "'".addslashes($row2['phrase'])."'" : "''";
							}
							else {
								$value = addslashes($value);
								$fields[] = $key;
								$values[] = "'$value'";
							}
						}
					}
					$list_fields = implode(",", $fields);
					$list_values = implode(",", $values);
					$q = <<<SQL
INSERT INTO dt_images_diaporamas ($list_fields) VALUES ($list_values)
SQL;
					$update->query($q);
				}
			}
		}

		$last_row = $row;
	}
	foreach ($last_row as $key => $value) {
		if (strpos($key, "phrase_") === 0) {
			$q = <<<SQL
ALTER TABLE dt_diaporamas DROP $key
SQL;
			$update->query($q);
		}
	}

	$q = <<<SQL
ALTER TABLE dt_images_diaporamas DROP phrase_legende
SQL;
	$update->query($q);
}

function update_49($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `notification_email` TEXT NOT NULL AFTER `notification`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `notification_email` TEXT NOT NULL AFTER `notification`
SQL;
	$update->query($q);
}

function update_50($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_produits_perso_gabarits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL,
  `ref` varchar(128) NOT NULL,
  `phrase_nom` int(11) NOT NULL,
  `apercu` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_produits_perso_textes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits_perso_gabarits` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `min_caracteres` int(11) NOT NULL,
  `max_caracteres` int(11) NOT NULL,
  `min_lignes` int(11) NOT NULL,
  `max_lignes` int(11) NOT NULL,
  `css` text NOT NULL,
  `statut` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_produits_perso_gabarits` (`id_produits_perso_gabarits`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_produits_perso_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits_perso_gabarits` int(11) NOT NULL,
  `fichier` varchar(128) NOT NULL,
  `formats` varchar(128) NOT NULL,
  `min_largeur` int(11) NOT NULL,
  `max_largeur` int(11) NOT NULL,
  `min_hauteur` int(11) NOT NULL,
  `max_hauteur` int(11) NOT NULL,
  `min_poids` int(11) NOT NULL,
  `max_poids` int(11) NOT NULL,
  `css` text NOT NULL,
  `statut` int(1) NOT NULL DEFAULT 0,
  `background` int(1) NOT NULL DEFAULT 0,
  `contain` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_produits_perso_gabarits` (`id_produits_perso_gabarits`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_commandes_perso_gabarits` (
  `id_commandes_produits` int(11) NOT NULL,
  `id_produits_perso_gabarits` int(11) NOT NULL,
  PRIMARY KEY (`id_commandes_produits`),
  KEY `id_produits_perso_gabarits` (`id_produits_perso_gabarits`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_commandes_perso_textes` (
  `id_commandes_produits` int(11) NOT NULL,
  `id_produits_perso_textes` int(11) NOT NULL,
  `texte` text NOT NULL,
  PRIMARY KEY (`id_commandes_produits`, `id_produits_perso_textes`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_commandes_perso_images` (
  `id_commandes_produits` int(11) NOT NULL,
  `id_produits_perso_images` int(11) NOT NULL,
  `fichier` varchar(128) NOT NULL,
  `apercu` varchar(128) NOT NULL,
  PRIMARY KEY (`id_commandes_produits`, `id_produits_perso_images`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` ADD `personnalisation_cle` VARCHAR( 128 ) NOT NULL DEFAULT ''
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` ADD `personnalisation_cle` VARCHAR( 128 ) NOT NULL DEFAULT ''
SQL;
	$update->query($q);
}

function update_51($update) {
	$q = <<<SQL
ALTER TABLE dt_attributs ADD `id_groupes_attributs` int(11) NOT NULL DEFAULT 0 AFTER `id_types_attributs` 
SQL;
	$update->query($q);

	$q = <<<SQL
SELECT id_attributs, groupe FROM dt_gammes_attributs_management WHERE groupe > 0
SQL;
	$res = $update->query($q);

	while ($row = $update->sql->fetch($res)) {
		$q = <<<SQL
UPDATE dt_attributs SET id_groupes_attributs = {$row['groupe']} WHERE id = {$row['id_attributs']}
SQL;
		$update->query($q);
	}

	$q = <<<SQL
ALTER TABLE `dt_gammes_attributs_management` DROP `groupe`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_produits_attributs_management` DROP `groupe`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs_management` DROP `groupe`
SQL;
	$update->query($q);
}

function update_52($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(250) NOT NULL,
  `phrase_nom` int(11) NOT NULL,
  `phrase_description` int(11) NOT NULL,
  `fichier` varchar(150) NOT NULL,
  `actif` tinyint(1) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `copyright` varchar(150) NOT NULL,
  `date_creation` int(11) NOT NULL,
  `date_modification` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_langues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_assets` int(11) NOT NULL,
  `id_langues` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_assets` (`id_assets`),
  KEY `id_langues` (`id_langues`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) NOT NULL,
  `phrase_nom` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_tags_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_assets` int(11) NOT NULL,
  `id_assets_tags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_assets` (`id_assets`),
  KEY `id_assets_tags` (`id_assets_tags`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_assets` int(11) NOT NULL,
  `link_type` varchar(64) NOT NULL,
  `link_id` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `link` (`link_type`,`link_id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);
}

function update_53($update) {
	$q = <<<SQL
ALTER TABLE `dt_prix` ADD `frais_port` FLOAT NOT NULL DEFAULT '0' AFTER `franco`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` ADD `franco` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `echantillon`, ADD `frais_port` FLOAT NOT NULL DEFAULT '0' AFTER `franco`
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` ADD `franco` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `echantillon`, ADD `frais_port` FLOAT NOT NULL DEFAULT '0' AFTER `franco`
SQL;
	$update->query($q);
}

function update_54($update) {
	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `api_tracked` VARCHAR( 64 ) NOT NULL DEFAULT '' AFTER `id_api_keys` ;
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `api_tracked` VARCHAR( 64 ) NOT NULL DEFAULT '' AFTER `id_api_keys` ;
SQL;
	$update->query($q);
}

function update_55($update) {
	$q = <<<SQL
ALTER TABLE `dt_assets` ADD `infos` TEXT NOT NULL;
SQL;
	$update->query($q);
}

function update_56($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(128) NOT NULL,
  `phrase_nom` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_assets_targets_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_assets` int(11) NOT NULL,
  `id_assets_targets` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_assets` (`id_assets`),
  KEY `id_assets_targets` (`id_assets_targets`)
)
SQL;
	$update->query($q);
}

function update_57($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_comptes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) NOT NULL,
  `id_contacts_organisations` int(11) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contacts_organisations` (`id_contacts_organisations`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_correspondants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `civilite` int(11) NOT NULL,
  `nom` varchar(128) NOT NULL,
  `prenom` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `date_password` int(11) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_correspondants_comptes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contacts_correspondants` int(11) NOT NULL,
  `id_contacts_comptes` int(11) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contacts_correspondants` (`id_contacts_correspondants`,`id_contacts_comptes`)
)
SQL;
	$update->query($q);

	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_correspondants_donnees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contacts_correspondants` int(11) NOT NULL,
  `id_contacts_donnees` int(11) NOT NULL,
  `valeur` varchar(512) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contacts_correspondants` (`id_contacts_correspondants`,`id_contacts_donnees`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_donnees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(32) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL

CREATE TABLE IF NOT EXISTS `dt_contacts_fonctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_organisations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contacts_organisations_types` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL,
  `complement` varchar(64) NOT NULL,
  `id_parent` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `www` varchar(128) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_organisations_correspondants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contacts_organisations` int(11) NOT NULL,
  `id_contacts_correspondants` int(11) NOT NULL,
  `id_contacts_fonctions` int(11) NOT NULL,
  `id_contacts_services` int(11) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contacts_organisations` (`id_contacts_organisations`,`id_contacts_correspondants`,`id_contacts_fonctions`,`id_contacts_services`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_organisations_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `phrase_nom` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);

	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(64) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->query($q);
}

function update_58($update) {
	$q = <<<SQL
ALTER TABLE `dt_contacts_correspondants` ADD `tiers_id` VARCHAR( 32 ) NOT NULL AFTER `id` ,
ADD INDEX ( `tiers_id` )
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_contacts_organisations` ADD `tiers_id` VARCHAR( 32 ) NOT NULL AFTER `id` ,
ADD INDEX ( `tiers_id` )
SQL;
	$update->query($q);
}

function update_59($update) {
	$q = <<<SQL
ALTER TABLE `dt_catalogues` ADD `phrase_nom` INT NOT NULL DEFAULT '0';
SQL;
	$update->query($q);

	$q = <<<SQL
ALTER TABLE `dt_catalogues_categories` ADD `phrase_nom` INT NOT NULL DEFAULT '0',
ADD `phrase_titre_url` INT NOT NULL DEFAULT '0',
ADD `phrase_meta_title` INT NOT NULL DEFAULT '0',
ADD `phrase_meta_description` INT NOT NULL DEFAULT '0',
ADD `phrase_meta_keywords` INT NOT NULL DEFAULT '0'
SQL;
	$update->query($q);
}

function update_60($update) {
	$q = <<<SQL
CREATE TABLE IF NOT EXISTS `dt_contacts_organisations_adresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(256) NOT NULL,
  `id_contacts_organisations` int(11) NOT NULL,
  `societe` varchar(256) NOT NULL,
  `complement` varchar(256) NOT NULL,
  `adresse_1` varchar(256) NOT NULL,
  `adresse_2` varchar(256) NOT NULL,
  `adresse_3` varchar(256) NOT NULL,
  `code_postal` varchar(32) NOT NULL,
  `ville` varchar(256) NOT NULL,
  `etat` varchar(256) NOT NULL,
  `id_pays` int(11) NOT NULL,
  `telephone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `statut` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pays` (`id_pays`),
  KEY `id_contacts_organisations` (`id_contacts_organisations`)
)
SQL;
	$update->query($q);
}
