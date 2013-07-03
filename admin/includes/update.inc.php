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
	$update->sql->query($q);

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
	$update->sql->query($q);
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
	$update->sql->query($q);

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
	$update->sql->query($q);
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
	$update->sql->query($q);
};

function update_4($update) {
	$q = <<<SQL
ALTER TABLE  `dt_commandes` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE  `dt_commandes_revisions` CHANGE  `paiement`  `paiement` ENUM(  'cheque',  'mandat',  'facture',  'cb',  'paypal',  'devis',  'manuel' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
SQL;
	$update->sql->query($q);
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
	$update->sql->query($q);

	$q = <<<SQL
CREATE TABLE `dt_clients_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_clients` int(11) NOT NULL,
  `key` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;	
SQL;
	$update->sql->query($q);
};

function update_6($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `classement` INT NOT NULL;
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `classement` INT NOT NULL;
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `classement` INT NOT NULL;
SQL;

	$update->sql->query($q);
};

function update_7($update) {
	$q = <<<SQL
ALTER TABLE `dt_gammes` ADD `phrase_description_courte` INT NOT NULL ,
ADD `phrase_url_key` INT NOT NULL ,
ADD `ref` VARCHAR( 20 ) NOT NULL 
SQL;
	$update->sql->query($q);
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
	$update->sql->query($q);
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
	$update->sql->query($q);
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
	$update->sql->query($q);
	$q = <<<SQL
ALTER TABLE `dt_attributs_valeurs` ADD `type_valeur` VARCHAR( 32 ) NOT NULL AFTER `id_attributs`
SQL;
	$update->sql->query($q);
};

function update_11($update) {
	$q = <<<SQL
ALTER TABLE `dt_applications` ADD `ref` VARCHAR( 20 ) NOT NULL ,
ADD `phrase_description_courte` INT( 11 ) NOT NULL ,
ADD `phrase_description` INT( 11 ) NOT NULL ,
ADD `phrase_url_key` INT( 11 ) NOT NULL
SQL;
	$update->sql->query($q);
};

function update_12($update) {
	$q = <<<SQL
ALTER TABLE `dt_users` ADD `id_langues` INT NOT NULL
SQL;
	$update->sql->query($q);
};

function update_13($update) {
	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id` ) 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_phrases` ADD INDEX ( `id_langues` )
SQL;
	$update->sql->query($q);
};

function update_14($update) {
	$q = <<<SQL
ALTER TABLE `dt_groupes_users` ADD `perm` LONGTEXT NOT NULL
SQL;
	$update->sql->query($q);
};

function update_15($update) {
	$q = <<<SQL
ALTER TABLE `dt_attributs_valeurs` ADD `valeur_libre` TEXT NOT NULL
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_matieres_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `valeur_libre` TEXT NOT NULL AFTER `phrase_valeur`
SQL;
	$update->sql->query($q);
}

function update_16($update) {
	$q = <<<SQL
ALTER TABLE `dt_produits_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_produits`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_sku_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_sku`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes_attributs` ADD `type_valeur` ENUM( 'valeur_numerique', 'phrase_valeur', 'valeur_libre' ) NOT NULL AFTER `id_gammes`
SQL;
	$update->sql->query($q);

	$q = <<<SQL
UPDATE `dt_produits_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->sql->query($q);

	$q = <<<SQL
UPDATE `dt_sku_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->sql->query($q);

	$q = <<<SQL
UPDATE `dt_gammes_attributs` SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> 0
SQL;
	$update->sql->query($q);

	$q = <<<SQL
CREATE TABLE `dt_groupes_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->sql->query($q);
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
		$update->sql->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'valeur_numerique'
SQL;
		$update->sql->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'phrase_valeur' WHERE phrase_valeur <> ''
SQL;
		$update->sql->query($q);

		$q = <<<SQL
UPDATE $table SET type_valeur = 'valeur_libre' WHERE valeur_libre <> ''
SQL;
		$update->sql->query($q);
		
		$q = <<<SQL
SELECT * FROM $table 
SQL;
		$res = $update->sql->query($q);
	
		$i = 1;
		$values = array();
		while ($row = $update->sql->fetch($res)) {
			$values[] = "({$row['id_attributs']}, {$row[$id_field]}, {$row['classement']})";
			if ($i % 33000 == 0) { // on insère par paquet de 33000
				$values = implode (",", $values);
				$q = <<<SQL
INSERT INTO {$table}_management (id_attributs, $id_field, classement) VALUES $values 
SQL;
				$update->sql->query($q);
				$values = array();
			}
			$i++;
		}
		if ($values = implode (",", $values)) {
			$q = <<<SQL
INSERT INTO {$table}_management (id_attributs, $id_field, classement) VALUES $values 
SQL;
			$update->sql->query($q); // insertion de ce qui reste
		}
	}
}

function update_18($update) {
	$q = <<<SQL
ALTER TABLE `dt_catalogues` ADD `export_frequency` INT NOT NULL 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_exports_catalogues` ADD `auto` INT NOT NULL 
SQL;
	$update->sql->query($q);
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
	$update->sql->query($q);
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
	$update->sql->query($q);

	$q = <<<SQL
CREATE TABLE `dt_familles_taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_taxe` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes` ADD `ecotaxe` FLOAT NOT NULL AFTER `tva` 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_revisions` ADD `ecotaxe` FLOAT NOT NULL AFTER `tva` 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits` ADD `ecotaxe` FLOAT NOT NULL AFTER `prix_unitaire` 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_commandes_produits_revisions` ADD `ecotaxe` FLOAT NOT NULL AFTER `prix_unitaire` 
SQL;
	$update->sql->query($q);
}

function update_21($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` CHANGE `ref_ultralog` `ref_ultralog` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_produits` CHANGE `ref` `ref` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->sql->query($q);

	$q = <<<SQL
ALTER TABLE `dt_gammes` CHANGE `ref` `ref` VARCHAR(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
SQL;
	$update->sql->query($q);
}

function update_22($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0'
SQL;
	$update->sql->query($q);
}

function update_23($update) {
	$q = <<<SQL
SHOW TABLES
SQL;
	$res = $update->sql->query($q);
	while ($row = $update->sql->fetch($res)) {
		$table = array_pop($row);
		$q = <<<SQL
DESCRIBE $table
SQL;
		$res2 = $update->sql->query($q);
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
					$update->sql->query($q);
				}
				else if ($null) {
					$q = <<<SQL
ALTER TABLE `$table` CHANGE `{$row2['Field']}` `{$row2['Field']}` {$row2['Type']} NULL
SQL;
					$update->sql->query($q);
				}
			}
		}
	}
}

function update_24($update) {
	$q = <<<SQL
ALTER TABLE `dt_sku` DROP `echantillon` 
SQL;
	$update->sql->query($q);
	$q = <<<SQL
ALTER TABLE `dt_produits` ADD `echantillon` TINYINT( 1 ) NOT NULL DEFAULT '0'
SQL;
	$update->sql->query($q);
}
