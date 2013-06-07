<?php

global $sql, $page, $attributs, $attribut, $attribut_id, $phrase, $form, $config, $displayed_lang, $id_langues;


$attribut_management = new AttributManagement($sql, null, $phrase, $id_langues);
$attribut = new Attribut($sql, $phrase, $id_langues);
$groupes = array();

foreach ($attribut_management->groupes() as $id_groupe => $nom_groupe) {
	$groupes[$nom_groupe] = "";
	foreach ($attributs as $groupe => $grouped_attributs) {
		foreach ($grouped_attributs as $attribut_id => $dontcare) {
			if ($groupe == $id_groupe) {
				$groupes[$nom_groupe] .= $page->inc("snippets/attribut");		
			}
		}
	}
}

foreach ($attributs as $groupe => $grouped_attributs) {
	foreach ($grouped_attributs as $attribut_id => $dontcare) {
		if ($groupe == "") {
			echo $page->inc("snippets/attribut");		
		}
	}
}

foreach ($groupes as $nom_groupe => $groupe) {
	if ($groupe) {
		echo $form->fieldset_start(array('legend' => $nom_groupe));
		echo $groupe;
		echo $form->fieldset_end();
	}
}

