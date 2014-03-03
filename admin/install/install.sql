-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: doublet_prod
-- ------------------------------------------------------
-- Server version	5.5.35-0ubuntu0.12.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(32) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `table_id_name` varchar(16) NOT NULL DEFAULT '',
  `id_table` int(11) NOT NULL DEFAULT '0',
  `language` varchar(5) NOT NULL DEFAULT '',
  `date_creation` int(10) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `emails` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys_data`
--

DROP TABLE IF EXISTS `api_keys_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys_data` (
  `id_key` int(11) NOT NULL DEFAULT '0',
  `data_key` varchar(64) NOT NULL DEFAULT '',
  `data_value` text,
  PRIMARY KEY (`id_key`,`data_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys_roles`
--

DROP TABLE IF EXISTS `api_keys_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys_roles` (
  `id_key` int(11) NOT NULL DEFAULT '0',
  `id_role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_key`,`id_role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys_rules`
--

DROP TABLE IF EXISTS `api_keys_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(16) NOT NULL DEFAULT '',
  `uri` varchar(256) NOT NULL DEFAULT '',
  `type` enum('deny','allow') NOT NULL,
  `id_key` int(11) NOT NULL DEFAULT '0',
  `log` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys_vocabulary`
--

DROP TABLE IF EXISTS `api_keys_vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys_vocabulary` (
  `id_key` int(11) NOT NULL DEFAULT '0',
  `term_key` varchar(64) NOT NULL DEFAULT '',
  `term_value` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_key`,`term_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_key` int(11) NOT NULL DEFAULT '0',
  `method` varchar(16) NOT NULL DEFAULT '',
  `uri` varchar(256) NOT NULL DEFAULT '',
  `status` varchar(4) NOT NULL DEFAULT '',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_roles`
--

DROP TABLE IF EXISTS `api_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_roles_rules`
--

DROP TABLE IF EXISTS `api_roles_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_roles_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(16) NOT NULL DEFAULT '',
  `uri` varchar(256) NOT NULL DEFAULT '',
  `type` enum('deny','allow') NOT NULL,
  `id_role` int(11) NOT NULL DEFAULT '0',
  `log` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_tracker`
--

DROP TABLE IF EXISTS `api_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_widgets` int(11) NOT NULL DEFAULT '0',
  `id_keys` int(11) NOT NULL DEFAULT '0',
  `tracked` varchar(64) NOT NULL DEFAULT '',
  `action` varchar(64) NOT NULL DEFAULT '',
  `item` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_widgets` (`id_widgets`,`id_keys`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `at_emails`
--

DROP TABLE IF EXISTS `at_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `at_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL DEFAULT '',
  `date_record` int(11) NOT NULL DEFAULT '0',
  `adresse_ip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `combinaisons_drapeaux`
--

DROP TABLE IF EXISTS `combinaisons_drapeaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `combinaisons_drapeaux` (
  `id` int(11) NOT NULL DEFAULT '0',
  `numart` int(11) NOT NULL DEFAULT '0',
  `designation_nouvelle` varchar(250) NOT NULL DEFAULT '',
  `ssv` int(11) NOT NULL DEFAULT '0',
  `fond` int(11) NOT NULL DEFAULT '0',
  `matiere` int(11) NOT NULL DEFAULT '0',
  `dimension` int(11) NOT NULL DEFAULT '0',
  `finition` int(11) NOT NULL DEFAULT '0',
  `pays` int(11) NOT NULL DEFAULT '0',
  `region` int(11) NOT NULL DEFAULT '0',
  `organisation` int(11) NOT NULL DEFAULT '0',
  `sans_ecusson` int(11) NOT NULL DEFAULT '0',
  `serie` varchar(5) NOT NULL DEFAULT '',
  `prix_unitaire` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `pays` (`pays`),
  KEY `ssv` (`ssv`),
  KEY `numart` (`numart`),
  KEY `region` (`region`),
  KEY `organisation` (`organisation`),
  KEY `matiere` (`matiere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dpx_regions_articles`
--

DROP TABLE IF EXISTS `dpx_regions_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dpx_regions_articles` (
  `id` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(10) NOT NULL DEFAULT '',
  `type` enum('simple','grouped') NOT NULL,
  `nom` varchar(250) NOT NULL DEFAULT '',
  `short_description` text,
  `description` text,
  `description2` text,
  `description3` text,
  `image` varchar(150) NOT NULL DEFAULT '',
  `alt_image` int(250) NOT NULL DEFAULT '0',
  `prix` decimal(8,2) NOT NULL DEFAULT '0.00',
  `hauteur` int(11) NOT NULL DEFAULT '0',
  `largeur` int(11) NOT NULL DEFAULT '0',
  `code_matiere` int(11) NOT NULL DEFAULT '0',
  `meta_title` varchar(250) NOT NULL DEFAULT '',
  `meta_keywords` text,
  `meta_description` text,
  `url_path` varchar(250) NOT NULL DEFAULT '',
  `code_region` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_administrations`
--

DROP TABLE IF EXISTS `dt_administrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_administrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_applications`
--

DROP TABLE IF EXISTS `dt_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `phrase_description_courte` int(11) NOT NULL DEFAULT '0',
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `phrase_url_key` int(11) NOT NULL DEFAULT '0',
  `phrase_produit_description` int(11) NOT NULL DEFAULT '0',
  `phrase_produit_description_courte` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_applications_attributs`
--

DROP TABLE IF EXISTS `dt_applications_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_applications_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_applications` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  `obligatoire` tinyint(1) NOT NULL DEFAULT '0',
  `fiche_technique` tinyint(1) NOT NULL DEFAULT '0',
  `pictos_vente` tinyint(1) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `comparatif` tinyint(1) NOT NULL DEFAULT '0',
  `filtre` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_applications` (`id_applications`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_attributs`
--

DROP TABLE IF EXISTS `dt_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(60) NOT NULL DEFAULT '',
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `id_types_attributs` int(11) NOT NULL DEFAULT '0',
  `id_unites_mesure` int(11) NOT NULL DEFAULT '0',
  `norme` varchar(60) NOT NULL DEFAULT '',
  `actif` tinyint(1) NOT NULL DEFAULT '0',
  `matiere` tinyint(1) NOT NULL DEFAULT '0',
  `affichage_liste` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `id_types_attributs` (`id_types_attributs`),
  KEY `id_unites_mesure` (`id_unites_mesure`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_attributs_references`
--

DROP TABLE IF EXISTS `dt_attributs_references`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_attributs_references` (
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `field_label` varchar(256) NOT NULL DEFAULT '',
  `field_value` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_attributs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_attributs_valeurs`
--

DROP TABLE IF EXISTS `dt_attributs_valeurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_attributs_valeurs` (
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `type_valeur` varchar(32) NOT NULL DEFAULT '',
  `valeur` varchar(128) NOT NULL DEFAULT '',
  `valeur_libre` text,
  PRIMARY KEY (`id_attributs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_billets`
--

DROP TABLE IF EXISTS `dt_billets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_billets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(250) NOT NULL DEFAULT '',
  `texte` longtext,
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `meta_title` varchar(250) NOT NULL DEFAULT '',
  `meta_description` tinytext,
  `meta_keywords` tinytext,
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `date_affichage` int(10) NOT NULL DEFAULT '0',
  `id_users` int(11) NOT NULL DEFAULT '0',
  `date_creation` int(10) NOT NULL DEFAULT '0',
  `date_update` int(10) NOT NULL DEFAULT '0',
  `titre_url` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_langues` (`id_langues`),
  KEY `id_users` (`id_users`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_billets_themes_blogs`
--

DROP TABLE IF EXISTS `dt_billets_themes_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_billets_themes_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_billets` int(11) NOT NULL DEFAULT '0',
  `id_themes_blogs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_billets` (`id_billets`),
  KEY `id_themes_blogs` (`id_themes_blogs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_blocs`
--

DROP TABLE IF EXISTS `dt_blocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_blocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(128) NOT NULL DEFAULT '',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `contenu` text,
  `actif` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `id_langues` (`id_langues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_blocs_dependances`
--

DROP TABLE IF EXISTS `dt_blocs_dependances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_blocs_dependances` (
  `id_blocs_parent` int(11) NOT NULL DEFAULT '0',
  `id_blocs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_blocs_parent`,`id_blocs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_blogs`
--

DROP TABLE IF EXISTS `dt_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL DEFAULT '',
  `access` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_blogs_langues`
--

DROP TABLE IF EXISTS `dt_blogs_langues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_blogs_langues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_blogs` int(11) NOT NULL DEFAULT '0',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_blogs` (`id_blogs`),
  KEY `id_langues` (`id_langues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_blogs_themes_blogs`
--

DROP TABLE IF EXISTS `dt_blogs_themes_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_blogs_themes_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_blogs` int(11) NOT NULL DEFAULT '0',
  `id_themes_blogs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_blogs` (`id_blogs`),
  KEY `id_themes_blogs` (`id_themes_blogs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_boutiques`
--

DROP TABLE IF EXISTS `dt_boutiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_boutiques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(60) NOT NULL,
  `id_catalogues` int(11) NOT NULL,
  `id_api_keys` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_catalogues` (`id_catalogues`,`id_api_keys`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_boutiques_data`
--

DROP TABLE IF EXISTS `dt_boutiques_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_boutiques_data` (
  `id_boutiques` int(11) NOT NULL DEFAULT '0',
  `data_key` varchar(64) NOT NULL DEFAULT '',
  `data_value` text,
  PRIMARY KEY (`id_boutiques`,`data_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_catalogues`
--

DROP TABLE IF EXISTS `dt_catalogues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(128) NOT NULL DEFAULT '',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `type` int(4) NOT NULL DEFAULT '0',
  `statut` tinyint(1) NOT NULL DEFAULT '0',
  `home` int(11) NOT NULL DEFAULT '0',
  `export_frequency` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_langues` (`id_langues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_catalogues_categories`
--

DROP TABLE IF EXISTS `dt_catalogues_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_catalogues_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `nom` varchar(128) NOT NULL DEFAULT '',
  `correspondance` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  `statut` tinyint(1) NOT NULL DEFAULT '0',
  `date_modification` int(11) NOT NULL DEFAULT '0',
  `id_blocs` int(11) NOT NULL DEFAULT '0',
  `titre_url` varchar(60) NOT NULL DEFAULT '',
  `meta_title` text,
  `meta_description` text,
  `meta_keywords` text,
  PRIMARY KEY (`id`),
  KEY `id_catalogues` (`id_catalogues`),
  KEY `id_parent` (`id_parent`),
  KEY `id_blocs` (`id_blocs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_catalogues_categories_produits`
--

DROP TABLE IF EXISTS `dt_catalogues_categories_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_catalogues_categories_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_catalogues_categories` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_catalogues_categories` (`id_catalogues_categories`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_clients`
--

DROP TABLE IF EXISTS `dt_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `acces` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_clients_password`
--

DROP TABLE IF EXISTS `dt_clients_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_clients_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_clients` int(11) NOT NULL DEFAULT '0',
  `key` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_codes_articles`
--

DROP TABLE IF EXISTS `dt_codes_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_codes_articles` (
  `code_article` int(11) NOT NULL DEFAULT '0',
  `code_fam` varchar(10) NOT NULL DEFAULT '000',
  `designation` varchar(100) NOT NULL DEFAULT '',
  `famille` varchar(100) NOT NULL DEFAULT '',
  `sous_famille` varchar(100) NOT NULL DEFAULT '',
  `franco` varchar(4) NOT NULL DEFAULT '',
  `prix_catalogue` decimal(8,2) NOT NULL DEFAULT '0.00',
  `type` varchar(100) NOT NULL DEFAULT '',
  `kit` varchar(4) NOT NULL DEFAULT '',
  `date_debut` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_fin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cg_10` tinyint(1) NOT NULL DEFAULT '0',
  `table_dpx` tinyint(1) NOT NULL DEFAULT '0',
  `code_pays` char(2) NOT NULL DEFAULT '',
  `ventes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `code_matiere` int(11) NOT NULL DEFAULT '0',
  `hauteur` int(11) NOT NULL DEFAULT '0',
  `largeur` int(11) NOT NULL DEFAULT '0',
  `code_montage` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code_article`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_commandes`
--

DROP TABLE IF EXISTS `dt_commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop` int(11) NOT NULL DEFAULT '0',
  `id_api_keys` int(11) NOT NULL DEFAULT '0',
  `id_boutiques` int(11) NOT NULL DEFAULT '0',
  `token` varchar(16) NOT NULL DEFAULT '',
  `etat` int(11) NOT NULL DEFAULT '0',
  `montant` float NOT NULL DEFAULT '0',
  `frais_de_port` float NOT NULL DEFAULT '0',
  `tva` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `nom` varchar(128) NOT NULL DEFAULT '',
  `prenom` varchar(128) NOT NULL DEFAULT '',
  `profil` tinyint(4) NOT NULL DEFAULT '0',
  `societe` varchar(128) NOT NULL DEFAULT '',
  `num_client` varchar(20) NOT NULL DEFAULT '',
  `siret` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `telephone` varchar(32) NOT NULL DEFAULT '',
  `fax` varchar(32) NOT NULL DEFAULT '',
  `livraison_societe` varchar(128) NOT NULL DEFAULT '',
  `livraison_societe2` varchar(128) NOT NULL DEFAULT '',
  `livraison_adresse` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse2` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse3` varchar(256) NOT NULL DEFAULT '',
  `livraison_cp` varchar(16) NOT NULL DEFAULT '',
  `livraison_ville` varchar(64) NOT NULL DEFAULT '',
  `livraison_cedex` varchar(32) NOT NULL DEFAULT '',
  `livraison_pays` int(11) NOT NULL DEFAULT '0',
  `facturation_societe` varchar(128) NOT NULL DEFAULT '',
  `facturation_societe2` varchar(128) NOT NULL DEFAULT '',
  `facturation_adresse` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse2` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse3` varchar(256) NOT NULL DEFAULT '',
  `facturation_cp` varchar(16) NOT NULL DEFAULT '',
  `facturation_ville` varchar(64) NOT NULL DEFAULT '',
  `facturation_cedex` varchar(32) NOT NULL DEFAULT '',
  `facturation_pays` int(11) NOT NULL DEFAULT '0',
  `date_commande` int(10) NOT NULL DEFAULT '0',
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis','manuel') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text,
  `notification` int(11) NOT NULL DEFAULT '0',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `id_api_keys` (`id_api_keys`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_commandes_produits`
--

DROP TABLE IF EXISTS `dt_commandes_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_commandes_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_commandes` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `nom` varchar(256) NOT NULL DEFAULT '',
  `prix_unitaire` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `quantite` float NOT NULL DEFAULT '0',
  `echantillon` tinyint(1) NOT NULL DEFAULT '0',
  `personnalisation_texte` text,
  `personnalisation_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_nom_fichier` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_commandes` (`id_commandes`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_commandes_produits_revisions`
--

DROP TABLE IF EXISTS `dt_commandes_produits_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_commandes_produits_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `revision` int(11) NOT NULL DEFAULT '0',
  `date_revision` int(11) NOT NULL DEFAULT '0',
  `id_commandes_produits` int(11) NOT NULL DEFAULT '0',
  `id_commandes` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `nom` varchar(256) NOT NULL DEFAULT '',
  `prix_unitaire` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `quantite` float NOT NULL DEFAULT '0',
  `echantillon` tinyint(1) NOT NULL DEFAULT '0',
  `personnalisation_texte` text,
  `personnalisation_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_nom_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_objet` longblob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `revision` (`revision`,`id_commandes_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_commandes_revisions`
--

DROP TABLE IF EXISTS `dt_commandes_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_commandes_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `revision` int(11) NOT NULL DEFAULT '0',
  `date_revision` int(11) NOT NULL DEFAULT '0',
  `id_users` int(11) NOT NULL DEFAULT '0',
  `id_commandes` int(11) NOT NULL DEFAULT '0',
  `shop` int(11) NOT NULL DEFAULT '0',
  `id_api_keys` int(11) NOT NULL DEFAULT '0',
  `id_boutiques` int(11) NOT NULL DEFAULT '0',
  `token` varchar(16) NOT NULL DEFAULT '',
  `etat` int(11) NOT NULL DEFAULT '0',
  `montant` float NOT NULL DEFAULT '0',
  `frais_de_port` float NOT NULL DEFAULT '0',
  `tva` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `nom` varchar(128) NOT NULL DEFAULT '',
  `prenom` varchar(128) NOT NULL DEFAULT '',
  `profil` tinyint(4) NOT NULL DEFAULT '0',
  `societe` varchar(128) NOT NULL DEFAULT '',
  `num_client` varchar(20) NOT NULL DEFAULT '',
  `siret` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `telephone` varchar(32) NOT NULL DEFAULT '',
  `fax` varchar(32) NOT NULL DEFAULT '',
  `livraison_societe` varchar(128) NOT NULL DEFAULT '',
  `livraison_societe2` varchar(128) NOT NULL DEFAULT '',
  `livraison_adresse` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse2` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse3` varchar(256) NOT NULL DEFAULT '',
  `livraison_cp` varchar(16) NOT NULL DEFAULT '',
  `livraison_ville` varchar(64) NOT NULL DEFAULT '',
  `livraison_cedex` varchar(32) NOT NULL DEFAULT '',
  `livraison_pays` int(11) NOT NULL DEFAULT '0',
  `facturation_societe` varchar(128) NOT NULL DEFAULT '',
  `facturation_societe2` varchar(128) NOT NULL DEFAULT '',
  `facturation_adresse` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse2` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse3` varchar(256) NOT NULL DEFAULT '',
  `facturation_cp` varchar(16) NOT NULL DEFAULT '',
  `facturation_ville` varchar(64) NOT NULL DEFAULT '',
  `facturation_cedex` varchar(32) NOT NULL DEFAULT '',
  `facturation_pays` int(11) NOT NULL DEFAULT '0',
  `date_commande` int(10) NOT NULL DEFAULT '0',
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis','manuel') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text,
  `notification` int(11) NOT NULL DEFAULT '0',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `revision` (`revision`,`id_commandes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_commentaires`
--

DROP TABLE IF EXISTS `dt_commentaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_commentaires` (
  `id` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('approved','disapproved','spam') NOT NULL,
  `texte` longtext,
  `nature` enum('blog','produit') NOT NULL,
  `item` int(11) NOT NULL DEFAULT '0',
  `date_creation` int(10) NOT NULL DEFAULT '0',
  `nom_auteur` varchar(60) NOT NULL DEFAULT '',
  `ip_auteur` varchar(60) NOT NULL DEFAULT '',
  `email_auteur` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `item` (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_couleurs`
--

DROP TABLE IF EXISTS `dt_couleurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_couleurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_couleurs` int(11) NOT NULL DEFAULT '0',
  `hexadecimal` varchar(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `phrase_couleurs` (`phrase_couleurs`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_devis_pose`
--

DROP TABLE IF EXISTS `dt_devis_pose`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_devis_pose` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_commande` int(11) NOT NULL DEFAULT '0',
  `num_devis` int(11) NOT NULL DEFAULT '0',
  `type_pose` varchar(32) NOT NULL DEFAULT '',
  `champ` varchar(128) NOT NULL DEFAULT '',
  `valeur` text,
  `date_creation` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_diaporamas`
--

DROP TABLE IF EXISTS `dt_diaporamas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_diaporamas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(128) NOT NULL DEFAULT '',
  `id_themes_photos` int(11) NOT NULL DEFAULT '0',
  `phrase_titre` int(11) NOT NULL DEFAULT '0',
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `phrase_url_key` int(11) NOT NULL DEFAULT '0',
  `vignette` varchar(250) NOT NULL DEFAULT '',
  `section` enum('equipments','branding','events','venues') NOT NULL,
  `classement` int(11) NOT NULL DEFAULT '0',
  `actif` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_themes_photos` (`id_themes_photos`),
  KEY `phrase_titre` (`phrase_titre`),
  KEY `phrase_description` (`phrase_description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_dimensions`
--

DROP TABLE IF EXISTS `dt_dimensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_dimensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `largeur` decimal(10,2) NOT NULL DEFAULT '0.00',
  `longueur` decimal(10,2) NOT NULL DEFAULT '0.00',
  `profondeur` decimal(10,2) NOT NULL DEFAULT '0.00',
  `id_unites_mesure` int(11) NOT NULL DEFAULT '0',
  `pavillon` enum('','horizontal','vertical','event') NOT NULL,
  `trainee` decimal(3,2) NOT NULL DEFAULT '0.00',
  `ratio` decimal(3,0) NOT NULL DEFAULT '0',
  `forme_event` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_unites_mesure` (`id_unites_mesure`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_documents`
--

DROP TABLE IF EXISTS `dt_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(250) NOT NULL DEFAULT '',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `fichier` varchar(150) NOT NULL DEFAULT '',
  `vignette` varchar(150) NOT NULL DEFAULT '',
  `actif` tinyint(1) NOT NULL DEFAULT '0',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `id_types_documents` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_langues` (`id_langues`),
  KEY `id_types_documents` (`id_types_documents`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_documents_gammes`
--

DROP TABLE IF EXISTS `dt_documents_gammes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_documents_gammes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_gammes` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_gammes` (`id_gammes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_documents_produits`
--

DROP TABLE IF EXISTS `dt_documents_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_documents_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_produits` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_documents_sku`
--

DROP TABLE IF EXISTS `dt_documents_sku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_documents_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_documents` int(11) NOT NULL,
  `id_sku` int(11) NOT NULL,
  `classement` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_documents` (`id_documents`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_duo_couleurs`
--

DROP TABLE IF EXISTS `dt_duo_couleurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_duo_couleurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `image_duo` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_eco_contribution`
--

DROP TABLE IF EXISTS `dt_eco_contribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_eco_contribution` (
  `id` int(11) NOT NULL DEFAULT '0',
  `nom_famille` varchar(80) NOT NULL DEFAULT '',
  `montant` decimal(4,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_ecolabels`
--

DROP TABLE IF EXISTS `dt_ecolabels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_ecolabels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `logo_label` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_ecotaxes`
--

DROP TABLE IF EXISTS `dt_ecotaxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_ecotaxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `id_familles_taxes` int(11) NOT NULL DEFAULT '0',
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `montant` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`,`id_pays`,`id_familles_taxes`,`id_catalogues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_exports_catalogues`
--

DROP TABLE IF EXISTS `dt_exports_catalogues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_exports_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `etat` enum('tobuild','building','built') NOT NULL,
  `fichier` varchar(128) NOT NULL DEFAULT '',
  `date_export` int(11) NOT NULL DEFAULT '0',
  `data` text,
  `auto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_catalogues` (`id_catalogues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_exports_produits`
--

DROP TABLE IF EXISTS `dt_exports_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_exports_produits` (
  `id_produits` int(11) NOT NULL,
  `date_export` int(11) NOT NULL,
  PRIMARY KEY (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_factures`
--

DROP TABLE IF EXISTS `dt_factures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_factures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(256) NOT NULL DEFAULT '',
  `id_commandes` int(11) NOT NULL DEFAULT '0',
  `shop` int(11) NOT NULL DEFAULT '0',
  `id_api_keys` int(11) NOT NULL DEFAULT '0',
  `token` varchar(16) NOT NULL DEFAULT '',
  `etat` int(11) NOT NULL DEFAULT '0',
  `montant` float NOT NULL DEFAULT '0',
  `frais_de_port` float NOT NULL DEFAULT '0',
  `tva` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `nom` varchar(128) NOT NULL DEFAULT '',
  `prenom` varchar(128) NOT NULL DEFAULT '',
  `profil` tinyint(4) NOT NULL DEFAULT '0',
  `societe` varchar(128) NOT NULL DEFAULT '',
  `num_client` varchar(20) NOT NULL DEFAULT '',
  `siret` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `telephone` varchar(32) NOT NULL DEFAULT '',
  `fax` varchar(32) NOT NULL DEFAULT '',
  `livraison_societe` varchar(128) NOT NULL DEFAULT '',
  `livraison_societe2` varchar(128) NOT NULL DEFAULT '',
  `livraison_adresse` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse2` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse3` varchar(256) NOT NULL DEFAULT '',
  `livraison_cp` varchar(16) NOT NULL DEFAULT '',
  `livraison_ville` varchar(64) NOT NULL DEFAULT '',
  `livraison_cedex` varchar(32) NOT NULL DEFAULT '',
  `livraison_pays` int(11) NOT NULL DEFAULT '0',
  `facturation_societe` varchar(128) NOT NULL DEFAULT '',
  `facturation_societe2` varchar(128) NOT NULL DEFAULT '',
  `facturation_adresse` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse2` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse3` varchar(256) NOT NULL DEFAULT '',
  `facturation_cp` varchar(16) NOT NULL DEFAULT '',
  `facturation_ville` varchar(64) NOT NULL DEFAULT '',
  `facturation_cedex` varchar(32) NOT NULL DEFAULT '',
  `facturation_pays` int(11) NOT NULL DEFAULT '0',
  `date_commande` int(10) NOT NULL DEFAULT '0',
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `id_api_keys` (`id_api_keys`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_factures_produits`
--

DROP TABLE IF EXISTS `dt_factures_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_factures_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_factures` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `nom` varchar(256) NOT NULL DEFAULT '',
  `prix_unitaire` float NOT NULL DEFAULT '0',
  `ecotaxe` float NOT NULL DEFAULT '0',
  `quantite` int(11) NOT NULL DEFAULT '0',
  `echantillon` tinyint(1) NOT NULL DEFAULT '0',
  `personnalisation_texte` text,
  `personnalisation_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_nom_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_objet` longblob,
  PRIMARY KEY (`id`),
  KEY `id_factures` (`id_factures`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_familles_matieres`
--

DROP TABLE IF EXISTS `dt_familles_matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_familles_matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_familles_taxes`
--

DROP TABLE IF EXISTS `dt_familles_taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_familles_taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_taxe` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_familles_ventes`
--

DROP TABLE IF EXISTS `dt_familles_ventes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_familles_ventes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL DEFAULT '0',
  `phrase_famille` int(11) NOT NULL DEFAULT '0',
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `ref` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `phrase_famille` (`phrase_famille`),
  KEY `id_parent` (`id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_fiches_matieres_modeles`
--

DROP TABLE IF EXISTS `dt_fiches_matieres_modeles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_fiches_matieres_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `html` text,
  `css` text,
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_fiches_modeles`
--

DROP TABLE IF EXISTS `dt_fiches_modeles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_fiches_modeles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `html` text,
  `css` text,
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_fiches_produits`
--

DROP TABLE IF EXISTS `dt_fiches_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_fiches_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users` int(11) NOT NULL DEFAULT '0',
  `element` varchar(50) NOT NULL DEFAULT '',
  `zone` varchar(50) NOT NULL DEFAULT '',
  `classement` int(11) NOT NULL DEFAULT '0',
  `html` text,
  `xml` text,
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_filiales`
--

DROP TABLE IF EXISTS `dt_filiales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_filiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_version` varchar(4) NOT NULL DEFAULT '',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_pays` (`id_pays`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_filtre_emails`
--

DROP TABLE IF EXISTS `dt_filtre_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_filtre_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(250) NOT NULL DEFAULT '',
  `niveau` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_frais_port`
--

DROP TABLE IF EXISTS `dt_frais_port`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_frais_port` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_boutiques` int(11) NOT NULL DEFAULT '0',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `id_pays` int(11) DEFAULT NULL,
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `methode` varchar(128) NOT NULL DEFAULT '',
  `phrase_info` int(11) NOT NULL DEFAULT '0',
  `prix_min` int(11) NOT NULL DEFAULT '0',
  `forfait` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_langues` (`id_langues`),
  KEY `id_pays` (`id_pays`),
  KEY `id_catalogues` (`id_catalogues`),
  KEY `phrase_info` (`phrase_info`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_gabarits_produits`
--

DROP TABLE IF EXISTS `dt_gabarits_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_gabarits_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_gabarits_sku`
--

DROP TABLE IF EXISTS `dt_gabarits_sku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_gabarits_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_gammes`
--

DROP TABLE IF EXISTS `dt_gammes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_gammes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `phrase_description_courte` int(11) NOT NULL DEFAULT '0',
  `phrase_url_key` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `phrase_description` (`phrase_description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_gammes_attributs`
--

DROP TABLE IF EXISTS `dt_gammes_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_gammes_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_gammes` int(11) NOT NULL DEFAULT '0',
  `type_valeur` enum('valeur_numerique','phrase_valeur','valeur_libre') NOT NULL,
  `valeur_numerique` float NOT NULL DEFAULT '0',
  `phrase_valeur` int(11) NOT NULL DEFAULT '0',
  `valeur_libre` text,
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_gammes` (`id_gammes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_gammes_attributs_management`
--

DROP TABLE IF EXISTS `dt_gammes_attributs_management`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_gammes_attributs_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_gammes` int(11) NOT NULL DEFAULT '0',
  `groupe` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_gammes` (`id_gammes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_green_ticket`
--

DROP TABLE IF EXISTS `dt_green_ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_green_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) NOT NULL DEFAULT '',
  `company` varchar(150) NOT NULL DEFAULT '',
  `ticket` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `facture` int(11) NOT NULL DEFAULT '0',
  `client` int(11) NOT NULL DEFAULT '0',
  `colis` int(11) NOT NULL DEFAULT '0',
  `version` varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_groupes_attributs`
--

DROP TABLE IF EXISTS `dt_groupes_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_groupes_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_groupes_users`
--

DROP TABLE IF EXISTS `dt_groupes_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_groupes_users` (
  `id` int(11) NOT NULL DEFAULT '0',
  `nom` varchar(120) NOT NULL DEFAULT '',
  `perm` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_images_diaporamas`
--

DROP TABLE IF EXISTS `dt_images_diaporamas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_images_diaporamas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_diaporamas` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_images_gammes`
--

DROP TABLE IF EXISTS `dt_images_gammes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_images_gammes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gammes` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `vignette` tinyint(1) NOT NULL DEFAULT '0',
  `diaporama` tinyint(1) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  `hd_extension` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_gammes`),
  KEY `phrase_legende` (`phrase_legende`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_images_matieres`
--

DROP TABLE IF EXISTS `dt_images_matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_images_matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_matieres` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `vignette` tinyint(1) NOT NULL DEFAULT '0',
  `diaporama` tinyint(1) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_matieres` (`id_matieres`),
  KEY `phrase_legende` (`phrase_legende`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_images_produits`
--

DROP TABLE IF EXISTS `dt_images_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_images_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `vignette` tinyint(1) NOT NULL DEFAULT '0',
  `diaporama` tinyint(1) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  `hd_extension` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `phrase_legende` (`phrase_legende`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_images_sku`
--

DROP TABLE IF EXISTS `dt_images_sku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_images_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(250) NOT NULL DEFAULT '',
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `vignette` tinyint(1) NOT NULL DEFAULT '0',
  `diaporama` tinyint(1) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  `hd_extension` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`),
  KEY `phrase_legende` (`phrase_legende`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_index_phrases`
--

DROP TABLE IF EXISTS `dt_index_phrases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_index_phrases` (
  `id_phrases` int(11) NOT NULL DEFAULT '0',
  `table` varchar(64) NOT NULL DEFAULT '',
  `field` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_phrases`,`table`,`field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_infos`
--

DROP TABLE IF EXISTS `dt_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_infos` (
  `champ` varchar(64) NOT NULL DEFAULT '',
  `valeur` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`champ`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_langues`
--

DROP TABLE IF EXISTS `dt_langues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_langues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_langue` varchar(5) NOT NULL DEFAULT '',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `format` enum('fr','en') CHARACTER SET utf8 NOT NULL DEFAULT 'fr',
  PRIMARY KEY (`id`),
  KEY `id_pays` (`id_pays`),
  KEY `code_langue` (`code_langue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_magento_commandes`
--

DROP TABLE IF EXISTS `dt_magento_commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_magento_commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop` int(11) NOT NULL DEFAULT '0',
  `id_api_keys` int(11) NOT NULL DEFAULT '0',
  `token` varchar(16) NOT NULL DEFAULT '',
  `etat` int(11) NOT NULL DEFAULT '0',
  `montant` float NOT NULL DEFAULT '0',
  `frais_de_port` float NOT NULL DEFAULT '0',
  `tva` float NOT NULL DEFAULT '0',
  `nom` varchar(128) NOT NULL DEFAULT '',
  `prenom` varchar(128) NOT NULL DEFAULT '',
  `profil` tinyint(4) NOT NULL DEFAULT '0',
  `societe` varchar(128) NOT NULL DEFAULT '',
  `num_client` varchar(20) NOT NULL DEFAULT '',
  `siret` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `telephone` varchar(32) NOT NULL DEFAULT '',
  `fax` varchar(32) NOT NULL DEFAULT '',
  `livraison_societe` varchar(128) NOT NULL DEFAULT '',
  `livraison_societe2` varchar(128) NOT NULL DEFAULT '',
  `livraison_adresse` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse2` varchar(256) NOT NULL DEFAULT '',
  `livraison_adresse3` varchar(256) NOT NULL DEFAULT '',
  `livraison_cp` varchar(16) NOT NULL DEFAULT '',
  `livraison_ville` varchar(64) NOT NULL DEFAULT '',
  `livraison_cedex` varchar(32) NOT NULL DEFAULT '',
  `livraison_pays` int(11) NOT NULL DEFAULT '0',
  `facturation_societe` varchar(128) NOT NULL DEFAULT '',
  `facturation_societe2` varchar(128) NOT NULL DEFAULT '',
  `facturation_adresse` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse2` varchar(256) NOT NULL DEFAULT '',
  `facturation_adresse3` varchar(256) NOT NULL DEFAULT '',
  `facturation_cp` varchar(16) NOT NULL DEFAULT '',
  `facturation_ville` varchar(64) NOT NULL DEFAULT '',
  `facturation_cedex` varchar(32) NOT NULL DEFAULT '',
  `facturation_pays` int(11) NOT NULL DEFAULT '0',
  `date_commande` int(10) NOT NULL DEFAULT '0',
  `paiement` enum('cheque','mandat','facture','cb','paypal','devis') NOT NULL,
  `paiement_statut` enum('attente','valide','refuse','annule','rembourse','test') NOT NULL,
  `commentaire` text,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `id_api_keys` (`id_api_keys`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_magento_commandes_produits`
--

DROP TABLE IF EXISTS `dt_magento_commandes_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_magento_commandes_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_magento_commandes` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(20) NOT NULL DEFAULT '',
  `nom` varchar(256) NOT NULL DEFAULT '',
  `prix_unitaire` float NOT NULL DEFAULT '0',
  `quantite` int(11) NOT NULL DEFAULT '0',
  `personnalisation_texte` text,
  `personnalisation_fichier` varchar(128) NOT NULL DEFAULT '',
  `personnalisation_nom_fichier` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_magento_commandes` (`id_magento_commandes`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_matieres`
--

DROP TABLE IF EXISTS `dt_matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_matieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_matiere` varchar(70) NOT NULL DEFAULT '',
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `phrase_description_courte` int(11) NOT NULL DEFAULT '0',
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `id_familles_matieres` int(11) NOT NULL DEFAULT '0',
  `phrase_entretien` int(11) NOT NULL DEFAULT '0',
  `phrase_marques_fournisseurs` int(11) NOT NULL DEFAULT '0',
  `id_ecolabels` int(11) NOT NULL DEFAULT '0',
  `id_recyclage` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_description_courte` (`phrase_description_courte`),
  KEY `phrase_description` (`phrase_description`),
  KEY `id_familles_matieres` (`id_familles_matieres`),
  KEY `phrase_entretien` (`phrase_entretien`),
  KEY `phrase_marques_fournisseurs` (`phrase_marques_fournisseurs`),
  KEY `id_ecolabels` (`id_ecolabels`),
  KEY `id_recyclage` (`id_recyclage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_matieres_applications`
--

DROP TABLE IF EXISTS `dt_matieres_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_matieres_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_matieres` int(11) NOT NULL DEFAULT '0',
  `id_applications` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_matieres` (`id_matieres`),
  KEY `id_applications` (`id_applications`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_matieres_attributs`
--

DROP TABLE IF EXISTS `dt_matieres_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_matieres_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_matieres` int(11) NOT NULL DEFAULT '0',
  `valeur_numerique` int(11) NOT NULL DEFAULT '0',
  `phrase_valeur` int(11) NOT NULL DEFAULT '0',
  `valeur_libre` text,
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_mats`
--

DROP TABLE IF EXISTS `dt_mats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_mats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forme` enum('conique','cylindrique') NOT NULL,
  `matiere` enum('acier','alu','fibre') NOT NULL,
  `hauteur` int(11) NOT NULL DEFAULT '0',
  `diametre_haut` int(11) NOT NULL DEFAULT '0',
  `diametre_bas` int(11) NOT NULL DEFAULT '0',
  `diametre_moyen` int(11) NOT NULL DEFAULT '0',
  `inertie` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_messages`
--

DROP TABLE IF EXISTS `dt_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL DEFAULT '',
  `date_envoi` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `version` varchar(4) NOT NULL DEFAULT '',
  `profil` tinyint(4) NOT NULL DEFAULT '0',
  `siret` varchar(25) NOT NULL DEFAULT '',
  `organisme` varchar(50) NOT NULL DEFAULT '',
  `civilite` enum('M','Mlle','Mme') NOT NULL,
  `nom` varchar(50) NOT NULL DEFAULT '',
  `prenom` varchar(50) NOT NULL DEFAULT '',
  `fonction` varchar(50) NOT NULL DEFAULT '',
  `num_client` varchar(20) NOT NULL DEFAULT '',
  `adresse` varchar(200) NOT NULL DEFAULT '',
  `adresse2` varchar(200) NOT NULL DEFAULT '',
  `adresse3` varchar(200) NOT NULL DEFAULT '',
  `cp` varchar(10) NOT NULL DEFAULT '',
  `ville` varchar(50) NOT NULL DEFAULT '',
  `pays` varchar(2) NOT NULL DEFAULT '',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `email` varchar(100) NOT NULL DEFAULT '',
  `tel` varchar(20) NOT NULL DEFAULT '',
  `mob` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(20) NOT NULL DEFAULT '',
  `accepter_email` int(1) NOT NULL DEFAULT '0',
  `accepter_tel` int(1) NOT NULL DEFAULT '0',
  `accepter_catalogue` int(1) NOT NULL DEFAULT '0',
  `message` text,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_messages_devis`
--

DROP TABLE IF EXISTS `dt_messages_devis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_messages_devis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_messages` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `produit` varchar(100) NOT NULL DEFAULT '',
  `fichier` varchar(300) NOT NULL DEFAULT '',
  `delai` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_messages` (`id_messages`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_montages`
--

DROP TABLE IF EXISTS `dt_montages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_montages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_montages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_montages` (`phrase_montages`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_motifs_drapeaux`
--

DROP TABLE IF EXISTS `dt_motifs_drapeaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_motifs_drapeaux` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img_motif` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_options_attributs`
--

DROP TABLE IF EXISTS `dt_options_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_options_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `phrase_option` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `phrase_option` (`phrase_option`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_organisations_internationales`
--

DROP TABLE IF EXISTS `dt_organisations_internationales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_organisations_internationales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_abreviations` int(11) NOT NULL DEFAULT '0',
  `phrase_organisations` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_abreviations` (`phrase_abreviations`),
  KEY `phrase_organisations` (`phrase_organisations`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_pages`
--

DROP TABLE IF EXISTS `dt_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(128) NOT NULL DEFAULT '',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `contenu` text,
  `actif` tinyint(4) NOT NULL DEFAULT '0',
  `design` enum('none','header/footer') NOT NULL,
  `meta_title` text,
  `meta_description` text,
  `meta_keywords` text,
  `intitule_url` varchar(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `id_langues` (`id_langues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_paniers`
--

DROP TABLE IF EXISTS `dt_paniers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_paniers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL DEFAULT '',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `quantite` int(11) NOT NULL DEFAULT '0',
  `personnalisation` text,
  `date_ajout` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_pays`
--

DROP TABLE IF EXISTS `dt_pays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_pays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `code_iso` char(2) NOT NULL DEFAULT '',
  `code_ultralog` int(11) NOT NULL DEFAULT '0',
  `id_continents` int(11) NOT NULL DEFAULT '0',
  `niveau_priorite` enum('0','1','2') NOT NULL,
  `num_serie` enum('0','1','2','3','11','12','13') NOT NULL DEFAULT '0',
  `phrase_nom_courant` int(11) NOT NULL DEFAULT '0',
  `phrase_adjectif` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `code_iso` (`code_iso`),
  KEY `code_ultralog` (`code_ultralog`),
  KEY `id_continents` (`id_continents`),
  KEY `phrase_nom_courant` (`phrase_nom_courant`),
  KEY `phrase_adjectif` (`phrase_adjectif`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_pays_details`
--

DROP TABLE IF EXISTS `dt_pays_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_pays_details` (
  `id_pays_details` int(11) NOT NULL DEFAULT '0',
  `langue` char(2) NOT NULL DEFAULT '',
  `code_pays` char(2) NOT NULL DEFAULT '',
  `nom_officiel` varchar(150) NOT NULL DEFAULT '',
  `nom_courant` varchar(150) NOT NULL DEFAULT '',
  `adjectif` varchar(100) NOT NULL DEFAULT '',
  `capitale` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_pays_details`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_pays_export`
--

DROP TABLE IF EXISTS `dt_pays_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_pays_export` (
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `code_pays` char(2) NOT NULL DEFAULT '',
  `nom_pays_fr` varchar(150) NOT NULL DEFAULT '',
  `niveau` tinyint(4) NOT NULL DEFAULT '0',
  `serie` tinyint(4) NOT NULL DEFAULT '0',
  `ecusson` tinyint(1) NOT NULL DEFAULT '0',
  `img_drapeau` varchar(100) NOT NULL DEFAULT '',
  `img_drapeau_sans_ecusson` varchar(100) NOT NULL DEFAULT '',
  `code_odiso` int(11) NOT NULL DEFAULT '0',
  `code_ultralog` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_pays`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_personnalisations`
--

DROP TABLE IF EXISTS `dt_personnalisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_personnalisations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `quantite` int(11) NOT NULL DEFAULT '0',
  `texte` text,
  `fichier` varchar(40) NOT NULL DEFAULT '',
  `nom_fichier` varchar(128) NOT NULL DEFAULT '',
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `date_personnalisation` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_personnalisations_produits`
--

DROP TABLE IF EXISTS `dt_personnalisations_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_personnalisations_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `type` enum('texte','fichier') NOT NULL,
  `libelle` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_photographes`
--

DROP TABLE IF EXISTS `dt_photographes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_photographes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_photos`
--

DROP TABLE IF EXISTS `dt_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref` varchar(255) NOT NULL DEFAULT '',
  `type` enum('ambiance','technique','logo','illustration') NOT NULL,
  `acces` enum('prive','public') NOT NULL,
  `phrase_legende` int(11) NOT NULL DEFAULT '0',
  `id_photographes` int(11) NOT NULL DEFAULT '0',
  `date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_legende` (`phrase_legende`),
  KEY `id_photographes` (`id_photographes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_photos_themes_photos`
--

DROP TABLE IF EXISTS `dt_photos_themes_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_photos_themes_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_photos` int(11) NOT NULL DEFAULT '0',
  `id_themes_photos` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_photos` (`id_photos`),
  KEY `id_themes_photos` (`id_themes_photos`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_phrases`
--

DROP TABLE IF EXISTS `dt_phrases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_phrases` (
  `id` int(11) NOT NULL DEFAULT '0',
  `phrase` text,
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `date_creation` int(10) NOT NULL DEFAULT '0',
  `date_update` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`id_langues`),
  KEY `id` (`id`),
  KEY `id_langues` (`id_langues`),
  FULLTEXT KEY `phrase` (`phrase`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_prix`
--

DROP TABLE IF EXISTS `dt_prix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_prix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `montant_ht` decimal(8,2) NOT NULL DEFAULT '0.00',
  `franco` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`),
  KEY `id_catalogues` (`id_catalogues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_prix_degressifs`
--

DROP TABLE IF EXISTS `dt_prix_degressifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_prix_degressifs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `id_catalogues` int(11) NOT NULL DEFAULT '0',
  `montant_ht` decimal(8,2) NOT NULL DEFAULT '0.00',
  `pourcentage` float NOT NULL DEFAULT '0',
  `quantite` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`),
  KEY `id_catalogues` (`id_catalogues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits`
--

DROP TABLE IF EXISTS `dt_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `id_types_produits` int(11) NOT NULL DEFAULT '0',
  `id_applications` int(11) NOT NULL DEFAULT '0',
  `phrase_commercial` int(11) NOT NULL DEFAULT '0',
  `ref` varchar(60) NOT NULL DEFAULT '',
  `actif` tinyint(1) NOT NULL DEFAULT '0',
  `nouveau` tinyint(1) NOT NULL DEFAULT '0',
  `id_gammes` int(11) NOT NULL DEFAULT '0',
  `offre` int(11) NOT NULL DEFAULT '0',
  `phrase_description_courte` int(11) NOT NULL DEFAULT '0',
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `phrase_url_key` int(11) NOT NULL DEFAULT '0',
  `phrase_meta_title` int(11) NOT NULL DEFAULT '0',
  `phrase_meta_description` int(11) NOT NULL DEFAULT '0',
  `phrase_meta_keywords` int(11) NOT NULL DEFAULT '0',
  `date_creation` int(11) NOT NULL DEFAULT '0',
  `date_modification` int(11) NOT NULL DEFAULT '0',
  `phrase_entretien` int(11) NOT NULL DEFAULT '0',
  `phrase_mode_emploi` int(11) NOT NULL DEFAULT '0',
  `phrase_avantages_produit` int(11) NOT NULL DEFAULT '0',
  `id_recyclage` int(11) NOT NULL DEFAULT '0',
  `phrase_designation_auto` int(11) NOT NULL DEFAULT '0',
  `echantillon` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `id_types_produits` (`id_types_produits`),
  KEY `id_applications` (`id_applications`),
  KEY `phrase_commercial` (`phrase_commercial`),
  KEY `id_gammes` (`id_gammes`),
  KEY `phrase_description_courte` (`phrase_description_courte`),
  KEY `phrase_description` (`phrase_description`),
  KEY `phrase_url_key` (`phrase_url_key`),
  KEY `phrase_meta_title` (`phrase_meta_title`),
  KEY `phrase_meta_description` (`phrase_meta_description`),
  KEY `phrase_meta_keywords` (`phrase_meta_keywords`),
  KEY `phrase_entretien` (`phrase_entretien`),
  KEY `phrase_mode_emploi` (`phrase_mode_emploi`),
  KEY `phrase_avantages_produit` (`phrase_avantages_produit`),
  KEY `id_recyclage` (`id_recyclage`),
  KEY `phrase_designation_auto` (`phrase_designation_auto`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits_attributs`
--

DROP TABLE IF EXISTS `dt_produits_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `type_valeur` enum('valeur_numerique','phrase_valeur','valeur_libre') NOT NULL,
  `valeur_numerique` float NOT NULL DEFAULT '0',
  `phrase_valeur` int(11) NOT NULL DEFAULT '0',
  `valeur_libre` text,
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits_attributs_management`
--

DROP TABLE IF EXISTS `dt_produits_attributs_management`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits_attributs_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `groupe` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_produits` (`id_produits`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits_complementaires`
--

DROP TABLE IF EXISTS `dt_produits_complementaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits_complementaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_produits_compl` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `id_produits_compl` (`id_produits_compl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits_complements`
--

DROP TABLE IF EXISTS `dt_produits_complements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits_complements` (
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `id_regions` int(11) NOT NULL DEFAULT '0',
  `id_administrations` int(11) NOT NULL DEFAULT '0',
  `id_matieres` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_produits_similaires`
--

DROP TABLE IF EXISTS `dt_produits_similaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_produits_similaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_produits_sim` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `id_produits_sim` (`id_produits_sim`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_ral`
--

DROP TABLE IF EXISTS `dt_ral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_ral` (
  `id` int(11) NOT NULL DEFAULT '0',
  `hexadecimal` varchar(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_recherches`
--

DROP TABLE IF EXISTS `dt_recherches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_recherches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(64) NOT NULL DEFAULT '',
  `terme` varchar(256) NOT NULL DEFAULT '',
  `date_recherche` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `terme` (`terme`),
  KEY `site` (`site`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_recyclage`
--

DROP TABLE IF EXISTS `dt_recyclage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_recyclage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` tinyint(4) NOT NULL DEFAULT '0',
  `phrase_abreviations` int(11) NOT NULL DEFAULT '0',
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `logo` varchar(120) NOT NULL DEFAULT '',
  `phrase_recyclage` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_abreviations` (`phrase_abreviations`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `phrase_recyclage` (`phrase_recyclage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_references_catalogues`
--

DROP TABLE IF EXISTS `dt_references_catalogues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_references_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sku` int(11) NOT NULL,
  `id_catalogues` int(11) NOT NULL,
  `reference` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_sku` (`id_sku`,`id_catalogues`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_regions`
--

DROP TABLE IF EXISTS `dt_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_region` int(11) NOT NULL DEFAULT '0',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `id_administrations` int(11) NOT NULL DEFAULT '0',
  `id_motifs_drapeaux` int(11) NOT NULL DEFAULT '0',
  `num_serie` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_region` (`phrase_region`),
  KEY `id_pays` (`id_pays`),
  KEY `id_administrations` (`id_administrations`),
  KEY `id_motifs_drapeaux` (`id_motifs_drapeaux`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_signalisation`
--

DROP TABLE IF EXISTS `dt_signalisation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_signalisation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(5) NOT NULL DEFAULT '',
  `designation` varchar(150) NOT NULL DEFAULT '',
  `visuel` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sites_tiers`
--

DROP TABLE IF EXISTS `dt_sites_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sites_tiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt_table` varchar(64) NOT NULL DEFAULT '',
  `dt_id` int(11) NOT NULL DEFAULT '0',
  `site` varchar(64) NOT NULL DEFAULT '',
  `entity_id` int(11) NOT NULL DEFAULT '0',
  `entity_table` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `dt_table` (`dt_table`),
  KEY `dt_id` (`dt_id`),
  KEY `site` (`site`),
  KEY `entity_id` (`entity_id`),
  KEY `entity_table` (`entity_table`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sites_tiers_synchro`
--

DROP TABLE IF EXISTS `dt_sites_tiers_synchro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sites_tiers_synchro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(64) NOT NULL DEFAULT '',
  `item` varchar(32) NOT NULL DEFAULT '',
  `date_synchro` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku`
--

DROP TABLE IF EXISTS `dt_sku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_ultralog` varchar(60) NOT NULL DEFAULT '',
  `id_motifs_drapeaux` int(11) NOT NULL DEFAULT '0',
  `id_montages` int(11) NOT NULL DEFAULT '0',
  `id_dimensions` int(11) NOT NULL DEFAULT '0',
  `id_matieres` int(11) NOT NULL DEFAULT '0',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `id_regions` int(11) NOT NULL DEFAULT '0',
  `id_organisations_internationales` int(11) NOT NULL DEFAULT '0',
  `id_familles_vente` int(11) NOT NULL DEFAULT '0',
  `id_unites_mesure` int(11) NOT NULL DEFAULT '0',
  `id_unites_vente` int(11) NOT NULL DEFAULT '1',
  `id_couleurs` int(11) NOT NULL DEFAULT '0',
  `id_ral` int(11) NOT NULL DEFAULT '0',
  `id_duo_couleurs` int(11) NOT NULL DEFAULT '0',
  `poids` decimal(8,2) NOT NULL DEFAULT '0.00',
  `colisage` float NOT NULL DEFAULT '1',
  `min_commande` float NOT NULL DEFAULT '1',
  `phrase_ultralog` int(11) NOT NULL DEFAULT '0',
  `phrase_commercial` int(11) NOT NULL DEFAULT '0',
  `phrase_path` int(11) NOT NULL DEFAULT '0',
  `date_creation` int(10) NOT NULL DEFAULT '0',
  `date_fin` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date_modification` int(11) NOT NULL DEFAULT '0',
  `actif` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_motifs_drapeaux` (`id_motifs_drapeaux`),
  KEY `id_montages` (`id_montages`),
  KEY `id_dimensions` (`id_dimensions`),
  KEY `id_matieres` (`id_matieres`),
  KEY `id_pays` (`id_pays`),
  KEY `id_regions` (`id_regions`),
  KEY `id_organisations_internationales` (`id_organisations_internationales`),
  KEY `id_familles_vente` (`id_familles_vente`),
  KEY `id_unites_mesure` (`id_unites_mesure`),
  KEY `id_unites_vente` (`id_unites_vente`),
  KEY `id_couleurs` (`id_couleurs`),
  KEY `id_ral` (`id_ral`),
  KEY `id_duo_couleurs` (`id_duo_couleurs`),
  KEY `phrase_ultralog` (`phrase_ultralog`),
  KEY `phrase_commercial` (`phrase_commercial`),
  KEY `phrase_path` (`phrase_path`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku_accessoires`
--

DROP TABLE IF EXISTS `dt_sku_accessoires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku_accessoires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku_attributs`
--

DROP TABLE IF EXISTS `dt_sku_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `type_valeur` enum('valeur_numerique','phrase_valeur','valeur_libre') NOT NULL,
  `valeur_numerique` float NOT NULL DEFAULT '0',
  `phrase_valeur` int(11) NOT NULL DEFAULT '0',
  `valeur_libre` text,
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku_attributs_management`
--

DROP TABLE IF EXISTS `dt_sku_attributs_management`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku_attributs_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_attributs` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `groupe` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_attributs` (`id_attributs`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku_composants`
--

DROP TABLE IF EXISTS `dt_sku_composants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku_composants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sku_variantes`
--

DROP TABLE IF EXISTS `dt_sku_variantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sku_variantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `id_sku` int(11) NOT NULL DEFAULT '0',
  `classement` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `id_sku` (`id_sku`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_sondage_satisfaction`
--

DROP TABLE IF EXISTS `dt_sondage_satisfaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_sondage_satisfaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_reponse` int(10) NOT NULL DEFAULT '0',
  `num_cde` int(11) NOT NULL DEFAULT '0',
  `q1` int(11) NOT NULL DEFAULT '0',
  `q2` int(11) NOT NULL DEFAULT '0',
  `q3` int(11) NOT NULL DEFAULT '0',
  `q4` int(11) NOT NULL DEFAULT '0',
  `q5` int(11) NOT NULL DEFAULT '0',
  `q6` int(11) NOT NULL DEFAULT '0',
  `q7` int(11) NOT NULL DEFAULT '0',
  `scoring` int(11) NOT NULL DEFAULT '0',
  `satisfait` tinyint(4) NOT NULL DEFAULT '0',
  `langue` varchar(5) NOT NULL DEFAULT 'fr_FR',
  `commentaires` text,
  `email` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_statistiques`
--

DROP TABLE IF EXISTS `dt_statistiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_statistiques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(32) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL DEFAULT '',
  `item` int(11) NOT NULL DEFAULT '0',
  `date_requete` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_stats_emailing`
--

DROP TABLE IF EXISTS `dt_stats_emailing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_stats_emailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emailing` varchar(100) NOT NULL DEFAULT '',
  `date_envoi` int(11) NOT NULL DEFAULT '0',
  `id_filiales` int(11) NOT NULL DEFAULT '0',
  `nb_emails_db` int(11) NOT NULL DEFAULT '0',
  `nb_desabonnements` int(11) NOT NULL DEFAULT '0',
  `nb_emails_send` int(11) NOT NULL DEFAULT '0',
  `nb_emails_opened` int(11) NOT NULL DEFAULT '0',
  `nb_emails_clics` int(11) NOT NULL DEFAULT '0',
  `commentaires` text,
  `img_emailing` varchar(150) NOT NULL DEFAULT '',
  `pourcentage_npai` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pourcentage_ouverture` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pourcentage_clic` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pourcentage_reactivite` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pourcentage_desabonnements` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_themes_blogs`
--

DROP TABLE IF EXISTS `dt_themes_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_themes_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL DEFAULT '',
  `affichage` tinyint(1) NOT NULL DEFAULT '0',
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `titre_url` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id_parent` (`id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_themes_photos`
--

DROP TABLE IF EXISTS `dt_themes_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_themes_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  `id_parent` int(11) NOT NULL DEFAULT '0',
  `annee` year(4) NOT NULL DEFAULT '0000',
  `actif` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phrase_nom` (`phrase_nom`),
  KEY `id_parent` (`id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_tribunes_configurations`
--

DROP TABLE IF EXISTS `dt_tribunes_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_tribunes_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `salle` enum('spectacle','sports','pleinair') NOT NULL DEFAULT 'spectacle',
  `emplacement` enum('interieur','exterieur') NOT NULL DEFAULT 'interieur',
  `sieges_par_bloc` int(11) NOT NULL DEFAULT '0',
  `type` enum('telescopique','fixe','demontable') NOT NULL DEFAULT 'telescopique',
  `gradin_hauteur` int(11) NOT NULL DEFAULT '0',
  `siege_type` enum('banc','coque','coque_dossier','banquette','fauteuil_nez','fauteuil_sur','fauteuil_fond') NOT NULL DEFAULT 'banc',
  `gradin_profondeur` int(11) NOT NULL DEFAULT '0',
  `siege_largeur` int(11) NOT NULL DEFAULT '0',
  `siege_profondeur` int(11) NOT NULL DEFAULT '0',
  `siege_hauteur` int(11) NOT NULL DEFAULT '0',
  `vue_haut` varchar(150) NOT NULL DEFAULT '',
  `vue_coupe_1` varchar(150) NOT NULL DEFAULT '',
  `vue_coupe_2` varchar(150) NOT NULL DEFAULT '',
  `niveau` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_tribunes_projets`
--

DROP TABLE IF EXISTS `dt_tribunes_projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_tribunes_projets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(256) NOT NULL DEFAULT '',
  `commune` varchar(256) NOT NULL DEFAULT '',
  `contact` varchar(256) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `telephone` varchar(32) NOT NULL DEFAULT '',
  `commercial` varchar(256) NOT NULL DEFAULT '',
  `date_modification` int(10) NOT NULL DEFAULT '0',
  `parametres` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_types_attributs`
--

DROP TABLE IF EXISTS `dt_types_attributs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_types_attributs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '',
  `phrase_nom` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_types_documents`
--

DROP TABLE IF EXISTS `dt_types_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_types_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_types_images`
--

DROP TABLE IF EXISTS `dt_types_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_types_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_description` int(11) NOT NULL DEFAULT '0',
  `largeur` int(5) NOT NULL DEFAULT '0',
  `hauteur` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_types_produits`
--

DROP TABLE IF EXISTS `dt_types_produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_types_produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_unites_mesure`
--

DROP TABLE IF EXISTS `dt_unites_mesure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_unites_mesure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unite` varchar(9) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_unites_vente`
--

DROP TABLE IF EXISTS `dt_unites_vente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_unites_vente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase_unite` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_update`
--

DROP TABLE IF EXISTS `dt_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_update` (
  `nature` varchar(32) NOT NULL DEFAULT '',
  `number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nature`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_url_redirections`
--

DROP TABLE IF EXISTS `dt_url_redirections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_url_redirections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_langues` int(11) NOT NULL DEFAULT '0',
  `code_url` varchar(255) NOT NULL DEFAULT '',
  `table` varchar(128) NOT NULL DEFAULT '',
  `variable` int(11) NOT NULL DEFAULT '0',
  `niveau` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_url` (`code_url`),
  KEY `id_langues` (`id_langues`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_url_redirections_contenu`
--

DROP TABLE IF EXISTS `dt_url_redirections_contenu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_url_redirections_contenu` (
  `id_url_redirections` int(11) NOT NULL DEFAULT '0',
  `contenu` text,
  PRIMARY KEY (`id_url_redirections`),
  FULLTEXT KEY `contenu` (`contenu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_users`
--

DROP TABLE IF EXISTS `dt_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `acces` tinyint(1) NOT NULL DEFAULT '0',
  `id_groupes_users` int(11) NOT NULL DEFAULT '0',
  `id_langues` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `login` (`login`),
  KEY `id_groupes_users` (`id_groupes_users`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_users_blogs`
--

DROP TABLE IF EXISTS `dt_users_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_users_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users` int(11) NOT NULL DEFAULT '0',
  `id_blogs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_users` (`id_users`),
  KEY `id_blogs` (`id_blogs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_users_password`
--

DROP TABLE IF EXISTS `dt_users_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_users_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_users` int(11) NOT NULL DEFAULT '0',
  `key` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dt_zones_vents`
--

DROP TABLE IF EXISTS `dt_zones_vents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dt_zones_vents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `zone` int(11) NOT NULL DEFAULT '0',
  `force` int(11) NOT NULL DEFAULT '0',
  `valeur` enum('min','max') NOT NULL DEFAULT 'min',
  `vent_km` decimal(4,1) NOT NULL DEFAULT '0.0',
  `vent_m` decimal(4,1) NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`),
  KEY `id_pays` (`id_pays`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ecotaxe`
--

DROP TABLE IF EXISTS `ecotaxe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ecotaxe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produits` int(11) NOT NULL DEFAULT '0',
  `produit` varchar(150) NOT NULL DEFAULT '',
  `ref_ultralog` int(11) NOT NULL DEFAULT '0',
  `designation` varchar(150) NOT NULL DEFAULT '',
  `poids` decimal(6,2) NOT NULL DEFAULT '0.00',
  `actif` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_produits` (`id_produits`),
  KEY `ref_ultralog` (`ref_ultralog`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages_manquantes`
--

DROP TABLE IF EXISTS `pages_manquantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_manquantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_url` varchar(255) NOT NULL DEFAULT '',
  `date_requete` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shorturl_log`
--

DROP TABLE IF EXISTS `shorturl_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shorturl_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_url` varchar(16) NOT NULL DEFAULT '',
  `code_url` varchar(255) NOT NULL DEFAULT '',
  `date_requete` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_flags`
--

DROP TABLE IF EXISTS `temp_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_flags` (
  `id` int(11) NOT NULL DEFAULT '0',
  `nom` varchar(250) NOT NULL DEFAULT '',
  `id_pays` int(11) NOT NULL DEFAULT '0',
  `id_regions` int(11) NOT NULL DEFAULT '0',
  `id_matieres` varchar(8) NOT NULL DEFAULT '',
  `id_administrations` int(11) NOT NULL DEFAULT '0',
  `description` int(11) NOT NULL DEFAULT '0',
  `description_courte` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-03-03 12:32:00
