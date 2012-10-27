<?php

function _create_dirs () {

	if (!file_exists(BAF_APP_AST_CFG)) {
		mkdir(BAF_APP_AST_CFG);
		chown(BAF_APP_AST_CFG, 'admin');
		chgrp(BAF_APP_AST_CFG, 'admin');
	}
}

function _save_conf ($fn, $fc) {

	if (($fp = @fopen($fn, 'w'))) {
		fputs($fp, $fc);
		fclose($fp);
		chown($fn, 'admin');
		chgrp($fn, 'admin');
	}
}

?>
