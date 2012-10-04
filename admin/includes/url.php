<?php
include "url.inc.php";

$url = new DefaultUrl();
$url->set_base($config->base_url());
$url->elements('langue', 'pays', 'page_id', 'page_keyword', 'action', 'id');

$url2 = new TypeUrl();
$url2->set_base($config->base_url());
$url2->elements('langue', 'pays', 'page_id', 'page_keyword', 'type', 'action', 'id');

$url3 = new FileUrl();
$url3->set_base($config->base_url());
$url3->elements('langue', 'pays', 'page_id', 'page_keyword', 'id', 'file');

$url4 = new NatureUrl();
$url4->set_base($config->base_url());
$url4->elements('langue', 'pays', 'page_id', 'page_keyword', 'type', 'action', 'nature', 'id');

$url5 = new FicheUrl();
$url5->set_base($config->base_url());
$url5->elements('langue', 'pays', 'page_id', 'page_keyword', 'id', 'fiche_id', 'file');

$url0 = new SimpleUrl();
$url0->set_base($config->base_url());
$url0->elements('langue', 'pays', 'page_id', 'page_keyword', 'id');
