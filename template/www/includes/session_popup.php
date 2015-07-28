<?php

# BEGIN session management #
@session_start();
if (!isset($_SESSION['beroari_time'])) {
	echo	'<script>window.opener.location="/app/berogui/includes/login.php?userapp=OpenPBX"</script>' . "\n" .
		'<script>this.window.close();</script>' . "\n";
	exit();
} elseif ((isset($_SESSION['beroari_time'])) && (($_SESSION['beroari_time'] + 1200) < time())) {
	@session_unset();
	@session_destroy();
	echo	'<script>window.opener.location="/app/berogui/includes/login.php?reason=sess_expd&userapp=OpenPBX"</script>' . "\n" .
		'<script>this.window.close();</script>' . "\n";
	exit();
}


$_SESSION['beroari_time'] = time();
# END session management #

?>
