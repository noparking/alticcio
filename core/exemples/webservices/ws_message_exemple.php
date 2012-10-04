<?php

include "../../outils/mysql.php";
include "../../outils/xml.php";
include "../../outils/message.php";
include "../../webservices/ws_message.php";

$sql = new MySql(array(
	'database' => "doublet_prod",
));

$ws_message = new WSMessage($sql);
$ws_message->get_messages("1250764690", "1250765221");

?>