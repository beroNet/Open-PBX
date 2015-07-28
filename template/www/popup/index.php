<?php

# includes

$newTarget = '/userapp/OpenPBX/index.php?m=' . $_GET['m'];
require_once(file_exists('/home/admin/lib/php/session_popup.php') ? '/home/admin/lib/php/session_popup.php' : '/apps/OpenPBX/www/includes/session_popup.php');
unset($newTarget);

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/beroGui.php');
include(BAF_APP_WWW . '/includes/lang_wrapper.php');

# check if module-name is valid, then include fitting module or exit
$mod_file = BAF_APP_WWW . '/popup/modules/' . $_GET['m'] . '.php';
if (!file_exists($mod_file)) {
	echo "<script>this.window.close();</script>\n";
	exit();
}

include($mod_file);

$lang = new lang();
$gui = new beroGui($lang);
$mod = new PopupModule($lang);

# display the gui
echo	$gui->popup_header($mod) .
	$mod->execute() .
	$mod->display() .
	$gui->popup_footer();

?>
