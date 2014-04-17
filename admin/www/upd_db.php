<?php 
header('Content-type: text/html; charset=UTF-8');

include dirname(__FILE__)."/../includes/config.php";
$config->core_include("outils/mysql", "outils/url", "outils/dico");

$sql = new Mysql($config->db());

/*
 * ----------------- SCRIPTS
 */
// Scripts pour mettre à jour les noms et adjectifs des pays
//include dirname(__FILE__)."/../scripts/upd_pays.php";

// Appelle le script pour mettre à jour les descriptifs des drapeaux dans les produits
//include dirname(__FILE__)."/../scripts/update_flags.php";

// Appelle le script pour mettre à jour les sku
//include dirname(__FILE__)."/../scripts/clean_sku.php";

// Appelle le script pour mettre à jour les sku
//include dirname(__FILE__)."/../scripts/upd_sku_ultralog.php";

// Appelle le script pour importer dans magento
//include dirname(__FILE__)."/../scripts/import_prod_magento_10.php";

// Appelle le script pour mettre à jour les keywords
//include dirname(__FILE__)."/../scripts/upd_keywords.php";

// Appelle le script pour extraits codes SKU personnalisables
//include dirname(__FILE__)."/../scripts/sku_custom.php";

// Appelle le script pour mettre à jour les keywords
//include dirname(__FILE__)."/../scripts/upd_familles_vente.php";

// Appelle le script pour contrôler des références articles dans un fichier CSV
//include dirname(__FILE__)."/../scripts/check_references.php";

// Appelle le script pour la création des codes familles en lettres
//include dirname(__FILE__)."/../scripts/upd_familles_vente.php";

// Appelle le script pour fichier routage élections
//include dirname(__FILE__)."/../scripts/routage.php";

// Appelle le script pour créer les ref des pavillons des nations
//include dirname(__FILE__)."/../scripts/creation_drapeaux.php";

// Appelle le script pour mettre les images des drapeaux et leurs accessoires
//include dirname(__FILE__)."/../scripts/upd_images_flags.php";

// Appelle le script pour fichier routage podiums
//include dirname(__FILE__)."/../scripts/routage2.php";

// Appelle le script pour fichier routage coussins
//include dirname(__FILE__)."/../scripts/routage3.php";

// Appelle le script pour fichier routage 24 pages pavoisement
//include dirname(__FILE__)."/../scripts/routage4.php";

// Appelle le script pour créer les signalétiques de cimetière
//include dirname(__FILE__)."/../scripts/import_cimetiere.php";

// Appelle le script pour mettre à jour les attributs des chaises
//include dirname(__FILE__)."/../scripts/upd_chaises.php";

// Appelle le script pour importer les 84000 codes drapeaux
//include dirname(__FILE__)."/../scripts/import_new_drapeaux_2012.php";

// Appelle le script pour nettoyer les derniers codes drapeaux
//include dirname(__FILE__)."/../scripts/nettoyage-flags.php";

// Appelle le script pour nettoyer les derniers codes drapeaux
//include dirname(__FILE__)."/../../../project/scripts/update_flags_prices.php";

// Appelle le script pour nettoyer les derniers codes drapeaux
//include dirname(__FILE__)."/../../../project/scripts/update_flags_2012.php";

// Appelle le script pour le routage du CG2013
//include dirname(__FILE__)."/../../../project/scripts/routage5.php";

// Appelle le script pour le changer les url_keys
//include dirname(__FILE__)."/../scripts/check_url_keys.php";

// Appelle le script pour nettoyer les derniers codes drapeaux
//include dirname(__FILE__)."/../../../project/scripts/update_flags.php";

// Appelle le script pour le routage catalogue collèges
include dirname(__FILE__)."/../../../project/scripts/routage14.php";
?>