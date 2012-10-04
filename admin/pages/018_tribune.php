<?php
$titre = "Tribune";
$menu->current('main/tools/tribune');

$config->core_include("outils/form", "outils/mysql");
$config->core_include("outils/filter", "outils/pager");
$config->core_include("tribune/tribune2", "tribune/projet");
$config->core_include("tribune/projet");

$page->javascript[] = $config->core_media("jquery.min.js");
$page->javascript[] = $config->core_media("filter-edit.js");
$page->javascript[] = $config->media("tribune.js");
$page->jsvars[] = array(
	"edit_url" => $url->make("current", array('action' => 'projet', 'id' => "")),	
	"dico" => $dico->export(array(
		'ConfirmerSuppression',	
	)),
);
$page->css[] = $config->media("tribune.css");

$sql = new Mysql($config->db());

$projet = new TribuneProjet($sql);

$q = <<<SQL
SELECT * FROM dt_tribunes_configurations WHERE niveau = 1
SQL;

$res = $sql->query($q);
$tribunes_configurations = array();
while ($row = $sql->fetch($res)) {
	$tribunes_configurations[$row['salle']][$row['type']][$row['siege_type']] = $row;
}
$page->my_javascript[] = "var tribunes_configurations = ".json_encode($tribunes_configurations).";";

$form = new Form(array(
	'id' => "form-tribune",
	'actions' => array("draw", "calculate", "expert", "normal", "save", "register", "load"),
));

$form->template = <<<HTML
<div class="ligne_form">
<span style="display: inline-block; width: 180px;">#{label} : </span>
#{field}
</div>
HTML;

$image_dessus = "";
$image_cote = "";
$infos = "";

$action = "normal";
if ($form->is_submitted()) {
	$data = $form->escaped_values();
	$action = $form->action();
	switch ($action) {
		case "normal":
		case "expert":
			break;
		case "draw":
		case "calculate":
		case "print":
			$params = $data['tribune'];
			foreach ($params as $key => $value) {
				switch ($key) {
					case "siege_type" :
					case "sieges_par_bloc" :
					case "emplacement" :
					case "type" :
					case "salle" :
					case "garde_corps_gauche" :
					case "garde_corps_droite" :
					case "garde_corps_arriere" :
					case "garde_corps_telescopique" :
					case "bardage_gauche" :
					case "bardage_droite" :
					case "bardage_telescopique" :
						break;
					default :
						$value = str_replace(",", ".", $value);
						$value = str_replace(" ", "", $value);
						$params[$key] = (int)((float)$value * 1000);
						break;
				}
			}
			$params['echelle'] = $data['echelle'];
			$query_string = http_build_query($params);
			$tribune = new Tribune2($params);
			if ($action == "print") {
				$page->my_javascript[] = <<<JAVASCRIPT
window.print();
JAVASCRIPT;
			}
			break;
		case "save":
			$id = $projet->save($data);
			break;
	}
}
else {
	$form->reset();
	if ($url->get('action') == "projet") {
		$action = "register";
	}
}
if ($url->get('action') == "projet") {
	$id = $url->get('id');
}

$projet_values = array(
	'tribune' => array(
		'largeur' => 10,
		'profondeur'=> 6,
		'plafond'=> 8,
		'plafond_marge' => 2,
		'rangement_hauteur' => 8,
		'rangement_marge' => 0.5,
		'gradin_hauteur' => 0.4,
		'gradin_profondeur' => 1,
		'gradin_deporte_profondeur' => 0.5,
		'siege_largeur' => 0.4,
		'siege_profondeur' => 0.4,
		'siege_hauteur' => 0.4,
		'siege_epaisseur' => 0.1,
		'sieges_par_bloc' => 16,
		'emplacement' => 'interieur',
		'pied_arriere' => 0.5,
		'garde_corps_gauche' => 0,
		'garde_corps_droite' => 0,
		'garde_corps_arriere' => 0,
		'garde_corps_telescopique' => 0,
		'bardage_gauche' => 0,
		'bardage_droite' => 0,
		'bardage_telescopique' => 0,
	),
	'echelle' => 10,
	'projet' => array(
		'nom' => "",
		'commune' => "",
		'contact' => "",
		'commercial' => "",
	),
);

$nom_projet = $dico->t('Tribune')." (nouveau projet)";
if (isset($id)) {
	$projet_values = $projet->load($id);
}
$form->default_values = $projet_values;

if ($projet_values['projet']['nom']) {
	$nom_projet = $projet_values['projet']['nom'];
}

$titre_page = $nom_projet;

$form_start = $form->form_start();

$buttons['normal'] = $form->input(array('type' => "submit", 'name' => "normal", 'value' => "Mode normal"));
$buttons['expert'] = $form->input(array('type' => "submit", 'name' => "expert", 'value' => "Mode expert"));
$buttons['calculate'] = $form->input(array('type' => "submit", 'name' => "calculate", 'value' => $dico->t('Calculer')));
$buttons['draw'] = $form->input(array('type' => "submit", 'name' => "draw", 'value' => $dico->t('Dessiner')));
$buttons['print'] = $form->input(array('type' => "submit", 'name' => "print", 'value' => "Imprimer"));
$buttons['new'] = $page->l("Nouveau", $url->make("current", array('action' => "", 'id' => "")));
$buttons['register'] = $form->input(array('type' => "submit", 'name' => "register", 'value' => $dico->t('Sauvegarder')));
$buttons['select'] = $form->input(array('type' => "submit", 'name' => "select", 'value' => $dico->t('Charger')));

$types_de_sieges = array(
	'banc' => "Banc",
	'banquette' => "Banquette",
	'coque_dossier' => "Coque avec dossier",
	'coque' => "Coque sans dossier",
	'fauteuil_fond' => "Fauteuil fond de gradin",
	'fauteuil_nez' => "Fauteuil nez de gradin",
	'fauteuil_sur' => "Fauteuil sur gradin",
);

if (isset($tribune)) {
	$txt_largeur = ($tribune->params['largeur'] / 10.0)." cm";
	$txt_hauteur = ($tribune->hauteur_reelle() / 10.0)." cm";
	$txt_profondeur = ($tribune->profondeur_reelle() / 10.0)." cm";
	$txt_gradin_hauteur = ($tribune->params['gradin_hauteur'] / 10.0)." cm";
	$txt_gradin_profondeur = ($tribune->params['gradin_profondeur'] / 10.0)." cm";
	$txt_siege_largeur = ($tribune->params['siege_largeur'] / 10.0)." cm";
	$txt_siege_hauteur = ($tribune->params['siege_hauteur'] / 10.0)." cm";
	$txt_siege_profondeur = ($tribune->params['siege_profondeur'] / 10.0)." cm";
}

switch ($action) {
	case "calculate" :
		$main = <<<HTML
<table>
<tr><td>Nombre de places :</td><td>{$tribune->nb_sieges()}</td></tr>
<tr><td>Nombre de gradins :</td><td>{$tribune->nb_gradins()}</td></tr>
<tr><td>Nombre de dégagements :</td><td>{$tribune->nb_degagements()}</td></tr>
<tr><td>Largeur de la tribune :</td><td>{$txt_largeur}</td></tr>
<tr><td>Hauteur de la tribune :</td><td>{$txt_hauteur}</td></tr>
<tr><td>Profondeur de la tribune :</td><td>{$txt_profondeur}</td></tr>
<tr><td>Hauteur d'un gradin :</td><td>{$txt_gradin_hauteur}</td></tr>
<tr><td>Profondeur d'un gradin :</td><td>{$txt_gradin_profondeur}</td></tr>
<tr><td>Largeur d'un siege :</td><td>{$txt_siege_largeur}</td></tr>
<tr><td>Hauteur d'un siege :</td><td>{$txt_siege_hauteur}</td></tr>
<tr><td>Profondeur d'un siege :</td><td>{$txt_siege_profondeur}</td></tr>
</table>
HTML;
		break;
	case "draw" :
		$main = <<<HTML
<h2>Vue de dessus</h2>
<img alt="vue de dessus" src="{$url->make("TribuneImage")}?{$query_string}" />
<h2>Vue de côté</h2>
<img alt="vue de côté" src="{$url->make("TribuneImageCote")}?{$query_string}" />
HTML;
		break;
	case "print" :
		$main = <<<HTML
<table>
<tr><td>Projet :</td><td>{$projet_values['projet']['nom']}</td></tr>
<tr><td>Commune :</td><td>{$projet_values['projet']['commune']}</td></tr>
<tr><td>Contact client :</td><td>{$projet_values['projet']['contact']}</td></tr>
<tr><td>Contact commercial :</td><td>{$projet_values['projet']['commercial']}</td></tr>

<tr><td>Nombre de places :</td><td>{$tribune->nb_sieges()}</td></tr>
<tr><td>Nombre de gradins :</td><td>{$tribune->nb_gradins()}</td></tr>
<tr><td>Nombre de dégagements :</td><td>{$tribune->nb_degagements()}</td></tr>
<tr><td>Largeur de la tribune :</td><td>{$txt_largeur}</td></tr>
<tr><td>Hauteur de la tribune :</td><td>{$txt_hauteur}</td></tr>
<tr><td>Profondeur de la tribune :</td><td>{$txt_profondeur}</td></tr>
<tr><td>Hauteur d'un gradin :</td><td>{$txt_gradin_hauteur}</td></tr>
<tr><td>Profondeur d'un gradin :</td><td>{$txt_gradin_profondeur}</td></tr>
<tr><td>Largeur d'un siege :</td><td>{$txt_siege_largeur}</td></tr>
<tr><td>Hauteur d'un siege :</td><td>{$txt_siege_hauteur}</td></tr>
<tr><td>Profondeur d'un siege :</td><td>{$txt_siege_profondeur}</td></tr>
</table>
<h2 class="new-page">Vue de dessus</h2>
<img alt="vue de dessus" src="{$url->make("TribuneImage")}?{$query_string}" />
<h2 class="new-page">Vue de côté</h2>
<img alt="vue de côté" src="{$url->make("TribuneImageCote")}?{$query_string}" />
HTML;
		break;
	case "expert" :
		$main = <<<HTML
{$form->fieldset_start(array('legend' => "Dimensions de la salle"))}
{$form->input(array('type' => "text", 'name' => "tribune[largeur]", 'label' => "Largeur (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[profondeur]", 'label' => "Profondeur (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[plafond]", 'label' => "Plafond (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[plafond_marge]", 'label' => "Marge pour le plafond (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[rangement_hauteur]", 'label' => "Hauteur du rangement (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[rangement_marge]", 'label' => "Hauteur de la marge pour le rangement (en m)"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => "Dimensions des gradins"))}
{$form->input(array('type' => "text", 'name' => "tribune[gradin_hauteur]", 'label' => "Hauteur d'un gradin (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[gradin_profondeur]", 'label' => "Profondeur d'un gradin (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[gradin_deporte_profondeur]", 'label' => "Profondeur du rang déporté (en m)"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => "Caractéristique des sièges"))}
{$form->select(array('name' => "tribune[siege_type]", 'label' => "Type de siège", 'options' => $types_de_sieges))}
{$form->input(array('type' => "text", 'name' => "tribune[siege_largeur]", 'label' => "Largeur d'un siège (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[siege_profondeur]", 'label' => "Profondeur d'un siège (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[siege_hauteur]", 'label' => "Hauteur d'un siège (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[siege_epaisseur]", 'label' => "Épaisseur d'un siège replié (en m)"))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => "Paramètres"))}
{$form->input(array('type' => "text", 'name' => "echelle", 'label' => "1 pixel représente (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[sieges_par_bloc]", 'label' => "Nb de sièges par bloc"))}
{$form->select(array('name' => "tribune[emplacement]", 'label' => "Emplacement", 'options' => array('interieur' => "Intérieur", 'exterieur' => "Extérieur")))}
{$form->select(array('name' => "tribune[type]", 'label' => "Type de tribune", 'options' => array('fixe' => "fixe", 'demontable' => "démontable", 'telescopique' => "téléscopique")))}
{$form->input(array('type' => "text", 'name' => "tribune[pied_arriere]", 'label' => "Débordement du pied arrière"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[garde_corps_gauche]", 'label' => "Garde corps à gauche"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[garde_corps_droite]", 'label' => "Garde corps à droite"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[garde_corps_arriere]", 'label' => "Garde corps arriere"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[garde_corps_telescopique]", 'label' => "Garde corps téléscopique"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[bardage_gauche]", 'label' => "Bardage à gauche"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[bardage_droite]", 'label' => "Bardage à droite"))}
{$form->input(array('type' => "checkbox", 'name' => "tribune[bardage_telescopique]", 'label' => "Bardage téléscopique"))}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "projet[id]"))}
{$form->input(array('type' => "hidden", 'name' => "projet[nom]", 'label' => "Nom du projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[commune]", 'label' => "Nom de la commune"))}
{$form->input(array('type' => "hidden", 'name' => "projet[contact]", 'label' => "Nom du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[email]", 'label' => "Email du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[telephone]", 'label' => "Téléphone du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[commercial]", 'label' => "Nom du commercial"))}
HTML;
		break;
	case "register" :
		$main = <<<HTML
{$form->input(array('type' => "hidden", 'name' => "projet[id]"))}

{$form->fieldset_start(array('legend' => "Informations du projet"))}
{$form->input(array('type' => "text", 'name' => "projet[nom]", 'label' => "Nom du projet"))}
{$form->input(array('type' => "text", 'name' => "projet[commune]", 'label' => "Nom de la commune"))}
{$form->input(array('type' => "text", 'name' => "projet[contact]", 'label' => "Nom du contact projet"))}
{$form->input(array('type' => "text", 'name' => "projet[email]", 'label' => "Email du contact projet"))}
{$form->input(array('type' => "text", 'name' => "projet[telephone]", 'label' => "Téléphone du contact projet"))}
{$form->input(array('type' => "text", 'name' => "projet[commercial]", 'label' => "Nom du commercial"))}
{$form->input(array('type' => "submit", 'name' => "save", 'value' => 'Valider'))}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "tribune[salle]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[type]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_type]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[largeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[plafond]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[plafond_marge]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[rangement_hauteur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[rangement_marge]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_hauteur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_deporte_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_largeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_hauteur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_epaisseur]"))}
{$form->input(array('type' => "hidden", 'name' => "echelle"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[pied_arriere]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[sieges_par_bloc]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[emplacement]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_gauche]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_droite]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_arriere]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_telescopique]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_gauche]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_droite]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_telescopique]"))}
HTML;
		break;
	case "select" :
	case "filter" :
		$pager = new Pager($sql, array(20, 30, 50, 100, 200));
		$filter = new Filter($pager, array(
			'id' => array(
				'title' => 'ID',
				'type' => 'between',
				'order' => 'DESC',
			),
			'nom' => array(
				'title' => $dico->t('Nom'),
				'type' => 'contain',
			),
			'contact' => array(
				'title' => $dico->t('Contact'),
				'type' => 'contain',
			),
			'commercial' => array(
				'title' => $dico->t('Commercial'),
				'type' => 'contain',
			),
		), array(), "filter_projets");
		$projet->liste($filter);
		$main = <<<HTML
{$page->inc("snippets/filter")}	
HTML;
		break;
	default :
		$main = <<<HTML
{$form->fieldset_start(array('legend' => "Configuration"))}
{$form->select(array('name' => "tribune[salle]", 'label' => "Type de salle", 'options' => array('spectacle' => "Spectacle", 'sports' => "Sports", 'pleinair' => "Plein air")))}
{$form->select(array('name' => "tribune[type]", 'label' => "Type de tribune", 'options' => array('fixe' => "fixe", 'demontable' => "démontable", 'telescopique' => "téléscopique")))}
{$form->select(array('name' => "tribune[siege_type]", 'label' => "Type de siège", 'options' => $types_de_sieges))}
{$form->fieldset_end()}

{$form->fieldset_start(array('legend' => "Dimensions de la salle"))}
{$form->input(array('type' => "text", 'name' => "tribune[largeur]", 'label' => "Largeur (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[profondeur]", 'label' => "Profondeur (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[plafond]", 'label' => "Plafond (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[plafond_marge]", 'label' => "Marge pour le plafond (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[rangement_hauteur]", 'label' => "Hauteur du rangement (en m)"))}
{$form->input(array('type' => "text", 'name' => "tribune[rangement_marge]", 'label' => "Hauteur de la marge pour le rangement (en m)"))}
{$form->fieldset_end()}

{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_hauteur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[gradin_deporte_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_largeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_profondeur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_hauteur]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[siege_epaisseur]"))}
{$form->input(array('type' => "hidden", 'name' => "echelle"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[pied_arriere]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[sieges_par_bloc]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[emplacement]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_gauche]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_droite]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_arriere]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[garde_corps_telescopique]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_gauche]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_droite]"))}
{$form->input(array('type' => "hidden", 'name' => "tribune[bardage_telescopique]"))}

{$form->input(array('type' => "hidden", 'name' => "projet[id]"))}
{$form->input(array('type' => "hidden", 'name' => "projet[nom]", 'label' => "Nom du projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[commune]", 'label' => "Nom de la commune"))}
{$form->input(array('type' => "hidden", 'name' => "projet[contact]", 'label' => "Nom du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[email]", 'label' => "Email du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[telephone]", 'label' => "Téléphone du contact projet"))}
{$form->input(array('type' => "hidden", 'name' => "projet[commercial]", 'label' => "Nom du commercial"))}
HTML;
		break;
}

$form_end = $form->form_end();

