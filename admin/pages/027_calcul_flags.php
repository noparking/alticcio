<?php
/*
 * Configuration
 */
$config->core_include("outils/mysql", "outils/langue", "outils/form");
$config->core_include("flags/vents");
$dirname = dirname(__FILE__).'/../traductions/';
$page->css[] = $config->media("flags.css");
$main_lg = 'fr_FR';

$menu->current('main/tools/flags');

/*
 * On initialise les classes et on créé le formulaire de recherche
 */
$id_langue = 1;
$sql = new Mysql($config->db());
$vents = new Vents($sql, $id_langue);
$form1 = new Form(array(
	'id' => "form-vent",
	'class' => "form-vent",
));
$form2 = new Form(array(
	'id' => "form-base",
	'class' => "form-base",
));

/*
 * On liste les valeurs présentes dans les menus déroulants
 */
$liste_zones = $vents->liste_zones(77);
$liste_forces = $vents->liste_force_vents('max');
$liste_eventflags = $vents->liste_event_flags();
$liste_bases = array("...");
$group_bases = array();

/*
 * On liste les bases
 */
$q_bases = "SELECT p.id, ph.phrase, a1.valeur_numerique AS longueur, a2.valeur_numerique AS poids, a3.valeur_numerique AS option_calcul
				FROM dt_produits AS p 
				INNER JOIN dt_phrases AS ph
				ON ph.id = p.phrase_nom AND ph.id_langues =".$id_langue." 
				LEFT JOIN dt_produits_attributs AS a1 
				ON a1.id_produits = p.id AND a1.id_attributs = 5
				LEFT JOIN dt_produits_attributs AS a2 
				ON a2.id_produits = p.id AND a2.id_attributs = 95 
				LEFT JOIN dt_produits_attributs AS a3 
				ON a3.id_produits = p.id AND a3.id_attributs = 216
				WHERE id_applications = 59";
				
$rs_bases = $sql->query($q_bases);
while ($row_bases = $sql->fetch($rs_bases)) {
	if ($row_bases['option_calcul'] == 1) {
		$liste_bases[$row_bases['id']] = $row_bases['phrase'].' ('.$row_bases['id'].')';
		$group_bases[$row_bases['id']] = array("poids"=>$row_bases['poids'], "longueur"=>$row_bases['longueur']);
	}
}




/*
 * Si le formulaire de recherche est renseigné
 * On fait un calcul
 */
$html = "";
if ($form1->is_submitted()) {
	$data = $form1->escape_values();
	$liste_diametres = array(40, 50, 60, 70, 75, 80, 90, 100, 120);
	$html = '<table id="resistance_flags" summary="">';
	$html .= '<tr>';
	$html .= '<th>'.$dico->t('DiametreBase').' (cm)</th>';
	$html .= '<th>'.$dico->t('PoidsNecessaire').' (kg)</th>';
	$html .= '<th>'.$dico->t('Surface').' (m²)</th>';
	$html .= '<th>'.$dico->t('EffortPavillon').'</th>';
	$html .= '<th>'.$dico->t('MomentPied').'</th>';
	$html .= '</tr>';
	foreach($liste_diametres as $diametre) {
		$resultat = $vents->calculer_mobile($data['zone_vents'], $data['eventflags'], $diametre,  0);
		$html .= '<tr>';
		$html .= '<td class="aligner_centre"><strong>'.$diametre.' cm</strong></td>';
		$html .= '<td class="aligner_centre"><strong>'.$resultat.' kg</strong></td>';
		$html .= '<td class="aligner_centre">'.$vents->surface.' m²</td>';
		$html .= '<td class="aligner_centre">'.$vents->effort_pav_reel.'</td>';
		$html .= '<td class="aligner_centre">'.$vents->moment_pied.'</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
}
else {
	$data['zone_vents'] = "";
	$data['eventflags'] = "";
	$data['base'] = "";
	$data['hauteurmat'] = "";
}


if ($form2->is_submitted()) {
	$data = $form2->escape_values();
	$html .= '<p>'.$dico->t('LegendeCalculVent').'</p>';
	$html .= '<table id="resistance_flags" summary="">';
	$html .= '<tr>';
	$html .= '<th></th>';
	foreach($liste_forces as $key => $force) {
		$html .= '<th>'.$force.'</th>';
	}
	$html .= '</tr>';
	
	$tab_bases = array();
	if (!empty($data['base'])) {
		$tab_bases[] = $data['base'];
	}
	else {
		foreach($liste_bases as $b => $val_bases) {
			$tab_bases[] = $b;
		}
	}
	foreach($tab_bases as $base) {
		$base_selected = $group_bases[$base];
		$nom_base = $liste_bases[$base];
		if ($nom_base != "...") {
			$html .= '<tr>';
			$html .= '<td>'.$nom_base.' ('.$base_selected['poids'].' kg)</td>';
			foreach($liste_forces as $key => $force) {
					$poids_necessaire = $vents->calculer_mobile($key, $data['eventflags'], $base_selected['longueur'], 0);
					if ($poids_necessaire <= $base_selected['poids']) {
						$html .= '<td style="background-color:green;color:white;">'.$poids_necessaire.' kg</td>';
					}
					else {
						$html .= '<td style="background-color:red;color:white;">'.$poids_necessaire.' kg</td>';
					}
				}
			$html .= '</tr>';
		}
	}
	$html .= '</table>';
}





/*
 * Affichage
 */
$titre_page = $dico->t('CalculPoidsEvent');
$left = <<<HTML
<div class="aside_flags">
	<ul>
		<li><a href="{$url->make("CalculFlags")}/base">{$dico->t('ApartirBase')}</a></li>
		<li><a href="{$url->make("CalculFlags")}/vent">{$dico->t('ApartirVent')}</a></li>
	</ul>
</div>
HTML;

$main = '<div class="contenu_flags">';
if ($url->get("action") == "base") {
	$form_start = $form2->form_start();
	$template_inline = <<<HTML
<p>#{label} : #{field} #{description}</p>
HTML;
	$main .= <<<HTML
{$form2->fieldset_start(array('legend' => $dico->t('CalculApartirBase'), 'class' => "", 'id' => ""))}
{$form2->select(array('name' => 'eventflags', 'label' => $dico->t("FormeEventFlags"), 'id' => 'eventflags', 'options' => $liste_eventflags, 'value' => $data['eventflags'], 'template' => $template_inline))}
{$form2->select(array('name' => 'base', 'label' => $dico->t("BaseCatalogue"), 'id' => 'base', 'options' => $liste_bases, 'value' => $data['base'], 'template' => $template_inline))}
{$form2->input(array('type' => "submit", 'name' => "calcul_vent", 'value' => $dico->t('Calculer')))}
{$form2->fieldset_end()}
$html
HTML;
	$form_end = $form2->form_end();
}
else if ($url->get("action") == "vent") {
	$form_start = $form1->form_start();
	$template_inline = <<<HTML
<p>#{label} : #{field} #{description}</p>
HTML;
	$main .= <<<HTML
{$form1->fieldset_start(array('legend' => $dico->t('CalculApartirVent'), 'class' => "", 'id' => ""))}
{$form1->select(array('name' => 'zone_vents', 'label' => $dico->t("ZoneVents"), 'id' => 'zone_vents', 'options' => $liste_zones, 'value' => $data['zone_vents'], 'template' => $template_inline))}
{$form1->select(array('name' => 'eventflags', 'label' => $dico->t("FormeEventFlags"), 'id' => 'eventflags', 'options' => $liste_eventflags, 'value' => $data['eventflags'], 'template' => $template_inline))}
{$form1->input(array('type' => "submit", 'name' => "calcul_vent", 'value' => $dico->t('Calculer')))}
{$form1->fieldset_end()}
$html
HTML;
	$form_end = $form1->form_end();
}
$main .= '</div>';




?>