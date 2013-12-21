#!/bin/bash

BAF_BASE_DIR=/apps/OpenPBX
BAF_CONF_DIR=${BAF_BASE_DIR}/etc
BAF_LIBS_DIR=${BAF_BASE_DIR}/lib
BAF_EXEC_DIR=${BAF_BASE_DIR}/bin

AST_CONF_DIR=${BAF_CONF_DIR}/asterisk

if [ ! -d ${AST_CONF_DIR} ]; then
	mkdir -p ${AST_CONF_DIR}
fi

cp ${BAF_BASE_DIR}/setup/extensions.ael ${AST_CONF_DIR}
cp ${BAF_BASE_DIR}/setup/extensions.conf ${AST_CONF_DIR}
cp ${BAF_BASE_DIR}/setup/manager.conf ${AST_CONF_DIR}
cp ${BAF_BASE_DIR}/setup/minivm.conf ${AST_CONF_DIR}
cp ${BAF_BASE_DIR}/setup/sip.conf ${AST_CONF_DIR}

if [ ! -d ${BAF_LIBS_DIR} ]; then
	mkdir -p ${BAF_LIBS_DIR}
fi

if [ ! -d ${BAF_CONF_DIR}/settings/default ]; then
	mkdir -p ${BAF_CONF_DIR}/settings/default
fi

# import changes to database from previous installations
if [ -f /tmp/OpenPBX_migration.sql ]; then
	${BAF_EXEC_DIR}/database_migration.sh import
fi

# create dynamic asterisk conf files
/usr/bin/env -i bash -c "/usr/bin/php -q /apps/OpenPBX/www/includes/create_files.php"

# reset default template for SNOM provisioning
cp ${BAF_BASE_DIR}/setup/snom_default.xml ${BAF_CONF_DIR}/settings/default/snom.xml

