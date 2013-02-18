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
$lang_set = 0;
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $user_lang) {
		foreach ($lang_codes as $lang_code) {
			if (strtolower($lang_code) == strtolower($user_lang)) {
				$lang_file = $lang_code . '.php';
				$lang_set = 1;
				break;
			}
		}

		if ($lang_set == 1) {
			break;
		}
	}
}

# include language-file for language selected
include(BAF_APP_WWW . '/includes/lang/' . $lang_file);
?>
