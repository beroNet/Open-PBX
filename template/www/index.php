<?php

# includes
require_once(file_exists('/home/admin/lib/php/session.php') ? '/home/admin/lib/php/session.php' : '/apps/OpenPBX/www/includes/session.php');
include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/beroGui.php');
include(BAF_APP_WWW . '/includes/lang_wrapper.php');

# check if module-name is valid, then include fitting module or fallback
$mod_file = BAF_APP_WWW . '/modules/' . (isset($_GET['m']) ? $_GET['m'] : 'management_state') . '.php';
if (!file_exists($mod_file)) {
	$mod_file = BAF_APP_WWW . '/modules/management_state.php';
}
include($mod_file);

$lang	= new lang();
$gui	= new beroGui($lang);
$mod	= new MainModule($lang);

# display the gui
echo	$gui->main_header('OpenPBX', $mod) .
	$mod->execute() .
	$mod->display() .
	$gui->main_footer();

?>
