CFLAGS  += -std=gnu99 -Wall -Werror
LDFLAGS += -lcurl -lregexp -lsqlite

BIN     := snom_pnp_daemon
OBJ	:= $(BIN).o

%.o: %.c
	$(CC) $(CFLAGS) -c -o $@ $<

$(BIN): $(OBJ)
	$(CC) -o $@ $(OBJ) $(LDFLAGS)

all: $(BIN)

clean:
	rm -rf $(OBJ)
	rm -rf $(BIN)

.PHONY: all
