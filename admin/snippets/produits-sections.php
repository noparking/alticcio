<?php

global $sections, $section, $hidden;

echo '<ul class="produit-sections">';

foreach ($sections as $section_id => $label) {
	echo '<li class="produit-section-item';
	if ($section_id == $section) {
		echo " selected";
		$hidden[$section_id] = "";
	}
	else {
		$hidden[$section_id] = " hidden";
	}
	echo '" id="produit-section-'.$section_id.'-item">'.$label.'</li>';
}

echo '</ul>';
