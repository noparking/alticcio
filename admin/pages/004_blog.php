<?php
$menu->current('main/content');

$titre_page = "Blogs";

if ($type = $url2->get("type")) {
	require include_path($page->part($type));
}
else {
	require include_path($page->part("blogs"));
}
