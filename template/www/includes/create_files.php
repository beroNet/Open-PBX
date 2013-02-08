<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/amifunc.php');
include(BAF_APP_WWW . '/includes/create_files_functions.php');
include(BAF_APP_WWW . '/includes/create_files_ext.php');
include(BAF_APP_WWW . '/includes/create_files_sip.php');
include(BAF_APP_WWW . '/includes/create_files_mailbox.php');

$ba = new beroAri();

$query = $ba->select("SELECT option FROM activate WHERE id = 'activate'");
$entry = $ba->fetch_array($query);

$ba->update("UPDATE activate SET option = '0' WHERE id = 'activate'");

if ($ba->is_error()) {
	echo	"<script>\n" .
		"alert('" . $ba->error() . "');\n" .
		"window.location.href='" . $redir_url . "';\n" .
		"</script>\n";
	exit();
}

// create files
$ami = new AsteriskManager();
$ami->connect();

create_ext();
create_ext_OpenPBX($ba, $ami);

create_sip($ba);
create_sip_OpenPBX($ba, $ami);

create_mailbox($ba);
create_mailbox_OpenPBX($ba, $ami);

$ami->Reload();
$ami->Logout();

unset($ami);

// apply changes
switch ($entry['option']) {
case 2:
case 3:
	exec('su admin /apps/asterisk/init/S01asterisk stop');
	exec('su admin /apps/asterisk/init/S01asterisk start');
	break;
}

header("Location:" . BAF_URL_BASE . "/index.php?m=" . $_GET['m']);

?>
