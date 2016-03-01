<?php

function print_checkbox_tree($tree, $form, $checked = array(), $name = "tree") {
	$html = '<ul class="'.$name.'">';
	foreach ($tree as $element) {
		$html .= '<li class="'.$name.'">';
		$html .= $form->input(array(
			'type' => "checkbox",
			'name' => "{$name}[{$element['id']}]",
			'id' => "$name-{$element['id']}",
			'label' => $element['nom'],
			'template' => "#{field}#{label}",
			'value' => 1,
			'checked' => in_array($element['id'], $checked),
		));
		if (isset($element['children']) and count($element['children'])) {
			$html .= print_checkbox_tree($element['children'], $form, $checked, $name);
		}
		$html .= '</li>';
	}
	$html .= '</ul>';
	return $html;
}

function options_select_tree($tree, $form = null, $name = "tree", $options = array(0 => ""), $i = 0) {
	foreach ($tree as $element) {
		$item = "";
		for ($j = 0; $j < $i; $j++) {
			$item .= "--";
		}
		$item .= $element['nom'];
		$options[$element['id']] = $item;
		if (isset($element['children']) and count($element['children'])) {
			$options = options_select_tree($element['children'], $form,  $name, $options, $i + 1);
		}
	}
	return $options;
}

function print_link_tree($tree, $url, $name = "tree") {
	$links = "<ul class='$name'>";
	foreach ($tree as $element) {
		$links .= "<li class='$name'>";
		if (isset($element['children']) and count($element['children'])) {
			$links .= '<span class="with-children">+</span>';
		}
		else {
			$links .= "<span>-</span>";
		}
		$nom = $element['nom'];
		if (isset($element['statut']) and !$element['statut']) {
			$nom = "<strike>$nom</strike>";
		}
		$links .= "<a href='$url/{$element['id']}'>{$nom}</a>";

		if (isset($element['children']) and count($element['children'])) {
			$links .= print_link_tree($element['children'], $url,  $name);
		}
		$links .= "</li>";
	}
	$links .= "</ul>";

	return $links;
}

function print_callback_tree($tree, $callback, $name = "tree") {
	$links = "<ul class='$name'>";
	foreach ($tree as $element) {
		$links .= "<li class='$name'>";
		$links .= $callback($element);
		if (isset($element['children']) and count($element['children'])) {
			$links .= print_callback_tree($element['children'], $callback,  $name);
		}
		$links .= "</li>";
	}
	$links .= "</ul>";

	return $links;
}
