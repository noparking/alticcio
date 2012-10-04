<?php
/*
 * Configuration
 */
$config->core_include("outils/mysql", "outils/langue", "outils/form");
$config->core_include("flags/vents");
$dirname = dirname(__FILE__).'/../traductions/';
$main_lg = 'fr_FR';

$menu->current('main/tools/flags');

/*
 * On initialise les classes et on créé le formulaire de recherche
 */
$id_langue = 1;
$sql = new Mysql($config->db());
$vents = new Vents($sql, $id_langue);
$form = new Form(array(
	'id' => "form-vent",
	'class' => "form-vent",
));


/*
 * On liste les valeurs présentes dans les menus déroulants
 */
$liste_zones = $vents->liste_zones(77);
$liste_mats = $vents->liste_hauteurs_mats();
$liste_pavillons = $vents->liste_pavillons();


/*
 * Si le formulaire de recherche est renseigné
 * On fait un calcul
 */
$html = "";
if ($form->is_submitted()) {
	$data = $form->escape_values();
	$html .= '<h3>'.$dico->t('HypotheseCalculs').'</h3>';
	
	$texte_resultats = array(	0 => $dico->t('MatDeconseille'), 
								1 => $dico->t('MatConseille'),
							);
	$texte_matiere = array(		"acier" => $dico->t('Acier'),
								"alu" => $dico->t('Alu'),
								"fibre" => $dico->t('Fibre'),
							);
	$texte_forme = array(	"conique" => $dico->t('Conique'),
							"cylindrique" => $dico->t('Cylindrique'),
							);
	// on récupère tous les id de mâts en fonction de la hauteur
	$q = "SELECT id FROM dt_mats WHERE hauteur = ".$data['mats'];
	$rs = $sql->query($q);
	while ($row = $sql->fetch($rs)) {
		$calcul = $vents->calculer_fixe($data['zone_vents'], $data['pavillons'], $row['id']);
		$html .= <<<HTML
<div class="" style="float:left;width:150px;margin:10px;">
	<p><strong>{$dico->t('Mat')} : {$vents->hauteur_mat}m {$texte_matiere[$vents->matiere_mat]} {$texte_forme[$vents->forme_mat]}</strong></p>
	<p><strong>{$dico->t('Pavillon')} : {$vents->largeur} x {$vents->longueur} m </strong></p>
	<p>{$dico->t('CoefficientAltitude')} : <br/>
		{$dico->t('Mat')} : {$vents->qh_mat} <br/>
		{$dico->t('Pavillon')} : {$vents->qh_pav} <br/>
	</p>
	<p>{$dico->t('CoefficientTraineeMat')} :  {$vents->coef_trainee_mat} </p> 
	<p>{$dico->t('EffortMat')} : {$vents->effort_mat} daN</p> 
	<p>{$dico->t('EffortPavillon')} : {$vents->effort_pav} daN</p>
	<p>{$dico->t('MomentPied')} : {$vents->moment_pied} mdaN</p>
	<p>{$dico->t('MomentInertieMini')} : {$vents->inertie} cm3</p>
	<p>{$dico->t('Resistance')} : {$vents->resistance}</p>
	<p>{$dico->t('Resultat')} : <strong>{$texte_resultats[$calcul]}</strong>
</div>
HTML;
	}
	$html .= '<div class="spacer"></div>';
}
else {
	$data['zone_vents'] = "";
	$data['mats'] = "";
	$data['pavillons'] = "";
}

/*
 * Affichage
 */
$titre_page = $dico->t('CalculResistanceVent');

$form_start = $form->form_start();
$template_inline = <<<HTML
<p>#{label} : #{field} #{description}</p>
HTML;
$main = <<<HTML
{$form->fieldset_start(array('legend' => $dico->t('Calcul'), 'class' => "", 'id' => ""))}
{$form->select(array('name' => 'zone_vents', 'label' => $dico->t("ZoneVents"), 'id' => 'zone_vents', 'options' => $liste_zones, 'value' => $data['zone_vents'], 'template' => $template_inline))}
{$form->select(array('name' => 'mats', 'label' => $dico->t("HauteurMats"), 'id' => 'mats', 'options' => $liste_mats, 'value' => $data['mats'], 'template' => $template_inline))}
{$form->select(array('name' => 'pavillons', 'label' => $dico->t("TaillesPavillons"), 'id' => 'pavillons', 'options' => $liste_pavillons, 'value' => $data['pavillons'], 'template' => $template_inline))}
{$form->input(array('type' => "submit", 'name' => "calcul_vent", 'value' => $dico->t('Calculer')))}
{$form->fieldset_end()}
$html
HTML;
$form_end = $form->form_end();



?>