<?php
include "url.inc.php";

$url = new StandardUrl($url_redirection);
$url->set_base($config->base_url());
$url->elements('langue', 'pays', 'section', 'code_url');

$page_url = new PageUrl($url_redirection);
$page_url->set_base($config->base_url());
$page_url->elements('langue', 'pays', 'section', 'code_url', 'nb', 'page');

$ajax_url = new AjaxUrl();
$ajax_url->set_base($config->base_url());
$ajax_url->elements('langue', 'pays', 'format', 'page', 'id', 'extra');

$item_url = new ItemUrl();
$item_url->set_base($config->base_url());
$item_url->elements('langue', 'pays', 'item', 'id');

$payment_url = new PaymentUrl();
$payment_url->set_base($config->base_url());
$payment_url->elements('payment', 'type');

