<?php
$menu->current('main/contacts');

$titre_page = $dico->t('Contacts');

$main = $dico->t('GestionContacts');

$buttons = 	array();

if ($type = $url2->get("type")) {
	require include_path($page->part($type));
}

