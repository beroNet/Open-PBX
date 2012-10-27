# Makefile skeleton for baf modules

CUR_DIR		:= $(shell pwd)
BAF_DIR		:= /usr/local/share/baf

TOOLCHAIN	:= $(BAF_DIR)/toolchain/bin
TOOLPREFX	:= arm-linux-uclibc

TOOL_INC	:= $(BAF_DIR)/toolchain/usr/include
TOOL_LIB	:= $(BAF_DIR)/toolchain/lib
TOOL_USR_LIB	:= $(BAF_DIR)/toolchain/usr/lib

BFX_INC		:= $(BAF_DIR)/include
BFX_LIB		:= $(BAF_DIR)/lib

CFLAGS		:= "-I$(TOOL_INC) -I$(BFX_INC)"
LDFLAGS		:= "-L$(TOOL_LIB) -L$(TOOL_USR_LIB) -L$(BFX_LIB)"

DEST_ROOT	:= /apps/beroPBX
INST_PATH	:= $(CUR_DIR)

CONF_OPTS	:= --build=$(shell uname -m) --host=$(TOOLPREFX) --prefix=$(DEST_ROOT) --enable-ssl
MAKE_OPTS	:= -s
INST_OPTS	:= exec_prefix=$(INST_PATH) -s

INST_DIRS	:= bin conf include lib
REMV_DIRS	:= bin include lib

all:
	@for dir in $(INST_DIRS); do \
		mkdir -p $(CUR_DIR)/$$dir; \
	done
	@export PATH="$(TOOLCHAIN):$$PATH"; \
	export CC="$(TOOLPREFX)-gcc"; \
	export CPP="$(TOOLPREFX)-cpp"; \
	export CXX="$(TOOLPREFX)-g++"; \
	export CXXCPP="$(TOOLPREFX)-g++ -E"; \
	export AR="$(TOOLPREFX)-ar"; \
	export LD="$(TOOLPREFX)-ld"; \
	export NM="$(TOOLPREFX)-nm"; \
	export RANLIB="$(TOOLPREFX)-ranlib"; \
	export STRIP="$(TOOLPREFX)-strip"; \
	export CFLAGS=$(CFLAGS); \
	export LDFLAGS=$(LDFLAGS); \
	for i in `/bin/ls $(CUR_DIR)/src`; do \
		cd $(CUR_DIR)/src/$$i/; \
		if [ -x configure ]; then \
			./configure $(CONF_OPTS); \
		fi; \
		make $(MAKE_OPTS); \
	done;

install:
	@for i in `/bin/ls $(CUR_DIR)/src/`; do \
		cd $(CUR_DIR)/src/$$i/; \
		make install $(INST_OPTS); \
	done;

clean:
	@for dir in $(REMV_DIRS); do \
		mkdir -p $(CUR_DIR)/$$dir; \
	done;
	@for i in `/bin/ls $(CUR_DIR)/src`; do \
		cd $(CUR_DIR)/src/$$i/; \
		make clean $(MAKE_OPTS); \
	done;

