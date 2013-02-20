#!/bin/bash

BAF_BASE_DIR=/apps/OpenPBX
BAF_CONF_DIR=${BAF_BASE_DIR}/etc
BAF_LIBS_DIR=${BAF_BASE_DIR}/lib
BAF_EXEC_DIR=${BAF_BASE_DIR}/bin

AST_CONF_DIR=${BAF_CONF_DIR}/asterisk

${BAF_EXEC_DIR}/database_migration.sh export

