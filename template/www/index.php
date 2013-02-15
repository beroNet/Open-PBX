<?php

$app_name = 'OpenPBX';

# BEGIN session management #
$redir_login = '/app/berogui/includes/login.php';

@session_start();
if (!isset($_SESSION['beroari_time'])) {
	header('Location:' . $redir_login . '?userapp=' . $app_name);
	exit();
} elseif ((isset($_SESSION['beroari_time'])) && (($_SESSION['beroari_time'] + 1200) < time())) {
	@session_unset();
	@session_destroy();
	header('Location:' . $redir_login . '?reason=sess_expd&userapp=' . $app_name);
	exit();
}

unset($redir_login);

$_SESSION['beroari_time'] = time();

# END session management #

# main code
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
echo	$gui->main_header($app_name, $mod) .
	$mod->execute() .
	$mod->display() .
	$gui->main_footer();

?>
