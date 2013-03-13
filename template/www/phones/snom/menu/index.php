<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/phones/snom/menu/includes/xml_browser_func.php');
include(BAF_APP_WWW . '/phones/snom/menu/includes/module_func.php');

ob_start();

# connect to db
#
$ba = new beroAri();


# get user_id
#
$user_id = (int)$ba->fetch_single($ba->query('SELECT u.id FROM sip_users AS u, phone_devices AS d WHERE u.id = d.userid AND d.ipaddr = \''. $_SERVER['REMOTE_ADDR'] .'\''));
// TODO: for test with Mozilla Firefox
// $user_id = (int)$ba->fetch_single($ba->query('SELECT u.id FROM sip_users AS u, phone_devices AS d WHERE u.id = d.userid AND d.ipaddr = \'10.253.1.240\''));


# get and set user language
#
$user_lang = $ba->fetch_single($ba->query('SELECT language FROM sip_users WHERE id = '. $user_id));
include(BAF_APP_WWW . '/includes/lang/' . $user_lang . '.php');
unset($user_lang);
$lang = new lang();


# load module file
#
$mod_file = BAF_APP_WWW . '/phones/snom/menu/modules/' . (isset($_GET['m']) ? $_GET['m'] : 'main') . '.php';
if (!file_exists($mod_file)) {
	$mod_file = BAF_APP_WWW . '/phones/snom/menu/modules/main.php';
}
include($mod_file);


# create, generate and display module
#
$mod = new snomModule($ba, $user_id, $lang);
$mod->generate();
$mod->display();
unset ($mod);

ob_end_flush();

?>
