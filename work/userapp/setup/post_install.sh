#!/bin/bash

BAF_BASE_DIR=/apps/beroPBX
BAF_CONF_DIR=${BAF_BASE_DIR}/etc
BAF_LIBS_DIR=${BAF_BASE_DIR}/lib

AST_CONF_DIR=${BAF_CONF_DIR}/asterisk

if [ ! -d ${AST_CONF_DIR} ]; then
	mkdir -p ${AST_CONF_DIR}
fi

cp ${BAF_BASE_DIR}/setup/manager.conf ${AST_CONF_DIR}

if [ ! -d ${BAF_LIBS_DIR} ]; then
	mkdir -p ${BAF_LIBS_DIR}
fi

if [ ! -d ${BAF_CONF_DIR}/settings/default ]; then
	mkdir -p ${BAF_CONF_DIR}/settings/default
fi

cp ${BAF_BASE_DIR}/setup/snom_default.xml ${BAF_CONF_DIR}/settings/default/snom.xml

if [ -z "$(grep multicast_addresses /usr/conf/isgw.conf)" ]; then
	echo "multicast_addresses=224.0.1.75:5060" >> /usr/conf/isgw.conf
elif [ -z "$(grep 224.0.1.75:5060 /usr/conf/isgw.conf)" ]; then
	cp /usr/conf/isgw.conf /usr/conf/isgw.conf.tmp
	sed 's/multicast_addresses=/multicast_addresses=224.0.1.75:5060,/' /usr/conf/isgw.conf.tmp > /usr/conf/isgw.conf
	rm /usr/conf/isgw.conf.tmp
fi
