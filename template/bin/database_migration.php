#!/usr/bin/php -q
<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');

$export_name = '/tmp/OpenPBX_migration.sql';
$export_name_full = '/tmp/OpenPBX_full.sql';


$ba = new beroAri();

switch ($argv[1]) {
case 'export':
	if (file_exists($export_name)) {
		unlink($export_name);
	}
	echo "[database_migration] Exporting OpenPBX-Database to " . $export_name . ": ";
	$ba->export_database($export_name, 'yes');
	echo "Done.\n";
	break;
case 'full_export':
	if (file_exists($export_name_full)) {
		unlink($export_name_full);
	}
	echo "[database_migration] Exporting OpenPBX-Database to " . $export_name_full . ": ";
	$ba->export_database($export_name_full, 'no');
	echo "Done.\n";
	break;
case 'import':
	if (file_exists($export_name)) {
		echo "[database_migration] Importing OpenPBX-Database from " . $export_name . ": ";
		$ba->import_database($export_name);
		if ($ba->is_error()) {
			echo $ba->error() . "\n\n";
		}
		unlink($export_name);
		echo "Done.\n";
	} else {
		echo "File " . $export_name . " does not exist!\n";
		exit(1);
	}
	break;
default:
	echo "Usage: " . $argv[0] . " [export | import]\n";
	exit(1);
	break;
}

?>
