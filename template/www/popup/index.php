<?php

$app_name = 'OpenPBX';

# BEGIN session management #
$redir_login = '/app/berogui/includes/login.php';

@session_start();
if (!isset($_SESSION['beroari_time'])) {
	echo	"<script>window.opener.location=\"" . $redir_login . "?userapp=" . $app_name . "\"</script>\n" .
		"<script>this.window.close();</script>\n";
	exit();
} elseif ((isset($_SESSION['beroari_time'])) && (($_SESSION['beroari_time'] + 1200) < time())) {
	@session_unset();
	@session_destroy();
	echo	"<script>window.opener.location=\"" . $redir_login . "?reason=sess_expd&userapp=" . $app_name . "\"</script>\n" .
		"<script>this.window.close();</script>\n";
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
