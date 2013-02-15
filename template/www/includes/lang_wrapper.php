<?php

# get installed language-files
if ($handle = opendir(BAF_APP_WWW . '/includes/lang/')) {
	while (($file = readdir($handle)) !== false) {
		if (($file == '.') || ($file == '..')) {
			continue;
		}

		$lang_codes[] = str_ireplace('.php', '', $file);
	}
}

# get the language the user has configured
$lang_file = 'en.php';
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $user_lang) {
		if (in_array($user_lang, $lang_codes)) {
			$lang_file = $user_lang . '.php';
		}
	}
}

# include language-file for language selected
include(BAF_APP_WWW . '/includes/lang/' . $lang_file);
?>
