<?php
header('Content-type: text/html; charset=UTF-8');

include dirname(__FILE__)."/../../includes/config.php";
$config->core_include("outils/mysql", "outils/url", "outils/dico");

$sql = new Mysql($config->db());



/*
 * Les fonctions pour lister les données
 */
function lister_drapeau($id_drapeau=0) {
    $tab_drapeau = array( 1=> "drapeau/pavillon", 2=> "oriflamme", 3=> "drapeau de table", 4=>"fond de pavillon" );
    $html = '<option value="0">...</option>';
    foreach($tab_drapeau as $k => $v) {
        $html .= '<option value="'.$k.'" ';
        if ($id_drapeau > 0 AND $k == $id_drapeau) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$v.'</option>';
    }
    return $html;
}
function lister_pays($id_pays=0) {
	global $sql;
    $q = "SELECT p.id, p.code_iso, ph.phrase
    FROM dt_pays AS p
    INNER JOIN dt_phrases AS ph
    ON ph.id = p.phrase_nom_courant AND ph.id_langues = 1
    AND p.id NOT IN ('3','247','243','235','226','217','185','191','194','195','198','153','163','165','179','143','140','95','97','101','103','86','87','81','73','33','46','48','10','83')";
    $rs = $sql->query($q);
    $html = '<option value="0">...</option>';
    while($row = $sql->fetch($rs)) {
        $html .= '<option value="'.$row['id'].'" ';
        if ($id_pays > 0 AND $row['id'] == $id_pays) {
            $html .= ' selected="selected" ';
        }
        $html .= '>('.$row['code_iso'].') '.$row['phrase'].'</option>';
    }
    return $html;
}
function lister_region($id_region=0) {
	global $sql;
    $q = "SELECT r.id, ph.phrase, ph1.phrase AS administration, p.code_iso
        FROM dt_regions AS r
        INNER JOIN dt_phrases AS ph
        ON ph.id = r.phrase_region AND ph.id_langues = 1
        AND r.id NOT IN ('201','280','321')
        INNER JOIN dt_administrations AS ad
        ON ad.id = r.id_administrations
        INNER JOIN dt_phrases AS ph1
        ON ph1.id = ad.phrase_nom AND ph1.id_langues = 1
        INNER JOIN dt_pays AS p
        ON p.id = r.id_pays
        ORDER BY p.code_iso, r.id_administrations";
    $rs = $sql->query($q);
    $html = '<option value="0">...</option>';
    while($row = $sql->fetch($rs)) {
        $html .= '<option value="'.$row['id'].'" ';
        if ($id_region > 0 AND $row['id'] == $id_region) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$row['code_iso'].' - '.$row['phrase'].' ('.$row['administration'].')</option>';
    }
    return $html;
}
function lister_organisation($id_organisation=0) {
    $tab_organisation = array( 1=> "Europe", 2 => "O.N.U.", 3 => "O.T.A.N.", 4 => "C.I.O. - J.O.", 5 => "O.U.A." );
    $html = '<option value="0">...</option>';
    foreach($tab_organisation as $k => $v) {
        $html .= '<option value="'.$k.'" ';
        if ($id_organisation > 0 AND $k == $id_organisation) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$v.'</option>';
    }
    return $html;
}
function lister_dimensions($id_dimension=0) {
    $tab_dim = array( 167 => "10x15", 173 => "14x21", 1 => "40x60", 58 => "50x75", 31 => "60x90", 60 => "80x120", 61 => "100x150", 111 => "120x180", 231 => "125x45", 35 => "150x225", 232 => "200x75", 36 => "200x300", 181 => "300x120", 37 => "300x450", 39 => "400x600" );
    $html = '<option value="0">...</option>';
    foreach($tab_dim as $k => $v) {
        $html .= '<option value="'.$k.'" ';
        if ($id_dimension > 0 AND $k == $id_dimension) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$v.' cm</option>';
    }
    return $html;
}
function lister_matiere($id_matiere=0) {
    $tab_mat = array(   0=>"...", 1=> "Ecofix", 2=> "Rilfix", 3=> "Polyspun Marine", 5=> "Satin", 61=> "Papier");
    $html = '';
    foreach($tab_mat as $k => $v) {
        $html .= '<option value="'.$k.'" ';
        if ($id_matiere > 0 AND $k == $id_matiere) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$v.'</option>';
    }
    return $html;
}
function lister_finition($id_finition=0) {
    $tab_finition = array(  0=>"...", 1=>"Fixover", 2=>"Oeillets", 3=>"Anneaux", 4=>"Hampe", 9=>"Hampe plastique noir/or", 10=>"Hampe PVC noire", 11=>"Hampe métal");
    $html = '';
    foreach($tab_finition as $k => $v) {
        $html .= '<option value="'.$k.'" ';
        if ($id_finition > 0 AND $k == $id_finition) {
            $html .= ' selected="selected" ';
        }
        $html .= '>'.$v.'</option>';
    }
    return $html;
}


/*
 * Fonction de mise en page
 */
function bloc_ref($ref, $nom, $prix) {
    $bloc = '<dl>';
    $bloc .= '<dt>'.$ref.'</dt>';
    $bloc .= '<dd>'.$nom.'<br/><strong>'.$prix.' € HT.</strong></dd>';
    $bloc .= '</dl>';
    return $bloc;
}


if (isset($_POST['ctrl']) AND $_POST['ctrl'] > 0) {
    if ($_POST['drapeau'] > 0) {
        $query = "SELECT numart, designation_nouvelle, prix_unitaire
                    FROM combinaisons_drapeaux WHERE ";
        if ($_POST['drapeau'] == 4) {
            $query .= " fond = 1 ";
        }
        else {
            $query .= " fond = 0 ";
        }
        if ($_POST['drapeau'] == 2) {
            $query .= " AND finition = 0 ";
        }
        else if ($_POST['drapeau'] == 3) {
            $query .= " AND ssv > 4 ";
        }
        else {
            $query .= " AND ssv < 4 ";
        }
        if ($_POST['matiere'] > 0) {
            $query .= " AND matiere = ".$_POST['matiere'];
        }
        if ($_POST['dimension'] > 0) {
            $query .= " AND dimension = ".$_POST['dimension'];
        }
        if ($_POST['finition'] > 0) {
            $query .= " AND finition = ".$_POST['finition'];
        }
        if ($_POST['region'] > 0) {
            $query .= " AND region = ".$_POST['region'];
        }
        else if ($_POST['organisation'] > 0) {
            $query .= " AND organisation = ".$_POST['organisation'];
        }
        else if ($_POST['pays'] > 0) {
            $query .= " AND pays = ".$_POST['pays'];
        }
    }
    else {
        $query = "";
    }
}
else {
    $query = "";
}
?>

<html>
    <head>
        <title>Doublet : Recherche de drapeaux</title>
        <meta name="robots" content="noindex,nofollow" />
        <link href="main.css" rel="stylesheet" type="text/css" media="all" />
    </head>
    <body>
        <div id="container">
            <div id="recherche">
                <form name="search" method="post" action="#">
                    <fieldset>
                        <legend>Le type de drapeau</legend>
                        <p><label for="drapeau">
                            <span class="initule">Le modèle : </span>
                            <span class="valeur"><select name="drapeau" class=""><?php echo lister_drapeau($_POST['drapeau']); ?></select></span>
                        </label></p>
                    </fieldset>
                    <fieldset>
                        <legend>Son motif</legend>
                        <p><label for="pays">
                            <span class="initule">Le pays : </span>
                            <span class="valeur"><select name="pays" class=""><?php echo lister_pays($_POST['pays']); ?></select></span>
                        </label></p>
                        <p><label for="region">
                            <span class="initule">La région : </span>
                            <span class="valeur"><select name="region" class=""><?php echo lister_region($_POST['region']); ?></select></span>
                        </label></p>
                        <p><label for="region">
                            <span class="initule">L'organisation : </span>
                            <span class="valeur"><select name="organisation" class=""><?php echo lister_organisation($_POST['organisation']); ?></select></span>
                        </label></p>
                    </fieldset>
                    <fieldset>
                        <legend>Ses caractéristiques</legend>    
                        <p><label for="matiere">
                            <span class="initule">La matière : </span>
                            <span class="valeur"><select name="matiere" class=""><?php echo lister_matiere($_POST['matiere']); ?></select></span>
                        </label></p>
                        <p><label for="dimension">
                            <span class="initule">La dimension : </span>
                            <span class="valeur"><select name="dimension" class=""><?php echo lister_dimensions($_POST['dimension']); ?></select></span>
                        </label></p>
                        <p><label for="finition">
                            <span class="initule">La finition : </span>
                            <span class="valeur"><select name="finition" class=""><?php echo lister_finition($_POST['finition']); ?></select></span>
                        </label></p>
                    </fieldset>
                    <fieldset>      
                        <p><input type="submit" name="valider" id="valider" value="Envoyer" /></p>
                        <input type="hidden" name="ctrl" id="ctrl" value="1" />
                    </fieldset>
                    
                </form>
            </div>
            <div id="resultat">
                <?php
                    if (!empty($query)) {
                        $rs = $sql->query($query);
                        if ($sql->num_rows($rs) > 1) {
                            echo '<p>'.$sql->num_rows($rs).' résultats</p>';
                            echo '<ul>';
                            while($row = $sql->fetch($rs)) {
                                echo '<li>'.bloc_ref($row['numart'],$row['designation_nouvelle'],$row['prix_unitaire']).'</li>';
                            }
                            echo '</ul>';
                        }
                        else if ($sql->num_rows($rs) == 1) {
                            $row = $sql->fetch($rs);
                            echo '<p>1 résultat</p>';
                            echo bloc_ref($row['numart'],$row['designation_nouvelle'],$row['prix_unitaire']);
                        }
                        else {
                            echo '<p>aucune combinaison disponible</p>';
                        }
                    }
                    else {
                        echo '';
                    }
                ?>
            </div>
        </div>
    </body>
</html>
